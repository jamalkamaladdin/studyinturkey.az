<?php
/**
 * URL routing: /{lang}/ prefix, permalink filters, hreflang, root redirect.
 */

defined( 'ABSPATH' ) || exit;

class SIT_Rewrite {

    public static function init(): void {
        add_action( 'init', [ __CLASS__, 'early_set_current_lang' ], 0 );
        add_filter( 'do_parse_request', [ __CLASS__, 'strip_lang_prefix' ], 1, 2 );
        add_filter( 'locale', [ __CLASS__, 'filter_locale' ], 10, 1 );
        add_filter( 'language_attributes', [ __CLASS__, 'filter_language_attributes' ], 20, 1 );
        add_filter( 'body_class', [ __CLASS__, 'filter_body_class' ], 10, 1 );

        add_filter( 'post_link', [ __CLASS__, 'filter_post_link' ], 10, 2 );
        add_filter( 'page_link', [ __CLASS__, 'filter_page_link' ], 10, 2 );
        add_filter( 'post_type_link', [ __CLASS__, 'filter_post_type_link' ], 10, 2 );
        add_filter( 'term_link', [ __CLASS__, 'filter_term_link' ], 10, 3 );
        add_filter( 'attachment_link', [ __CLASS__, 'filter_attachment_link' ], 10, 2 );
        add_filter( 'feed_link', [ __CLASS__, 'filter_feed_link' ], 10, 2 );
        add_filter( 'post_type_archive_link', [ __CLASS__, 'filter_post_type_archive_link' ], 10, 2 );
        add_filter( 'home_url', [ __CLASS__, 'filter_home_url' ], 10, 4 );

        add_filter( 'redirect_canonical', [ __CLASS__, 'filter_redirect_canonical' ], 10, 2 );

        add_action( 'wp_head', [ __CLASS__, 'output_hreflang' ], 1 );
    }

    /**
     * Front-end-də routing tətbiq olunmasın.
     */
    public static function should_bypass_routing(): bool {
        if ( is_admin() ) {
            return true;
        }
        if ( wp_doing_ajax() ) {
            return true;
        }
        if ( wp_doing_cron() ) {
            return true;
        }
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return true;
        }
        if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
            return true;
        }
        if ( defined( 'WP_CLI' ) && constant( 'WP_CLI' ) ) {
            return true;
        }
        return (bool) apply_filters( 'sit_multilang_bypass_routing', false );
    }

    /**
     * İlk path seqmenti sistem üçün rezervdir (dil prefiksi kimi qəbul edilmir).
     */
    public static function is_reserved_segment( string $segment ): bool {
        $segment = strtolower( $segment );
        $reserved = [
            'wp-admin',
            'wp-login.php',
            'wp-content',
            'wp-includes',
            'wp-json',
            'xmlrpc.php',
            'robots.txt',
            'favicon.ico',
            '.well-known',
            'index.php',
        ];
        if ( in_array( $segment, $reserved, true ) ) {
            return true;
        }
        if ( is_string( $segment ) && str_starts_with( $segment, 'wp-' ) ) {
            return true;
        }
        return (bool) apply_filters( 'sit_multilang_reserved_path_segment', false, $segment );
    }

    /**
     * Home path-dən sonrakı path (subdir çıxarılmış), slashsız.
     * Məs: /subdir/az/hello -> az/hello, / -> ''.
     */
    public static function get_request_path_after_home(): string {
        $uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
        $path = (string) wp_parse_url( $uri, PHP_URL_PATH );
        $path = trim( $path, '/' );
        $home_path = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
        if ( $home_path !== '' ) {
            if ( $path === $home_path ) {
                return '';
            }
            $prefix = $home_path . '/';
            if ( str_starts_with( $path, $prefix ) ) {
                $path = substr( $path, strlen( $prefix ) );
            }
        }
        return trim( $path, '/' );
    }

    /**
     * init:0 — URL-dən dili oxu (REQUEST_URI hələ dəyişməyib).
     */
    public static function early_set_current_lang(): void {
        if ( self::should_bypass_routing() ) {
            return;
        }
        $after = self::get_request_path_after_home();
        if ( $after === '' ) {
            return;
        }
        $first = explode( '/', $after )[0];
        if ( $first && SIT_Languages::is_valid_code( $first ) ) {
            $GLOBALS['sit_current_lang'] = $first;
            $GLOBALS['current_lang']     = $first;
        }
    }

    /**
     * Dil prefiksini çıxar ki, WordPress standart parse etsin.
     *
     * @param bool  $do_parse Whether to parse the request.
     * @param WP    $wp       WP instance.
     */
    public static function strip_lang_prefix( $do_parse, $wp ) {
        if ( ! $do_parse || ! $wp instanceof WP ) {
            return $do_parse;
        }
        if ( self::should_bypass_routing() ) {
            return $do_parse;
        }

        $after = self::get_request_path_after_home();

        if ( $after === '' ) {
            $default = SIT_Languages::get_default_language_code() ?: 'az';
            wp_safe_redirect( self::build_url_with_lang( $default, '' ), 301 );
            exit;
        }

        $segments = explode( '/', $after );
        $first    = $segments[0];

        if ( self::is_reserved_segment( $first ) ) {
            return $do_parse;
        }

        if ( SIT_Languages::is_valid_code( $first ) ) {
            $GLOBALS['sit_current_lang'] = $first;
            $GLOBALS['current_lang']     = $first;
            array_shift( $segments );
            self::set_request_uri_after_home( $segments );
            return $do_parse;
        }

        $default = SIT_Languages::get_default_language_code() ?: 'az';
        wp_safe_redirect( self::build_url_with_lang( $default, $after ), 301 );
        exit;
    }

    /**
     * @param string $lang        Dil kodu.
     * @param string $after_home  Home-dan sonrakı path (dil olmadan).
     */
    public static function build_url_with_lang( string $lang, string $after_home ): string {
        $after_home = trim( $after_home, '/' );
        $path       = $lang . ( $after_home !== '' ? '/' . $after_home : '' );
        return home_url( user_trailingslashit( $path ) );
    }

    /**
     * @param string[] $segments Path seqmentləri (dil çıxarılıb).
     */
    private static function set_request_uri_after_home( array $segments ): void {
        $uri   = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
        $query = wp_parse_url( $uri, PHP_URL_QUERY );

        $new_rel   = implode( '/', array_filter( $segments ) );
        $home_path = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );

        if ( $home_path === '' ) {
            $new_path = '/' . $new_rel;
        } else {
            $new_path = '/' . $home_path . ( $new_rel !== '' ? '/' . $new_rel : '' );
        }

        if ( $new_rel === '' && $home_path !== '' ) {
            $new_path = '/' . trim( $home_path, '/' ) . '/';
        }
        if ( $new_rel === '' && $home_path === '' ) {
            $new_path = '/';
        }

        $orig_path = (string) wp_parse_url( $uri, PHP_URL_PATH );
        if ( $new_rel !== '' && str_ends_with( $orig_path, '/' ) && ! str_ends_with( $new_path, '/' ) ) {
            $new_path .= '/';
        }

        $_SERVER['REQUEST_URI'] = $new_path . ( $query ? '?' . $query : '' );
    }

    public static function filter_locale( string $locale ): string {
        if ( self::should_bypass_routing() ) {
            return $locale;
        }
        if ( ! did_action( 'init' ) ) {
            return $locale;
        }
        return SIT_Languages::get_locale_for_code( sit_get_current_lang() );
    }

    public static function filter_language_attributes( string $output ): string {
        if ( self::should_bypass_routing() ) {
            return $output;
        }
        $code = sit_get_current_lang();
        $lang = SIT_Languages::get_language( $code );
        $wp_locale = SIT_Languages::get_locale_for_code( $code );
        $hreflang  = strtolower( str_replace( '_', '-', $wp_locale ) );
        $dir       = ( $lang && 'rtl' === $lang->direction ) ? 'rtl' : 'ltr';

        $output = preg_replace( '/lang="[^"]*"/i', 'lang="' . esc_attr( $hreflang ) . '"', $output );
        if ( ! preg_match( '/\sdir="/i', $output ) ) {
            $output .= ' dir="' . esc_attr( $dir ) . '"';
        } else {
            $output = preg_replace( '/dir="[^"]*"/i', 'dir="' . esc_attr( $dir ) . '"', $output );
        }
        return $output;
    }

    /**
     * @param string[] $classes
     * @return string[]
     */
    public static function filter_body_class( array $classes ): array {
        if ( self::should_bypass_routing() ) {
            return $classes;
        }
        $classes[] = 'sit-lang-' . sanitize_html_class( sit_get_current_lang() );
        return $classes;
    }

    /**
     * Daxili linkə cari və ya göstərilən dil prefiksi əlavə edir (köhnə prefiks əvəzlənir).
     */
    public static function localize_url( string $url, ?string $lang = null ): string {
        $lang = $lang ?? sit_get_current_lang();
        if ( '' === $url || ! SIT_Languages::is_valid_code( $lang ) ) {
            return $url;
        }

        $parsed = wp_parse_url( $url );
        if ( ! $parsed || empty( $parsed['host'] ) ) {
            return $url;
        }

        $home_parsed = wp_parse_url( home_url() );
        if ( ! $home_parsed || empty( $home_parsed['host'] ) ) {
            return $url;
        }
        if ( strtolower( $parsed['host'] ) !== strtolower( $home_parsed['host'] ) ) {
            return $url;
        }

        $path = isset( $parsed['path'] ) ? $parsed['path'] : '/';
        $home_path = isset( $home_parsed['path'] ) ? trim( (string) $home_parsed['path'], '/' ) : '';
        $path_trim = trim( $path, '/' );

        $relative = $path_trim;
        if ( $home_path !== '' ) {
            if ( $path_trim === $home_path ) {
                $relative = '';
            } elseif ( str_starts_with( $path_trim, $home_path . '/' ) ) {
                $relative = substr( $path_trim, strlen( $home_path ) + 1 );
            } else {
                return $url;
            }
        }

        $segments = ( '' === $relative ) ? [] : explode( '/', $relative );
        if ( ! empty( $segments[0] ) && SIT_Languages::is_valid_code( $segments[0] ) ) {
            array_shift( $segments );
        }
        $tail = implode( '/', $segments );

        $mid       = $lang . ( $tail !== '' ? '/' . $tail : '' );
        $full_path = '/' . ( $home_path !== '' ? $home_path . '/' : '' ) . $mid;
        $full_path = str_replace( '//', '/', $full_path );
        $full_path = user_trailingslashit( $full_path );

        $scheme   = isset( $parsed['scheme'] ) ? $parsed['scheme'] . '://' : ( is_ssl() ? 'https://' : 'http://' );
        $port     = isset( $parsed['port'] ) ? ':' . (int) $parsed['port'] : '';
        $query    = isset( $parsed['query'] ) ? '?' . $parsed['query'] : '';
        $fragment = isset( $parsed['fragment'] ) ? '#' . $parsed['fragment'] : '';

        return $scheme . $parsed['host'] . $port . $full_path . $query . $fragment;
    }

    public static function filter_post_link( string $permalink, WP_Post $post ): string {
        if ( self::should_bypass_routing() ) {
            return $permalink;
        }
        return self::localize_url( $permalink );
    }

    public static function filter_page_link( string $link, int $post_id ): string {
        if ( self::should_bypass_routing() ) {
            return $link;
        }
        return self::localize_url( $link );
    }

    public static function filter_post_type_link( string $post_link, WP_Post $post ): string {
        if ( self::should_bypass_routing() ) {
            return $post_link;
        }
        return self::localize_url( $post_link );
    }

    public static function filter_term_link( string $termlink, $term, string $taxonomy ): string {
        if ( self::should_bypass_routing() ) {
            return $termlink;
        }
        return self::localize_url( $termlink );
    }

    public static function filter_attachment_link( string $link, int $post_id ): string {
        if ( self::should_bypass_routing() ) {
            return $link;
        }
        return self::localize_url( $link );
    }

    public static function filter_feed_link( string $feed_link, string $feed ): string {
        if ( self::should_bypass_routing() ) {
            return $feed_link;
        }
        return self::localize_url( $feed_link );
    }

    public static function filter_post_type_archive_link( string $link, string $post_type ): string {
        if ( self::should_bypass_routing() ) {
            return $link;
        }
        return self::localize_url( $link );
    }

    /**
     * @param string      $url         The complete home URL including scheme and path.
     * @param string      $path        Path relative to the home URL.
     * @param string|null $scheme      Scheme.
     * @param int|string  $blog_id     Blog ID.
     */
    public static function filter_home_url( $url, $path, $scheme, $blog_id ) {
        if ( is_admin() ) {
            return $url;
        }
        if ( self::should_bypass_routing() ) {
            return $url;
        }
        $path = $path ?? '';
        if ( '' !== $path && '/' !== $path ) {
            return $url;
        }

        $home = get_option( 'home' );
        if ( ! $home ) {
            return $url;
        }

        $lang = sit_get_current_lang();
        $base = untrailingslashit( $home );
        return trailingslashit( $base ) . user_trailingslashit( $lang . '/' );
    }

    /**
     * @param string|false $redirect_url  Redirect URL.
     * @param string       $requested     Requested URL.
     * @return string|false
     */
    public static function filter_redirect_canonical( $redirect_url, string $requested ) {
        if ( self::should_bypass_routing() ) {
            return $redirect_url;
        }
        if ( ! $redirect_url || ! is_string( $redirect_url ) ) {
            return $redirect_url;
        }
        $lang = $GLOBALS['sit_current_lang'] ?? '';
        if ( ! $lang || ! SIT_Languages::is_valid_code( $lang ) ) {
            return $redirect_url;
        }
        return self::localize_url( $redirect_url, $lang );
    }

    public static function output_hreflang(): void {
        if ( self::should_bypass_routing() ) {
            return;
        }
        if ( ! is_singular() && ! is_front_page() && ! is_home() && ! is_category() && ! is_tag() && ! is_tax() ) {
            return;
        }

        $default = SIT_Languages::get_default_language_code() ?: 'az';
        $urls    = [];

        if ( is_singular() ) {
            $id = get_queried_object_id();
            foreach ( SIT_Languages::get_active_languages() as $lang ) {
                $permalink = get_permalink( $id );
                if ( ! $permalink ) {
                    continue;
                }
                $urls[ $lang->code ] = self::localize_url( $permalink, $lang->code );
            }
        } elseif ( is_front_page() || is_home() ) {
            foreach ( SIT_Languages::get_active_languages() as $lang ) {
                $urls[ $lang->code ] = self::build_url_with_lang( $lang->code, '' );
            }
        } elseif ( is_category() || is_tag() || is_tax() ) {
            $term = get_queried_object();
            if ( $term instanceof WP_Term ) {
                $link = get_term_link( $term );
                if ( is_wp_error( $link ) ) {
                    return;
                }
                foreach ( SIT_Languages::get_active_languages() as $lang ) {
                    $urls[ $lang->code ] = self::localize_url( $link, $lang->code );
                }
            }
        }

        if ( empty( $urls ) ) {
            return;
        }

        foreach ( SIT_Languages::get_active_languages() as $lang ) {
            if ( empty( $urls[ $lang->code ] ) ) {
                continue;
            }
            $hreflang = strtolower( str_replace( '_', '-', $lang->locale ) );
            printf(
                '<link rel="alternate" hreflang="%s" href="%s" />' . "\n",
                esc_attr( $hreflang ),
                esc_url( $urls[ $lang->code ] )
            );
        }

        if ( ! empty( $urls[ $default ] ) ) {
            printf(
                '<link rel="alternate" hreflang="x-default" href="%s" />' . "\n",
                esc_url( $urls[ $default ] )
            );
        }
    }
}
