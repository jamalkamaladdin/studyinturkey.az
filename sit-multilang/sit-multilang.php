<?php
/**
 * Plugin Name: SIT Multilang
 * Plugin URI:  https://studyinturkey.az
 * Description: Custom multilingual system for StudyInTurkey.az — 6 languages with RTL support.
 * Version:     1.1.0
 * Author:      StudyInTurkey
 * Author URI:  https://studyinturkey.az
 * Text Domain: studyinturkey
 * Domain Path: /languages
 * Requires PHP: 8.1
 * License:     GPL-2.0+
 */

defined( 'ABSPATH' ) || exit;

define( 'SIT_MULTILANG_VERSION', '1.1.0' );
define( 'SIT_MULTILANG_FILE', __FILE__ );
define( 'SIT_MULTILANG_DIR', plugin_dir_path( __FILE__ ) );
define( 'SIT_MULTILANG_URL', plugin_dir_url( __FILE__ ) );
define( 'SIT_MULTILANG_BASENAME', plugin_basename( __FILE__ ) );

require_once SIT_MULTILANG_DIR . 'includes/class-sit-db.php';
require_once SIT_MULTILANG_DIR . 'includes/class-sit-languages.php';
require_once SIT_MULTILANG_DIR . 'includes/class-sit-activator.php';

register_activation_hook( __FILE__, [ 'SIT_Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'SIT_Activator', 'deactivate' ] );

/**
 * Main plugin class — singleton.
 */
final class SIT_Multilang {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks(): void {
        add_action( 'init', [ $this, 'load_textdomain' ] );
        add_action( 'init', [ $this, 'detect_language' ], 1 );
        add_action( 'plugins_loaded', [ $this, 'check_db_version' ] );
        add_action(
            'plugins_loaded',
            static function (): void {
                if ( is_admin() ) {
                    require_once SIT_MULTILANG_DIR . 'admin/class-sit-admin-languages.php';
                    SIT_Admin_Languages::init();
                }
            },
            5
        );
    }

    public function load_textdomain(): void {
        load_plugin_textdomain(
            'studyinturkey',
            false,
            dirname( SIT_MULTILANG_BASENAME ) . '/languages'
        );
    }

    /**
     * Detect current language from URL or fallback to default.
     * Full routing will be implemented in Mərhələ 2.4.
     */
    public function detect_language(): void {
        if ( ! defined( 'SIT_CURRENT_LANG' ) ) {
            $default = SIT_Languages::get_default_language_code();
            define( 'SIT_CURRENT_LANG', $default ?: 'az' );
        }
    }

    /**
     * Run DB migration when plugin version changes.
     */
    public function check_db_version(): void {
        $stored = get_option( 'sit_multilang_db_version', '0' );
        if ( version_compare( $stored, SIT_MULTILANG_VERSION, '<' ) ) {
            SIT_Activator::activate( true );
        }
    }
}

SIT_Multilang::instance();
