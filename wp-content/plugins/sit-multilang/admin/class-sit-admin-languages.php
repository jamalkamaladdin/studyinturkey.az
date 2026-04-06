<?php
/**
 * Admin: Dillər səhifəsi — siyahı, əlavə/redaktə/silmə, aktiv/default.
 */

defined( 'ABSPATH' ) || exit;

class SIT_Admin_Languages {

    private const PAGE_SLUG = 'sit-languages';

    public static function init(): void {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'handle_requests' ] );
    }

    public static function register_menu(): void {
        add_menu_page(
            esc_html__( 'Dillər', 'studyinturkey' ),
            esc_html__( 'Dillər', 'studyinturkey' ),
            'manage_options',
            self::PAGE_SLUG,
            [ __CLASS__, 'render_screen' ],
            'dashicons-translation',
            26
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

        if ( ! empty( $_POST['sit_language_save'] ) ) {
            self::handle_save_post();
            return;
        }

        if ( empty( $_GET['action'] ) || empty( $_GET['lang_id'] ) ) {
            return;
        }

        $action = sanitize_key( wp_unslash( $_GET['action'] ) );
        $id     = absint( $_GET['lang_id'] );

        if ( ! isset( $_GET['_sit_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_sit_nonce'] ) ), 'sit_languages_admin' ) ) {
            return;
        }

        $redirect = admin_url( 'admin.php?page=' . self::PAGE_SLUG );

        switch ( $action ) {
            case 'delete':
                $r = SIT_Languages::delete_language( $id );
                if ( true === $r ) {
                    $redirect = add_query_arg( 'message', 'deleted', $redirect );
                } elseif ( 'last_language' === $r ) {
                    $redirect = add_query_arg( 'message', 'last_language', $redirect );
                } else {
                    $redirect = add_query_arg( 'message', 'error', $redirect );
                }
                break;

            case 'activate':
                SIT_Languages::set_language_active( $id, true );
                $redirect = add_query_arg( 'message', 'activated', $redirect );
                break;

            case 'deactivate':
                $r = SIT_Languages::set_language_active( $id, false );
                if ( true === $r ) {
                    $redirect = add_query_arg( 'message', 'deactivated', $redirect );
                } else {
                    $redirect = add_query_arg( 'message', 'default_cannot_deactivate', $redirect );
                }
                break;

            case 'set_default':
                if ( SIT_Languages::set_default_by_id( $id ) ) {
                    $redirect = add_query_arg( 'message', 'default_set', $redirect );
                } else {
                    $redirect = add_query_arg( 'message', 'error', $redirect );
                }
                break;

            default:
                return;
        }

        wp_safe_redirect( $redirect );
        exit;
    }

    private static function handle_save_post(): void {
        if ( ! isset( $_POST['sit_language_save_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sit_language_save_nonce'] ) ), 'sit_language_save' ) ) {
            wp_die( esc_html__( 'Təhlükəsizlik yoxlaması uğursuz oldu.', 'studyinturkey' ) );
        }

        $lang_id = isset( $_POST['lang_id'] ) ? absint( $_POST['lang_id'] ) : 0;

        $code = isset( $_POST['code'] ) ? strtolower( sanitize_text_field( wp_unslash( $_POST['code'] ) ) ) : '';
        $code = preg_replace( '/[^a-z0-9\-]/', '', $code );
        $code = substr( $code, 0, 10 );

        $name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        $native_name = isset( $_POST['native_name'] ) ? sanitize_text_field( wp_unslash( $_POST['native_name'] ) ) : '';
        $locale      = isset( $_POST['locale'] ) ? sanitize_text_field( wp_unslash( $_POST['locale'] ) ) : '';
        $locale      = substr( $locale, 0, 20 );
        $direction   = isset( $_POST['direction'] ) && 'rtl' === $_POST['direction'] ? 'rtl' : 'ltr';
        $flag = isset( $_POST['flag'] ) ? sanitize_text_field( wp_unslash( $_POST['flag'] ) ) : '';
        if ( function_exists( 'mb_substr' ) ) {
            $flag = mb_substr( $flag, 0, 10, 'UTF-8' );
        } else {
            $flag = substr( $flag, 0, 10 );
        }
        $sort_order  = isset( $_POST['sort_order'] ) ? absint( $_POST['sort_order'] ) : 0;
        $is_active   = ! empty( $_POST['is_active'] ) ? 1 : 0;
        $is_default  = ! empty( $_POST['is_default'] ) ? 1 : 0;

        $redirect = admin_url( 'admin.php?page=' . self::PAGE_SLUG );

        if ( '' === $code || '' === $name || '' === $native_name || '' === $locale ) {
            wp_safe_redirect( add_query_arg( 'message', 'validation', $redirect ) );
            exit;
        }

        if ( SIT_Languages::code_exists( $code, $lang_id > 0 ? $lang_id : null ) ) {
            wp_safe_redirect( add_query_arg( 'message', 'duplicate_code', $redirect ) );
            exit;
        }

        $row = [
            'code'        => $code,
            'name'        => $name,
            'native_name' => $native_name,
            'locale'      => $locale,
            'direction'   => $direction,
            'flag'        => $flag,
            'sort_order'  => $sort_order,
            'is_active'   => $is_active,
            'is_default'  => $is_default,
        ];

        if ( $is_default && ! $is_active ) {
            wp_safe_redirect( add_query_arg( 'message', 'default_must_be_active', $redirect ) );
            exit;
        }

        if ( $lang_id > 0 ) {
            $existing = SIT_Languages::get_language_by_id( $lang_id );
            if ( ! $existing ) {
                wp_safe_redirect( add_query_arg( 'message', 'error', $redirect ) );
                exit;
            }
            if ( (int) $existing->is_default === 1 && ! $is_default ) {
                wp_safe_redirect( add_query_arg( 'message', 'unset_default_first', $redirect ) );
                exit;
            }
            if ( (int) $existing->is_default === 1 && ! $is_active ) {
                wp_safe_redirect( add_query_arg( 'message', 'default_cannot_deactivate', $redirect ) );
                exit;
            }
            SIT_Languages::update_language( $lang_id, $row );
            wp_safe_redirect( add_query_arg( 'message', 'updated', $redirect ) );
            exit;
        }

        SIT_Languages::insert_language( $row );
        wp_safe_redirect( add_query_arg( 'message', 'created', $redirect ) );
        exit;
    }

    public static function render_screen(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bu səhifəyə giriş icazəniz yoxdur.', 'studyinturkey' ) );
        }

        $action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'list';

        if ( 'add' === $action || 'edit' === $action ) {
            self::render_form( 'edit' === $action ? absint( $_GET['lang_id'] ?? 0 ) : 0 );
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
            'created'                    => [ 'success', __( 'Dil əlavə edildi.', 'studyinturkey' ) ],
            'updated'                    => [ 'success', __( 'Dil yeniləndi.', 'studyinturkey' ) ],
            'deleted'                    => [ 'success', __( 'Dil silindi.', 'studyinturkey' ) ],
            'activated'                  => [ 'success', __( 'Dil aktivləşdirildi.', 'studyinturkey' ) ],
            'deactivated'                => [ 'success', __( 'Dil deaktiv edildi.', 'studyinturkey' ) ],
            'default_set'                => [ 'success', __( 'Default dil dəyişdirildi.', 'studyinturkey' ) ],
            'validation'                 => [ 'error', __( 'Kod, ad, yerli ad və locale mütləqdir.', 'studyinturkey' ) ],
            'duplicate_code'             => [ 'error', __( 'Bu kod artıq mövcuddur.', 'studyinturkey' ) ],
            'last_language'              => [ 'error', __( 'Son dili silmək olmaz.', 'studyinturkey' ) ],
            'default_cannot_deactivate'  => [ 'error', __( 'Default dili deaktiv etmək olmaz. Əvvəlcə başqa dili default edin.', 'studyinturkey' ) ],
            'default_must_be_active'     => [ 'error', __( 'Default dil aktiv olmalıdır.', 'studyinturkey' ) ],
            'unset_default_first'        => [
                'error',
                __( 'Əvvəlcə başqa dili siyahıdan "Default et" ilə əsas dil seçin, sonra köhnə dildən default işarəsini götürün.', 'studyinturkey' ),
            ],
            'error'                      => [ 'error', __( 'Əməliyyat uğursuz oldu.', 'studyinturkey' ) ],
        ];

        if ( ! isset( $map[ $msg ] ) ) {
            return;
        }

        $class = 'success' === $map[ $msg ][0] ? 'notice notice-success' : 'notice notice-error';
        printf(
            '<div class="%1$s is-dismissible"><p>%2$s</p></div>',
            esc_attr( $class ),
            esc_html( $map[ $msg ][1] )
        );
    }

    private static function render_list(): void {
        require_once SIT_MULTILANG_DIR . 'admin/class-sit-languages-list-table.php';

        $table = new SIT_Languages_List_Table();
        $table->prepare_items();

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'Dillər', 'studyinturkey' ); ?></h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG . '&action=add' ) ); ?>" class="page-title-action">
                <?php esc_html_e( 'Yeni dil', 'studyinturkey' ); ?>
            </a>
            <hr class="wp-header-end" />
            <?php self::render_notices(); ?>
            <form method="get">
                <input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE_SLUG ); ?>" />
                <?php $table->display(); ?>
            </form>
        </div>
        <?php
    }

    private static function render_form( int $lang_id ): void {
        $item = $lang_id > 0 ? SIT_Languages::get_language_by_id( $lang_id ) : null;
        if ( $lang_id > 0 && ! $item ) {
            wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE_SLUG . '&message=error' ) );
            exit;
        }

        $is_edit = (bool) $item;
        $title   = $is_edit
            ? esc_html__( 'Dili redaktə et', 'studyinturkey' )
            : esc_html__( 'Yeni dil', 'studyinturkey' );

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( $title ); ?></h1>
            <?php self::render_notices(); ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ); ?>">
                <?php wp_nonce_field( 'sit_language_save', 'sit_language_save_nonce' ); ?>
                <input type="hidden" name="sit_language_save" value="1" />
                <?php if ( $is_edit ) : ?>
                    <input type="hidden" name="lang_id" value="<?php echo esc_attr( (string) $item->id ); ?>" />
                <?php endif; ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="sit_lang_code"><?php esc_html_e( 'Kod', 'studyinturkey' ); ?></label></th>
                        <td>
                            <input name="code" id="sit_lang_code" type="text" class="regular-text" required maxlength="10"
                                value="<?php echo $item ? esc_attr( $item->code ) : ''; ?>"
                                pattern="[a-z0-9\-]{1,10}" />
                            <p class="description"><?php esc_html_e( 'URL üçün: yalnız kiçik hərf, rəqəm və tire (məs: az, en).', 'studyinturkey' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sit_lang_name"><?php esc_html_e( 'Ad (ingilis)', 'studyinturkey' ); ?></label></th>
                        <td>
                            <input name="name" id="sit_lang_name" type="text" class="regular-text" required
                                value="<?php echo $item ? esc_attr( $item->name ) : ''; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sit_lang_native"><?php esc_html_e( 'Yerli ad', 'studyinturkey' ); ?></label></th>
                        <td>
                            <input name="native_name" id="sit_lang_native" type="text" class="regular-text" required
                                value="<?php echo $item ? esc_attr( $item->native_name ) : ''; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sit_lang_locale"><?php esc_html_e( 'Locale', 'studyinturkey' ); ?></label></th>
                        <td>
                            <input name="locale" id="sit_lang_locale" type="text" class="regular-text" required maxlength="20"
                                value="<?php echo $item ? esc_attr( $item->locale ) : ''; ?>" placeholder="az_AZ" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'İstiqamət', 'studyinturkey' ); ?></th>
                        <td>
                            <fieldset>
                                <label><input type="radio" name="direction" value="ltr" <?php checked( ! $item || 'ltr' === $item->direction ); ?> /> LTR</label><br />
                                <label><input type="radio" name="direction" value="rtl" <?php checked( $item && 'rtl' === $item->direction ); ?> /> RTL</label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sit_lang_flag"><?php esc_html_e( 'Bayraq (emoji)', 'studyinturkey' ); ?></label></th>
                        <td>
                            <input name="flag" id="sit_lang_flag" type="text" class="regular-text" maxlength="10"
                                value="<?php echo $item ? esc_attr( (string) $item->flag ) : ''; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sit_lang_sort"><?php esc_html_e( 'Sıra', 'studyinturkey' ); ?></label></th>
                        <td>
                            <input name="sort_order" id="sit_lang_sort" type="number" class="small-text" min="0" step="1"
                                value="<?php echo $item ? esc_attr( (string) $item->sort_order ) : '0'; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Aktiv', 'studyinturkey' ); ?></th>
                        <td>
                            <label>
                                <input name="is_active" type="checkbox" value="1" <?php checked( ! $item || (int) $item->is_active === 1 ); ?> />
                                <?php esc_html_e( 'Saytda istifadə olunsun', 'studyinturkey' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Default dil', 'studyinturkey' ); ?></th>
                        <td>
                            <label>
                                <input name="is_default" type="checkbox" value="1" <?php checked( $item && (int) $item->is_default === 1 ); ?> />
                                <?php esc_html_e( 'Əsas dil (yalnız biri)', 'studyinturkey' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <?php
                submit_button( $is_edit ? __( 'Yenilə', 'studyinturkey' ) : __( 'Əlavə et', 'studyinturkey' ) );
                ?>
                <p>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ); ?>"><?php esc_html_e( '← Dillərə qayıt', 'studyinturkey' ); ?></a>
                </p>
            </form>
        </div>
        <?php
    }
}
