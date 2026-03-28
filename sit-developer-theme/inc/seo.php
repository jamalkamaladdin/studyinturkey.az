<?php
/**
 * SEO: meta təsvir, Open Graph, canonical, Schema.org JSON-LD.
 *
 * Yoast / Rank Math / AIOSEO aktivdirsə, ikiqat teqlərin qarşısını almaq üçün bu blok əksər çıxışları ötürür.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Məşhur SEO pluginləri ilə toqquşma.
 */
function sit_theme_seo_third_party_active(): bool {
	return defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SEOPRESS_VERSION' );
}

/**
 * Cari səhifə üçün qısa meta təsvir (təxminən 160 simvol).
 */
function sit_theme_seo_get_description(): string {
	if ( is_singular() ) {
		$id = get_queried_object_id();
		if ( $id > 0 && function_exists( 'sit_theme_get_post_excerpt' ) ) {
			$text = sit_theme_get_post_excerpt( $id );
		} else {
			$text = has_excerpt() ? get_the_excerpt() : '';
			$text = is_string( $text ) ? wp_strip_all_tags( $text ) : '';
		}
		if ( '' === $text && $id > 0 ) {
			$post = get_post( $id );
			$text = $post ? wp_trim_words( wp_strip_all_tags( (string) $post->post_content ), 24, '…' ) : '';
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		$text = ( $term instanceof WP_Term ) ? term_description( $term->term_id, $term->taxonomy ) : '';
		$text = wp_strip_all_tags( (string) $text );
		if ( '' === $text && $term instanceof WP_Term ) {
			$text = $term->name;
		}
	} elseif ( is_post_type_archive() ) {
		$text = get_the_archive_description();
		$text = wp_strip_all_tags( (string) $text );
		if ( '' === $text ) {
			$text = get_the_archive_title();
		}
	} elseif ( is_front_page() || is_home() ) {
		$text = get_bloginfo( 'description', 'display' );
	} else {
		$text = '';
	}

	$text = preg_replace( '/\s+/u', ' ', trim( (string) $text ) );
	if ( '' === $text ) {
		$text = get_bloginfo( 'name', 'display' );
	}

	if ( function_exists( 'mb_substr' ) ) {
		return mb_strlen( $text ) > 165 ? mb_substr( $text, 0, 162 ) . '…' : $text;
	}

	return strlen( $text ) > 165 ? substr( $text, 0, 162 ) . '…' : $text;
}

/**
 * Canonical URL (yalnız tək yazıdan kənar səhifələr üçün — core rel_canonical təki yazını əhatə edir).
 */
function sit_theme_seo_canonical_url(): ?string {
	if ( is_singular() ) {
		return null;
	}
	if ( is_front_page() ) {
		return function_exists( 'sit_theme_localize_url' )
			? sit_theme_localize_url( home_url( '/' ) )
			: home_url( '/' );
	}
	if ( is_home() && ! is_front_page() && function_exists( 'sit_theme_blog_index_url' ) ) {
		return sit_theme_blog_index_url();
	}
	if ( is_post_type_archive() ) {
		$pt = get_query_var( 'post_type' );
		if ( is_array( $pt ) ) {
			$pt = reset( $pt );
		}
		if ( ! is_string( $pt ) || '' === $pt ) {
			return null;
		}
		$link = get_post_type_archive_link( $pt );
		if ( ! $link ) {
			return null;
		}
		return function_exists( 'sit_theme_localize_url' ) ? sit_theme_localize_url( $link ) : $link;
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( ! $term instanceof WP_Term ) {
			return null;
		}
		$link = get_term_link( $term );
		if ( is_wp_error( $link ) ) {
			return null;
		}
		return function_exists( 'sit_theme_localize_url' ) ? sit_theme_localize_url( $link ) : $link;
	}

	return null;
}

/**
 * Cari səhifənin tam URL-i (OG və s.).
 */
function sit_theme_seo_current_url(): string {
	if ( is_singular() ) {
		$url = get_permalink();
		return $url ? $url : home_url( '/' );
	}
	$canon = sit_theme_seo_canonical_url();
	if ( $canon ) {
		return $canon;
	}
	$scheme = is_ssl() ? 'https' : 'http';
	$host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_HOST'] ) ) : '';
	$uri    = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( (string) $_SERVER['REQUEST_URI'] ) : '/';
	if ( '' === $host ) {
		return home_url( '/' );
	}
	return esc_url_raw( $scheme . '://' . $host . $uri );
}

/**
 * OG şəkli (öne çıxan şəkil, loqo və ya sayt ikonu).
 */
function sit_theme_seo_image_url(): string {
	if ( is_singular() && has_post_thumbnail() ) {
		$url = get_the_post_thumbnail_url( null, 'large' );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	if ( $custom_logo_id ) {
		$src = wp_get_attachment_image_url( (int) $custom_logo_id, 'full' );
		if ( $src ) {
			return $src;
		}
	}
	$icon = get_site_icon_url( 512 );
	if ( $icon ) {
		return $icon;
	}

	return '';
}

/**
 * Open Graph üçün dil kodları (og:locale və alternativlər).
 *
 * @return array{primary: string, alternates: string[]}
 */
function sit_theme_seo_og_locales(): array {
	$primary = strtolower( str_replace( '_', '-', get_locale() ) );
	$alts    = [];
	if ( class_exists( 'SIT_Languages' ) ) {
		foreach ( SIT_Languages::get_active_languages() as $lang ) {
			$h = strtolower( str_replace( '_', '-', $lang->locale ) );
			if ( $h !== $primary ) {
				$alts[] = $h;
			}
		}
	}

	return [
		'primary'    => $primary,
		'alternates' => array_values( array_unique( $alts ) ),
	];
}

/**
 * JSON-LD: WebSite + Organization (yalnız ana səhifə).
 */
function sit_theme_seo_schema_website(): void {
	if ( ! is_front_page() ) {
		return;
	}
	$home = home_url( '/' );
	$name = get_bloginfo( 'name', 'display' );
	$desc = get_bloginfo( 'description', 'display' );

	$graph = [
		'@context' => 'https://schema.org',
		'@graph'   => [
			[
				'@type'       => 'WebSite',
				'@id'         => trailingslashit( $home ) . '#website',
				'url'         => $home,
				'name'        => $name,
				'description' => $desc,
				'publisher'   => [ '@id' => trailingslashit( $home ) . '#org' ],
				'potentialAction' => [
					'@type'       => 'SearchAction',
					'target'      => ( function_exists( 'sit_theme_localize_url' )
						? trailingslashit( sit_theme_localize_url( home_url( '/' ) ) )
						: trailingslashit( $home ) ) . '?s={search_term_string}',
					'query-input' => 'required name=search_term_string',
				],
			],
			[
				'@type' => 'Organization',
				'@id'   => trailingslashit( $home ) . '#org',
				'name'  => $name,
				'url'   => $home,
			],
		],
	];

	$logo = sit_theme_seo_image_url();
	if ( '' !== $logo ) {
		$graph['@graph'][1]['logo'] = $logo;
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}

/**
 * JSON-LD: CollegeOrUniversity.
 */
function sit_theme_seo_schema_university( int $post_id ): void {
	$name = function_exists( 'sit_theme_get_post_title' ) ? sit_theme_get_post_title( $post_id ) : get_the_title( $post_id );
	$desc = function_exists( 'sit_theme_get_post_excerpt' ) ? sit_theme_get_post_excerpt( $post_id ) : '';
	$url  = get_permalink( $post_id );
	if ( ! $url ) {
		return;
	}

	$data = [
		'@context' => 'https://schema.org',
		'@type'    => 'CollegeOrUniversity',
		'name'     => $name,
		'url'      => $url,
		'description' => $desc,
	];

	$web = (string) get_post_meta( $post_id, 'sit_website_url', true );
	if ( '' !== $web ) {
		$data['sameAs'] = esc_url_raw( $web );
	}

	$logo_id = (int) get_post_meta( $post_id, 'sit_logo_id', true );
	if ( $logo_id > 0 ) {
		$logo_src = wp_get_attachment_image_url( $logo_id, 'full' );
		if ( $logo_src ) {
			$data['logo'] = $logo_src;
		}
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}

/**
 * JSON-LD: Course (proqram).
 */
function sit_theme_seo_schema_course( int $post_id ): void {
	$name = function_exists( 'sit_theme_get_post_title' ) ? sit_theme_get_post_title( $post_id ) : get_the_title( $post_id );
	$desc = function_exists( 'sit_theme_get_post_excerpt' ) ? sit_theme_get_post_excerpt( $post_id ) : '';
	$url  = get_permalink( $post_id );
	if ( ! $url ) {
		return;
	}

	$data = [
		'@context'    => 'https://schema.org',
		'@type'       => 'Course',
		'name'        => $name,
		'description' => $desc,
		'url'         => $url,
	];

	$uid = function_exists( 'sit_theme_get_program_university_id' ) ? sit_theme_get_program_university_id( $post_id ) : (int) get_post_meta( $post_id, 'sit_university_id', true );
	if ( $uid > 0 ) {
		$org_name = function_exists( 'sit_theme_get_post_title' ) ? sit_theme_get_post_title( $uid ) : get_the_title( $uid );
		$org_url  = get_permalink( $uid );
		$prov     = [
			'@type' => 'CollegeOrUniversity',
			'name'  => $org_name,
		];
		if ( $org_url ) {
			$prov['url'] = $org_url;
		}
		$data['provider'] = $prov;
	}

	$fee = get_post_meta( $post_id, 'sit_tuition_fee', true );
	if ( is_numeric( $fee ) && (float) $fee > 0 ) {
		$data['offers'] = [
			'@type'         => 'Offer',
			'price'         => (string) (float) $fee,
			'priceCurrency' => apply_filters( 'sit_theme_seo_course_price_currency', 'TRY' ),
		];
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}

/**
 * JSON-LD: Review.
 */
function sit_theme_seo_schema_review( int $post_id ): void {
	$name   = function_exists( 'sit_theme_get_post_title' ) ? sit_theme_get_post_title( $post_id ) : get_the_title( $post_id );
	$body   = function_exists( 'sit_theme_get_post_excerpt' ) ? sit_theme_get_post_excerpt( $post_id ) : '';
	$url    = get_permalink( $post_id );
	$rating = (float) get_post_meta( $post_id, 'sit_rating', true );
	$author = (string) get_post_meta( $post_id, 'sit_student_name', true );

	$item = [
		'@type' => 'Organization',
		'name'  => get_bloginfo( 'name', 'display' ),
	];

	$uid = (int) get_post_meta( $post_id, 'sit_university_id', true );
	if ( $uid > 0 ) {
		$item = [
			'@type' => 'CollegeOrUniversity',
			'name'  => function_exists( 'sit_theme_get_post_title' ) ? sit_theme_get_post_title( $uid ) : get_the_title( $uid ),
			'url'   => get_permalink( $uid ) ?: home_url( '/' ),
		];
	}

	$data = [
		'@context' => 'https://schema.org',
		'@type'    => 'Review',
		'name'     => $name,
		'reviewBody' => $body,
		'itemReviewed' => $item,
	];

	if ( $url ) {
		$data['url'] = $url;
	}
	if ( '' !== $author ) {
		$data['author'] = [
			'@type' => 'Person',
			'name'  => $author,
		];
	}
	if ( $rating > 0 ) {
		$data['reviewRating'] = [
			'@type'       => 'Rating',
			'ratingValue' => min( 5, max( 1, $rating ) ),
			'bestRating'  => 5,
			'worstRating' => 1,
		];
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}

/**
 * wp_head çıxışı.
 */
function sit_theme_seo_wp_head(): void {
	if ( ! apply_filters( 'sit_theme_seo_enabled', true ) ) {
		return;
	}
	if ( sit_theme_seo_third_party_active() ) {
		return;
	}
	if ( is_404() || is_feed() || is_embed() ) {
		return;
	}

	$desc = sit_theme_seo_get_description();
	$url  = sit_theme_seo_current_url();
	$title = wp_get_document_title();

	echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";

	echo '<meta property="og:type" content="' . esc_attr( is_singular() ? 'article' : 'website' ) . '" />' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '" />' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '" />' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" />' . "\n";

	$locales = sit_theme_seo_og_locales();
	echo '<meta property="og:locale" content="' . esc_attr( str_replace( '-', '_', $locales['primary'] ) ) . '" />' . "\n";
	foreach ( $locales['alternates'] as $alt ) {
		echo '<meta property="og:locale:alternate" content="' . esc_attr( str_replace( '-', '_', $alt ) ) . '" />' . "\n";
	}

	$img = sit_theme_seo_image_url();
	if ( '' !== $img ) {
		echo '<meta property="og:image" content="' . esc_url( $img ) . '" />' . "\n";
	}

	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '" />' . "\n";
	if ( '' !== $img ) {
		echo '<meta name="twitter:image" content="' . esc_url( $img ) . '" />' . "\n";
	}

	$canon = sit_theme_seo_canonical_url();
	if ( $canon ) {
		echo '<link rel="canonical" href="' . esc_url( $canon ) . '" />' . "\n";
	}

	if ( is_front_page() ) {
		sit_theme_seo_schema_website();
	} elseif ( is_singular( 'university' ) ) {
		sit_theme_seo_schema_university( get_queried_object_id() );
	} elseif ( is_singular( 'program' ) ) {
		sit_theme_seo_schema_course( get_queried_object_id() );
	} elseif ( is_singular( 'review' ) ) {
		sit_theme_seo_schema_review( get_queried_object_id() );
	}
}
add_action( 'wp_head', 'sit_theme_seo_wp_head', 4 );

/**
 * robots.txt — çoxdilli indeks sitemap əlavəsi.
 *
 * @param string $output robots məzmunu.
 * @param bool   $public Sayt axtarış mühərriklərinə açıqdır.
 */
function sit_theme_seo_robots_txt( string $output, $public ): string {
	if ( ! $public ) {
		return $output;
	}
	$sitemap = home_url( '/sit-sitemap.xml' );
	if ( false === strpos( $output, 'sit-sitemap.xml' ) ) {
		$output .= "\n# StudyInTurkey theme\nSitemap: {$sitemap}\n";
	}

	return $output;
}
add_filter( 'robots_txt', 'sit_theme_seo_robots_txt', 10, 2 );
