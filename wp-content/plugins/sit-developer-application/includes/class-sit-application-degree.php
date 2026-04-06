<?php
/**
 * Proqram dərəcəsi səviyyəsi (müraciət formu üçün).
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Application_Degree {

	public const LEVEL_UNDERGRADUATE = 'undergraduate';
	public const LEVEL_GRADUATE      = 'graduate';
	public const LEVEL_DOCTORAL      = 'doctoral';

	/**
	 * @return self::LEVEL_*
	 */
	public static function level_for_program( int $program_id ): string {
		if ( $program_id <= 0 ) {
			return self::LEVEL_UNDERGRADUATE;
		}

		$terms = get_the_terms( $program_id, 'degree_type' );
		if ( ! is_array( $terms ) || is_wp_error( $terms ) ) {
			return self::LEVEL_UNDERGRADUATE;
		}

		foreach ( $terms as $t ) {
			$slug = strtolower( (string) $t->slug );
			$name = strtolower( (string) $t->name );
			if ( str_contains( $slug, 'phd' ) || str_contains( $name, 'phd' )
				|| str_contains( $name, 'doktor' ) || str_contains( $name, 'doctor' ) ) {
				return self::LEVEL_DOCTORAL;
			}
		}

		foreach ( $terms as $t ) {
			$slug = strtolower( (string) $t->slug );
			$name = strtolower( (string) $t->name );
			if ( str_contains( $slug, 'master' ) || str_contains( $name, 'master' )
				|| str_contains( $name, 'magistr' ) || str_contains( $name, 'mba' ) ) {
				return self::LEVEL_GRADUATE;
			}
		}

		return self::LEVEL_UNDERGRADUATE;
	}
}
