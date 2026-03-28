<?php
/**
 * Admin menyusu, detal, parametrlər, fayl yükləmə.
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Application_Admin {

    public const PAGE_SLUG     = 'sit-applications';
    public const SETTINGS_SLUG = 'sit-applications-settings';

    public static function register(): void {
        add_action( 'admin_menu', [ __CLASS__, 'add_menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'maybe_save_detail' ], 1 );
        add_action( 'admin_init', [ __CLASS__, 'maybe_download' ], 1 );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        add_action( 'load-toplevel_page_' . self::PAGE_SLUG, [ __CLASS__, 'add_screen_options' ] );
        add_filter( 'set_screen_option_sit_applications_per_page', [ __CLASS__, 'set_per_page' ], 10, 3 );
    }

    public static function add_menu(): void {
        add_menu_page(
            __( 'Müraciətlər', 'studyinturkey' ),
            __( 'Müraciətlər', 'studyinturkey' ),
            'manage_options',
            self::PAGE_SLUG,
            [ __CLASS__, 'render_router' ],
            'dashicons-clipboard',
            58
        );

        add_submenu_page(
            self::PAGE_SLUG,
            __( 'Müraciətlər', 'studyinturkey' ),
            __( 'Siyahı', 'studyinturkey' ),
            'manage_options',
            self::PAGE_SLUG,
            [ __CLASS__, 'render_router' ]
        );

        add_submenu_page(
            self::PAGE_SLUG,
            __( 'Parametrlər', 'studyinturkey' ),
            __( 'Parametrlər', 'studyinturkey' ),
            'manage_options',
            self::SETTINGS_SLUG,
            [ __CLASS__, 'render_settings' ]
        );
    }

    public static function add_screen_options(): void {
        $args = [
            'label'   => __( 'Səhifə başına', 'studyinturkey' ),
            'default' => 20,
            'option'  => 'sit_applications_per_page',
        ];
        add_screen_option( 'per_page', $args );
    }

    /**
     * @param mixed $status
     * @param mixed $option
     * @param mixed $value
     * @return int|mixed
     */
    public static function set_per_page( $status, $option, $value ) {
        if ( 'sit_applications_per_page' === $option ) {
            return max( 1, (int) $value );
        }
        return $status;
    }

    public static function register_settings(): void {
        register_setting(
            'sit_application_settings_group',
            'sit_application_notify_admin_new',
            [
                'type'              => 'string',
                'sanitize_callback' => [ __CLASS__, 'sanitize_checkbox_option' ],
                'default'           => '1',
            ]
        );

        register_setting(
            'sit_application_settings_group',
            'sit_application_notify_applicant_status',
            [
                'type'              => 'string',
                'sanitize_callback' => [ __CLASS__, 'sanitize_checkbox_option' ],
                'default'           => '1',
            ]
        );

        register_setting(
            'sit_application_settings_group',
            'sit_application_notify_extra_emails',
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ]
        );

        register_setting(
            'sit_application_settings_group',
            'sit_application_whatsapp_number',
            [
                'type'              => 'string',
                'sanitize_callback' => [ __CLASS__, 'sanitize_whatsapp_number' ],
                'default'           => '',
            ]
        );
    }

    /**
     * @param mixed $value Raw value.
     */
    public static function sanitize_checkbox_option( $value ): string {
        return '1' === (string) $value ? '1' : '0';
    }

    /**
     * @param mixed $value Raw value.
     */
    public static function sanitize_whatsapp_number( $value ): string {
        $digits = preg_replace( '/\D+/', '', (string) $value );
        return is_string( $digits ) ? $digits : '';
    }

    public static function maybe_save_detail(): void {
        if ( ! is_admin() || ! isset( $_POST['sit_app_admin_save'] ) ) {
            return;
        }

        if ( empty( $_GET['page'] ) || self::PAGE_SLUG !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            return;
        }

        check_admin_referer( 'sit_app_admin_detail', 'sit_app_detail_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'İcazə yoxdur.', 'studyinturkey' ) );
        }

        $id = isset( $_POST['sit_app_id'] ) ? absint( $_POST['sit_app_id'] ) : 0;
        $new = isset( $_POST['sit_app_status'] ) ? sanitize_key( wp_unslash( $_POST['sit_app_status'] ) ) : '';

        $allowed = apply_filters(
            'sit_application_allowed_statuses',
            [ 'pending', 'under_review', 'processing', 'approved', 'rejected' ]
        );

        if ( $id <= 0 || ! in_array( $new, $allowed, true ) ) {
            wp_safe_redirect(
                add_query_arg(
                    [ 'page' => self::PAGE_SLUG, 'sit_app_err' => '1' ],
                    admin_url( 'admin.php' )
                )
            );
            exit;
        }

        $old_row = SIT_Application_Queries::get_application_by_id( $id );
        if ( ! $old_row ) {
            wp_die( esc_html__( 'Müraciət tapılmadı.', 'studyinturkey' ) );
        }

        $old_status = isset( $old_row['status'] ) ? (string) $old_row['status'] : '';

        global $wpdb;

        if ( $old_status !== $new ) {
            // phpcs:disable WordPress.DB.DirectDatabaseQuery
            $wpdb->update(
                SIT_Application_Db::applications_table(),
                [ 'status' => $new ],
                [ 'id' => $id ],
                [ '%s' ],
                [ '%d' ]
            );
            // phpcs:enable

            /**
             * Müraciət statusu dəyişəndə.
             *
             * @param int    $id          Müraciət ID.
             * @param string $old_status  Köhnə status.
             * @param string $new_status  Yeni status.
             */
            do_action( 'sit_application_status_changed', $id, $old_status, $new, $old_row );

            SIT_Application_Notifications::maybe_notify_applicant_status( $id, $old_status, $new, $old_row );
        }

        wp_safe_redirect(
            add_query_arg(
                [
                    'page'       => self::PAGE_SLUG,
                    'action'     => 'view',
                    'sit_app_id' => $id,
                    'updated'    => '1',
                ],
                admin_url( 'admin.php' )
            )
        );
        exit;
    }

    public static function maybe_download(): void {
        if ( ! is_admin() ) {
            return;
        }

        if ( empty( $_GET['page'] ) || self::PAGE_SLUG !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            return;
        }

        if ( empty( $_GET['sit_app_action'] ) || 'download' !== sanitize_key( wp_unslash( $_GET['sit_app_action'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            return;
        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sit_app_download_doc' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            wp_die( esc_html__( 'Təhlükəsizlik yoxlaması uğursuz oldu.', 'studyinturkey' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'İcazə yoxdur.', 'studyinturkey' ) );
        }

        $doc_id = isset( $_GET['doc_id'] ) ? absint( $_GET['doc_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
        if ( $doc_id <= 0 ) {
            wp_die( esc_html__( 'Fayl tapılmadı.', 'studyinturkey' ) );
        }

        $doc = SIT_Application_Queries::get_document_by_id( $doc_id );
        if ( ! $doc || empty( $doc['file_path'] ) ) {
            wp_die( esc_html__( 'Fayl tapılmadı.', 'studyinturkey' ) );
        }

        $upload = wp_upload_dir();
        if ( ! empty( $upload['error'] ) ) {
            wp_die( esc_html__( 'Yükləmə qovluğu əlçatan deyil.', 'studyinturkey' ) );
        }

        $basedir = trailingslashit( $upload['basedir'] );
        $path    = $basedir . ltrim( (string) $doc['file_path'], '/' );
        $path    = wp_normalize_path( $path );
        $root    = wp_normalize_path( $basedir );

        if ( ! str_starts_with( $path, $root ) || ! is_file( $path ) || ! is_readable( $path ) ) {
            wp_die( esc_html__( 'Fayl tapılmadı.', 'studyinturkey' ) );
        }

        $filename = ! empty( $doc['file_name'] ) ? (string) $doc['file_name'] : basename( $path );
        $filename = sanitize_file_name( $filename );

        nocache_headers();
        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename="' . rawurlencode( $filename ) . '"' );
        header( 'Content-Length: ' . (string) filesize( $path ) );

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_readfile
        readfile( $path );
        exit;
    }

    public static function render_router(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'İcazə yoxdur.', 'studyinturkey' ) );
        }

        $action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
        $id     = isset( $_GET['sit_app_id'] ) ? absint( $_GET['sit_app_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

        if ( 'view' === $action && $id > 0 ) {
            self::render_detail( $id );
            return;
        }

        self::render_list();
    }

    private static function render_list(): void {
        require_once SIT_APPLICATION_DIR . 'includes/class-sit-application-list-table.php';

        $table = new SIT_Application_List_Table();
        $table->prepare_items();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'Müraciətlər', 'studyinturkey' ); ?></h1>
            <hr class="wp-header-end" />

            <?php if ( ! empty( $_GET['sit_app_err'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
                <div class="notice notice-error"><p><?php esc_html_e( 'Yadda saxlanılmadı. Məlumatları yoxlayın.', 'studyinturkey' ); ?></p></div>
            <?php endif; ?>

            <form method="get">
                <input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE_SLUG ); ?>" />
                <?php $table->search_box( __( 'Axtarış', 'studyinturkey' ), 'sit-app' ); ?>
            </form>

            <?php $table->display(); ?>
        </div>
        <?php
    }

    private static function render_detail( int $application_id ): void {
        $app = SIT_Application_Queries::get_application_by_id( $application_id );
        if ( ! $app ) {
            wp_die( esc_html__( 'Müraciət tapılmadı.', 'studyinturkey' ) );
        }

        $docs = SIT_Application_Queries::get_documents_full_by_application_id( $application_id );

        $pid    = isset( $app['program_id'] ) ? (int) $app['program_id'] : 0;
        $ptitle = $pid ? get_the_title( $pid ) : '';
        $plink  = $pid ? get_edit_post_link( $pid, 'raw' ) : '';

        $allowed = apply_filters(
            'sit_application_allowed_statuses',
            [ 'pending', 'under_review', 'processing', 'approved', 'rejected' ]
        );

        $wa_number = (string) get_option( 'sit_application_whatsapp_number', '' );
        $wa_digits = preg_replace( '/\D+/', '', $wa_number );
        $wa_url    = '';
        if ( '' !== $wa_digits ) {
            $wa_msg = sprintf(
                /* translators: 1: application ID, 2: applicant name */
                __( 'Salam, StudyInTurkey müraciət #%1$d (%2$s) üzrə əlaqə saxlayıram.', 'studyinturkey' ),
                $application_id,
                isset( $app['applicant_name'] ) ? (string) $app['applicant_name'] : ''
            );
            $wa_url = 'https://wa.me/' . $wa_digits . '?text=' . rawurlencode( $wa_msg );
        }

        $list_url = admin_url( 'admin.php?page=' . self::PAGE_SLUG );
        ?>
        <div class="wrap sit-app-admin-detail">
            <h1 class="wp-heading-inline">
                <?php
                printf(
                    /* translators: %d: application ID */
                    esc_html__( 'Müraciət #%d', 'studyinturkey' ),
                    (int) $application_id
                );
                ?>
            </h1>
            <a href="<?php echo esc_url( $list_url ); ?>" class="page-title-action"><?php esc_html_e( 'Siyahıya qayıt', 'studyinturkey' ); ?></a>
            <hr class="wp-header-end" />

            <?php if ( ! empty( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Yadda saxlanıldı.', 'studyinturkey' ); ?></p></div>
            <?php endif; ?>

            <div class="sit-app-admin-grid" style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;max-width:1100px;">
                <div>
                    <h2><?php esc_html_e( 'Namizəd', 'studyinturkey' ); ?></h2>
                    <table class="widefat striped">
                        <tbody>
                            <tr><th><?php esc_html_e( 'Ad', 'studyinturkey' ); ?></th><td><?php echo esc_html( isset( $app['applicant_name'] ) ? (string) $app['applicant_name'] : '' ); ?></td></tr>
                            <tr><th><?php esc_html_e( 'E-poçt', 'studyinturkey' ); ?></th><td><a href="mailto:<?php echo esc_attr( isset( $app['applicant_email'] ) ? (string) $app['applicant_email'] : '' ); ?>"><?php echo esc_html( isset( $app['applicant_email'] ) ? (string) $app['applicant_email'] : '' ); ?></a></td></tr>
                            <tr><th><?php esc_html_e( 'Telefon', 'studyinturkey' ); ?></th><td><?php echo esc_html( isset( $app['applicant_phone'] ) ? (string) $app['applicant_phone'] : '' ); ?></td></tr>
                            <tr><th><?php esc_html_e( 'Proqram', 'studyinturkey' ); ?></th><td>
                                <?php if ( $plink ) : ?>
                                    <a href="<?php echo esc_url( $plink ); ?>"><?php echo esc_html( $ptitle ? $ptitle : (string) $pid ); ?></a>
                                <?php else : ?>
                                    <?php echo esc_html( $ptitle ? $ptitle : ( $pid ? (string) $pid : '—' ) ); ?>
                                <?php endif; ?>
                            </td></tr>
                            <tr><th><?php esc_html_e( 'Qeyd', 'studyinturkey' ); ?></th><td><?php echo isset( $app['applicant_message'] ) && '' !== (string) $app['applicant_message'] ? esc_html( (string) $app['applicant_message'] ) : '—'; ?></td></tr>
                            <tr><th><?php esc_html_e( 'İstifadəçi ID', 'studyinturkey' ); ?></th><td><?php echo isset( $app['user_id'] ) && (int) $app['user_id'] > 0 ? (int) $app['user_id'] : '—'; ?></td></tr>
                            <tr><th><?php esc_html_e( 'IP', 'studyinturkey' ); ?></th><td><?php echo esc_html( isset( $app['ip_address'] ) ? (string) $app['ip_address'] : '—' ); ?></td></tr>
                            <tr><th><?php esc_html_e( 'Yaradılıb', 'studyinturkey' ); ?></th><td><?php echo esc_html( isset( $app['created_at'] ) ? (string) $app['created_at'] : '—' ); ?></td></tr>
                        </tbody>
                    </table>

                    <h2 style="margin-top:2rem;"><?php esc_html_e( 'Sənədlər', 'studyinturkey' ); ?></h2>
                    <?php if ( empty( $docs ) ) : ?>
                        <p><?php esc_html_e( 'Sənəd yoxdur.', 'studyinturkey' ); ?></p>
                    <?php else : ?>
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Növ', 'studyinturkey' ); ?></th>
                                    <th><?php esc_html_e( 'Fayl', 'studyinturkey' ); ?></th>
                                    <th><?php esc_html_e( 'Ölçü', 'studyinturkey' ); ?></th>
                                    <th><?php esc_html_e( 'Əməliyyat', 'studyinturkey' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $docs as $doc ) : ?>
                                    <?php
                                    $did = isset( $doc['id'] ) ? (int) $doc['id'] : 0;
                                    $dl  = wp_nonce_url(
                                        add_query_arg(
                                            [
                                                'page'           => self::PAGE_SLUG,
                                                'sit_app_action' => 'download',
                                                'doc_id'         => $did,
                                            ],
                                            admin_url( 'admin.php' )
                                        ),
                                        'sit_app_download_doc'
                                    );
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html( SIT_Application_Account::document_type_label( isset( $doc['document_type'] ) ? (string) $doc['document_type'] : '' ) ); ?></td>
                                        <td><?php echo esc_html( isset( $doc['file_name'] ) ? (string) $doc['file_name'] : '' ); ?></td>
                                        <td><?php echo isset( $doc['file_size'] ) ? esc_html( size_format( (int) $doc['file_size'] ) ) : '—'; ?></td>
                                        <td><a class="button button-small" href="<?php echo esc_url( $dl ); ?>"><?php esc_html_e( 'Yüklə', 'studyinturkey' ); ?></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div>
                    <div class="postbox">
                        <div class="postbox-header"><h2 class="hndle"><?php esc_html_e( 'Status', 'studyinturkey' ); ?></h2></div>
                        <div class="inside">
                            <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG . '&action=view&sit_app_id=' . $application_id ) ); ?>">
                                <?php wp_nonce_field( 'sit_app_admin_detail', 'sit_app_detail_nonce' ); ?>
                                <input type="hidden" name="sit_app_admin_save" value="1" />
                                <input type="hidden" name="sit_app_id" value="<?php echo esc_attr( (string) $application_id ); ?>" />

                                <p>
                                    <label for="sit_app_status" class="screen-reader-text"><?php esc_html_e( 'Status', 'studyinturkey' ); ?></label>
                                    <select name="sit_app_status" id="sit_app_status" class="widefat">
                                        <?php foreach ( $allowed as $st ) : ?>
                                            <option value="<?php echo esc_attr( $st ); ?>" <?php selected( isset( $app['status'] ) ? (string) $app['status'] : '', $st ); ?>>
                                                <?php echo esc_html( SIT_Application_Account::status_label( $st ) ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </p>
                                <p>
                                    <button type="submit" class="button button-primary"><?php esc_html_e( 'Yadda saxla', 'studyinturkey' ); ?></button>
                                </p>
                            </form>
                        </div>
                    </div>

                    <?php if ( '' !== $wa_url ) : ?>
                        <div class="postbox" style="margin-top:16px;">
                            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e( 'WhatsApp', 'studyinturkey' ); ?></h2></div>
                            <div class="inside">
                                <p><?php esc_html_e( 'Namizədlə ofis nömrənizdən yazmaq üçün:', 'studyinturkey' ); ?></p>
                                <p><a class="button button-secondary" href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WhatsApp-da aç', 'studyinturkey' ); ?></a></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    public static function render_settings(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'İcazə yoxdur.', 'studyinturkey' ) );
        }

        if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            add_settings_error( 'sit_application_messages', 'sit_application_updated', __( 'Parametrlər yadda saxlanıldı.', 'studyinturkey' ), 'success' );
        }

        settings_errors( 'sit_application_messages' );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Müraciət parametrləri', 'studyinturkey' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'sit_application_settings_group' ); ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Yeni müraciət — admin e-poçtu', 'studyinturkey' ); ?></th>
                        <td>
                            <input type="hidden" name="sit_application_notify_admin_new" value="0" />
                            <label>
                                <input type="checkbox" name="sit_application_notify_admin_new" value="1" <?php checked( '1', (string) get_option( 'sit_application_notify_admin_new', '1' ) ); ?> />
                                <?php esc_html_e( 'Yeni müraciət qəbul ediləndə admin(lər)ə bildiriş göndər', 'studyinturkey' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Əlavə admin e-poçtları', 'studyinturkey' ); ?></th>
                        <td>
                            <input type="text" class="regular-text" name="sit_application_notify_extra_emails" value="<?php echo esc_attr( (string) get_option( 'sit_application_notify_extra_emails', '' ) ); ?>" />
                            <p class="description"><?php esc_html_e( 'Vergüllə ayırın. Əsas admin e-poçtu həmişə daxildir.', 'studyinturkey' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Status dəyişikliyi — namizəd', 'studyinturkey' ); ?></th>
                        <td>
                            <input type="hidden" name="sit_application_notify_applicant_status" value="0" />
                            <label>
                                <input type="checkbox" name="sit_application_notify_applicant_status" value="1" <?php checked( '1', (string) get_option( 'sit_application_notify_applicant_status', '1' ) ); ?> />
                                <?php esc_html_e( 'Status dəyişəndə namizədin e-poçtuna bildiriş göndər', 'studyinturkey' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'WhatsApp ofis nömrəsi', 'studyinturkey' ); ?></th>
                        <td>
                            <input type="text" class="regular-text" name="sit_application_whatsapp_number" value="<?php echo esc_attr( (string) get_option( 'sit_application_whatsapp_number', '' ) ); ?>" placeholder="994501234567" />
                            <p class="description"><?php esc_html_e( 'Yalnız rəqəmlər (ölkə kodu ilə). Admin detal səhifəsində wa.me linki göstərilir.', 'studyinturkey' ); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
