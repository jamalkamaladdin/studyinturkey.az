<?php
/**
 * Şablon köməkçiləri.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Daxili URL-ə cari dil prefiksi əlavə edir (sit-multilang).
 *
 * @param string $url Tam URL (məs. get_permalink nəticəsi).
 */
function sit_theme_localize_url( string $url ): string {
	if ( class_exists( 'SIT_Rewrite' ) && function_exists( 'sit_get_current_lang' ) ) {
		return SIT_Rewrite::localize_url( $url, sit_get_current_lang() );
	}
	return $url;
}

/**
 * Giriş / qeydiyyat / portal linkləri (application plugin və ya WP default).
 *
 * @return array{login: string, register: string, portal: string}
 */
function sit_theme_account_urls(): array {
	if ( class_exists( 'SIT_Application_Account' ) ) {
		return SIT_Application_Account::default_urls();
	}

	return [
		'login'    => wp_login_url(),
		'register' => wp_registration_url() ? wp_registration_url() : home_url( '/' ),
		'portal'   => home_url( '/' ),
	];
}

/**
 * Universitet arxiv linki.
 */
function sit_theme_universities_archive_url(): string {
	if ( ! post_type_exists( 'university' ) ) {
		return home_url( '/' );
	}
	return sit_theme_localize_url( get_post_type_archive_link( 'university' ) );
}

/**
 * Proqram arxiv linki.
 */
function sit_theme_programs_archive_url(): string {
	if ( ! post_type_exists( 'program' ) ) {
		return home_url( '/' );
	}
	return sit_theme_localize_url( get_post_type_archive_link( 'program' ) );
}

/**
 * Bloq siyahısı URL-i (statik yazılar səhifəsi və ya post arxivi).
 */
function sit_theme_blog_index_url(): string {
	if ( get_option( 'show_on_front' ) === 'page' ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		if ( $posts_page > 0 ) {
			return sit_theme_localize_url( get_permalink( $posts_page ) );
		}
	}
	$link = get_post_type_archive_link( 'post' );
	return $link ? sit_theme_localize_url( $link ) : sit_theme_localize_url( home_url( '/' ) );
}

/**
 * Post başlığı — çoxdillilik üçün tərcümə.
 *
 * @param int $post_id Post ID.
 */
function sit_theme_get_post_title( int $post_id ): string {
	$lang = function_exists( 'sit_get_current_lang' ) ? sit_get_current_lang() : '';
	$def  = class_exists( 'SIT_Languages' )
		? ( SIT_Languages::get_default_language_code() ?: 'az' )
		: 'az';

	if ( function_exists( 'sit_get_translation' ) && class_exists( 'SIT_Translations' ) && $lang && $lang !== $def ) {
		$t = sit_get_translation( $post_id, SIT_Translations::OBJECT_POST, $lang, 'title', false );
		if ( is_string( $t ) && '' !== $t ) {
			return $t;
		}
	}

	return get_the_title( $post_id );
}

/**
 * Qısa təsvir və ya excerpt.
 *
 * @param int $post_id Post ID.
 */
function sit_theme_get_post_excerpt( int $post_id ): string {
	$lang = function_exists( 'sit_get_current_lang' ) ? sit_get_current_lang() : '';
	$def  = class_exists( 'SIT_Languages' ) ? ( SIT_Languages::get_default_language_code() ?: 'az' ) : 'az';

	if ( function_exists( 'sit_get_translation' ) && class_exists( 'SIT_Translations' ) && $lang && $lang !== $def ) {
		$t = sit_get_translation( $post_id, SIT_Translations::OBJECT_POST, $lang, 'excerpt', false );
		if ( is_string( $t ) && '' !== $t ) {
			return wp_strip_all_tags( $t );
		}
	}

	$post = get_post( $post_id );
	if ( ! $post ) {
		return '';
	}
	if ( has_excerpt( $post ) ) {
		return wp_strip_all_tags( get_the_excerpt( $post ) );
	}
	return wp_trim_words( wp_strip_all_tags( (string) $post->post_content ), 24, '…' );
}

/**
 * Post məzmunu (çoxdillilik) — the_content filtrləri tətbiq olunur.
 *
 * @param int $post_id Post ID.
 */
function sit_theme_get_post_content_filtered( int $post_id ): string {
	$html = '';
	if ( function_exists( 'sit_get_translation' ) && class_exists( 'SIT_Translations' ) && function_exists( 'sit_get_current_lang' ) ) {
		$lang = sit_get_current_lang();
		$html = sit_get_translation( $post_id, SIT_Translations::OBJECT_POST, $lang, SIT_Translations::FIELD_CONTENT, '' );
	}
	if ( '' === $html ) {
		$html = (string) get_post_field( 'post_content', $post_id );
	}
	return apply_filters( 'the_content', $html );
}

/**
 * Term adı (çoxdillilik).
 *
 * @param int    $term_id  Term ID.
 * @param string $taxonomy Taksonomiya.
 */
function sit_theme_get_term_name( int $term_id, string $taxonomy ): string {
	$term = get_term( $term_id, $taxonomy );
	if ( is_wp_error( $term ) || ! $term instanceof WP_Term ) {
		return '';
	}
	if ( function_exists( 'sit_get_translation' ) && class_exists( 'SIT_Translations' ) && function_exists( 'sit_get_current_lang' ) && class_exists( 'SIT_Languages' ) ) {
		$lang = sit_get_current_lang();
		$def  = SIT_Languages::get_default_language_code() ?: 'az';
		if ( $lang && $lang !== $def ) {
			$t = sit_get_translation( $term_id, SIT_Translations::OBJECT_TERM, $lang, SIT_Translations::FIELD_TITLE, '' );
			if ( is_string( $t ) && '' !== $t ) {
				return $t;
			}
		}
	}
	return $term->name;
}

/**
 * Universitet arxivi üçün saxlanılan GET parametrləri (səhifələmə üçün).
 *
 * @return array<string, string>
 */
function sit_theme_university_archive_filter_params(): array {
	$out = [];
	if ( isset( $_GET['sit_city'] ) && (string) $_GET['sit_city'] !== '' ) {
		$out['sit_city'] = sanitize_title( wp_unslash( (string) $_GET['sit_city'] ) );
	}
	if ( isset( $_GET['sit_type'] ) && (string) $_GET['sit_type'] !== '' ) {
		$out['sit_type'] = sanitize_title( wp_unslash( (string) $_GET['sit_type'] ) );
	}
	if ( isset( $_GET['sit_q'] ) && (string) $_GET['sit_q'] !== '' ) {
		$out['sit_q'] = sanitize_text_field( wp_unslash( (string) $_GET['sit_q'] ) );
	}
	return $out;
}

/**
 * Universitet ID-sinə bağlı CPT sorğusu.
 *
 * @param string               $post_type       CPT adı.
 * @param int                  $university_id   Universitet post ID.
 * @param array<string, mixed> $override_query  wp_query arqumentləri.
 */
function sit_theme_query_posts_by_university( string $post_type, int $university_id, array $override_query = [] ): WP_Query {
	if ( $university_id < 1 ) {
		return new WP_Query( [ 'post__in' => [ 0 ] ] );
	}

	$args = [
		'post_type'      => $post_type,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
		'meta_query'     => [
			[
				'key'     => 'sit_university_id',
				'value'   => $university_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			],
		],
	];

	if ( 'faq' === $post_type ) {
		$args['orderby'] = [
			'meta_value_num' => 'ASC',
			'title'          => 'ASC',
		];
		$args['meta_key'] = 'sit_sort_order';
	} else {
		$args['orderby'] = 'title';
		$args['order']   = 'ASC';
	}

	return new WP_Query( wp_parse_args( $override_query, $args ) );
}

/**
 * Vergüllə ayrılmış slug-ların siyahısı (REST ilə uyğun).
 *
 * @param string $csv Giriş.
 * @return string[]
 */
function sit_theme_csv_to_slugs( string $csv ): array {
	if ( '' === trim( $csv ) ) {
		return [];
	}
	$parts = array_map( 'trim', explode( ',', $csv ) );
	$out   = [];
	foreach ( $parts as $p ) {
		$s = sanitize_title( $p );
		if ( '' !== $s ) {
			$out[] = $s;
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * Proqram arxivi GET parametrləri (səhifələmə və keçidlər).
 *
 * @return array<string, string|int>
 */
function sit_theme_program_archive_filter_params(): array {
	$out = [];
	if ( isset( $_GET['sit_degree'] ) && (string) $_GET['sit_degree'] !== '' ) {
		$out['sit_degree'] = sanitize_text_field( wp_unslash( (string) $_GET['sit_degree'] ) );
	}
	if ( isset( $_GET['sit_language'] ) && (string) $_GET['sit_language'] !== '' ) {
		$out['sit_language'] = sanitize_text_field( wp_unslash( (string) $_GET['sit_language'] ) );
	}
	if ( isset( $_GET['sit_field'] ) && (string) $_GET['sit_field'] !== '' ) {
		$out['sit_field'] = sanitize_text_field( wp_unslash( (string) $_GET['sit_field'] ) );
	}
	if ( isset( $_GET['sit_city'] ) && (string) $_GET['sit_city'] !== '' ) {
		$out['sit_city'] = sanitize_title( wp_unslash( (string) $_GET['sit_city'] ) );
	}
	if ( isset( $_GET['sit_price_min'] ) && (string) $_GET['sit_price_min'] !== '' && is_numeric( $_GET['sit_price_min'] ) ) {
		$out['sit_price_min'] = (string) (float) wp_unslash( $_GET['sit_price_min'] );
	}
	if ( isset( $_GET['sit_price_max'] ) && (string) $_GET['sit_price_max'] !== '' && is_numeric( $_GET['sit_price_max'] ) ) {
		$out['sit_price_max'] = (string) (float) wp_unslash( $_GET['sit_price_max'] );
	}
	if ( isset( $_GET['sit_sort'] ) && (string) $_GET['sit_sort'] !== '' ) {
		$out['sit_sort'] = sanitize_key( wp_unslash( (string) $_GET['sit_sort'] ) );
	}
	if ( isset( $_GET['sit_university'] ) && (string) $_GET['sit_university'] !== '' ) {
		$uid = absint( wp_unslash( $_GET['sit_university'] ) );
		if ( $uid > 0 ) {
			$out['sit_university'] = $uid;
		}
	}
	return $out;
}

/**
 * Əsas menyu təyin olunmayanda göstəriləcək keçidlər.
 */
function sit_theme_primary_menu_fallback(): void {
	$items = [
		[
			'label' => function_exists( 'sit__' ) ? sit__( 'nav.home', __( 'Ana səhifə', 'studyinturkey' ), 'nav' ) : __( 'Ana səhifə', 'studyinturkey' ),
			'url'   => sit_theme_localize_url( home_url( '/' ) ),
		],
		[
			'label' => function_exists( 'sit__' ) ? sit__( 'nav.universities', __( 'Universitetlər', 'studyinturkey' ), 'nav' ) : __( 'Universitetlər', 'studyinturkey' ),
			'url'   => sit_theme_universities_archive_url(),
		],
		[
			'label' => function_exists( 'sit__' ) ? sit__( 'nav.programs', __( 'Proqramlar', 'studyinturkey' ), 'nav' ) : __( 'Proqramlar', 'studyinturkey' ),
			'url'   => sit_theme_programs_archive_url(),
		],
		[
			'label' => function_exists( 'sit__' ) ? sit__( 'nav.blog', __( 'Bloq', 'studyinturkey' ), 'nav' ) : __( 'Bloq', 'studyinturkey' ),
			'url'   => sit_theme_localize_url( get_post_type_archive_link( 'post' ) ?: home_url( '/' ) ),
		],
	];

	echo '<ul class="flex flex-col gap-1 text-sm font-medium text-slate-700 sm:flex-row sm:items-center sm:gap-6 dark:text-slate-200">';
	foreach ( $items as $item ) {
		printf(
			'<li><a class="block min-h-[2.75rem] rounded-md px-2 py-2 hover:bg-slate-100 hover:text-slate-900 sm:min-h-0 sm:py-1 dark:hover:bg-slate-800 dark:hover:text-white" href="%s">%s</a></li>',
			esc_url( $item['url'] ),
			esc_html( $item['label'] )
		);
	}
	echo '</ul>';
}
