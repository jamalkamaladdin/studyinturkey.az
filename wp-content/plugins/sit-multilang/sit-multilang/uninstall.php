<?php
/**
 * Fired when the SIT Multilang plugin is uninstalled (deleted).
 *
 * Removes all custom DB tables and plugin options.
 * This file is called by WordPress automatically — it is NOT a class.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-sit-db.php';

SIT_DB::drop_tables();

delete_option( 'sit_multilang_db_version' );
delete_option( 'sit_default_language' );
