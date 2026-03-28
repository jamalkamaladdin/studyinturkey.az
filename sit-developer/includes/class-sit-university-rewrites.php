<?php
/**
 * Universitet alt səhifələri: yataqxanalar siyahısı, tək kampus, qəbul tələbləri.
 *
 * URL nümunələri (dil prefiksi sit-multilang tərəfindən ayrıca):
 * universitetler/{slug}/yataqxanalar/
 * universitetler/{slug}/kampus/{campus-slug}/
 * universitetler/{slug}/qebul-telablari/
 */

defined( 'ABSPATH' ) || exit;

final class SIT_University_Rewrites {

    public const QUERY_VIEW = 'sit_univ_view';

    public const QUERY_CAMPUS_SLUG = 'sit_campus_slug';

    public const VIEW_DORMITORIES = 'dormitories';

    public const VIEW_CAMPUS = 'campus';

    public const VIEW_ADMISSION = 'admission';

    public static function register(): void {
        add_filter( 'query_vars', [ __CLASS__, 'query_vars' ] );
        add_action( 'init', [ __CLASS__, 'add_rules' ], 20 );
        add_action( 'init', [ __CLASS__, 'maybe_flush_rewrites' ], 999 );
    }

    /**
     * @param string[] $vars Vars.
     * @return string[]
     */
    public static function query_vars( array $vars ): array {
        $vars[] = self::QUERY_VIEW;
        $vars[] = self::QUERY_CAMPUS_SLUG;
        return $vars;
    }

    public static function add_rules(): void {
        if ( ! post_type_exists( SIT_University_CPT::POST_TYPE ) ) {
            return;
        }
        $slug = self::get_university_rewrite_slug();
        add_rewrite_rule(
            '^' . preg_quote( $slug, '/' ) . '/([^/]+)/yataqxanalar/?$',
            'index.php?post_type=' . SIT_University_CPT::POST_TYPE . '&name=$matches[1]&' . self::QUERY_VIEW . '=' . self::VIEW_DORMITORIES,
            'top'
        );
        add_rewrite_rule(
            '^' . preg_quote( $slug, '/' ) . '/([^/]+)/qebul-telablari/?$',
            'index.php?post_type=' . SIT_University_CPT::POST_TYPE . '&name=$matches[1]&' . self::QUERY_VIEW . '=' . self::VIEW_ADMISSION,
            'top'
        );
        add_rewrite_rule(
            '^' . preg_quote( $slug, '/' ) . '/([^/]+)/kampus/([^/]+)/?$',
            'index.php?post_type=' . SIT_University_CPT::POST_TYPE . '&name=$matches[1]&' . self::QUERY_VIEW . '=' . self::VIEW_CAMPUS . '&' . self::QUERY_CAMPUS_SLUG . '=$matches[2]',
            'top'
        );
    }

    /**
     * CPT rewrite slug (dil dəyişəndə eyni qalır).
     */
    public static function get_university_rewrite_slug(): string {
        $pto = get_post_type_object( SIT_University_CPT::POST_TYPE );
        if ( $pto && ! empty( $pto->rewrite['slug'] ) && is_string( $pto->rewrite['slug'] ) ) {
            return $pto->rewrite['slug'];
        }
        return 'universitetler';
    }

    public static function maybe_flush_rewrites(): void {
        if ( wp_installing() ) {
            return;
        }
        $ver = (string) get_option( 'sit_developer_rewrite_ver', '' );
        if ( version_compare( $ver, SIT_DEVELOPER_VERSION, '>=' ) ) {
            return;
        }
        flush_rewrite_rules( false );
        update_option( 'sit_developer_rewrite_ver', SIT_DEVELOPER_VERSION );
    }
}
