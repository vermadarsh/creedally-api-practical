<?php
/**
 * The file that defines the hooks executed at the admin end.
 *
 * A class definition that holds all the hooks regarding all the custom functionalities in the admin dashboard.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Api_Integration
 * @subpackage Api_Integration/includes/admin
 */

/**
 * The core plugin admin class.
 *
 * A class definition that holds all the hooks regarding all the custom functionalities in the admin dashboard.
 *
 * @since      1.0.0
 * @package    Api_Integration
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class CreedAlly_Api_Integration_Admin {

	/**
	 * Actions to be taken at admin initialization.
	 */
	public function cai_admin_init_callback() {
		// Redirect after plugin redirect.
		if ( get_option( 'cai_do_activation_redirect' ) ) {
			delete_option( 'cai_do_activation_redirect' );
			wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=api-integration' ) );
			exit;
		}
	}

	/**
	 * Admin settings for api integration.
	 *
	 * @param array $settings Array of WC settings.
	 * @return array
	 * @since 1.0.0
	 */
	public function cai_woocommerce_get_settings_pages_callback( $settings ) {
		$settings[] = include 'settings/class-creedally-api-integration-settings.php';

		return $settings;
	}

	/**
	 * Register a widget for showing the news items.
	 *
	 * @since 1.0.0
	 */
	public function cai_widgets_init_callback() {
		require_once CAI_PLUGIN_PATH . 'includes/class-creedally-api-integration-news-widget.php';
		register_widget( 'CreedAlly_Api_Integration_News_Widget' );
	}
}
