<?php
/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    CreedAlly_Api_Integration
 * @subpackage CreedAlly_Api_Integration/includes
 */

/**
 * The file that defines the core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    CreedAlly_Api_Integration
 * @subpackage CreedAlly_Api_Integration/includes
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class CreedAlly_Api_Integration {
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Load all the admin hooks here.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->load_dependant_files(); // Load the required files.
		$this->define_admin_side_hooks(); // Define the hooks to run on the admin side.
		$this->define_public_facing_hooks(); // Define the hook running on the public facing of the site.
	}

	/**
	 * Load the required files for this plugin.
	 *
	 * Include the following files that make the plugin work:
	 *
	 * - CreedAlly_Api_Integration_Admin. Defines all hooks for the admin area.
	 * - CreedAlly_Api_Integration_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependant_files() {
		// This function is used to write reusable functions.
		require_once CAI_PLUGIN_PATH . 'includes/creedally-api-integration-functions.php';

		// The class responsible for defining the API functions.
		require_once CAI_PLUGIN_PATH . 'includes/class-creedally-api-integration-news.php';

		// The class responsible for defining all actions that occur in the admin area.
		require_once CAI_PLUGIN_PATH . 'includes/admin/class-creedally-api-integration-admin.php';

		// The class responsible for defining all actions that occur in the public-facing side of the site.
		require_once CAI_PLUGIN_PATH . 'includes/public/class-creedally-api-integration-public.php';
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_side_hooks() {
		$admin_class_obj = new CreedAlly_Api_Integration_Admin();

		add_action( 'admin_init', array( $admin_class_obj, 'cai_admin_init_callback' ) );
		add_filter( 'woocommerce_get_settings_pages', array( $admin_class_obj, 'cai_woocommerce_get_settings_pages_callback' ) );
		add_action( 'widgets_init', array( $admin_class_obj, 'cai_widgets_init_callback' ) );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_facing_hooks() {
		$public_class_obj = new CreedAlly_Api_Integration_Public();

		add_action( 'wp_enqueue_scripts', array( $public_class_obj, 'cai_wp_enqueue_scripts_callback' ) );
		add_filter( 'woocommerce_account_menu_items', array( $public_class_obj, 'cai_woocommerce_account_menu_items_callback' ) );
		add_action( 'init', array( $public_class_obj, 'cai_init_callback' ) );
		add_action( 'woocommerce_account_news_endpoint', array( $public_class_obj, 'cai_woocommerce_account_news_endpoint_callback' ) );
		add_action( 'api_integration_save_customer_news_preferences', array( $public_class_obj, 'cai_api_integration_save_customer_news_preferences_callback' ) );
		add_action( 'wp_ajax_paginate_news', array( $public_class_obj, 'cai_paginate_news_callback' ) );
	}
}
