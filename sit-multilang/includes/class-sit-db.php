<?php
/**
 * Database schema and table management for SIT Multilang.
 */

defined( 'ABSPATH' ) || exit;

class SIT_DB {

    /**
     * Table name helpers — always include WP prefix.
     */
    public static function languages_table(): string {
        global $wpdb;
        return $wpdb->prefix . 'sit_languages';
    }

    public static function translations_table(): string {
        global $wpdb;
        return $wpdb->prefix . 'sit_translations';
    }

    public static function strings_table(): string {
        global $wpdb;
        return $wpdb->prefix . 'sit_strings';
    }

    /**
     * Create all custom tables.
     * Uses dbDelta for safe, idempotent migrations.
     */
    public static function create_tables(): void {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql_languages = "CREATE TABLE " . self::languages_table() . " (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            code varchar(10) NOT NULL,
            name varchar(100) NOT NULL,
            native_name varchar(100) NOT NULL,
            locale varchar(20) NOT NULL,
            direction enum('ltr','rtl') NOT NULL DEFAULT 'ltr',
            flag varchar(10) DEFAULT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            is_default tinyint(1) NOT NULL DEFAULT 0,
            sort_order int(11) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY code (code),
            KEY is_active (is_active)
        ) $charset_collate;";

        $sql_translations = "CREATE TABLE " . self::translations_table() . " (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            object_id bigint(20) unsigned NOT NULL,
            object_type varchar(50) NOT NULL,
            lang_code varchar(10) NOT NULL,
            field_name varchar(100) NOT NULL,
            field_value longtext DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY object_field_lang (object_id,object_type,lang_code,field_name),
            KEY object_lookup (object_id,object_type),
            KEY lang_code (lang_code)
        ) $charset_collate;";

        $sql_strings = "CREATE TABLE " . self::strings_table() . " (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            string_key varchar(255) NOT NULL,
            context varchar(100) DEFAULT 'general',
            lang_code varchar(10) NOT NULL,
            string_value text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY key_lang (string_key,lang_code),
            KEY context (context),
            KEY lang_code (lang_code)
        ) $charset_collate;";

        dbDelta( $sql_languages );
        dbDelta( $sql_translations );
        dbDelta( $sql_strings );
    }

    /**
     * Drop all custom tables — used only on uninstall.
     */
    public static function drop_tables(): void {
        global $wpdb;

        // phpcs:disable WordPress.DB.DirectDatabaseQuery
        $wpdb->query( "DROP TABLE IF EXISTS " . self::translations_table() );
        $wpdb->query( "DROP TABLE IF EXISTS " . self::strings_table() );
        $wpdb->query( "DROP TABLE IF EXISTS " . self::languages_table() );
        // phpcs:enable
    }
}
