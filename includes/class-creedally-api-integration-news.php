<?php
/**
 * The file that defines the API functions.
 *
 * A class definition that holds the code that would fetch the news from the API
 * and manage it locally.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    CreedAlly_Api_Integration
 * @subpackage CreedAlly_Api_Integration/includes
 */

/**
 * The file that defines the API functions.
 *
 * A class definition that holds the code that would fetch the news from the API
 * and manage it locally.
 *
 * @since      1.0.0
 * @package    CreedAlly_Api_Integration
 * @subpackage CreedAlly_Api_Integration/includes
 * @author     CreedAlly_Api_Integration Verma <adarsh.srmcem@gmail.com>
 */
class CreedAlly_Api_Integration_News {
	/**
	 * Fetch the news.
	 */
	public static function get() {
		$customer_id = get_current_user_id(); // Current customer ID.
		$news_items  = get_transient( "ai_newsapi_news_{$customer_id}" ); // Fetch the news data from the cache.

		// If there news items in the cache.
		if ( false !== $news_items ) :
			return self::slice_data( $news_items );
		endif;

		// Access the API.
		$news_items = self::api(); // Shoot the API to get news items.

		/**
		 * Cache the response data.
		 * This cached data will be used to display the news items.
		 */
		if ( false !== $news_items ) :
			set_transient( "ai_newsapi_news_{$customer_id}", $news_items, ( 60 * 60 * 4 ) );
		endif;

		// Get the news items into sliced array to serve the pagination.
		$news_items = self::slice_data( $news_items );

		return $news_items;
	}

	/**
	 * Slice the news items array.
	 *
	 * @param array $news_items News items array.
	 * @return array
	 * @since 1.0.0
	 */
	private static function slice_data( $news_items ) {
		$per_page   = get_option( 'ai_news_per_page' ); // News per page.
		$news_items = json_decode( $news_items, true );
		$news_items = ( ! empty( $news_items ) && is_array( $news_items ) ) ? array_slice( $news_items, 0, $per_page ) : array();

		return $news_items;
	}

	/**
	 * Access the API fetching the news.
	 */
	private static function api() {
		// Get the admin configurations.
		$api_key              = get_option( 'ai_news_api_key' ); // News API key.
		$api_endpoint         = get_option( 'ai_news_api_endpoint' ); // News API endpoint.
		$customer_preferences = cai_get_customer_preferences(); // Get the customer preferences.
		$news_interest        = ( ! empty( $customer_preferences['news_interest'] ) ) ? $customer_preferences['news_interest'] : ''; // Customer interest.
		$news_domains         = ( ! empty( $customer_preferences['news_domains'] ) ) ? $customer_preferences['news_domains'] : ''; // News domains.
		$news_date_from       = ( ! empty( $customer_preferences['news_date_from'] ) ) ? $customer_preferences['news_date_from'] : ''; // News date from.
		$news_date_to         = ( ! empty( $customer_preferences['news_date_to'] ) ) ? $customer_preferences['news_date_to'] : ''; // News date to.
		$server_arr           = wp_unslash( $_SERVER );
		$api_payload          = array(
			'q'       => $news_interest,
			'from'    => $news_date_from,
			'to'      => $news_date_to,
			'domains' => $news_domains,
			'sortBy'  => 'popularity',
			'apiKey'  => $api_key,
		);

		// Log message.
		/* translators: 1: %d: current user id */
		$message = sprintf( __( 'NOTICE: Fetching news for customer ID, %1$d started.', 'api-integration' ), get_current_user_id() );
		cai_write_api_log( $message, true );

		/* translators: 1: %s: new api payload */
		$message = sprintf( __( 'NOTICE: API Payload: %1$s', 'api-integration' ), wp_json_encode( $api_payload ) );
		cai_write_api_log( $message, true ); // Write API log.

		// See if the news throw error.
		try {
			$news_api_url      = add_query_arg( $api_payload, $api_endpoint );
			$news_api_response = wp_remote_get( // Hit the API response.
				$news_api_url,
				array(
					'headers'   => array(
						'Content-Type' => 'application/json',
						'User-Agent'   => $server_arr['HTTP_USER_AGENT'],
					),
					'sslverify' => false,
					'timeout'   => 3600,
				)
			);
		} catch ( CreedAlly_Api_Integration_Exception $excep ) {
			// Display custom message.
			echo wp_kses_post( $excep->errorMessage() );
		}

		// Get the API response code.
		$news_api_response_code = wp_remote_retrieve_response_code( $news_api_response ); // Get the response code.

		/* translators: 1: %s: news api response code */
		$message = sprintf( __( 'NOTICE: API Response Code: %1$d', 'api-integration' ), $news_api_response_code );
		cai_write_api_log( $message, true ); // Write API log.

		// Return false, if the response if not 200 OK.
		if ( 200 !== $news_api_response_code ) :
			$response_message = ( ! empty( $news_api_response['response']['message'] ) ) ? $news_api_response['response']['message'] : '';
			/* translators: 1: %d: response code, 2: %s: response message */
			$message = sprintf( __( 'FAILURE: The API couldn\'t proceed due to the response code received: %1$d. Response message: %2$s', 'api-integration' ), $news_api_response_code, $response_message );
			cai_write_api_log( $message, true ); // Write API log.
			return false;
		endif;

		// Log message.
		$message = __( 'SUCCESS: News retrieved.', 'agreement-grid-service-contract' );
		cai_write_api_log( $message, true ); // Write API log.

		// Decode the response.
		$news_api_response_body = wp_remote_retrieve_body( $news_api_response ); // Get the response body.
		$news_api_response_body = ( ! empty( $news_api_response_body ) ) ? json_decode( $news_api_response_body ) : array();

		return ( ! empty( $news_api_response_body->articles ) ) ? wp_json_encode( $news_api_response_body->articles ) : false;
	}
}
