<?php
/**
 * Müraciət və sənəd cədvəlləri.
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Application_Db {

    public static function applications_table(): string {
        global $wpdb;
        return $wpdb->prefix . 'sit_applications';
    }

    public static function documents_table(): string {
        global $wpdb;
        return $wpdb->prefix . 'sit_application_documents';
    }

    public static function create_tables(): void {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $applications = "CREATE TABLE " . self::applications_table() . " (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            applicant_name varchar(191) NOT NULL,
            applicant_email varchar(191) NOT NULL,
            applicant_phone varchar(50) NOT NULL,
            program_id bigint(20) unsigned NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            applicant_message text DEFAULT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY program_id (program_id),
            KEY status (status),
            KEY applicant_email (applicant_email),
            KEY created_at (created_at)
        ) $charset_collate;";

        $documents = "CREATE TABLE " . self::documents_table() . " (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            application_id bigint(20) unsigned NOT NULL,
            document_type varchar(32) NOT NULL,
            file_path varchar(500) NOT NULL,
            file_name varchar(255) NOT NULL,
            mime_type varchar(100) NOT NULL,
            file_size bigint(20) unsigned NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY application_id (application_id),
            KEY document_type (document_type)
        ) $charset_collate;";

        dbDelta( $applications );
        dbDelta( $documents );
    }

    public static function drop_tables(): void {
        global $wpdb;

        // phpcs:disable WordPress.DB.DirectDatabaseQuery
        $wpdb->query( 'DROP TABLE IF EXISTS ' . self::documents_table() );
        $wpdb->query( 'DROP TABLE IF EXISTS ' . self::applications_table() );
        // phpcs:enable
    }
}
