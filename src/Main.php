<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://cru.io
 * @since      1.0.0
 */

namespace CruReports;

use CruReports\utils\Loader;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @author     CRU Team <info@cru.io>
 */
class Main {
	
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;
	
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->loader = new Loader();
		
		if (is_admin()) {
			// define admin hooks
			$this->define_admin_hooks();
		}
	}
	
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}
		
	private function define_admin_hooks(){
		$plugin_admin = new admin\Cruclub_Reports_Controller();

		//admin_enqueue_scripts		
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');

		//admin_footer
		$this->loader->add_action('admin_footer', $plugin_admin, 'enqueue_ajaxScripts');
		$this->loader->add_action('admin_footer', $plugin_admin, 'enqueue_scripts');

		//admin_menu
		$this->loader->add_action('admin_menu', $plugin_admin, 'crureports_add_menu');

		//function used Ajax
		$this->loader->add_action( 'wp_ajax_crureports_get_products_by_category', $plugin_admin, 'crureports_get_products_by_category' );
		$this->loader->add_action( 'wp_ajax_nopriv_crureports_get_products_by_category', $plugin_admin, 'crureports_get_products_by_category' );	

	}
}