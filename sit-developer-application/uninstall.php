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
