<?php
/**
 * Müraciət sorğuları (istifadəçi portalı üçün).
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Application_Queries {

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function get_applications_by_user_id( int $user_id ): array {
        global $wpdb;

        if ( $user_id <= 0 ) {
            return [];
        }

        $table = SIT_Application_Db::applications_table();

        // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC",
                $user_id
            ),
            ARRAY_A
        );
        // phpcs:enable

        return is_array( $rows ) ? $rows : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function get_application_for_user( int $application_id, int $user_id ): ?array {
        global $wpdb;

        if ( $application_id <= 0 || $user_id <= 0 ) {
            return null;
        }

        $table = SIT_Application_Db::applications_table();

        // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE id = %d AND user_id = %d LIMIT 1",
                $application_id,
                $user_id
            ),
            ARRAY_A
        );
        // phpcs:enable

        return is_array( $row ) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function get_documents_by_application_id( int $application_id ): array {
        global $wpdb;

        if ( $application_id <= 0 ) {
            return [];
        }

        $table = SIT_Application_Db::documents_table();

        // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, document_type, file_name, mime_type, file_size, created_at FROM {$table} WHERE application_id = %d ORDER BY id ASC",
                $application_id
            ),
            ARRAY_A
        );
        // phpcs:enable

        return is_array( $rows ) ? $rows : [];
    }
}
