<?php
/**
 * Language management for SIT Multilang.
 */

defined( 'ABSPATH' ) || exit;

class SIT_Languages {

    /**
     * Default languages to seed on activation.
     */
    private static function get_defaults(): array {
        return [
            [
                'code'        => 'az',
                'name'        => 'Azerbaijani',
                'native_name' => 'Azərbaycan',
                'locale'      => 'az_AZ',
                'direction'   => 'ltr',
                'flag'        => '🇦🇿',
                'is_active'   => 1,
                'is_default'  => 1,
                'sort_order'  => 1,
            ],
            [
                'code'        => 'en',
                'name'        => 'English',
                'native_name' => 'English',
                'locale'      => 'en_US',
                'direction'   => 'ltr',
                'flag'        => '🇬🇧',
                'is_active'   => 1,
                'is_default'  => 0,
                'sort_order'  => 2,
            ],
            [
                'code'        => 'ru',
                'name'        => 'Russian',
                'native_name' => 'Русский',
                'locale'      => 'ru_RU',
                'direction'   => 'ltr',
                'flag'        => '🇷🇺',
                'is_active'   => 1,
                'is_default'  => 0,
                'sort_order'  => 3,
            ],
            [
                'code'        => 'fa',
                'name'        => 'Persian',
                'native_name' => 'فارسی',
                'locale'      => 'fa_IR',
                'direction'   => 'rtl',
                'flag'        => '🇮🇷',
                'is_active'   => 1,
                'is_default'  => 0,
                'sort_order'  => 4,
            ],
            [
                'code'        => 'ar',
                'name'        => 'Arabic',
                'native_name' => 'العربية',
                'locale'      => 'ar',
                'direction'   => 'rtl',
                'flag'        => '🇸🇦',
                'is_active'   => 1,
                'is_default'  => 0,
                'sort_order'  => 5,
            ],
            [
                'code'        => 'kk',
                'name'        => 'Kazakh',
                'native_name' => 'Қазақша',
                'locale'      => 'kk_KZ',
                'direction'   => 'ltr',
                'flag'        => '🇰🇿',
                'is_active'   => 1,
                'is_default'  => 0,
                'sort_order'  => 6,
            ],
        ];
    }

    /**
     * Insert default languages if the table is empty.
     */
    public static function seed_defaults(): void {
        global $wpdb;

        $table = SIT_DB::languages_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );

        if ( $count > 0 ) {
            return;
        }

        foreach ( self::get_defaults() as $lang ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->insert( $table, $lang );
        }
    }

    /**
     * Get all active languages.
     *
     * @return array<object>
     */
    public static function get_active_languages(): array {
        global $wpdb;

        $table = SIT_DB::languages_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $results = $wpdb->get_results(
            "SELECT * FROM {$table} WHERE is_active = 1 ORDER BY sort_order ASC"
        );

        return $results ?: [];
    }

    /**
     * Get all languages (including inactive).
     *
     * @return array<object>
     */
    public static function get_all_languages(): array {
        global $wpdb;

        $table = SIT_DB::languages_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $results = $wpdb->get_results(
            "SELECT * FROM {$table} ORDER BY sort_order ASC"
        );

        return $results ?: [];
    }

    /**
     * Get a single language by code.
     */
    public static function get_language( string $code ): ?object {
        global $wpdb;

        $table = SIT_DB::languages_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE code = %s", $code )
        );
    }

    /**
     * Get the default language code.
     */
    public static function get_default_language_code(): string {
        global $wpdb;

        $table = SIT_DB::languages_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $code = $wpdb->get_var(
            "SELECT code FROM {$table} WHERE is_default = 1 LIMIT 1"
        );

        return $code ?: 'az';
    }

    /**
     * Get active language codes as a flat array.
     *
     * @return string[]
     */
    public static function get_active_codes(): array {
        $languages = self::get_active_languages();
        return array_column( $languages, 'code' );
    }

    /**
     * Check if a language code is valid and active.
     */
    public static function is_valid_code( string $code ): bool {
        return in_array( $code, self::get_active_codes(), true );
    }

    /**
     * Get direction for a language code.
     */
    public static function get_direction( string $code ): string {
        $lang = self::get_language( $code );
        return $lang->direction ?? 'ltr';
    }
}
