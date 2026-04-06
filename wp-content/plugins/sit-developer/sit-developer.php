<?php
/**
 * Plugin Name: SIT Developer
 * Plugin URI:  https://studyinturkey.az
 * Description: Universitet, proqram və əlaqəli məzmun tipləri (StudyInTurkey.az).
 * Version:     0.5.0
 * Author:      StudyInTurkey
 * Author URI:  https://studyinturkey.az
 * Text Domain: studyinturkey
 * Domain Path: /languages
 * Requires PHP: 8.1
 * License:     GPL-2.0+
 */

defined( 'ABSPATH' ) || exit;

define( 'SIT_DEVELOPER_VERSION', '0.5.0' );
define( 'SIT_DEVELOPER_FILE', __FILE__ );
define( 'SIT_DEVELOPER_DIR', plugin_dir_path( __FILE__ ) );
define( 'SIT_DEVELOPER_URL', plugin_dir_url( __FILE__ ) );
define( 'SIT_DEVELOPER_BASENAME', plugin_basename( __FILE__ ) );

require_once SIT_DEVELOPER_DIR . 'includes/class-sit-developer-activator.php';
require_once SIT_DEVELOPER_DIR . 'includes/class-sit-university-cpt.php';
require_once SIT_DEVELOPER_DIR . 'includes/class-sit-university-meta.php';
require_once SIT_DEVELOPER_DIR . 'includes/class-sit-university-rewrites.php';
require_once SIT_DEVELOPER_DIR . 'includes/class-sit-university-admission-meta.php';
require_once SIT_DEVELOPER_DIR . 'includes/class-sit-university-about-meta.php';
require_once SIT_DEVELOPER_DIR . 'includes/class-sit-program-cpt.php';
require_once SIT_DEVELOPER_DIR . 'includes/class-sit-program-meta.php';
require_once SIT_DEVELOPER_DIR . 'includes/class-sit-extra-cpts.php';
require_once SIT_DEVELOPER_DIR . 'includes/class-sit-extra-meta.php';
require_once SIT_DEVELOPER_DIR . 'includes/class-sit-rest-api.php';
require_once SIT_DEVELOPER_DIR . 'includes/class-sit-developer.php';

register_activation_hook( __FILE__, [ 'SIT_Developer_Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'SIT_Developer_Activator', 'deactivate' ] );

SIT_Developer::instance();
