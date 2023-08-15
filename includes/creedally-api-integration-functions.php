<?php
/**
 * This file is used for writing all the re-usable custom functions.
 *
 * @since   1.0.0
 * @package Api_Integration
 * @subpackage Api_Integration/inc
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Check if the function, 'ai_get_customer_preferences' exists.
 */
if ( ! function_exists( 'ai_get_customer_preferences' ) ) {
	/**
	 * Get the customer news preferences.
	 *
	 * @return array|bool
	 * @since 1.0.0
	 */
	function ai_get_customer_preferences() {
		$customer_id          = get_current_user_id();
		$customer_preferences = array_filter(
			array(
				'news_interest'  => get_user_meta( $customer_id, 'news_interest', true ),
				'news_domains'   => get_user_meta( $customer_id, 'news_domains', true ),
				'news_date_from' => get_user_meta( $customer_id, 'news_date_from', true ),
				'news_date_to'   => get_user_meta( $customer_id, 'news_date_to', true ),
			)
		);

		return ( empty( $customer_preferences ) || ! is_array( $customer_preferences ) ) ? false : $customer_preferences;
	}
}

/**
 * Check if the function, 'ai_get_news' exists.
 */
if ( ! function_exists( 'ai_get_news' ) ) {
	/**
	 * Get the news from the API.
	 *
	 * @return array|bool
	 * @since 1.0.0
	 */
	function ai_get_news() {
		// Get the admin configurations.
		$api_key              = get_option( 'ai_news_api_key' ); // News API key.
		$api_endpoint         = get_option( 'ai_news_api_endpoint' ); // News API endpoint.
		$customer_preferences = ai_get_customer_preferences(); // Get the customer preferences.
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
		ai_write_api_log( $message, true );

		/* translators: 1: %s: new api payload */
		$message = sprintf( __( 'NOTICE: API Payload: %1$s', 'api-integration' ), wp_json_encode( $api_payload ) );
		ai_write_api_log( $message, true ); // Write API log.

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

		// Get the API response code.
		$news_api_response_code = wp_remote_retrieve_response_code( $news_api_response ); // Get the response code.

		/* translators: 1: %s: news api response code */
		$message = sprintf( __( 'NOTICE: API Response Code: %1$d', 'api-integration' ), $news_api_response_code );
		ai_write_api_log( $message, true ); // Write API log.

		// Return false, if the response if not 200 OK.
		if ( 200 !== $news_api_response_code ) {
			$response_message = ( ! empty( $news_api_response['response']['message'] ) ) ? $news_api_response['response']['message'] : '';
			/* translators: 1: %d: response code, 2: %s: response message */
			$message = sprintf( __( 'FAILURE: The API couldn\'t proceed due to the response code received: %1$d. Response message: %2$s', 'api-integration' ), $news_api_response_code, $response_message );
			ai_write_api_log( $message, true ); // Write API log.
			return false;
		}

		// Log message.
		$message = __( 'SUCCESS: News retrieved.', 'agreement-grid-service-contract' );
		ai_write_api_log( $message, true ); // Write API log.

		// Decode the response.
		$news_api_response_body = wp_remote_retrieve_body( $news_api_response ); // Get the response body.
		$news_api_response_body = ( ! empty( $news_api_response_body ) ) ? json_decode( $news_api_response_body ) : array();

		return ( ! empty( $news_api_response_body->articles ) ) ? $news_api_response_body->articles : false;
	}
}

/**
 * Check if the function, 'ai_write_api_log' exists.
 */
if ( ! function_exists( 'ai_write_api_log' ) ) {
	/**
	 * Write log to the log file.
	 *
	 * @param string  $message Holds the log message.
	 * @param boolean $include_date_time Include date time in the message.
	 * @return void
	 */
	function ai_write_api_log( $message = '', $include_date_time = false ) {
		global $wp_filesystem;

		// Return, if the message is empty.
		if ( empty( $message ) ) {
			return;
		}

		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();

		// Log file.
		$user_id  = get_current_user_id();
		$log_file = CAIWC_LOG_DIR_PATH . "customer_preferred_news_{$user_id}.log";

		// Check if the file is created.
		if ( ! $wp_filesystem->exists( $log_file ) ) {
			$wp_filesystem->put_contents( $log_file, '', FS_CHMOD_FILE ); // Create the file.
		}

		// Fetch the old content.
		$content  = $wp_filesystem->get_contents( $log_file );
		$content .= ( $include_date_time ) ? "\n" . ai_get_current_datetime( 'Y-m-d H:i:s' ) . ' :: ' . $message : "\n" . $message;

		// Put the updated content.
		$wp_filesystem->put_contents(
			$log_file,
			$content,
			FS_CHMOD_FILE // predefined mode settings for WP files.
		);
	}
}

/**
 * Check if the function, 'ai_get_current_datetime' exists.
 */
if ( ! function_exists( 'ai_get_current_datetime' ) ) {
	/**
	 * Return the current date according to local time.
	 *
	 * @param string $format Holds the format string.
	 * @return string
	 */
	function ai_get_current_datetime( $format = 'Y-m-d' ) {
		$timezone_format = _x( $format, 'timezone date format' ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Format is a dynamic value.

		return date_i18n( $timezone_format );
	}
}

/**
 * Check if the function, 'cai_get_template' exists.
 */
if ( ! function_exists( 'cai_get_template' ) ) {
	/**
	 * Get other templates (e.g. product attributes) passing attributes and including the file.
	 *
	 * @param string $template_name Template name.
	 * @param string $template_path Template path. (default: '').
	 * @param string $default_path  Default path. (default: '').
	 */
	/**
	 * Return the current date according to local time.
	 *
	 * @param string $format Holds the format string.
	 * @return string
	 */
	function cai_get_template( $template_name, $template_path = '', $default_path = '' ) {
		$template = cai_locate_template( $template_name, $template_path, $default_path );

		// Allow 3rd party plugin filter template file from their plugin.
		$filter_template = apply_filters( 'cai_get_template', $template, $template_name, $template_path, $default_path );

		if ( $filter_template !== $template ) {
			if ( ! file_exists( $filter_template ) ) {
				/* translators: %s template */
				wc_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'api-integration' ), '<code>' . $filter_template . '</code>' ), '1.0.0' );
				return;
			}

			$template = $filter_template;
		}

		include $template;
	}
}

/**
 * Check if the function, 'cai_locate_template' exists.
 */
if ( ! function_exists( 'cai_locate_template' ) ) {
	/**
	 * Locate a template and return the path for inclusion.
	 *
	 * This is the load order:
	 *
	 * yourtheme/$template_path/$template_name
	 * yourtheme/$template_name
	 * $default_path/$template_name
	 *
	 * @param string $template_name Template name.
	 * @param string $template_path Template path. (default: '').
	 * @param string $default_path  Default path. (default: '').
	 * @return string
	 */
	function cai_locate_template( $template_name, $template_path = '', $default_path = '' ) {
		if ( ! $template_path ) {
			$template_path = 'creedally-api-integration/';
		}

		if ( ! $default_path ) {
			$default_path = CAI_PLUGIN_PATH . 'templates/';
		}

		// Look within passed path within the theme - this is priority.
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			)
		);

		// If the concerned template is not found in the theme, get the default path from the plugin.
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		// Return what is found.
		return apply_filters( 'cai_locate_template', $template, $template_name, $template_path );
	}
}

/**
 * Check if the function, 'cai_get_news_row_html' exists.
 */
if ( ! function_exists( 'cai_get_news_row_html' ) ) {
	/**
	 * Return the current date according to local time.
	 *
	 * @param string $format Holds the format string.
	 * @return string
	 */
	function cai_get_news_row_html( $news_item = array() ) {
		// Return, if the news item array is blank.
		if ( empty( $news_item ) || ! is_array( $news_item ) ) {
			return;
		}

		// Start preparing the HTML.
		ob_start();
		?>
		<tr>
			<td data-title="<?php esc_html_e( 'Image', 'api-integration' ); ?>"><img src="<?php echo esc_url( ( ! empty( $news_item['urlToImage'] ) ? $news_item['urlToImage'] : '' ) ); ?>" alt="news-item-featured-image" /></td>
			<td data-title="<?php esc_html_e( 'Title', 'api-integration' ); ?>"><a href="<?php echo esc_url( ( ! empty( $news_item['url'] ) ? $news_item['url'] : '' ) ); ?>" target="_blank" title="<?php echo wp_kses_post( ( ! empty( $news_item['title'] ) ? $news_item['title'] : '' ) ); ?>"><?php echo wp_kses_post( ( ! empty( $news_item['title'] ) ? $news_item['title'] : '' ) ); ?></a></td>
			<td data-title="<?php esc_html_e( 'Description', 'api-integration' ); ?>"><?php echo wp_kses_post( ( ! empty( $news_item['description'] ) ? $news_item['description'] : '' ) ); ?></td>
			<td data-title="<?php esc_html_e( 'Date', 'api-integration' ); ?>"><?php echo wp_kses_post( ( ! empty( $news_item['publishedAt'] ) ? gmdate( 'Y-m-d H:i', strtotime( $news_item['publishedAt'] ) ) : '' ) ); ?></td>
		</tr>
		<?php

		return ob_get_clean();
	}
}
