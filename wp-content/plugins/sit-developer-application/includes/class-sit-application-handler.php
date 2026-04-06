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

        $profile = self::profile_build_from_post();

        if ( $program > 0 && self::is_valid_program( $program ) ) {
            $profile_errors = self::profile_validate( $program, $profile );
            if ( ! empty( $profile_errors ) ) {
                $errors = array_merge( $errors, $profile_errors );
            }
        }

        $file_errors = self::validate_files_array( $program );
        if ( ! empty( $file_errors ) ) {
            $errors = array_merge( $errors, $file_errors );
        }

        $flash_old = self::profile_old_for_flash( $profile );
        $flash_old = array_merge(
            [
                'sit_app_name'         => $name,
                'sit_app_email'        => $email,
                'sit_app_phone'        => $phone,
                'sit_app_program_id'   => $program,
                'sit_app_message'      => $message,
            ],
            $flash_old
        );

        if ( ! empty( $errors ) ) {
            self::redirect_with_error( $redirect, $errors, $flash_old );
        }

        global $wpdb;

        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

        $profile_json = wp_json_encode( $profile, JSON_UNESCAPED_UNICODE );

        $row = [
            'applicant_name'         => $name,
            'applicant_email'        => $email,
            'applicant_phone'        => $phone,
            'program_id'             => $program,
            'status'                 => 'pending',
            'applicant_message'      => $message ? $message : null,
            'ip_address'             => $ip ? $ip : null,
            'applicant_profile_json'   => $profile_json ? $profile_json : null,
        ];
        $row_format = [ '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s' ];

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

        $upload_result = self::save_application_files( $application_id, $program );
        if ( is_wp_error( $upload_result ) ) {
            self::rollback_application( $application_id );
            self::redirect_with_error( $redirect, [ $upload_result->get_error_message() ] );
        }

        // Müraciət uğurla yaradıldı (sənədlər daxil).
        do_action( 'sit_application_created', $application_id );

        SIT_Application_Notifications::maybe_notify_admin_new( $application_id );

        self::redirect_with_success( $redirect );
    }

    /**
     * @return string[] Xəta mesajları.
     */
    private static function validate_files_array( int $program_id ): array {
        if ( ! function_exists( 'wp_check_filetype_and_ext' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $errors = [];
        $max    = (int) apply_filters( 'sit_application_max_upload_bytes', 5 * MB_IN_BYTES );
        $fields = self::get_file_fields_for_program( $program_id );

        foreach ( $fields as $meta ) {
            $field    = $meta[0];
            $label    = $meta[1];
            $dtype    = $meta[2];
            $required = $meta[3];

            if ( empty( $_FILES[ $field ] ) || ! isset( $_FILES[ $field ]['error'] ) ) {
                if ( $required ) {
                    $errors[] = sprintf(
                        /* translators: %s: field label */
                        __( '%s tələb olunur.', 'studyinturkey' ),
                        $label
                    );
                }
                continue;
            }

            $file = $_FILES[ $field ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
            if ( UPLOAD_ERR_NO_FILE === (int) $file['error'] ) {
                if ( $required ) {
                    $errors[] = sprintf(
                        /* translators: %s: field label */
                        __( '%s tələb olunur.', 'studyinturkey' ),
                        $label
                    );
                }
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

            $fname = isset( $file['name'] ) ? $file['name'] : '';
            $check = wp_check_filetype_and_ext( $file['tmp_name'], $fname, self::mimes_for_document_type( $dtype ) );
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
     * @return array<int, array{0: string, 1: string, 2: string, 3: bool}>
     */
    private static function get_file_fields_for_program( int $program_id ): array {
        $level = SIT_Application_Degree::level_for_program( $program_id );

        $rows = [
            [
                'sit_app_passport',
                __( 'Pasport (bütün məlumatlı səhifə)', 'studyinturkey' ),
                'passport',
                true,
            ],
            [
                'sit_app_transcript',
                __( 'Transkript / attestat', 'studyinturkey' ),
                'transcript',
                true,
            ],
            [
                'sit_app_photo',
                __( 'Pasport formatlı foto (ağ fon, üz aydın görünsün)', 'studyinturkey' ),
                'photo',
                true,
            ],
        ];

        if ( SIT_Application_Degree::LEVEL_UNDERGRADUATE === $level ) {
            $rows[] = [
                'sit_app_secondary_diploma',
                __( 'Orta təhsil haqqında şəhadətnamə və ya diplom', 'studyinturkey' ),
                'secondary_diploma',
                true,
            ];
        }

        if ( SIT_Application_Degree::LEVEL_GRADUATE === $level || SIT_Application_Degree::LEVEL_DOCTORAL === $level ) {
            $rows[] = [
                'sit_app_diploma_prior',
                __( 'Əvvəlki dərəcə diplomu (bakalavr və ya magistr)', 'studyinturkey' ),
                'diploma_prior',
                true,
            ];
            $rows[] = [
                'sit_app_cv',
                __( 'CV (təhsil və iş təcrübəsi)', 'studyinturkey' ),
                'cv',
                true,
            ];
            $rows[] = [
                'sit_app_motivation',
                __( 'Motivasiya məktubu', 'studyinturkey' ),
                'motivation_letter',
                true,
            ];
        }

        $rows[] = [
            'sit_app_language_cert',
            __( 'Dil sertifikatı (IELTS / TOEFL və s., istəyə bağlı)', 'studyinturkey' ),
            'language_cert',
            false,
        ];

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    private static function profile_build_from_post(): array {
        $t = static function ( string $key ): string {
            return isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] )
                ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) )
                : '';
        };

        $ta = static function ( string $key ): string {
            return isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] )
                ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) )
                : '';
        };

        return [
            'date_of_birth'           => $t( 'sit_app_dob' ),
            'nationality'             => $t( 'sit_app_nationality' ),
            'passport_number'         => $t( 'sit_app_passport_no' ),
            'address'                 => $ta( 'sit_app_address' ),
            'education_institution'   => $t( 'sit_app_edu_institution' ),
            'education_country'       => $t( 'sit_app_edu_country' ),
            'graduation_year'         => $t( 'sit_app_grad_year' ),
            'intake_period'           => $t( 'sit_app_intake' ),
            'research_interest'       => $ta( 'sit_app_research' ),
            'work_experience'         => $ta( 'sit_app_work_exp' ),
        ];
    }

    /**
     * @param array<string, string> $p Profil massivi.
     * @return string[]
     */
    private static function profile_validate( int $program_id, array $p ): array {
        $errors = [];
        $level  = SIT_Application_Degree::level_for_program( $program_id );

        if ( '' === $p['date_of_birth'] ) {
            $errors[] = __( 'Doğum tarixini daxil edin.', 'studyinturkey' );
        }
        if ( '' === $p['nationality'] || strlen( $p['nationality'] ) < 2 ) {
            $errors[] = __( 'Vətəndaşlığı / milliyyəti daxil edin.', 'studyinturkey' );
        }
        if ( '' === $p['passport_number'] || strlen( $p['passport_number'] ) < 4 ) {
            $errors[] = __( 'Pasport və ya şəxsiyyət nömrəsini daxil edin.', 'studyinturkey' );
        }
        if ( '' === $p['address'] || strlen( $p['address'] ) < 10 ) {
            $errors[] = __( 'Yaşayış ünvanınızı tam daxil edin.', 'studyinturkey' );
        }
        if ( '' === $p['education_institution'] || strlen( $p['education_institution'] ) < 2 ) {
            $errors[] = __( 'Son təhsil müəssisəsinin adını daxil edin.', 'studyinturkey' );
        }
        if ( '' === $p['education_country'] || strlen( $p['education_country'] ) < 2 ) {
            $errors[] = __( 'Təhsil aldığınız ölkəni daxil edin.', 'studyinturkey' );
        }
        if ( '' === $p['graduation_year'] || ! preg_match( '/^\d{4}$/', $p['graduation_year'] ) ) {
            $errors[] = __( 'Bitirmə ilini 4 rəqəmlə daxil edin (məs. 2024).', 'studyinturkey' );
        }
        if ( '' === $p['intake_period'] || strlen( $p['intake_period'] ) < 3 ) {
            $errors[] = __( 'Planlaşdırdığınız qəbul dövrünü daxil edin (məs. Payız 2026).', 'studyinturkey' );
        }

        if ( SIT_Application_Degree::LEVEL_GRADUATE === $level || SIT_Application_Degree::LEVEL_DOCTORAL === $level ) {
            if ( strlen( $p['work_experience'] ) < 10 ) {
                $errors[] = __( 'Magistr / doktorantura üçün qısa iş və ya təcrübə təsviri əlavə edin (ən azı 10 simvol).', 'studyinturkey' );
            }
        }

        if ( SIT_Application_Degree::LEVEL_DOCTORAL === $level ) {
            if ( strlen( $p['research_interest'] ) < 40 ) {
                $errors[] = __( 'Doktorantura üçün tədqiqat mövzusu və ya planınızı ətraflı yazın (ən azı 40 simvol).', 'studyinturkey' );
            }
        }

        return $errors;
    }

    /**
     * @param array<string, string> $profile profile_build_from_post nəticəsi.
     * @return array<string, string>
     */
    private static function profile_old_for_flash( array $profile ): array {
        return [
            'sit_app_dob'             => $profile['date_of_birth'],
            'sit_app_nationality'     => $profile['nationality'],
            'sit_app_passport_no'     => $profile['passport_number'],
            'sit_app_address'         => $profile['address'],
            'sit_app_edu_institution' => $profile['education_institution'],
            'sit_app_edu_country'     => $profile['education_country'],
            'sit_app_grad_year'       => $profile['graduation_year'],
            'sit_app_intake'          => $profile['intake_period'],
            'sit_app_research'        => $profile['research_interest'],
            'sit_app_work_exp'        => $profile['work_experience'],
        ];
    }

    /**
     * @return true|WP_Error
     */
    private static function save_application_files( int $application_id, int $program_id ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $fields = self::get_file_fields_for_program( $program_id );

        foreach ( $fields as $meta ) {
            $field    = $meta[0];
            $dtype    = $meta[2];
            $required = $meta[3];

            if ( empty( $_FILES[ $field ] ) || ! isset( $_FILES[ $field ]['error'] ) ) {
                if ( $required ) {
                    return new WP_Error( 'sit_app_file', __( 'Fayl yükləmə uğursuz oldu.', 'studyinturkey' ) );
                }
                continue;
            }

            $file = $_FILES[ $field ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

            if ( UPLOAD_ERR_NO_FILE === (int) $file['error'] ) {
                if ( $required ) {
                    return new WP_Error( 'sit_app_file', __( 'Fayl yükləmə uğursuz oldu.', 'studyinturkey' ) );
                }
                continue;
            }

            if ( UPLOAD_ERR_OK !== (int) $file['error'] ) {
                return new WP_Error( 'sit_app_upload', __( 'Fayl yüklənərkən xəta baş verdi.', 'studyinturkey' ) );
            }

            $mimes = self::mimes_for_document_type( $dtype );
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
                    'document_type'  => $dtype,
                    'file_path'      => $relative,
                    'file_name'      => isset( $file['name'] ) ? sanitize_file_name( $file['name'] ) : basename( $full_path ),
                    'mime_type'      => $result['type'],
                    'file_size'      => (int) @filesize( $full_path ),
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

        $doc_like = [ 'transcript', 'secondary_diploma', 'diploma_prior', 'cv', 'motivation_letter', 'language_cert' ];

        if ( in_array( $doc_type, $doc_like, true ) ) {
            $keys = [ 'pdf', 'doc', 'docx', 'jpg|jpeg|jpe', 'png' ];
        } elseif ( 'photo' === $doc_type ) {
            $keys = [ 'jpg|jpeg|jpe', 'png', 'webp' ];
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
