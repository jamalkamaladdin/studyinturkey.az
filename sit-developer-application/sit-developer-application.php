<?php
/**
 * Plugin Name: SIT Developer Application
 * Plugin URI:  https://studyinturkey.az
 * Description: Onlayn müraciət formu, sənəd yükləmə və müraciət qeydləri (StudyInTurkey.az).
 * Version:     0.4.0
 * Author:      StudyInTurkey
 * Author URI:  https://studyinturkey.az
 * Text Domain: studyinturkey
 * Domain Path: /languages
 * Requires PHP: 8.1
 * License:     GPL-2.0+
 */

defined( 'ABSPATH' ) || exit;

define( 'SIT_APPLICATION_VERSION', '0.4.0' );
define( 'SIT_APPLICATION_FILE', __FILE__ );
define( 'SIT_APPLICATION_DIR', plugin_dir_path( __FILE__ ) );
define( 'SIT_APPLICATION_URL', plugin_dir_url( __FILE__ ) );
define( 'SIT_APPLICATION_BASENAME', plugin_basename( __FILE__ ) );

require_once SIT_APPLICATION_DIR . 'includes/class-sit-application-db.php';
require_once SIT_APPLICATION_DIR . 'includes/class-sit-application-degree.php';
require_once SIT_APPLICATION_DIR . 'includes/class-sit-application-activator.php';
require_once SIT_APPLICATION_DIR . 'includes/class-sit-application-queries.php';
require_once SIT_APPLICATION_DIR . 'includes/class-sit-application-account.php';
require_once SIT_APPLICATION_DIR . 'includes/class-sit-application-notifications.php';
require_once SIT_APPLICATION_DIR . 'includes/class-sit-application-handler.php';
require_once SIT_APPLICATION_DIR . 'includes/class-sit-application-form.php';
require_once SIT_APPLICATION_DIR . 'includes/class-sit-application-admin.php';
require_once SIT_APPLICATION_DIR . 'includes/class-sit-developer-application.php';

register_activation_hook( __FILE__, [ 'SIT_Application_Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'SIT_Application_Activator', 'deactivate' ] );

SIT_Developer_Application::instance();
