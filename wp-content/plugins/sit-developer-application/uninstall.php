<?php
/**
 * Plugin silinəndə: cədvəllər və seçimlər.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-sit-application-db.php';

SIT_Application_Db::drop_tables();

delete_option( 'sit_application_db_version' );
delete_option( 'sit_application_notify_admin_new' );
delete_option( 'sit_application_notify_applicant_status' );
delete_option( 'sit_application_notify_extra_emails' );
delete_option( 'sit_application_whatsapp_number' );
