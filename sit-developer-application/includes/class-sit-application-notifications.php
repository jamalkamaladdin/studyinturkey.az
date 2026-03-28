<?php
/**
 * E-poçt bildirişləri (admin və namizəd).
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Application_Notifications {

    public static function maybe_notify_admin_new( int $application_id ): void {
        if ( '1' !== get_option( 'sit_application_notify_admin_new', '1' ) ) {
            return;
        }

        $app = SIT_Application_Queries::get_application_by_id( $application_id );
        if ( ! $app ) {
            return;
        }

        $recipients = self::admin_recipients();
        if ( empty( $recipients ) ) {
            return;
        }

        $program_title = '';
        $pid           = isset( $app['program_id'] ) ? (int) $app['program_id'] : 0;
        if ( $pid ) {
            $program_title = get_the_title( $pid );
        }

        $subject = apply_filters(
            'sit_application_email_admin_new_subject',
            sprintf(
                /* translators: %d: application ID */
                __( '[StudyInTurkey] Yeni müraciət #%d', 'studyinturkey' ),
                $application_id
            ),
            $application_id,
            $app
        );

        $admin_url = admin_url( 'admin.php?page=sit-applications&action=view&sit_app_id=' . $application_id );

        $body_lines = [
            __( 'Yeni müraciət qəbul edildi.', 'studyinturkey' ),
            '',
            sprintf( /* translators: %d: ID */ __( 'Nömrə: %d', 'studyinturkey' ), $application_id ),
            sprintf( /* translators: %s: name */ __( 'Ad: %s', 'studyinturkey' ), isset( $app['applicant_name'] ) ? $app['applicant_name'] : '' ),
            sprintf( /* translators: %s: email */ __( 'E-poçt: %s', 'studyinturkey' ), isset( $app['applicant_email'] ) ? $app['applicant_email'] : '' ),
            sprintf( /* translators: %s: phone */ __( 'Telefon: %s', 'studyinturkey' ), isset( $app['applicant_phone'] ) ? $app['applicant_phone'] : '' ),
            sprintf( /* translators: %s: program */ __( 'Proqram: %s', 'studyinturkey' ), $program_title ? $program_title : '—' ),
            sprintf( /* translators: %s: status */ __( 'Status: %s', 'studyinturkey' ), self::status_label( isset( $app['status'] ) ? (string) $app['status'] : '' ) ),
            '',
            sprintf( /* translators: %s: URL */ __( 'İdarə paneli: %s', 'studyinturkey' ), $admin_url ),
        ];

        $body = apply_filters(
            'sit_application_email_admin_new_body',
            implode( "\n", $body_lines ),
            $application_id,
            $app
        );

        self::send_mail( $recipients, $subject, $body );
    }

    /**
     * @param array<string, mixed> $application_row
     */
    public static function maybe_notify_applicant_status( int $application_id, string $old_status, string $new_status, array $application_row ): void {
        if ( '1' !== get_option( 'sit_application_notify_applicant_status', '1' ) ) {
            return;
        }

        if ( $old_status === $new_status ) {
            return;
        }

        $to = isset( $application_row['applicant_email'] ) ? sanitize_email( (string) $application_row['applicant_email'] ) : '';
        if ( ! is_email( $to ) ) {
            return;
        }

        $subject = apply_filters(
            'sit_application_email_status_subject',
            sprintf(
                /* translators: %d: application ID */
                __( '[StudyInTurkey] Müraciətiniz #%d — status yeniləndi', 'studyinturkey' ),
                $application_id
            ),
            $application_id,
            $old_status,
            $new_status,
            $application_row
        );

        $body_lines = [
            __( 'Salam,', 'studyinturkey' ),
            '',
            sprintf(
                /* translators: %1$d: ID, %2$s: new status */
                __( 'Müraciətiniz #%1$d üçün status "%2$s" olaraq yeniləndi.', 'studyinturkey' ),
                $application_id,
                self::status_label( $new_status )
            ),
            '',
            sprintf( /* translators: %s: old status */ __( 'Əvvəlki status: %s', 'studyinturkey' ), self::status_label( $old_status ) ),
            '',
            __( 'Hörmətlə,', 'studyinturkey' ),
            wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
        ];

        $body = apply_filters(
            'sit_application_email_status_body',
            implode( "\n", $body_lines ),
            $application_id,
            $old_status,
            $new_status,
            $application_row
        );

        self::send_mail( [ $to ], $subject, $body );
    }

    /**
     * @return string[]
     */
    private static function admin_recipients(): array {
        $emails = [];

        $primary = sanitize_email( (string) get_option( 'admin_email' ) );
        if ( is_email( $primary ) ) {
            $emails[] = $primary;
        }

        $extra = (string) get_option( 'sit_application_notify_extra_emails', '' );
        if ( '' !== $extra ) {
            foreach ( array_map( 'trim', explode( ',', $extra ) ) as $one ) {
                $one = sanitize_email( $one );
                if ( is_email( $one ) ) {
                    $emails[] = $one;
                }
            }
        }

        $emails = array_values( array_unique( $emails ) );

        return apply_filters( 'sit_application_admin_notification_recipients', $emails );
    }

    /**
     * @param string[] $to
     */
    private static function send_mail( array $to, string $subject, string $body ): void {
        $to = array_filter( $to, 'is_email' );
        if ( empty( $to ) ) {
            return;
        }

        $headers = apply_filters( 'sit_application_mail_headers', [] );

        wp_mail( $to, wp_specialchars_decode( $subject, ENT_QUOTES ), $body, $headers );
    }

    private static function status_label( string $status ): string {
        if ( class_exists( 'SIT_Application_Account', false ) ) {
            return SIT_Application_Account::status_label( $status );
        }

        return $status;
    }
}
