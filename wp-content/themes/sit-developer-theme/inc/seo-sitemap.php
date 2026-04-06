<?php
/**
 * Çoxdilli XML sitemap: /sit-sitemap.xml (indeks) və /sit-sitemap-{lang}.xml.
 *
 * Aktivasiyadan sonra: Parametrlər → Daimi keçidlər → Yadda saxla (rewrite flush).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Rewrite qaydaları.
 */
function sit_theme_seo_register_sitemap_rewrites(): void {
	add_rewrite_rule( '^sit-sitemap\.xml$', 'index.php?sit_sitemap=index', 'top' );
	add_rewrite_rule( '^sit-sitemap-([a-z]{2})\.xml$', 'index.php?sit_sitemap=lang&sit_map_lang=$matches[1]', 'top' );
}
add_action( 'init', 'sit_theme_seo_register_sitemap_rewrites', 20 );

/**
 * @param string[] $vars Sorğu dəyişənləri.
 * @return string[]
 */
function sit_theme_seo_sitemap_query_vars( array $vars ): array {
	$vars[] = 'sit_sitemap';
	$vars[] = 'sit_map_lang';

	return $vars;
}
add_filter( 'query_vars', 'sit_theme_seo_sitemap_query_vars' );

/**
 * Verilmiş dil üçün get_permalink (post_link filteri cari dili istifadə edir).
 */
function sit_theme_seo_permalink_for_lang( int $post_id, string $lang_code ): string {
	if ( ! class_exists( 'SIT_Languages' ) || ! SIT_Languages::is_valid_code( $lang_code ) ) {
		$link = get_permalink( $post_id );

		return $link ? $link : '';
	}

	$GLOBALS['sit_current_lang'] = $lang_code;
	$GLOBALS['current_lang']     = $lang_code;
	$link                          = get_permalink( $post_id );
	$def                           = SIT_Languages::get_default_language_code() ?: 'az';
	$GLOBALS['sit_current_lang']   = $def;
	$GLOBALS['current_lang']       = $def;

	return $link ? $link : '';
}

/**
 * İndeks və ya bir dil üçün URL siyahısı.
 */
function sit_theme_seo_collect_urls_for_lang( string $lang_code ): array {
	$types = get_post_types(
		[
			'public'             => true,
			'publicly_queryable' => true,
		],
		'names'
	);
	unset( $types['attachment'] );

	$urls = [];

	$type_slugs = array_keys( $types );
	if ( empty( $type_slugs ) ) {
		return $urls;
	}

	$q = new WP_Query(
		[
			'post_type'              => $type_slugs,
			'post_status'            => 'publish',
			'posts_per_page'         => 5000,
			'orderby'                => 'modified',
			'order'                  => 'DESC',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);
	foreach ( $q->posts as $pid ) {
		$pid = (int) $pid;
		$url = sit_theme_seo_permalink_for_lang( $pid, $lang_code );
		if ( '' === $url ) {
			continue;
		}
		$mod          = get_post_field( 'post_modified_gmt', $pid );
		$urls[ $url ] = is_string( $mod ) ? $mod : '';
	}
	wp_reset_postdata();

	return $urls;
}

/**
 * XML üçün təhlükəsiz mətn.
 */
function sit_theme_seo_esc_xml( string $text ): string {
	return htmlspecialchars( $text, ENT_XML1 | ENT_COMPAT, 'UTF-8' );
}

/**
 * Sitemap XML gövdəsi.
 */
function sit_theme_seo_sitemap_body( string $mode ): string {
	if ( 'index' === $mode ) {
		$lines   = [ '<?xml version="1.0" encoding="UTF-8"?>' ];
		$lines[] = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

		if ( class_exists( 'SIT_Languages' ) ) {
			$langs = SIT_Languages::get_active_languages();
		} else {
			$langs = [];
		}

		if ( empty( $langs ) ) {
			$loc = home_url( '/sit-sitemap-az.xml' );
			$lines[] = '<sitemap><loc>' . sit_theme_seo_esc_xml( $loc ) . '</loc></sitemap>';
		} else {
			foreach ( $langs as $lang ) {
				$code = sanitize_key( $lang->code );
				$loc  = home_url( '/sit-sitemap-' . $code . '.xml' );
				$lines[] = '<sitemap><loc>' . sit_theme_seo_esc_xml( $loc ) . '</loc><lastmod>' . gmdate( 'c' ) . '</lastmod></sitemap>';
			}
		}

		$lines[] = '</sitemapindex>';

		return implode( "\n", $lines ) . "\n";
	}

	if ( 'lang' !== $mode ) {
		return '';
	}

	$lang = isset( $_GET['sit_map_lang'] ) ? sanitize_key( wp_unslash( (string) $_GET['sit_map_lang'] ) ) : get_query_var( 'sit_map_lang' );
	$lang = is_string( $lang ) ? $lang : '';

	if ( ! class_exists( 'SIT_Languages' ) || ! SIT_Languages::is_valid_code( $lang ) ) {
		status_header( 404 );

		return '';
	}

	$urls = sit_theme_seo_collect_urls_for_lang( $lang );

	$lines   = [ '<?xml version="1.0" encoding="UTF-8"?>' ];
	$lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

	foreach ( $urls as $loc => $lastmod ) {
		$lines[] = '<url>';
		$lines[] = '<loc>' . sit_theme_seo_esc_xml( $loc ) . '</loc>';
		if ( '' !== $lastmod && '0000-00-00 00:00:00' !== $lastmod ) {
			$lines[] = '<lastmod>' . sit_theme_seo_esc_xml( mysql2date( 'c', $lastmod, true ) ) . '</lastmod>';
		}
		$lines[] = '</url>';
	}

	$lines[] = '</urlset>';

	return implode( "\n", $lines ) . "\n";
}

/**
 * Sitemap sorğusunu emal edir.
 */
function sit_theme_seo_sitemap_dispatch(): void {
	$mode = get_query_var( 'sit_sitemap' );
	if ( '' === $mode ) {
		return;
	}

	if ( '0' === (string) get_option( 'blog_public' ) ) {
		status_header( 403 );
		exit;
	}

	nocache_headers();
	header( 'Content-Type: application/xml; charset=UTF-8' );

	$body = sit_theme_seo_sitemap_body( $mode );
	if ( '' === $body && 'lang' === $mode ) {
		status_header( 404 );
		exit;
	}

	echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XML
	exit;
}
add_action( 'template_redirect', 'sit_theme_seo_sitemap_dispatch', 0 );

/**
 * Tema aktivləşəndə rewrite flush.
 */
function sit_theme_seo_flush_rewrites(): void {
	sit_theme_seo_register_sitemap_rewrites();
	flush_rewrite_rules( false );
}
add_action( 'after_switch_theme', 'sit_theme_seo_flush_rewrites' );

/**
 * Tema versiyası dəyişəndə bir dəfə rewrite flush (deploy / yeniləmə).
 */
function sit_theme_seo_maybe_flush_on_version(): void {
	$opt = 'sit_theme_rewrite_ver';
	if ( get_option( $opt ) === SIT_THEME_VERSION ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( $opt, SIT_THEME_VERSION, true );
}
add_action( 'init', 'sit_theme_seo_maybe_flush_on_version', 99 );
