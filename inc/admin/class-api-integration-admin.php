<?php
/**
 * The file that defines the hooks executed at the admin end.
 *
 * A class definition that holds all the hooks regarding all the custom functionalities in the admin dashboard.
 *
 * @link       https://github.com/vermadarsh/
 * @since      0.1.0
 *
 * @package    Api_Integration
 * @subpackage Api_Integration/inc/admin
 */

/**
 * The core plugin admin class.
 *
 * A class definition that holds all the hooks regarding all the custom functionalities in the admin dashboard.
 *
 * @since      0.1.0
 * @package    Api_Integration
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class Api_Integration_Admin {
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Load all the admin hooks here.
	 *
	 * @since    0.1.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'ai_admin_init_callback' ) );
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'ai_woocommerce_get_settings_pages_callback' ) );
		add_action( 'widgets_init', array( $this, 'ai_widgets_init_callback' ) );
	}

	/**
	 * Actions to be taken at admin initialization.
	 */
	public function ai_admin_init_callback() {
		// Redirect after plugin redirect.
		if ( get_option( 'ai_do_activation_redirect' ) ) {
			delete_option( 'ai_do_activation_redirect' );
			wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=api-integration' ) );
			exit;
		}
	}

	/**
	 * Admin settings for api integration.
	 *
	 * @param array $settings Array of WC settings.
	 * @return array
	 * @since 0.1.0
	 */
	public function ai_woocommerce_get_settings_pages_callback( $settings ) {
		$settings[] = include 'class-api-integration-settings.php';

		return $settings;
	}

	/**
	 * Register a widget for showing the news items.
	 *
	 * @since 0.1.0
	 */
	public function ai_widgets_init_callback() {
		require_once AI_PLUGIN_PATH . 'inc/admin/class-api-integration-news-widget.php';
		register_widget( 'Api_Integration_News_Widget' );
	}
}
