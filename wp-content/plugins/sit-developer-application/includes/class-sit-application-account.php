<?php
/**
 * Qeydiyyat, giriş və namizəd portalı (shortcode-lar).
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Application_Account {

    public const META_PHONE = 'sit_applicant_phone';

    public const ACTION_REGISTER = 'sit_app_register';
    public const ACTION_LOGIN    = 'sit_app_login';

    public const NONCE_REGISTER = 'sit_app_register_nonce';
    public const NONCE_LOGIN    = 'sit_app_login_nonce';

    public static function register(): void {
        add_action( 'init', [ __CLASS__, 'register_post_handlers' ], 8 );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
        add_shortcode( 'sit_auth_register', [ __CLASS__, 'shortcode_register' ] );
        add_shortcode( 'sit_auth_login', [ __CLASS__, 'shortcode_login' ] );
        add_shortcode( 'sit_applicant_portal', [ __CLASS__, 'shortcode_portal' ] );
    }

    public static function register_post_handlers(): void {
        add_action( 'admin_post_nopriv_' . self::ACTION_REGISTER, [ __CLASS__, 'handle_register' ] );
        add_action( 'admin_post_' . self::ACTION_REGISTER, [ __CLASS__, 'handle_register_logged_in' ] );
        add_action( 'admin_post_nopriv_' . self::ACTION_LOGIN, [ __CLASS__, 'handle_login' ] );
        add_action( 'admin_post_' . self::ACTION_LOGIN, [ __CLASS__, 'handle_login' ] );
    }

    public static function enqueue_assets(): void {
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) ) {
            return;
        }

        $tags = [ 'sit_auth_register', 'sit_auth_login', 'sit_applicant_portal' ];
        foreach ( $tags as $tag ) {
            if ( has_shortcode( $post->post_content, $tag ) ) {
                wp_enqueue_style(
                    'sit-application-account',
                    SIT_APPLICATION_URL . 'assets/css/sit-application-account.css',
                    [],
                    SIT_APPLICATION_VERSION
                );
                return;
            }
        }
    }

    /**
     * @return array{login: string, register: string, portal: string}
     */
    public static function default_urls(): array {
        $login = wp_login_url();

        return apply_filters(
            'sit_application_account_urls',
            [
                'login'    => $login,
                'register' => wp_registration_url() ? wp_registration_url() : home_url( '/' ),
                'portal'   => home_url( '/' ),
            ]
        );
    }

    public static function handle_register_logged_in(): void {
        $redirect = self::get_redirect_from_request();
        wp_safe_redirect( $redirect );
        exit;
    }

    public static function handle_register(): void {
        $redirect = self::get_redirect_from_request();

        if ( is_user_logged_in() ) {
            wp_safe_redirect( $redirect );
            exit;
        }

        if ( ! isset( $_POST[ self::NONCE_REGISTER ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_REGISTER ] ) ), self::ACTION_REGISTER ) ) {
            self::redirect_register_error( $redirect, [ __( 'Təhlükəsizlik yoxlaması uğursuz oldu.', 'studyinturkey' ) ] );
        }

        $name     = isset( $_POST['sit_reg_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sit_reg_name'] ) ) : '';
        $email    = isset( $_POST['sit_reg_email'] ) ? sanitize_email( wp_unslash( $_POST['sit_reg_email'] ) ) : '';
        $phone    = isset( $_POST['sit_reg_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['sit_reg_phone'] ) ) : '';
        $pass     = isset( $_POST['sit_reg_password'] ) ? (string) wp_unslash( $_POST['sit_reg_password'] ) : '';
        $pass2    = isset( $_POST['sit_reg_password2'] ) ? (string) wp_unslash( $_POST['sit_reg_password2'] ) : '';
        $remember = ! empty( $_POST['sit_reg_remember'] );

        $errors = [];

        if ( '' === $name || strlen( $name ) < 2 ) {
            $errors[] = __( 'Tam adınızı daxil edin.', 'studyinturkey' );
        }

        if ( ! is_email( $email ) ) {
            $errors[] = __( 'Etibarlı e-poçt ünvanı daxil edin.', 'studyinturkey' );
        }

        if ( '' === $phone || strlen( $phone ) < 5 ) {
            $errors[] = __( 'Telefon nömrəsini daxil edin.', 'studyinturkey' );
        }

        $min_len = (int) apply_filters( 'sit_application_password_min_length', 8 );
        if ( strlen( $pass ) < $min_len ) {
            /* translators: %d: minimum password length */
            $errors[] = sprintf( __( 'Şifrə ən azı %d simvol olmalıdır.', 'studyinturkey' ), $min_len );
        }

        if ( $pass !== $pass2 ) {
            $errors[] = __( 'Şifrələr uyğun gəlmir.', 'studyinturkey' );
        }

        if ( email_exists( $email ) ) {
            $errors[] = __( 'Bu e-poçt ünvanı ilə artıq hesab mövcuddur.', 'studyinturkey' );
        }

        $user_login = sanitize_user( $email, false );
        if ( '' === $user_login ) {
            $errors[] = __( 'İstifadəçi adı yaradıla bilmədi.', 'studyinturkey' );
        } elseif ( username_exists( $user_login ) ) {
            $errors[] = __( 'Bu istifadəçi adı artıq götürülüb.', 'studyinturkey' );
        }

        if ( ! empty( $errors ) ) {
            self::redirect_register_error(
                $redirect,
                $errors,
                [
                    'sit_reg_name'  => $name,
                    'sit_reg_email' => $email,
                    'sit_reg_phone' => $phone,
                ]
            );
        }

        $user_id = wp_insert_user(
            [
                'user_login'   => $user_login,
                'user_pass'    => $pass,
                'user_email'   => $email,
                'display_name' => $name,
                'role'         => 'subscriber',
            ]
        );

        if ( is_wp_error( $user_id ) ) {
            self::redirect_register_error(
                $redirect,
                [ $user_id->get_error_message() ],
                [
                    'sit_reg_name'  => $name,
                    'sit_reg_email' => $email,
                    'sit_reg_phone' => $phone,
                ]
            );
        }

        update_user_meta( (int) $user_id, self::META_PHONE, $phone );

        wp_signon(
            [
                'user_login'    => $user_login,
                'user_password' => $pass,
                'remember'      => $remember,
            ],
            is_ssl()
        );

        $success_to = $redirect;
        if ( isset( $_POST['sit_reg_portal'] ) ) {
            $success_to = wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['sit_reg_portal'] ) ), $redirect );
        }

        wp_safe_redirect( add_query_arg( 'sit_auth', 'registered', $success_to ) );
        exit;
    }

    public static function handle_login(): void {
        $redirect = self::get_redirect_from_request();

        if ( ! isset( $_POST[ self::NONCE_LOGIN ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_LOGIN ] ) ), self::ACTION_LOGIN ) ) {
            self::redirect_login_error( $redirect, [ __( 'Təhlükəsizlik yoxlaması uğursuz oldu.', 'studyinturkey' ) ] );
        }

        $login    = isset( $_POST['sit_login_user'] ) ? sanitize_text_field( wp_unslash( $_POST['sit_login_user'] ) ) : '';
        $password = isset( $_POST['sit_login_password'] ) ? (string) wp_unslash( $_POST['sit_login_password'] ) : '';
        $remember = ! empty( $_POST['sit_login_remember'] );

        if ( '' === $login || '' === $password ) {
            self::redirect_login_error( $redirect, [ __( 'E-poçt və şifrə daxil edin.', 'studyinturkey' ) ] );
        }

        $user = wp_signon(
            [
                'user_login'    => $login,
                'user_password' => $password,
                'remember'      => $remember,
            ],
            is_ssl()
        );

        if ( is_wp_error( $user ) ) {
            self::redirect_login_error( $redirect, [ __( 'Giriş uğursuz oldu. Məlumatları yoxlayın.', 'studyinturkey' ) ] );
        }

        wp_safe_redirect( add_query_arg( 'sit_auth', 'loggedin', $redirect ) );
        exit;
    }

    /**
     * @param array<string, string> $atts
     */
    public static function shortcode_register( $atts ): string {
        $defaults = self::default_urls();
        $atts     = shortcode_atts(
            [
                'login_url'  => $defaults['login'],
                'portal_url' => $defaults['portal'],
            ],
            $atts,
            'sit_auth_register'
        );

        if ( is_user_logged_in() ) {
            ob_start();
            if ( self::has_auth_success( 'registered' ) ) {
                ?>
                <div class="sit-app-account-notice sit-app-account-notice--success" role="status">
                    <?php esc_html_e( 'Hesab yaradıldı və daxil oldunuz.', 'studyinturkey' ); ?>
                </div>
                <?php
            }
            ?>
            <div class="sit-app-account-notice sit-app-account-notice--info">
                <?php esc_html_e( 'Siz artıq daxil olmusunuz.', 'studyinturkey' ); ?>
                <a href="<?php echo esc_url( $atts['portal_url'] ); ?>"><?php esc_html_e( 'Müraciətlərim', 'studyinturkey' ); ?></a>
            </div>
            <?php
            return (string) ob_get_clean();
        }

        $flash = self::consume_register_flash();
        $old   = $flash['old'];

        $action = esc_url( admin_url( 'admin-post.php' ) );
        ob_start();
        ?>
        <div class="sit-app-account sit-app-account--register">
            <?php if ( ! empty( $flash['errors'] ) ) : ?>
                <div class="sit-app-account-notice sit-app-account-notice--error" role="alert">
                    <ul class="sit-app-account-error-list">
                        <?php foreach ( $flash['errors'] as $err ) : ?>
                            <li><?php echo esc_html( $err ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="sit-app-account-form" method="post" action="<?php echo esc_url( $action ); ?>" autocomplete="off">
                <input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION_REGISTER ); ?>" />
                <input type="hidden" name="sit_reg_redirect" value="<?php echo esc_attr( wp_validate_redirect( self::current_page_url(), home_url( '/' ) ) ); ?>" />
                <input type="hidden" name="sit_reg_portal" value="<?php echo esc_attr( wp_validate_redirect( $atts['portal_url'], home_url( '/' ) ) ); ?>" />
                <?php wp_nonce_field( self::ACTION_REGISTER, self::NONCE_REGISTER, true, false ); ?>

                <p class="sit-app-form-field">
                    <label for="sit_reg_name"><?php esc_html_e( 'Tam ad', 'studyinturkey' ); ?> <span class="required">*</span></label>
                    <input type="text" id="sit_reg_name" name="sit_reg_name" required maxlength="191" value="<?php echo isset( $old['sit_reg_name'] ) ? esc_attr( (string) $old['sit_reg_name'] ) : ''; ?>" />
                </p>
                <p class="sit-app-form-field">
                    <label for="sit_reg_email"><?php esc_html_e( 'E-poçt', 'studyinturkey' ); ?> <span class="required">*</span></label>
                    <input type="email" id="sit_reg_email" name="sit_reg_email" required maxlength="191" value="<?php echo isset( $old['sit_reg_email'] ) ? esc_attr( sanitize_email( (string) $old['sit_reg_email'] ) ) : ''; ?>" />
                </p>
                <p class="sit-app-form-field">
                    <label for="sit_reg_phone"><?php esc_html_e( 'Telefon', 'studyinturkey' ); ?> <span class="required">*</span></label>
                    <input type="tel" id="sit_reg_phone" name="sit_reg_phone" required maxlength="50" value="<?php echo isset( $old['sit_reg_phone'] ) ? esc_attr( (string) $old['sit_reg_phone'] ) : ''; ?>" />
                </p>
                <p class="sit-app-form-field">
                    <label for="sit_reg_password"><?php esc_html_e( 'Şifrə', 'studyinturkey' ); ?> <span class="required">*</span></label>
                    <input type="password" id="sit_reg_password" name="sit_reg_password" required autocomplete="new-password" minlength="8" />
                </p>
                <p class="sit-app-form-field">
                    <label for="sit_reg_password2"><?php esc_html_e( 'Şifrə (təkrar)', 'studyinturkey' ); ?> <span class="required">*</span></label>
                    <input type="password" id="sit_reg_password2" name="sit_reg_password2" required autocomplete="new-password" minlength="8" />
                </p>
                <p class="sit-app-form-field sit-app-form-field--checkbox">
                    <label>
                        <input type="checkbox" name="sit_reg_remember" value="1" checked />
                        <?php esc_html_e( 'Məni xatırla', 'studyinturkey' ); ?>
                    </label>
                </p>
                <p class="sit-app-form-submit">
                    <button type="submit" class="sit-app-form-button"><?php esc_html_e( 'Qeydiyyat', 'studyinturkey' ); ?></button>
                </p>
                <p class="sit-app-account-switch">
                    <a href="<?php echo esc_url( $atts['login_url'] ); ?>"><?php esc_html_e( 'Artıq hesabınız var? Giriş', 'studyinturkey' ); ?></a>
                </p>
            </form>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * @param array<string, string> $atts
     */
    public static function shortcode_login( $atts ): string {
        $defaults = self::default_urls();
        $atts     = shortcode_atts(
            [
                'register_url' => $defaults['register'],
                'portal_url'   => $defaults['portal'],
                'redirect_to'  => '',
            ],
            $atts,
            'sit_auth_login'
        );

        if ( is_user_logged_in() ) {
            ob_start();
            if ( self::has_auth_success( 'loggedin' ) ) {
                ?>
                <div class="sit-app-account-notice sit-app-account-notice--success" role="status">
                    <?php esc_html_e( 'Uğurla daxil oldunuz.', 'studyinturkey' ); ?>
                </div>
                <?php
            }
            ?>
            <div class="sit-app-account-notice sit-app-account-notice--info">
                <?php esc_html_e( 'Siz daxil olmusunuz.', 'studyinturkey' ); ?>
                <a href="<?php echo esc_url( $atts['portal_url'] ); ?>"><?php esc_html_e( 'Müraciətlərim', 'studyinturkey' ); ?></a>
            </div>
            <?php
            return (string) ob_get_clean();
        }

        $flash = self::consume_login_flash();

        $hidden_redirect = '' !== $atts['redirect_to'] ? $atts['redirect_to'] : $atts['portal_url'];
        $hidden_redirect = wp_validate_redirect( $hidden_redirect, home_url( '/' ) );

        $action = esc_url( admin_url( 'admin-post.php' ) );
        ob_start();
        ?>
        <div class="sit-app-account sit-app-account--login">
            <?php if ( ! empty( $flash['errors'] ) ) : ?>
                <div class="sit-app-account-notice sit-app-account-notice--error" role="alert">
                    <ul class="sit-app-account-error-list">
                        <?php foreach ( $flash['errors'] as $err ) : ?>
                            <li><?php echo esc_html( $err ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="sit-app-account-form" method="post" action="<?php echo esc_url( $action ); ?>" autocomplete="off">
                <input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION_LOGIN ); ?>" />
                <input type="hidden" name="sit_login_redirect" value="<?php echo esc_attr( $hidden_redirect ); ?>" />
                <?php wp_nonce_field( self::ACTION_LOGIN, self::NONCE_LOGIN, true, false ); ?>

                <p class="sit-app-form-field">
                    <label for="sit_login_user"><?php esc_html_e( 'E-poçt (istifadəçi adı)', 'studyinturkey' ); ?> <span class="required">*</span></label>
                    <input type="text" id="sit_login_user" name="sit_login_user" required autocomplete="username" />
                </p>
                <p class="sit-app-form-field">
                    <label for="sit_login_password"><?php esc_html_e( 'Şifrə', 'studyinturkey' ); ?> <span class="required">*</span></label>
                    <input type="password" id="sit_login_password" name="sit_login_password" required autocomplete="current-password" />
                </p>
                <p class="sit-app-form-field sit-app-form-field--checkbox">
                    <label>
                        <input type="checkbox" name="sit_login_remember" value="1" checked />
                        <?php esc_html_e( 'Məni xatırla', 'studyinturkey' ); ?>
                    </label>
                </p>
                <p class="sit-app-form-submit">
                    <button type="submit" class="sit-app-form-button"><?php esc_html_e( 'Daxil ol', 'studyinturkey' ); ?></button>
                </p>
                <p class="sit-app-account-switch">
                    <a href="<?php echo esc_url( $atts['register_url'] ); ?>"><?php esc_html_e( 'Hesab yoxdur? Qeydiyyat', 'studyinturkey' ); ?></a>
                </p>
            </form>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * @param array<string, string> $atts
     */
    public static function shortcode_portal( $atts ): string {
        $defaults = self::default_urls();
        $atts     = shortcode_atts(
            [
                'login_url'    => $defaults['login'],
                'register_url' => $defaults['register'],
            ],
            $atts,
            'sit_applicant_portal'
        );

        wp_enqueue_style(
            'sit-application-account',
            SIT_APPLICATION_URL . 'assets/css/sit-application-account.css',
            [],
            SIT_APPLICATION_VERSION
        );

        $banner = '';
        if ( is_user_logged_in() && self::has_auth_success( 'registered' ) ) {
            $banner .= '<div class="sit-app-account-notice sit-app-account-notice--success sit-app-account--portal-banner" role="status">';
            $banner .= esc_html__( 'Hesab yaradıldı. Müraciətlərinizi buradan izləyə bilərsiniz.', 'studyinturkey' );
            $banner .= '</div>';
        }
        if ( is_user_logged_in() && self::has_auth_success( 'loggedin' ) ) {
            $banner .= '<div class="sit-app-account-notice sit-app-account-notice--success sit-app-account--portal-banner" role="status">';
            $banner .= esc_html__( 'Xoş gəldiniz.', 'studyinturkey' );
            $banner .= '</div>';
        }

        if ( ! is_user_logged_in() ) {
            ob_start();
            ?>
            <div class="sit-app-account sit-app-account--portal-guest">
                <p class="sit-app-account-notice sit-app-account-notice--info">
                    <?php esc_html_e( 'Müraciətlərinizi görmək üçün daxil olun və ya qeydiyyatdan keçin.', 'studyinturkey' ); ?>
                </p>
                <p class="sit-app-account-actions">
                    <a class="sit-app-form-button sit-app-form-button--secondary" href="<?php echo esc_url( $atts['login_url'] ); ?>"><?php esc_html_e( 'Giriş', 'studyinturkey' ); ?></a>
                    <a class="sit-app-form-button" href="<?php echo esc_url( $atts['register_url'] ); ?>"><?php esc_html_e( 'Qeydiyyat', 'studyinturkey' ); ?></a>
                </p>
            </div>
            <?php
            return $banner . (string) ob_get_clean();
        }

        $user_id = get_current_user_id();
        $base    = self::current_page_url();

        $view_id = isset( $_GET['sit_my_app'] ) ? absint( wp_unslash( $_GET['sit_my_app'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

        if ( $view_id > 0 ) {
            return $banner . self::render_application_detail( $view_id, $user_id, $base );
        }

        return $banner . self::render_application_list( $user_id, $base );
    }

    /**
     * @return array{errors: string[], old: array<string, string>}
     */
    private static function consume_register_flash(): array {
        return self::consume_flash_transient( 'sit_reg_err' );
    }

    /**
     * @return array{errors: string[], old: array<string, string>}
     */
    private static function consume_login_flash(): array {
        return self::consume_flash_transient( 'sit_login_err' );
    }

    /**
     * @return array{errors: string[], old: array<string, string>}
     */
    private static function consume_flash_transient( string $query_key ): array {
        $empty = [ 'errors' => [], 'old' => [] ];

        if ( empty( $_GET[ $query_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            return $empty;
        }

        $key = sanitize_text_field( wp_unslash( $_GET[ $query_key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
        if ( '' === $key ) {
            return $empty;
        }

        $payload = get_transient( $query_key . '_' . $key );
        delete_transient( $query_key . '_' . $key );

        if ( ! is_array( $payload ) ) {
            return $empty;
        }

        if ( isset( $payload['messages'] ) && is_array( $payload['messages'] ) ) {
            $old = [];
            if ( ! empty( $payload['fields'] ) && is_array( $payload['fields'] ) ) {
                foreach ( $payload['fields'] as $k => $v ) {
                    if ( is_string( $k ) && is_string( $v ) ) {
                        $old[ $k ] = $v;
                    }
                }
            }

            return [
                'errors' => array_map( 'wp_kses_post', $payload['messages'] ),
                'old'    => $old,
            ];
        }

        return $empty;
    }

    private static function has_auth_success( string $value ): bool {
        return isset( $_GET['sit_auth'] ) && $value === sanitize_key( wp_unslash( $_GET['sit_auth'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
    }

    /**
     * @param string[]              $messages
     * @param array<string, string> $fields
     */
    private static function redirect_register_error( string $redirect, array $messages, array $fields = [] ): void {
        $key = wp_generate_password( 12, false, false );
        set_transient(
            'sit_reg_err_' . $key,
            [
                'messages' => $messages,
                'fields'   => $fields,
            ],
            120
        );
        wp_safe_redirect( add_query_arg( 'sit_reg_err', $key, $redirect ) );
        exit;
    }

    /**
     * @param string[] $messages
     */
    private static function redirect_login_error( string $redirect, array $messages ): void {
        $key = wp_generate_password( 12, false, false );
        set_transient(
            'sit_login_err_' . $key,
            [
                'messages' => $messages,
                'fields'   => [],
            ],
            120
        );
        wp_safe_redirect( add_query_arg( 'sit_login_err', $key, $redirect ) );
        exit;
    }

    private static function get_redirect_from_request(): string {
        if ( isset( $_POST['sit_reg_redirect'] ) ) {
            return wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['sit_reg_redirect'] ) ), home_url( '/' ) );
        }
        if ( isset( $_POST['sit_login_redirect'] ) ) {
            return wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['sit_login_redirect'] ) ), home_url( '/' ) );
        }
        return self::current_page_url();
    }

    private static function current_page_url(): string {
        global $post;
        if ( $post instanceof WP_Post ) {
            $link = get_permalink( $post );
            if ( $link ) {
                return $link;
            }
        }
        if ( is_singular() ) {
            $link = get_permalink();
            if ( $link ) {
                return $link;
            }
        }
        $url = wp_get_referer();
        return $url ? wp_validate_redirect( esc_url_raw( $url ), home_url( '/' ) ) : home_url( '/' );
    }

    private static function render_application_list( int $user_id, string $base_url ): string {
        $rows = SIT_Application_Queries::get_applications_by_user_id( $user_id );

        ob_start();
        ?>
        <div class="sit-app-account sit-app-account--portal">
            <div class="sit-app-portal-header">
                <h2 class="sit-app-portal-title"><?php esc_html_e( 'Müraciətlərim', 'studyinturkey' ); ?></h2>
                <p class="sit-app-portal-user">
                    <?php
                    $u = wp_get_current_user();
                    echo esc_html( sprintf( /* translators: %s: display name */ __( 'Salam, %s', 'studyinturkey' ), $u->display_name ) );
                    ?>
                    <span class="sit-app-portal-sep">·</span>
                    <a href="<?php echo esc_url( wp_logout_url( $base_url ) ); ?>"><?php esc_html_e( 'Çıxış', 'studyinturkey' ); ?></a>
                </p>
            </div>

            <?php if ( empty( $rows ) ) : ?>
                <p class="sit-app-account-empty"><?php esc_html_e( 'Hələ müraciətiniz yoxdur.', 'studyinturkey' ); ?></p>
            <?php else : ?>
                <div class="sit-app-portal-table-wrap">
                    <table class="sit-app-portal-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( '№', 'studyinturkey' ); ?></th>
                                <th><?php esc_html_e( 'Proqram', 'studyinturkey' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'studyinturkey' ); ?></th>
                                <th><?php esc_html_e( 'Tarix', 'studyinturkey' ); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $rows as $row ) : ?>
                                <?php
                                $id   = isset( $row['id'] ) ? (int) $row['id'] : 0;
                                $pid  = isset( $row['program_id'] ) ? (int) $row['program_id'] : 0;
                                $prog = $pid ? get_the_title( $pid ) : '';
                                if ( '' === $prog ) {
                                    $prog = __( '(Proqram tapılmadı)', 'studyinturkey' );
                                }
                                $status = isset( $row['status'] ) ? (string) $row['status'] : '';
                                $created = isset( $row['created_at'] ) ? (string) $row['created_at'] : '';
                                $detail  = esc_url( add_query_arg( 'sit_my_app', $id, $base_url ) );
                                ?>
                                <tr>
                                    <td data-label="<?php esc_attr_e( '№', 'studyinturkey' ); ?>"><?php echo esc_html( (string) $id ); ?></td>
                                    <td data-label="<?php esc_attr_e( 'Proqram', 'studyinturkey' ); ?>"><?php echo esc_html( $prog ); ?></td>
                                    <td data-label="<?php esc_attr_e( 'Status', 'studyinturkey' ); ?>"><?php echo esc_html( self::status_label( $status ) ); ?></td>
                                    <td data-label="<?php esc_attr_e( 'Tarix', 'studyinturkey' ); ?>"><?php echo esc_html( self::format_datetime_local( $created ) ); ?></td>
                                    <td><a href="<?php echo esc_url( $detail ); ?>"><?php esc_html_e( 'Detallar', 'studyinturkey' ); ?></a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    private static function render_application_detail( int $application_id, int $user_id, string $base_url ): string {
        $app = SIT_Application_Queries::get_application_for_user( $application_id, $user_id );

        ob_start();

        if ( ! $app ) {
            ?>
            <div class="sit-app-account sit-app-account--portal">
                <div class="sit-app-account-notice sit-app-account-notice--error" role="alert">
                    <?php esc_html_e( 'Bu müraciət tapılmadı və ya sizə aid deyil.', 'studyinturkey' ); ?>
                </div>
                <p><a href="<?php echo esc_url( $base_url ); ?>"><?php esc_html_e( '← Müraciətlərə qayıt', 'studyinturkey' ); ?></a></p>
            </div>
            <?php
            return (string) ob_get_clean();
        }

        $pid  = isset( $app['program_id'] ) ? (int) $app['program_id'] : 0;
        $prog = $pid ? get_the_title( $pid ) : __( '(Proqram tapılmadı)', 'studyinturkey' );
        $docs = SIT_Application_Queries::get_documents_by_application_id( $application_id );

        ?>
        <div class="sit-app-account sit-app-account--portal sit-app-account--detail">
            <p class="sit-app-portal-back"><a href="<?php echo esc_url( $base_url ); ?>"><?php esc_html_e( '← Müraciətlərə qayıt', 'studyinturkey' ); ?></a></p>

            <h2 class="sit-app-portal-title"><?php esc_html_e( 'Müraciət detalları', 'studyinturkey' ); ?></h2>

            <dl class="sit-app-detail-list">
                <dt><?php esc_html_e( 'Nömrə', 'studyinturkey' ); ?></dt>
                <dd><?php echo esc_html( (string) $application_id ); ?></dd>
                <dt><?php esc_html_e( 'Proqram', 'studyinturkey' ); ?></dt>
                <dd><?php echo esc_html( $prog ); ?></dd>
                <dt><?php esc_html_e( 'Status', 'studyinturkey' ); ?></dt>
                <dd><?php echo esc_html( self::status_label( isset( $app['status'] ) ? (string) $app['status'] : '' ) ); ?></dd>
                <dt><?php esc_html_e( 'Göndərilmə tarixi', 'studyinturkey' ); ?></dt>
                <dd><?php echo esc_html( self::format_datetime_local( isset( $app['created_at'] ) ? (string) $app['created_at'] : '' ) ); ?></dd>
                <?php if ( ! empty( $app['applicant_message'] ) ) : ?>
                    <dt><?php esc_html_e( 'Qeyd', 'studyinturkey' ); ?></dt>
                    <dd><?php echo esc_html( (string) $app['applicant_message'] ); ?></dd>
                <?php endif; ?>
            </dl>

            <h3 class="sit-app-detail-subtitle"><?php esc_html_e( 'Yüklənmiş sənədlər', 'studyinturkey' ); ?></h3>
            <?php if ( empty( $docs ) ) : ?>
                <p class="sit-app-account-empty"><?php esc_html_e( 'Sənəd qeydi yoxdur.', 'studyinturkey' ); ?></p>
            <?php else : ?>
                <ul class="sit-app-detail-docs">
                    <?php foreach ( $docs as $doc ) : ?>
                        <?php
                        $type = isset( $doc['document_type'] ) ? (string) $doc['document_type'] : '';
                        $fn   = isset( $doc['file_name'] ) ? (string) $doc['file_name'] : '';
                        ?>
                        <li>
                            <strong><?php echo esc_html( self::document_type_label( $type ) ); ?></strong>
                            — <?php echo esc_html( $fn ); ?>
                            <?php if ( ! empty( $doc['file_size'] ) ) : ?>
                                <span class="sit-app-doc-meta">(<?php echo esc_html( size_format( (int) $doc['file_size'] ) ); ?>)</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <p class="sit-app-portal-user">
                <a href="<?php echo esc_url( wp_logout_url( $base_url ) ); ?>"><?php esc_html_e( 'Çıxış', 'studyinturkey' ); ?></a>
            </p>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    public static function status_label( string $status ): string {
        $map = apply_filters(
            'sit_application_status_labels',
            [
                'pending'      => __( 'Gözləmədə', 'studyinturkey' ),
                'under_review' => __( 'İncələnir', 'studyinturkey' ),
                'processing'   => __( 'Prosesdə', 'studyinturkey' ),
                'approved'     => __( 'Təsdiqləndi', 'studyinturkey' ),
                'rejected'     => __( 'Rədd edildi', 'studyinturkey' ),
            ]
        );

        if ( isset( $map[ $status ] ) ) {
            return $map[ $status ];
        }

        return $status ? $status : __( 'Naməlum', 'studyinturkey' );
    }

    public static function document_type_label( string $type ): string {
        $map = [
            'passport'           => __( 'Pasport', 'studyinturkey' ),
            'transcript'         => __( 'Transkript', 'studyinturkey' ),
            'photo'              => __( 'Pasport formatlı foto', 'studyinturkey' ),
            'secondary_diploma'  => __( 'Orta təhsil diplomu / şəhadətnamə', 'studyinturkey' ),
            'diploma_prior'      => __( 'Əvvəlki dərəcə diplomu', 'studyinturkey' ),
            'cv'                 => __( 'CV', 'studyinturkey' ),
            'motivation_letter'  => __( 'Motivasiya məktubu', 'studyinturkey' ),
            'language_cert'      => __( 'Dil sertifikatı', 'studyinturkey' ),
        ];

        return $map[ $type ] ?? $type;
    }

    private static function format_datetime_local( string $mysql_datetime ): string {
        if ( '' === $mysql_datetime ) {
            return '—';
        }

        $formatted = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $mysql_datetime, true );

        return is_string( $formatted ) && '' !== $formatted ? $formatted : $mysql_datetime;
    }
}
