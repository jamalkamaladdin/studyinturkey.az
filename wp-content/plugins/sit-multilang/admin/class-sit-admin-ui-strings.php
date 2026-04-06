<?php
/**
 * Admin: UI sətirləri idarəetməsi (wp_sit_strings).
 */

defined( 'ABSPATH' ) || exit;

class SIT_Admin_UI_Strings {

    private const PAGE_SLUG = 'sit-ui-strings';

    public static function init(): void {
        add_action( 'admin_menu', [ __CLASS__, 'register_submenu' ], 20 );
        add_action( 'admin_init', [ __CLASS__, 'handle_requests' ] );
    }

    public static function register_submenu(): void {
        add_submenu_page(
            'sit-languages',
            esc_html__( 'UI sətirləri', 'studyinturkey' ),
            esc_html__( 'UI sətirləri', 'studyinturkey' ),
            'manage_options',
            self::PAGE_SLUG,
            [ __CLASS__, 'render_screen' ]
        );
    }

    public static function handle_requests(): void {
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
        if ( self::PAGE_SLUG !== $page ) {
            return;
        }

        if ( ! empty( $_POST['sit_ui_strings_save'] ) ) {
            self::handle_save();
            return;
        }

        if ( empty( $_GET['action'] ) || empty( $_GET['key'] ) ) {
            return;
        }

        $action = sanitize_key( wp_unslash( $_GET['action'] ) );
        $key    = SIT_Strings::sanitize_string_key( (string) wp_unslash( $_GET['key'] ) );

        if ( 'delete' !== $action || '' === $key ) {
            return;
        }

        if ( ! isset( $_GET['_sit_ui_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_sit_ui_nonce'] ) ), 'sit_ui_string_delete_' . $key ) ) {
            return;
        }

        SIT_Strings::delete_key( $key );
        wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE_SLUG . '&message=deleted' ) );
        exit;
    }

    private static function handle_save(): void {
        if ( ! isset( $_POST['sit_ui_strings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sit_ui_strings_nonce'] ) ), 'sit_ui_strings_save' ) ) {
            wp_die( esc_html__( 'Təhlükəsizlik yoxlaması uğursuz oldu.', 'studyinturkey' ) );
        }

        $key     = SIT_Strings::sanitize_string_key( isset( $_POST['string_key'] ) ? (string) wp_unslash( $_POST['string_key'] ) : '' );
        $key_new = SIT_Strings::sanitize_string_key( isset( $_POST['string_key_new'] ) ? (string) wp_unslash( $_POST['string_key_new'] ) : '' );
        if ( '' === $key && '' !== $key_new ) {
            $key = $key_new;
        }
        if ( '' === $key ) {
            wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE_SLUG . '&message=no_key' ) );
            exit;
        }

        $context = isset( $_POST['context'] ) ? sanitize_key( wp_unslash( $_POST['context'] ) ) : 'general';
        if ( '' === $context ) {
            $context = 'general';
        }

        $values = isset( $_POST['sit_ui_lang'] ) && is_array( $_POST['sit_ui_lang'] ) ? wp_unslash( $_POST['sit_ui_lang'] ) : [];
        $clean  = [];
        foreach ( $values as $code => $text ) {
            $clean[ sanitize_key( (string) $code ) ] = is_string( $text ) ? $text : '';
        }

        SIT_Strings::save_key( $key, $context, $clean );

        wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE_SLUG . '&message=saved' ) );
        exit;
    }

    public static function render_screen(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bu səhifəyə giriş icazəniz yoxdur.', 'studyinturkey' ) );
        }

        $action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'list';
        if ( 'add' === $action || 'edit' === $action ) {
            $key = isset( $_GET['key'] ) ? SIT_Strings::sanitize_string_key( (string) wp_unslash( $_GET['key'] ) ) : '';
            if ( 'edit' === $action && '' === $key ) {
                wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) );
                exit;
            }
            self::render_form( 'add' === $action ? '' : $key );
            return;
        }

        self::render_list();
    }

    private static function render_notices(): void {
        if ( empty( $_GET['message'] ) ) {
            return;
        }
        $msg = sanitize_key( wp_unslash( $_GET['message'] ) );
        $map = [
            'saved'   => [ 'success', __( 'Saxlanıldı.', 'studyinturkey' ) ],
            'deleted' => [ 'success', __( 'Silindi.', 'studyinturkey' ) ],
            'no_key'  => [ 'error', __( 'Açar boş ola bilməz.', 'studyinturkey' ) ],
        ];
        if ( ! isset( $map[ $msg ] ) ) {
            return;
        }
        $c = 'success' === $map[ $msg ][0] ? 'notice-success' : 'notice-error';
        printf(
            '<div class="notice %1$s is-dismissible"><p>%2$s</p></div>',
            esc_attr( $c ),
            esc_html( $map[ $msg ][1] )
        );
    }

    private static function render_list(): void {
        require_once SIT_MULTILANG_DIR . 'admin/class-sit-ui-strings-list-table.php';

        $search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

        $table = new SIT_UI_Strings_List_Table();
        $table->set_search( $search );
        $table->prepare_items();

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'UI sətirləri', 'studyinturkey' ); ?></h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG . '&action=add' ) ); ?>" class="page-title-action">
                <?php esc_html_e( 'Yeni sətir', 'studyinturkey' ); ?>
            </a>
            <hr class="wp-header-end" />
            <?php self::render_notices(); ?>

            <form method="get">
                <input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE_SLUG ); ?>" />
                <p class="search-box">
                    <label class="screen-reader-text" for="sit-ui-search"><?php esc_html_e( 'Axtar', 'studyinturkey' ); ?></label>
                    <input type="search" id="sit-ui-search" name="s" value="<?php echo esc_attr( $search ); ?>" />
                    <?php submit_button( __( 'Axtar', 'studyinturkey' ), '', '', false ); ?>
                </p>
                <?php $table->display(); ?>
            </form>
        </div>
        <?php
    }

    private static function render_form( string $existing_key ): void {
        $is_new  = '' === $existing_key;
        $context = 'general';
        $values  = [];

        if ( ! $is_new ) {
            $context = SIT_Strings::get_context_for_key( $existing_key );
            $values  = SIT_Strings::get_all_values_for_key( $existing_key );
        }

        $title = $is_new
            ? esc_html__( 'Yeni UI sətri', 'studyinturkey' )
            : esc_html__( 'UI sətri redaktəsi', 'studyinturkey' );

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( $title ); ?></h1>
            <?php self::render_notices(); ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ); ?>">
                <?php wp_nonce_field( 'sit_ui_strings_save', 'sit_ui_strings_nonce' ); ?>
                <input type="hidden" name="sit_ui_strings_save" value="1" />

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="sit_ui_key"><?php esc_html_e( 'Açar', 'studyinturkey' ); ?></label></th>
                        <td>
                            <?php if ( $is_new ) : ?>
                                <input name="string_key_new" id="sit_ui_key" type="text" class="regular-text" required
                                    pattern="[a-z0-9._-]+" maxlength="255"
                                    placeholder="nav.home" />
                                <p class="description"><?php esc_html_e( 'Yalnız kiçik hərf, rəqəm, nöqtə, tire və alt xətt.', 'studyinturkey' ); ?></p>
                            <?php else : ?>
                                <code><?php echo esc_html( $existing_key ); ?></code>
                                <input type="hidden" name="string_key" value="<?php echo esc_attr( $existing_key ); ?>" />
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sit_ui_ctx"><?php esc_html_e( 'Kontekst', 'studyinturkey' ); ?></label></th>
                        <td>
                            <input name="context" id="sit_ui_ctx" type="text" class="regular-text" value="<?php echo esc_attr( $context ); ?>" />
                            <p class="description"><?php esc_html_e( 'Qrup (nav, buttons, footer…) — filtrasiya üçün.', 'studyinturkey' ); ?></p>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Tərcümələr', 'studyinturkey' ); ?></h2>
                <table class="form-table" role="presentation">
                    <?php foreach ( SIT_Languages::get_active_languages() as $lang ) : ?>
                        <tr>
                            <th scope="row">
                                <label for="sit_ui_<?php echo esc_attr( $lang->code ); ?>">
                                    <?php echo esc_html( $lang->native_name ); ?> (<?php echo esc_html( $lang->code ); ?>)
                                </label>
                            </th>
                            <td>
                                <textarea class="large-text" rows="2" id="sit_ui_<?php echo esc_attr( $lang->code ); ?>"
                                    name="sit_ui_lang[<?php echo esc_attr( $lang->code ); ?>]"><?php echo esc_textarea( $values[ $lang->code ] ?? '' ); ?></textarea>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <?php submit_button( __( 'Saxla', 'studyinturkey' ) ); ?>
                <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ); ?>"><?php esc_html_e( '← Siyahıya qayıt', 'studyinturkey' ); ?></a></p>
            </form>
        </div>
        <?php
    }
}
