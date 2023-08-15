<?php
/**
 * The file that defines the hooks executed at the public end.
 *
 * A class definition that holds all the hooks regarding all the custom functionalities at the public end.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Api_Integration
 * @subpackage Api_Integration/inc/public
 */

/**
 * The core plugin public class.
 *
 * A class definition that holds all the hooks regarding all the custom functionalities at the public end.
 *
 * @since      1.0.0
 * @package    Api_Integration
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class CreedAlly_Api_Integration_Public {

	/**
	 * Enqueue scripts on the public end.
	 */
	public function ai_wp_enqueue_scripts_callback() {
		// Custom public style.
		wp_register_style(
			'api-integration-jquery-ui-style',
			AI_PLUGIN_URL . 'inc/public/css/ui/jquery-ui.min.css',
			array(),
			filemtime( AI_PLUGIN_PATH . 'inc/public/css/ui/jquery-ui.min.css' )
		);

		// Custom public style.
		wp_enqueue_style(
			'api-integration-public-style',
			AI_PLUGIN_URL . 'inc/public/css/api-integration-public.css',
			array(),
			filemtime( AI_PLUGIN_PATH . 'inc/public/css/api-integration-public.css' ),
		);

		// Custom public script.
		wp_enqueue_script(
			'api-integration-public-script',
			AI_PLUGIN_URL . 'inc/public/js/api-integration-public.js',
			array( 'jquery', 'jquery-ui-datepicker' ),
			filemtime( AI_PLUGIN_PATH . 'inc/public/js/api-integration-public.js' ),
			true
		);

		// Localize public script.
		wp_localize_script(
			'api-integration-public-script',
			'AI_Public_JS_Obj',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Add custom endpoints in customer dashboard.
	 *
	 * @param array $endpoints Array of customer navigation endpoints.
	 * @return array
	 * @since 1.0.0
	 */
	public function ai_woocommerce_account_menu_items_callback( $endpoints ) {
		// Return, if the custom endpoint already exists.
		if ( array_key_exists( 'news', $endpoints ) ) {
			return $endpoints;
		}

		/**
		 * Prepare new set of endpoints.
		 * The code below add the endpoints after the "Orders" endpoint.
		 * Iterate through the endpoints to add custom endpoints after "orders".
		 */
		$new_endpoints = array();
		foreach ( $endpoints as $key => $endpoint ) {
			$new_endpoints[ $key ] = $endpoint;

			if ( 'orders' === $key ) {
				$new_endpoints['news'] = __( 'News', 'api-integration' );
			}
		}

		return $new_endpoints;
	}

	/**
	 * Rewrite the custom endpoints.
	 *
	 * @since 1.0.0
	 */
	public function ai_init_callback() {
		add_rewrite_endpoint( 'news', EP_ROOT | EP_PAGES ); // Rewrite the custom endpoint, news.

		// Flush the rewrite rules foor news endpoints.
		$set_news = get_option( 'customer_endpoint_news_flushed_rewrite_rules' );

		if ( 'yes' !== $set_news ) {
			flush_rewrite_rules( false );
			update_option( 'customer_endpoint_news_flushed_rewrite_rules', 'yes', false );
		}

		// Save the customer news preferences.
		$news_preferences_action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );
		$news_preferences_nonce  = filter_input( INPUT_POST, 'ai_customer_news_preferences_nonce', FILTER_SANITIZE_STRING );

		if ( ! is_null( $news_preferences_action ) && 'save_customer_news_preferences' === $news_preferences_action && wp_verify_nonce( $news_preferences_nonce, 'customer_news_preferences' ) ) {
			$this->ai_save_customer_news_preferences();
		}
	}

	/**
	 * Template for news list.
	 *
	 * @since 1.0.0
	 */
	public function ai_woocommerce_account_news_endpoint_callback() {
		// Include the news listing template.
		include_once 'templates/news.php';
	}

	/**
	 * Save the customer news preferences.
	 *
	 * @since 1.0.0
	 */
	private function ai_save_customer_news_preferences() {
		$news_interest  = filter_input( INPUT_POST, 'news_interest', FILTER_SANITIZE_STRING );
		$news_domains   = filter_input( INPUT_POST, 'news_domains', FILTER_SANITIZE_STRING );
		$news_date_from = filter_input( INPUT_POST, 'news_date_from', FILTER_SANITIZE_STRING );
		$news_date_to   = filter_input( INPUT_POST, 'news_date_to', FILTER_SANITIZE_STRING );
		$customer_id    = get_current_user_id();

		// Update the database.
		update_user_meta( $customer_id, 'news_interest', $news_interest );
		update_user_meta( $customer_id, 'news_domains', $news_domains );
		update_user_meta( $customer_id, 'news_date_from', $news_date_from );
		update_user_meta( $customer_id, 'news_date_to', $news_date_to );

		// Show the success notice.
		wc_add_notice( __( 'News preferences have been updated.', 'api-integration' ) );

		/**
		 * This hook runs after the customer news preferences are updated.
		 *
		 * This hook helps in doing additional actions after the customer news preferences are updated.
		 *
		 * @param int $customer_id Customer ID.
		 * @since 1.0.0
		 */
		do_action( 'api_integration_save_customer_news_preferences', $customer_id );
	}

	/**
	 * Delete the customer transient so that the news is fetched based on latest preferences.
	 *
	 * @param int $customer_id Customer ID.
	 * @since 1.0.0
	 */
	public function ai_api_integration_save_customer_news_preferences_callback( $customer_id = 0 ) {
		// Return, if the customer ID is invalid.
		if ( 0 === $customer_id ) {
			return;
		}

		delete_transient( "ai_newsapi_news_{$customer_id}" );
	}
}
