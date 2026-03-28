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
     * Get a single language by row ID.
     */
    public static function get_language_by_id( int $id ): ?object {
        global $wpdb;

        $table = SIT_DB::languages_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id )
        );
    }

    /**
     * Total number of language rows.
     */
    public static function count_languages(): int {
        global $wpdb;

        $table = SIT_DB::languages_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
    }

    /**
     * Set is_default = 0 on all rows.
     */
    public static function unset_all_defaults(): void {
        global $wpdb;

        $table = SIT_DB::languages_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $wpdb->update( $table, [ 'is_default' => 0 ], [ 'is_default' => 1 ] );
    }

    /**
     * Sync WordPress option with DB default language code.
     */
    public static function sync_default_option(): void {
        $code = self::get_default_language_code();
        if ( $code ) {
            update_option( 'sit_default_language', $code );
        }
    }

    /**
     * Insert a new language row.
     *
     * @param array<string, mixed> $data Row data (keys match table columns except id).
     * @return int|false New row ID or false on failure.
     */
    public static function insert_language( array $data ) {
        global $wpdb;

        $table = SIT_DB::languages_table();

        if ( ! empty( $data['is_default'] ) ) {
            self::unset_all_defaults();
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $ok = $wpdb->insert( $table, $data );

        if ( ! $ok ) {
            return false;
        }

        $new_id = (int) $wpdb->insert_id;

        self::sync_default_option();

        return $new_id;
    }

    /**
     * Update an existing language row.
     *
     * @param array<string, mixed> $data Fields to update.
     */
    public static function update_language( int $id, array $data ): bool {
        global $wpdb;

        $table = SIT_DB::languages_table();

        if ( ! self::get_language_by_id( $id ) ) {
            return false;
        }

        if ( ! empty( $data['is_default'] ) ) {
            self::unset_all_defaults();
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $ok = $wpdb->update( $table, $data, [ 'id' => $id ] );

        self::sync_default_option();

        return false !== $ok;
    }

    /**
     * Set one language as default (others cleared).
     */
    public static function set_default_by_id( int $id ): bool {
        $lang = self::get_language_by_id( $id );
        if ( ! $lang ) {
            return false;
        }

        self::unset_all_defaults();

        global $wpdb;
        $table = SIT_DB::languages_table();

        // Default dil həmişə aktiv olmalıdır.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $wpdb->update(
            $table,
            [
                'is_default' => 1,
                'is_active'  => 1,
            ],
            [ 'id' => $id ]
        );

        update_option( 'sit_default_language', $lang->code );

        return true;
    }

    /**
     * Toggle active flag. Cannot deactivate the default language.
     */
    public static function set_language_active( int $id, bool $active ): bool|string {
        $lang = self::get_language_by_id( $id );
        if ( ! $lang ) {
            return false;
        }

        if ( ! $active && (int) $lang->is_default === 1 ) {
            return 'default_cannot_deactivate';
        }

        global $wpdb;
        $table = SIT_DB::languages_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $wpdb->update( $table, [ 'is_active' => $active ? 1 : 0 ], [ 'id' => $id ] );

        return true;
    }

    /**
     * Delete a language. At least one must remain.
     * If the deleted row was default, assigns default to another active row.
     */
    public static function delete_language( int $id ): bool|string {
        if ( self::count_languages() <= 1 ) {
            return 'last_language';
        }

        $lang = self::get_language_by_id( $id );
        if ( ! $lang ) {
            return false;
        }

        $was_default = (int) $lang->is_default === 1;

        global $wpdb;
        $table = SIT_DB::languages_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $wpdb->delete( $table, [ 'id' => $id ] );

        if ( $was_default ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $next = $wpdb->get_row(
                "SELECT id FROM {$table} WHERE is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT 1"
            );
            if ( $next ) {
                self::set_default_by_id( (int) $next->id );
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $any = $wpdb->get_row( "SELECT id FROM {$table} ORDER BY sort_order ASC, id ASC LIMIT 1" );
                if ( $any ) {
                    self::set_default_by_id( (int) $any->id );
                }
            }
        }

        self::sync_default_option();

        return true;
    }

    /**
     * Check if a language code exists (optionally exclude one ID for updates).
     */
    public static function code_exists( string $code, ?int $exclude_id = null ): bool {
        global $wpdb;

        $table = SIT_DB::languages_table();

        if ( null !== $exclude_id ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $found = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$table} WHERE code = %s AND id != %d LIMIT 1",
                    $code,
                    $exclude_id
                )
            );
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $found = $wpdb->get_var(
                $wpdb->prepare( "SELECT id FROM {$table} WHERE code = %s LIMIT 1", $code )
            );
        }

        return (bool) $found;
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
