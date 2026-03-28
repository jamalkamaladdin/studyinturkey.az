<?php
/**
 * Plugin bootstrap.
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Developer_Application {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', [ $this, 'load_textdomain' ], 0 );
        add_action( 'init', [ $this, 'maybe_upgrade_db' ], 5 );
        add_action( 'init', [ 'SIT_Application_Handler', 'register' ], 9 );
        add_action( 'init', [ 'SIT_Application_Account', 'register' ], 10 );
        add_action( 'init', [ 'SIT_Application_Form', 'register' ], 11 );
    }

    public function load_textdomain(): void {
        load_plugin_textdomain(
            'studyinturkey',
            false,
            dirname( SIT_APPLICATION_BASENAME ) . '/languages'
        );
    }

    /**
     * Versiya dəyişəndə cədvəlləri yeniləyir (dbDelta).
     */
    public function maybe_upgrade_db(): void {
        $stored = get_option( 'sit_application_db_version', '' );
        if ( version_compare( (string) $stored, SIT_APPLICATION_VERSION, '>=' ) ) {
            return;
        }

        SIT_Application_Db::create_tables();
        update_option( 'sit_application_db_version', SIT_APPLICATION_VERSION );
    }
}
