<?php
/**
 * Əsas plugin sinfi.
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Developer {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        SIT_REST_API::register();
        SIT_University_Rewrites::register();
        add_action( 'init', [ $this, 'load_textdomain' ], 0 );
        add_action( 'init', [ 'SIT_University_CPT', 'register' ], 5 );
        add_action( 'init', [ 'SIT_University_Meta', 'register' ], 6 );
        add_action( 'init', [ 'SIT_University_Admission_Meta', 'register' ], 6 );
        add_action( 'init', [ 'SIT_Program_CPT', 'register' ], 5 );
        add_action( 'init', [ 'SIT_Program_Meta', 'register' ], 6 );
        add_action( 'init', [ 'SIT_Extra_Cpts', 'register' ], 5 );
        add_action( 'init', [ 'SIT_Extra_Meta', 'register' ], 6 );
    }

    public function load_textdomain(): void {
        load_plugin_textdomain(
            'studyinturkey',
            false,
            dirname( SIT_DEVELOPER_BASENAME ) . '/languages'
        );
    }
}
