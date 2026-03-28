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
 * Proqram üçün universitet ID-si (meta boşdursa FBU slug ehtiyatı).
 *
 * @param int $program_id Proqram post ID.
 */
function sit_theme_get_program_university_id( int $program_id ): int {
	$uid = (int) get_post_meta( $program_id, 'sit_university_id', true );
	if ( $uid > 0 ) {
		return $uid;
	}
	$post = get_post( $program_id );
	if ( ! $post instanceof WP_Post || 'program' !== $post->post_type ) {
		return 0;
	}
	if ( ! is_string( $post->post_name ) || ! str_ends_with( $post->post_name, '-fbu' ) ) {
		return 0;
	}
	$fbu = get_posts(
		[
			'post_type'              => 'university',
			'post_status'            => 'publish',
			'name'                   => 'fenerbahce-universitesi',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
		]
	);
	return $fbu ? (int) $fbu[0] : 0;
}

/**
 * Proqram üçün sahə (field_of_study) göstərim sətri — başlıqla uyğunsuz term təyin olunubsa düzəldir.
 *
 * @param int $program_id Proqram post ID.
 */
function sit_theme_get_program_field_display_line( int $program_id ): string {
	if ( ! taxonomy_exists( 'field_of_study' ) ) {
		return '';
	}
	$terms = get_the_terms( $program_id, 'field_of_study' );
	if ( ! is_array( $terms ) || is_wp_error( $terms ) || ! $terms ) {
		return '';
	}
	$title   = sit_theme_get_post_title( $program_id );
	$inferred = sit_theme_infer_field_category_from_title( $title );

	foreach ( $terms as $t ) {
		$name = strtolower( (string) $t->name );
		$slug = strtolower( (string) $t->slug );
		if ( 'sport' === $inferred && ( str_contains( $name, 'sport' ) || str_contains( $name, 'idman' ) || str_contains( $slug, 'sport' ) || str_contains( $slug, 'idman' ) ) ) {
			return sit_theme_get_term_name( (int) $t->term_id, 'field_of_study' );
		}
	}

	if ( 'sport' === $inferred ) {
		$fix = get_term_by( 'slug', 'sport-sciences', 'field_of_study' );
		if ( ! $fix instanceof WP_Term ) {
			$fix = get_term_by( 'name', 'Sport Sciences', 'field_of_study' );
		}
		if ( $fix instanceof WP_Term ) {
			return sit_theme_get_term_name( (int) $fix->term_id, 'field_of_study' );
		}
	}

	$prefer_slugs = [
		'engineering'   => [ 'engineering', 'muhendislik', 'mühəndislik' ],
		'business'      => [ 'business', 'biznes' ],
		'medicine'      => [ 'medicine', 'tibb', 'health' ],
		'architecture'  => [ 'architecture', 'memarliq' ],
		'communication' => [ 'communication', 'media', 'ictima' ],
		'social'        => [ 'social', 'psychology', 'political' ],
		'humanities'    => [ 'humanities', 'english', 'dil' ],
	];

	if ( isset( $prefer_slugs[ $inferred ] ) ) {
		foreach ( $prefer_slugs[ $inferred ] as $frag ) {
			foreach ( $terms as $t ) {
				if ( str_contains( strtolower( (string) $t->slug ), $frag ) || str_contains( strtolower( (string) $t->name ), $frag ) ) {
					return sit_theme_get_term_name( (int) $t->term_id, 'field_of_study' );
				}
			}
		}
	}

	$names = [];
	foreach ( $terms as $t ) {
		$names[] = sit_theme_get_term_name( (int) $t->term_id, 'field_of_study' );
	}
	return implode( ', ', array_filter( $names ) );
}

/**
 * Başlıqdan sahə kateqoriyası (import xəritəsi ilə uyğunlaşdırılmış).
 *
 * @param string $title Başlıq mətni.
 */
function sit_theme_infer_field_category_from_title( string $title ): string {
	if ( preg_match( '/\b(idman|idman\s+elmləri|spor|sport)\b/ui', $title ) ) {
		return 'sport';
	}
	if ( preg_match( '/\b(engineering|software|computer|electrical|industrial)\b/i', $title ) ) {
		return 'engineering';
	}
	if ( preg_match( '/\b(mba|business|economics|finance|management information|organizational behavior)\b/i', $title ) ) {
		return 'business';
	}
	if ( preg_match( '/\b(pharmacy|nursing|physiotherapy|midwifery|nutrition|speech|ergotherapy|clinical pharmacy|internal diseases nursing|anesthesia|dental|dialysis|emergency aid|medical imaging|laboratory|operating room|oral and dental|radiation|pathology|pharmacy services|orthopedic|dietetics)\b/i', $title ) ) {
		return 'medicine';
	}
	if ( preg_match( '/\b(architecture|interior architecture)\b/i', $title ) ) {
		return 'architecture';
	}
	if ( preg_match( '/\b(new media|public relations|radio|television|cinema|advertising)\b/i', $title ) ) {
		return 'communication';
	}
	if ( preg_match( '/\b(sports?\s+sciences?|coaching|exercise|physical education)\b/i', $title ) ) {
		return 'sport';
	}
	if ( preg_match( '/\b(psychology|political science|international relations)\b/i', $title ) ) {
		return 'social';
	}
	if ( preg_match( '/\benglish language and literature\b/i', $title ) ) {
		return 'humanities';
	}
	return 'business';
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
		if ( '' === (string) $lang && class_exists( 'SIT_Languages' ) ) {
			$lang = (string) SIT_Languages::get_default_language_code();
		}
		if ( '' === $lang ) {
			$lang = 'az';
		}
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
 * Universitet şablonunda ID: get_template_part() arqumenti bəzən şablona çatmayanda (köhnə WP və s.) sorğudan götürülür.
 *
 * @param mixed $from_args Şablonda gözlənilən university_id dəyəri.
 */
function sit_theme_resolve_university_id( $from_args = null ): int {
	if ( is_numeric( $from_args ) && (int) $from_args > 0 ) {
		return (int) $from_args;
	}
	if ( is_singular( 'university' ) ) {
		$qid = (int) get_queried_object_id();
		if ( $qid > 0 ) {
			return $qid;
		}
	}
	return 0;
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

/**
 * Telefon ölkə kodu <option> siyahısı.
 */
function sit_theme_phone_code_options(): void {
	$codes = [
		'+994'  => '🇦🇿 Azərbaycan +994',
		'+90'   => '🇹🇷 Türkiyə +90',
		'+7'    => '🇷🇺 Rusiya +7',
		'+98'   => '🇮🇷 İran +98',
		'+966'  => '🇸🇦 S.Ərəbistan +966',
		'+971'  => '🇦🇪 BƏƏ +971',
		'+962'  => '🇯🇴 İordaniya +962',
		'+961'  => '🇱🇧 Livan +961',
		'+964'  => '🇮🇶 İraq +964',
		'+965'  => '🇰🇼 Küveyt +965',
		'+968'  => '🇴🇲 Oman +968',
		'+974'  => '🇶🇦 Qətər +974',
		'+973'  => '🇧🇭 Bəhreyn +973',
		'+967'  => '🇾🇪 Yəmən +967',
		'+963'  => '🇸🇾 Suriya +963',
		'+970'  => '🇵🇸 Fələstin +970',
		'+218'  => '🇱🇾 Liviya +218',
		'+216'  => '🇹🇳 Tunis +216',
		'+212'  => '🇲🇦 Mərakeş +212',
		'+213'  => '🇩🇿 Əlcəzair +213',
		'+20'   => '🇪🇬 Misir +20',
		'+249'  => '🇸🇩 Sudan +249',
		'+252'  => '🇸🇴 Somali +252',
		'+993'  => '🇹🇲 Türkmənistan +993',
		'+996'  => '🇰🇬 Qırğızıstan +996',
		'+998'  => '🇺🇿 Özbəkistan +998',
		'+992'  => '🇹🇯 Tacikistan +992',
		'+77'   => '🇰🇿 Qazaxıstan +7',
		'+995'  => '🇬🇪 Gürcüstan +995',
		'+374'  => '🇦🇲 Ermənistan +374',
		'+380'  => '🇺🇦 Ukrayna +380',
		'+375'  => '🇧🇾 Belarus +375',
		'+373'  => '🇲🇩 Moldova +373',
		'+40'   => '🇷🇴 Rumıniya +40',
		'+359'  => '🇧🇬 Bolqarıstan +359',
		'+30'   => '🇬🇷 Yunanıstan +30',
		'+49'   => '🇩🇪 Almaniya +49',
		'+33'   => '🇫🇷 Fransa +33',
		'+44'   => '🇬🇧 B.Krallıq +44',
		'+39'   => '🇮🇹 İtaliya +39',
		'+34'   => '🇪🇸 İspaniya +34',
		'+31'   => '🇳🇱 Hollandiya +31',
		'+46'   => '🇸🇪 İsveç +46',
		'+47'   => '🇳🇴 Norveç +47',
		'+45'   => '🇩🇰 Danimarka +45',
		'+43'   => '🇦🇹 Avstriya +43',
		'+41'   => '🇨🇭 İsveçrə +41',
		'+48'   => '🇵🇱 Polşa +48',
		'+420'  => '🇨🇿 Çexiya +420',
		'+36'   => '🇭🇺 Macarıstan +36',
		'+1'    => '🇺🇸 ABŞ/Kanada +1',
		'+55'   => '🇧🇷 Braziliya +55',
		'+52'   => '🇲🇽 Meksika +52',
		'+91'   => '🇮🇳 Hindistan +91',
		'+92'   => '🇵🇰 Pakistan +92',
		'+93'   => '🇦🇫 Əfqanıstan +93',
		'+880'  => '🇧🇩 Banqladeş +880',
		'+60'   => '🇲🇾 Malayziya +60',
		'+62'   => '🇮🇩 İndoneziya +62',
		'+86'   => '🇨🇳 Çin +86',
		'+82'   => '🇰🇷 C.Koreya +82',
		'+81'   => '🇯🇵 Yaponiya +81',
		'+234'  => '🇳🇬 Nigeriya +234',
		'+27'   => '🇿🇦 C.Afrika +27',
		'+254'  => '🇰🇪 Keniya +254',
		'+255'  => '🇹🇿 Tanzaniya +255',
		'+256'  => '🇺🇬 Uqanda +256',
		'+233'  => '🇬🇭 Qana +233',
		'+225'  => '🇨🇮 Kotdivuar +225',
		'+237'  => '🇨🇲 Kamerun +237',
		'+61'   => '🇦🇺 Avstraliya +61',
	];
	foreach ( $codes as $code => $label ) {
		$val = ( '+77' === $code ) ? '+7' : $code;
		printf( '<option value="%s">%s</option>', esc_attr( $val ), esc_html( $label ) );
	}
}
