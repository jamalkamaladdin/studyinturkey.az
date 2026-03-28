<?php
/**
 * Aktivasiya: rewrite flush, default terminlər.
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Developer_Activator {

    public static function activate( bool $network_wide = false ): void {
        SIT_University_CPT::register();
        self::seed_default_terms();
        flush_rewrite_rules();
        update_option( 'sit_developer_version', SIT_DEVELOPER_VERSION );
    }

    public static function deactivate( bool $network_wide = false ): void {
        flush_rewrite_rules();
    }

    /**
     * Şəhər və universitet növü üçün başlanğıc terminlər (təkrarlanmır).
     */
    private static function seed_default_terms(): void {
        $cities = [
            __( 'İstanbul', 'studyinturkey' ),
            __( 'Ankara', 'studyinturkey' ),
            __( 'İzmir', 'studyinturkey' ),
            __( 'Bursa', 'studyinturkey' ),
            __( 'Antalya', 'studyinturkey' ),
        ];

        foreach ( $cities as $name ) {
            if ( ! term_exists( $name, 'city' ) ) {
                wp_insert_term( $name, 'city' );
            }
        }

        $types = [
            __( 'Dövlət', 'studyinturkey' ),
            __( 'Özəl', 'studyinturkey' ),
        ];

        foreach ( $types as $name ) {
            if ( ! term_exists( $name, 'university_type' ) ) {
                wp_insert_term( $name, 'university_type' );
            }
        }
    }
}
