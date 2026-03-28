<?php
/**
 * Form göndərmə, validasiya və fayl yükləmə.
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Application_Handler {

    public const NONCE_ACTION = 'sit_application_submit';
    public const NONCE_NAME   = 'sit_application_nonce';

    public static function register(): void {
        add_action( 'admin_post_nopriv_' . self::NONCE_ACTION, [ __CLASS__, 'handle_post' ] );
        add_action( 'admin_post_' . self::NONCE_ACTION, [ __CLASS__, 'handle_post' ] );
    }

    public static function handle_post(): void {
        $redirect = self::get_redirect_url();

        if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
            self::redirect_with_error( $redirect, [ __( 'Təhlükəsizlik yoxlaması uğursuz oldu. Zəhmət olmasa yenidən cəhd edin.', 'studyinturkey' ) ] );
        }

        $name    = isset( $_POST['sit_app_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sit_app_name'] ) ) : '';
        $email   = isset( $_POST['sit_app_email'] ) ? sanitize_email( wp_unslash( $_POST['sit_app_email'] ) ) : '';
        $phone   = isset( $_POST['sit_app_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['sit_app_phone'] ) ) : '';
        $program = isset( $_POST['sit_app_program_id'] ) ? absint( $_POST['sit_app_program_id'] ) : 0;
        $message = isset( $_POST['sit_app_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sit_app_message'] ) ) : '';

        $errors = [];

        if ( '' === $name || strlen( $name ) < 2 ) {
            $errors[] = __( 'Tam adınızı düzgün daxil edin.', 'studyinturkey' );
        }

        if ( ! is_email( $email ) ) {
            $errors[] = __( 'Etibarlı e-poçt ünvanı daxil edin.', 'studyinturkey' );
        }

        if ( '' === $phone || strlen( $phone ) < 5 ) {
            $errors[] = __( 'Telefon nömrəsini daxil edin.', 'studyinturkey' );
        }

        if ( $program <= 0 ) {
            $errors[] = __( 'Proqram seçin.', 'studyinturkey' );
        } elseif ( ! self::is_valid_program( $program ) ) {
            $errors[] = __( 'Seçilmiş proqram mövcud deyil.', 'studyinturkey' );
        }

        $file_errors = self::validate_files_array();
        if ( ! empty( $file_errors ) ) {
            $errors = array_merge( $errors, $file_errors );
        }

        if ( ! empty( $errors ) ) {
            self::redirect_with_error(
                $redirect,
                $errors,
                [
                    'sit_app_name'    => $name,
                    'sit_app_email'   => $email,
                    'sit_app_phone'   => $phone,
                    'sit_app_program_id' => $program,
                    'sit_app_message' => $message,
                ]
            );
        }

        global $wpdb;

        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

        $row = [
            'applicant_name'    => $name,
            'applicant_email'   => $email,
            'applicant_phone'   => $phone,
            'program_id'        => $program,
            'status'            => 'pending',
            'applicant_message' => $message ? $message : null,
            'ip_address'        => $ip ? $ip : null,
        ];
        $row_format = [ '%s', '%s', '%s', '%d', '%s', '%s', '%s' ];

        if ( is_user_logged_in() ) {
            $row['user_id'] = get_current_user_id();
            $row_format[]   = '%d';
        }

        // phpcs:disable WordPress.DB.DirectDatabaseQuery
        $inserted = $wpdb->insert(
            SIT_Application_Db::applications_table(),
            $row,
            $row_format
        );
        // phpcs:enable

        if ( false === $inserted ) {
            self::redirect_with_error( $redirect, [ __( 'Müraciət saxlanılmadı. Sonra yenidən cəhd edin.', 'studyinturkey' ) ] );
        }

        $application_id = (int) $wpdb->insert_id;

        $upload_result = self::save_application_files( $application_id );
        if ( is_wp_error( $upload_result ) ) {
            self::rollback_application( $application_id );
            self::redirect_with_error( $redirect, [ $upload_result->get_error_message() ] );
        }

        self::redirect_with_success( $redirect );
    }

    /**
     * @return string[] Xəta mesajları.
     */
    private static function validate_files_array(): array {
        if ( ! function_exists( 'wp_check_filetype_and_ext' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $errors  = [];
        $max     = (int) apply_filters( 'sit_application_max_upload_bytes', 5 * MB_IN_BYTES );
        $fields = [
            'sit_app_passport'   => [
                'label' => __( 'Pasport faylı', 'studyinturkey' ),
                'type'  => 'passport',
            ],
            'sit_app_transcript' => [
                'label' => __( 'Transkript faylı', 'studyinturkey' ),
                'type'  => 'transcript',
            ],
            'sit_app_photo'      => [
                'label' => __( 'Şəkil', 'studyinturkey' ),
                'type'  => 'photo',
            ],
        ];

        foreach ( $fields as $field => $meta ) {
            $label = $meta['label'];
            $dtype = $meta['type'];

            if ( empty( $_FILES[ $field ] ) || ! isset( $_FILES[ $field ]['error'] ) ) {
                $errors[] = sprintf(
                    /* translators: %s: field label */
                    __( '%s tələb olunur.', 'studyinturkey' ),
                    $label
                );
                continue;
            }

            $file = $_FILES[ $field ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
            if ( UPLOAD_ERR_NO_FILE === (int) $file['error'] ) {
                $errors[] = sprintf(
                    /* translators: %s: field label */
                    __( '%s tələb olunur.', 'studyinturkey' ),
                    $label
                );
                continue;
            }

            if ( UPLOAD_ERR_OK !== (int) $file['error'] ) {
                $errors[] = sprintf(
                    /* translators: %s: field label */
                    __( '%s yüklənərkən xəta baş verdi.', 'studyinturkey' ),
                    $label
                );
                continue;
            }

            if ( ! empty( $file['size'] ) && (int) $file['size'] > $max ) {
                $errors[] = sprintf(
                    /* translators: %s: field label */
                    __( '%s çox böyükdür (maksimum 5 MB).', 'studyinturkey' ),
                    $label
                );
                continue;
            }

            $name = isset( $file['name'] ) ? $file['name'] : '';
            $check = wp_check_filetype_and_ext( $file['tmp_name'], $name, self::mimes_for_document_type( $dtype ) );
            if ( empty( $check['ext'] ) || empty( $check['type'] ) ) {
                $errors[] = sprintf(
                    /* translators: %s: field label */
                    __( '%s üçün icazə verilən format deyil.', 'studyinturkey' ),
                    $label
                );
            }
        }

        return $errors;
    }

    /**
     * @return true|WP_Error
     */
    private static function save_application_files( int $application_id ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $map = [
            'sit_app_passport'   => 'passport',
            'sit_app_transcript' => 'transcript',
            'sit_app_photo'      => 'photo',
        ];

        foreach ( $map as $field => $doc_type ) {
            if ( empty( $_FILES[ $field ] ) || UPLOAD_ERR_OK !== (int) $_FILES[ $field ]['error'] ) {
                return new WP_Error( 'sit_app_file', __( 'Fayl yükləmə uğursuz oldu.', 'studyinturkey' ) );
            }

            $file = $_FILES[ $field ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

            $mimes = self::mimes_for_document_type( $doc_type );
            add_filter( 'upload_dir', [ __CLASS__, 'filter_upload_dir' ] );

            $overrides = [
                'test_form' => false,
                'mimes'     => $mimes,
            ];

            $result = wp_handle_upload( $file, $overrides );

            remove_filter( 'upload_dir', [ __CLASS__, 'filter_upload_dir' ] );

            if ( isset( $result['error'] ) ) {
                return new WP_Error( 'sit_app_upload', $result['error'] );
            }

            if ( empty( $result['file'] ) || empty( $result['type'] ) ) {
                return new WP_Error( 'sit_app_upload', __( 'Fayl saxlanılmadı.', 'studyinturkey' ) );
            }

            $upload_dir = wp_upload_dir();
            $basedir    = trailingslashit( $upload_dir['basedir'] );
            $full_path  = $result['file'];
            $relative   = str_starts_with( $full_path, $basedir )
                ? substr( $full_path, strlen( $basedir ) )
                : $full_path;

            global $wpdb;

            // phpcs:disable WordPress.DB.DirectDatabaseQuery
            $wpdb->insert(
                SIT_Application_Db::documents_table(),
                [
                    'application_id' => $application_id,
                    'document_type'    => $doc_type,
                    'file_path'        => $relative,
                    'file_name'        => isset( $file['name'] ) ? sanitize_file_name( $file['name'] ) : basename( $full_path ),
                    'mime_type'        => $result['type'],
                    'file_size'        => (int) @filesize( $full_path ),
                ],
                [ '%d', '%s', '%s', '%s', '%s', '%d' ]
            );
            // phpcs:enable
        }

        return true;
    }

    /**
     * @return array<string, string> mime map for wp_handle_upload
     */
    private static function mimes_for_document_type( string $doc_type ): array {
        $full = wp_get_mime_types();

        if ( 'transcript' === $doc_type ) {
            $keys = [ 'pdf', 'doc', 'docx', 'jpg|jpeg|jpe', 'png' ];
        } else {
            $keys = [ 'pdf', 'jpg|jpeg|jpe', 'png', 'webp' ];
        }

        $out = [];
        foreach ( $keys as $ext ) {
            if ( isset( $full[ $ext ] ) ) {
                $out[ $ext ] = $full[ $ext ];
            }
        }

        return apply_filters( 'sit_application_handle_upload_mimes', $out, $doc_type );
    }

    /**
     * @param array<string, mixed> $uploads Upload dir array.
     * @return array<string, mixed>
     */
    public static function filter_upload_dir( array $uploads ): array {
        if ( ! empty( $uploads['error'] ) ) {
            return $uploads;
        }

        $subdir = '/sit-applications/' . gmdate( 'Y/m' );

        $uploads['subdir'] = $subdir;
        $uploads['path']   = $uploads['basedir'] . $subdir;
        $uploads['url']    = $uploads['baseurl'] . $subdir;

        if ( ! wp_mkdir_p( $uploads['path'] ) ) {
            $uploads['error'] = __( 'Yükləmə qovluğu yaradıla bilmədi.', 'studyinturkey' );
        }

        return $uploads;
    }

    private static function rollback_application( int $application_id ): void {
        global $wpdb;

        // phpcs:disable WordPress.DB.DirectDatabaseQuery
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT file_path FROM ' . SIT_Application_Db::documents_table() . ' WHERE application_id = %d',
                $application_id
            ),
            ARRAY_A
        );
        // phpcs:enable

        if ( is_array( $rows ) ) {
            $upload_dir = wp_upload_dir();
            $basedir    = trailingslashit( $upload_dir['basedir'] );
            foreach ( $rows as $row ) {
                if ( empty( $row['file_path'] ) ) {
                    continue;
                }
                $path = $basedir . ltrim( $row['file_path'], '/' );
                if ( is_file( $path ) && str_starts_with( wp_normalize_path( $path ), wp_normalize_path( $basedir ) ) ) {
                    wp_delete_file( $path );
                }
            }
        }

        // phpcs:disable WordPress.DB.DirectDatabaseQuery
        $wpdb->delete( SIT_Application_Db::documents_table(), [ 'application_id' => $application_id ], [ '%d' ] );
        $wpdb->delete( SIT_Application_Db::applications_table(), [ 'id' => $application_id ], [ '%d' ] );
        // phpcs:enable
    }

    private static function is_valid_program( int $program_id ): bool {
        $post = get_post( $program_id );
        return $post && 'program' === $post->post_type && 'publish' === $post->post_status;
    }

    private static function get_redirect_url(): string {
        $ref = wp_get_referer();
        if ( $ref ) {
            return esc_url_raw( $ref );
        }
        return home_url( '/' );
    }

    /**
     * @param string[]              $messages Error strings.
     * @param array<string, mixed> $fields   Sanitized field values for repopulating the form.
     */
    private static function redirect_with_error( string $redirect, array $messages, array $fields = [] ): void {
        $key = wp_generate_password( 12, false, false );
        set_transient(
            'sit_app_err_' . $key,
            [
                'messages' => $messages,
                'fields'   => $fields,
            ],
            120
        );
        wp_safe_redirect( add_query_arg( 'sit_app_err', $key, $redirect ) );
        exit;
    }

    private static function redirect_with_success( string $redirect ): void {
        wp_safe_redirect( add_query_arg( 'sit_app_ok', '1', $redirect ) );
        exit;
    }

    /**
     * Bir dəfəlik: xəta mesajları və köhnə sahə dəyərləri (transient silinir).
     *
     * @return array{errors: string[], old: array<string, string|int>}
     */
    public static function consume_form_flash(): array {
        $empty = [
            'errors' => [],
            'old'    => [],
        ];

        if ( empty( $_GET['sit_app_err'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            return $empty;
        }

        $key = sanitize_text_field( wp_unslash( $_GET['sit_app_err'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
        if ( '' === $key ) {
            return $empty;
        }

        $payload = get_transient( 'sit_app_err_' . $key );
        delete_transient( 'sit_app_err_' . $key );

        if ( ! is_array( $payload ) ) {
            return $empty;
        }

        if ( isset( $payload['messages'] ) && is_array( $payload['messages'] ) ) {
            $old = [];
            if ( ! empty( $payload['fields'] ) && is_array( $payload['fields'] ) ) {
                foreach ( $payload['fields'] as $k => $v ) {
                    if ( is_string( $k ) && ( is_string( $v ) || is_numeric( $v ) ) ) {
                        $old[ $k ] = $v;
                    }
                }
            }

            return [
                'errors' => array_map( 'wp_kses_post', $payload['messages'] ),
                'old'    => $old,
            ];
        }

        return [
            'errors' => array_map( 'wp_kses_post', $payload ),
            'old'    => [],
        ];
    }

    public static function has_success_flag(): bool {
        return isset( $_GET['sit_app_ok'] ) && '1' === $_GET['sit_app_ok']; // phpcs:ignore WordPress.Security.NonceVerification
    }
}
