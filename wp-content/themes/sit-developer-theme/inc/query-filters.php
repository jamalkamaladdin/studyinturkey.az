<?php
/**
 * Əsas sorğu modifikasiyaları (universitet arxivi filtrləri).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Universitet arxivində GET ilə şəhər və növ süzməsi.
 *
 * @param WP_Query $query Əsas sorğu.
 */
function sit_theme_university_archive_pre_get_posts( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! $query->is_post_type_archive( 'university' ) ) {
		return;
	}

	$tax_query = [];

	$city = isset( $_GET['sit_city'] ) ? sanitize_title( wp_unslash( (string) $_GET['sit_city'] ) ) : '';
	if ( $city && term_exists( $city, 'city' ) ) {
		$tax_query[] = [
			'taxonomy' => 'city',
			'field'    => 'slug',
			'terms'    => $city,
		];
	}

	$type = isset( $_GET['sit_type'] ) ? sanitize_title( wp_unslash( (string) $_GET['sit_type'] ) ) : '';
	if ( $type && term_exists( $type, 'university_type' ) ) {
		$tax_query[] = [
			'taxonomy' => 'university_type',
			'field'    => 'slug',
			'terms'    => $type,
		];
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	if ( ! empty( $tax_query ) ) {
		$query->set( 'tax_query', $tax_query );
	}

	$q = isset( $_GET['sit_q'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['sit_q'] ) ) : '';
	if ( '' !== $q ) {
		$query->set( 's', $q );
	}
}
add_action( 'pre_get_posts', 'sit_theme_university_archive_pre_get_posts' );

/**
 * Proqram arxivi — REST ilə eyni filtr məntiqi (SSR + SEO).
 *
 * @param WP_Query $query Əsas sorğu.
 */
function sit_theme_program_archive_pre_get_posts( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! $query->is_post_type_archive( 'program' ) ) {
		return;
	}

	$query->set( 'posts_per_page', 12 );

	$tax_query = [];

	$degree = sit_theme_csv_to_slugs( isset( $_GET['sit_degree'] ) ? (string) wp_unslash( $_GET['sit_degree'] ) : '' );
	if ( ! empty( $degree ) ) {
		$tax_query[] = [
			'taxonomy' => 'degree_type',
			'field'    => 'slug',
			'terms'    => $degree,
			'operator' => 'IN',
		];
	}

	$lang = sit_theme_csv_to_slugs( isset( $_GET['sit_language'] ) ? (string) wp_unslash( $_GET['sit_language'] ) : '' );
	if ( ! empty( $lang ) ) {
		$tax_query[] = [
			'taxonomy' => 'program_language',
			'field'    => 'slug',
			'terms'    => $lang,
			'operator' => 'IN',
		];
	}

	$field = sit_theme_csv_to_slugs( isset( $_GET['sit_field'] ) ? (string) wp_unslash( $_GET['sit_field'] ) : '' );
	if ( ! empty( $field ) ) {
		$tax_query[] = [
			'taxonomy' => 'field_of_study',
			'field'    => 'slug',
			'terms'    => $field,
			'operator' => 'IN',
		];
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}
	if ( ! empty( $tax_query ) ) {
		$query->set( 'tax_query', $tax_query );
	}

	$meta_query = [];

	$univ_filter = isset( $_GET['sit_university'] ) ? absint( wp_unslash( $_GET['sit_university'] ) ) : 0;
	if ( $univ_filter > 0 ) {
		$meta_query[] = [
			'key'   => 'sit_university_id',
			'value' => $univ_filter,
		];
	}

	$city_slugs = sit_theme_csv_to_slugs( isset( $_GET['sit_city'] ) ? (string) wp_unslash( $_GET['sit_city'] ) : '' );
	if ( ! empty( $city_slugs ) ) {
		$univ_ids = get_posts(
			[
				'post_type'              => 'university',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'tax_query'              => [
					[
						'taxonomy' => 'city',
						'field'    => 'slug',
						'terms'    => $city_slugs,
						'operator' => 'IN',
					],
				],
			]
		);
		if ( empty( $univ_ids ) ) {
			$query->set( 'post__in', [ 0 ] );
		} else {
			$meta_query[] = [
				'key'     => 'sit_university_id',
				'value'   => array_map( 'absint', $univ_ids ),
				'compare' => 'IN',
				'type'    => 'NUMERIC',
			];
		}
	}

	$pmin = isset( $_GET['sit_price_min'] ) && is_numeric( $_GET['sit_price_min'] ) ? (float) wp_unslash( $_GET['sit_price_min'] ) : null;
	$pmax = isset( $_GET['sit_price_max'] ) && is_numeric( $_GET['sit_price_max'] ) ? (float) wp_unslash( $_GET['sit_price_max'] ) : null;

	if ( null !== $pmin && null !== $pmax ) {
		$meta_query[] = [
			'key'     => 'sit_tuition_fee',
			'value'   => [ $pmin, $pmax ],
			'compare' => 'BETWEEN',
			'type'    => 'NUMERIC',
		];
	} elseif ( null !== $pmin ) {
		$meta_query[] = [
			'key'     => 'sit_tuition_fee',
			'value'   => $pmin,
			'compare' => '>=',
			'type'    => 'NUMERIC',
		];
	} elseif ( null !== $pmax ) {
		$meta_query[] = [
			'key'     => 'sit_tuition_fee',
			'value'   => $pmax,
			'compare' => '<=',
			'type'    => 'NUMERIC',
		];
	}

	if ( count( $meta_query ) > 1 ) {
		$meta_query['relation'] = 'AND';
	}
	if ( ! empty( $meta_query ) ) {
		$query->set( 'meta_query', $meta_query );
	}

	$sort = isset( $_GET['sit_sort'] ) ? sanitize_key( wp_unslash( (string) $_GET['sit_sort'] ) ) : '';
	if ( '' === $sort ) {
		$sort = 'date_desc';
	}

	if ( 'price_asc' === $sort || 'price_desc' === $sort ) {
		$query->set( 'meta_key', 'sit_tuition_fee' );
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'order', 'price_asc' === $sort ? 'ASC' : 'DESC' );
	} elseif ( 'title_asc' === $sort ) {
		$query->set( 'orderby', 'title' );
		$query->set( 'order', 'ASC' );
	} elseif ( 'title_desc' === $sort ) {
		$query->set( 'orderby', 'title' );
		$query->set( 'order', 'DESC' );
	} elseif ( 'date_asc' === $sort ) {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'ASC' );
	} else {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}
}
add_action( 'pre_get_posts', 'sit_theme_program_archive_pre_get_posts' );
