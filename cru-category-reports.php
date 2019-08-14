<?php
/**
 * Plugin Name:       Cru Category Reports
 * Plugin URI:        http://cru.io
 * Description:       The CRU Category Reports
 * Version:           1.0.0
 * Author:            CRU Team (info@cru.io)
 * Author URI:        http://cru.io/
 * License:
 * License URI:
 * Text Domain:       cru-reports
 * Domain Path:       /languages
 */

namespace CruReports;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'CRUREPORTS_ROOT_DIR', plugin_dir_path( __FILE__ ) );
define( 'CRUREPORTS_PLUGIN_NAME', 'cru-reports' );
define( 'CRUREPORTS_VERSION', '1.0.0' );

// We load Composer's autoload file
require_once CRUREPORTS_ROOT_DIR . 'vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_cru_reports() {
	utils\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_cru_reports() {
	utils\Deactivator::deactivate();
}

register_activation_hook( __FILE__, '\CruReports\activate_cru_reports' );
register_deactivation_hook( __FILE__, '\CruReports\deactivate_cru_reports' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_cru_reports() {
	$plugin = new Main();
	$plugin->run();
}

run_cru_reports();