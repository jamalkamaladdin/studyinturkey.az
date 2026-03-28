<?php
/**
 * Activation, deactivation, and upgrade routines for SIT Multilang.
 */

defined( 'ABSPATH' ) || exit;

class SIT_Activator {

    /**
     * Runs on plugin activation and on version upgrades.
     *
     * 1. Creates / updates DB tables via dbDelta.
     * 2. Seeds default languages if table is empty.
     * 3. Stores current DB version in options.
     * 4. Flushes rewrite rules so language prefixes work immediately.
     */
    public static function activate(): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        SIT_DB::create_tables();
        SIT_Languages::seed_defaults();

        update_option( 'sit_multilang_db_version', SIT_MULTILANG_VERSION );
        update_option( 'sit_default_language', 'az' );

        flush_rewrite_rules();
    }

    /**
     * Runs on plugin deactivation.
     *
     * Only flushes rewrite rules — data is preserved.
     */
    public static function deactivate(): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        flush_rewrite_rules();
    }
}
