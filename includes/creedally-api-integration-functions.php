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
	 * Get the news row html.
	 *
	 * @param array $news_item News data array.
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
			<td data-title="<?php esc_html_e( 'Description', 'api-integration' ); ?>"><?php echo wp_kses_post( ( ! empty( $news_item['description'] ) ? wp_trim_words( $news_item['description'], 20, '...' ) : '' ) ); ?></td>
			<td data-title="<?php esc_html_e( 'Date', 'api-integration' ); ?>"><?php echo wp_kses_post( ( ! empty( $news_item['publishedAt'] ) ? gmdate( 'Y-m-d H:i', strtotime( $news_item['publishedAt'] ) ) : '' ) ); ?></td>
		</tr>
		<?php

		return ob_get_clean();
	}
}

/**
 * Check if the function, 'cai_get_news_widget_section_html' exists.
 */
if ( ! function_exists( 'cai_get_news_widget_section_html' ) ) {
	/**
	 * Get the news section html.
	 *
	 * @param array $news_item News data array.
	 * @return string
	 */
	function cai_get_news_widget_section_html( $news_item = array() ) {
		// Return, if the news item array is blank.
		if ( empty( $news_item ) || ! is_array( $news_item ) ) {
			return;
		}

		// Start preparing the HTML.
		ob_start();
		?>
		<div class="news-item">
			<img alt="news-item-featured-image" src="<?php echo esc_url( ( ! empty( $news_item['urlToImage'] ) ? $news_item['urlToImage'] : '' ) ); ?>" />
			<h4><a href="<?php echo esc_url( ( ! empty( $news_item['url'] ) ? $news_item['url'] : '' ) ); ?>" target="_blank" title="<?php echo wp_kses_post( ( ! empty( $news_item['title'] ) ? $news_item['title'] : '' ) ); ?>"><?php echo wp_kses_post( ( ! empty( $news_item['title'] ) ? $news_item['title'] : '' ) ); ?></a></h4>
			<p><?php echo wp_kses_post( ( ! empty( $news_item['description'] ) ? wp_trim_words( $news_item['description'], 20, '...' ) : '' ) ); ?></p>
		</div>
		<?php

		return ob_get_clean();
	}
}
