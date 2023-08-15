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
 * @subpackage Api_Integration/includes/public
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
	public function cai_wp_enqueue_scripts_callback() {
		// Custom public style.
		wp_register_style(
			'api-integration-jquery-ui-style',
			CAI_PLUGIN_URL . 'includes/public/css/ui/jquery-ui.min.css',
			array(),
			filemtime( CAI_PLUGIN_PATH . 'includes/public/css/ui/jquery-ui.min.css' )
		);

		// Custom public style.
		wp_enqueue_style(
			'api-integration-public-style',
			CAI_PLUGIN_URL . 'includes/public/css/api-integration-public.css',
			array(),
			filemtime( CAI_PLUGIN_PATH . 'includes/public/css/api-integration-public.css' ),
		);

		// Custom public script.
		wp_enqueue_script(
			'api-integration-public-script',
			CAI_PLUGIN_URL . 'includes/public/js/api-integration-public.js',
			array( 'jquery', 'jquery-ui-datepicker' ),
			filemtime( CAI_PLUGIN_PATH . 'includes/public/js/api-integration-public.js' ),
			true
		);

		// Localize public script.
		wp_localize_script(
			'api-integration-public-script',
			'CAI_Public_JS_Obj',
			array(
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'ajaxnonce' => wp_create_nonce( 'cai-ajax-nonce' ),
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
	public function cai_woocommerce_account_menu_items_callback( $endpoints ) {
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
	public function cai_init_callback() {
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
	public function cai_woocommerce_account_news_endpoint_callback() {
		// Include the news listing template.
		cai_get_template( 'myaccount/news.php' );
	}

	/**
	 * Save the customer news preferences.
	 *
	 * @since 1.0.0
	 */
	private function cai_save_customer_news_preferences() {
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
	public function cai_api_integration_save_customer_news_preferences_callback( $customer_id = 0 ) {
		// Return, if the customer ID is invalid.
		if ( 0 === $customer_id ) {
			return;
		}

		delete_transient( "ai_newsapi_news_{$customer_id}" );
	}

	/**
	 * Ajax callback to scan the site.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function cai_paginate_news_callback() {
		// Check for nonce security.
		$nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );

		// Check, if the nonce is verified.
		if ( ! wp_verify_nonce( $nonce, 'cai-ajax-nonce' ) ) {
			wp_send_json_error(
				array(
					'code'          => 'ajax-failed',
					'error_message' => __( 'AJAX could not be processed as nonce couldn\'t be validated. Please contact the administrator.', 'api-integration' ),
				)
			);
			wp_die();
		}

		// Get the news.
		$page        = (int) filter_input( INPUT_POST, 'page', FILTER_SANITIZE_NUMBER_INT );
		$section     = filter_input( INPUT_POST, 'section', FILTER_SANITIZE_STRING );
		$customer_id = get_current_user_id();
		$news_items  = get_transient( "ai_newsapi_news_{$customer_id}" ); // Fetch the news data from the cache.
		$news_items  = ( false !== $news_items ) ? json_decode( $news_items, true ) : false;

		// Check, if there are no news items.
		if ( false === $news_items ) {
			wp_send_json_error(
				array(
					'code'          => 'ajax-failed',
					'error_message' => __( 'AJAX could not be processed as there are no news items in the memory.', 'api-integration' ),
				)
			);
			wp_die();
		}

		// Get the paginated news.
		$per_page    = get_option( 'ai_news_per_page' ); // News per page.
		$total_pages = count( $news_items ) / $per_page;
		$total_pages = ( is_float( $total_pages ) ) ? ( $total_pages + 1 ) : $total_pages;
		$news_items  = array_slice( $news_items, ( ( $page - 1 ) * $per_page ), $per_page ); // Sliced news items.

		// Check, if there are no news items.
		if ( empty( $news_items ) || ! is_array( $news_items ) ) {
			wp_send_json_error(
				array(
					'code'          => 'ajax-failed',
					'error_message' => __( 'AJAX could not be processed as there are no news items in the pagination.', 'api-integration' ),
				)
			);
			wp_die();
		}

		$html = '';

		// Loop through the paginated news items.
		foreach ( $news_items as $news_item ) {
			$news_item = (array) $news_item;

			// Get the html based on the section.
			if ( 'customer-portal' === $section ) {
				$html .= cai_get_news_row_html( $news_item );
			} elseif ( 'widget' === $section ) {
				$html .= cai_get_news_widget_section_html( $news_item );
			}
		}

		// Return the ajax response.
		wp_send_json_success(
			array(
				'code'        => 'paginated',
				'html'        => $html,
				'page'        => $page,
				'total_pages' => $total_pages,
			)
		);
		wp_die();
	}
}
