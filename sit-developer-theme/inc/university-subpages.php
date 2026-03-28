<?php
/**
 * Universitet alt səhifələri: URL-lər, kampus həlli, dərəcə siyahısı.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Universitet alt səhifə URL-i (yataqxanalar, qəbul, kampus).
 *
 * @param int    $university_id Universitet post ID.
 * @param string $view          dormitories|admission|campus.
 * @param string $campus_slug   Kampus üçün slug.
 */
function sit_theme_university_sub_url( int $university_id, string $view, string $campus_slug = '' ): string {
	if ( $university_id < 1 ) {
		return home_url( '/' );
	}
	$post = get_post( $university_id );
	if ( ! $post instanceof WP_Post || 'university' !== $post->post_type || 'publish' !== $post->post_status ) {
		return home_url( '/' );
	}
	$base = get_permalink( $university_id );
	if ( ! $base ) {
		return home_url( '/' );
	}
	$base = trailingslashit( $base );
	switch ( $view ) {
		case 'dormitories':
			return $base . 'yataqxanalar/';
		case 'admission':
			return $base . 'qebul-telablari/';
		case 'campus':
			$slug = sanitize_title( $campus_slug );
			return '' !== $slug ? $base . 'kampus/' . $slug . '/' : $base;
		default:
			return $base;
	}
}

/**
 * Bu universitetdə istifadə olunan dərəcə (degree_type) slug-ları.
 *
 * @return string[]
 */
function sit_theme_university_program_degree_slugs( int $university_id ): array {
	if ( $university_id < 1 || ! taxonomy_exists( 'degree_type' ) ) {
		return [];
	}
	$ids = get_posts(
		[
			'post_type'      => 'program',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => [
				[
					'key'     => 'sit_university_id',
					'value'   => $university_id,
					'compare' => '=',
					'type'    => 'NUMERIC',
				],
			],
		]
	);
	$slugs = [];
	foreach ( $ids as $pid ) {
		$terms = get_the_terms( (int) $pid, 'degree_type' );
		if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
			continue;
		}
		foreach ( $terms as $t ) {
			$slugs[ $t->slug ] = true;
		}
	}
	return array_keys( $slugs );
}

/**
 * Universitetə aid kampus slug ilə tək yazı.
 */
function sit_theme_resolve_university_campus( int $university_id, string $campus_slug ): ?WP_Post {
	$slug = sanitize_title( $campus_slug );
	if ( '' === $slug || $university_id < 1 || ! post_type_exists( 'campus' ) ) {
		return null;
	}
	$posts = get_posts(
		[
			'post_type'              => 'campus',
			'name'                   => $slug,
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'update_post_meta_cache' => true,
		]
	);
	if ( ! $posts ) {
		return null;
	}
	$c = $posts[0];
	if ( (int) get_post_meta( $c->ID, 'sit_university_id', true ) !== $university_id ) {
		return null;
	}
	return $c;
}

/**
 * Qəbul bloku üçün məzmun varmı?
 *
 * @param array<string, mixed> $blk Dərəcə bloku.
 */
function sit_theme_admission_block_has_content( array $blk ): bool {
	$steps = isset( $blk['steps'] ) && is_array( $blk['steps'] ) ? $blk['steps'] : [];
	foreach ( $steps as $s ) {
		if ( is_string( $s ) && '' !== trim( wp_strip_all_tags( $s ) ) ) {
			return true;
		}
	}
	$docs = isset( $blk['documents'] ) && is_array( $blk['documents'] ) ? $blk['documents'] : [];
	foreach ( $docs as $d ) {
		if ( is_string( $d ) && '' !== trim( $d ) ) {
			return true;
		}
	}
	foreach ( [ 'intake_title', 'intake_start', 'intake_deadline' ] as $k ) {
		if ( isset( $blk[ $k ] ) && is_string( $blk[ $k ] ) && '' !== trim( $blk[ $k ] ) ) {
			return true;
		}
	}
	return false;
}

/**
 * @param array<string, array<string, mixed>> $req Admission decoded.
 * @return string[]
 */
function sit_theme_filter_admission_degrees_for_display( int $university_id, array $req ): array {
	$used = sit_theme_university_program_degree_slugs( $university_id );
	$out  = [];
	foreach ( $used as $slug ) {
		if ( isset( $req[ $slug ] ) && is_array( $req[ $slug ] ) && sit_theme_admission_block_has_content( $req[ $slug ] ) ) {
			$out[] = $slug;
		}
	}
	return $out;
}

/**
 * Alt şablon seçimi (sit_univ_view).
 */
function sit_theme_university_subpage_template( string $template ): string {
	if ( ! is_singular( 'university' ) ) {
		return $template;
	}
	$view = get_query_var( 'sit_univ_view' );
	if ( ! is_string( $view ) || '' === $view ) {
		return $template;
	}
	$map = [
		'dormitories' => 'university-dormitories.php',
		'admission'   => 'university-admission.php',
		'campus'      => 'university-campus.php',
	];
	if ( ! isset( $map[ $view ] ) ) {
		return $template;
	}
	$path = get_template_directory() . '/' . $map[ $view ];
	return is_readable( $path ) ? $path : $template;
}
add_filter( 'template_include', 'sit_theme_university_subpage_template', 20 );

/**
 * Yanlış kampus slug-u üçün 404.
 */
function sit_theme_university_subpage_404(): void {
	if ( ! is_singular( 'university' ) ) {
		return;
	}
	$view = get_query_var( 'sit_univ_view' );
	if ( 'campus' !== $view ) {
		return;
	}
	$slug = sanitize_title( (string) get_query_var( 'sit_campus_slug' ) );
	if ( '' === $slug ) {
		sit_theme_university_force_404();
		return;
	}
	$uid = (int) get_queried_object_id();
	if ( $uid < 1 || ! sit_theme_resolve_university_campus( $uid, $slug ) ) {
		sit_theme_university_force_404();
	}
}
add_action( 'template_redirect', 'sit_theme_university_subpage_404', 0 );

/**
 * Alt səhifə üçün sənəd başlığı.
 *
 * @param array<string, string> $parts Başlıq hissələri.
 * @return array<string, string>
 */
function sit_theme_university_subpage_document_title( array $parts ): array {
	if ( ! is_singular( 'university' ) ) {
		return $parts;
	}
	$view = get_query_var( 'sit_univ_view' );
	if ( ! is_string( $view ) || '' === $view ) {
		return $parts;
	}
	if ( 'campus' === $view ) {
		$slug = sanitize_title( (string) get_query_var( 'sit_campus_slug' ) );
		$c    = sit_theme_resolve_university_campus( (int) get_queried_object_id(), $slug );
		if ( $c instanceof WP_Post ) {
			/* translators: 1: campus name, 2: university title */
			$parts['title'] = sprintf(
				'%1$s – %2$s',
				sit_theme_get_post_title( $c->ID ),
				$parts['title']
			);
		}
		return $parts;
	}
	$labels = [
		'dormitories' => __( 'Yataqxanalar', 'studyinturkey' ),
		'admission'   => __( 'Qəbul tələbləri', 'studyinturkey' ),
	];
	if ( isset( $labels[ $view ] ) ) {
		$parts['title'] = $labels[ $view ] . ' – ' . $parts['title'];
	}
	return $parts;
}
add_filter( 'document_title_parts', 'sit_theme_university_subpage_document_title', 20 );

function sit_theme_university_force_404(): void {
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
}
