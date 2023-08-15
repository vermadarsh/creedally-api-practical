<?php
/**
 * This file is used for templating the customer news preferences.
 *
 * @since 1.0.0
 * @package CreedAlly_Api_Integration
 * @subpackage CreedAlly_Api_Integration/templates/myaccount
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$customer_preferences = ai_get_customer_preferences(); // Get the customer preferences.
$news_interest        = ( ! empty( $customer_preferences['news_interest'] ) ) ? $customer_preferences['news_interest'] : '';
$news_domains         = ( ! empty( $customer_preferences['news_domains'] ) ) ? $customer_preferences['news_domains'] : '';
$news_date_from       = ( ! empty( $customer_preferences['news_date_from'] ) ) ? $customer_preferences['news_date_from'] : '';
$news_date_to         = ( ! empty( $customer_preferences['news_date_to'] ) ) ? $customer_preferences['news_date_to'] : '';
$server_arr           = wp_unslash( $_SERVER );
?>
<!-- NEWS PREFERENCES -->
<h3><?php esc_html_e( 'New Preferences', 'api-integration' ); ?></h3>
<form class="woocommerce-NewsPreferencesForm news-preferences" action="" method="post">
	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="news_interest"><?php esc_html_e( 'News Interest', 'api-integration' ); ?>&nbsp;<span class="required">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="news_interest" id="news_interest" placeholder="<?php esc_html_e( 'Example: Sports', 'api-integration' ); ?>" value="<?php echo esc_attr( $news_interest ); ?>" required>
		<span><em><?php esc_html_e( 'Put in the topic you are interested to check the news about.', 'api-integration' ); ?></em></span>
	</p>
	<div class="clear"></div>
	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="news_domains"><?php esc_html_e( 'Domains', 'api-integration' ); ?></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="news_domains" id="news_domains" placeholder="<?php esc_html_e( 'Example: techcrunch.com,thenextweb.com', 'api-integration' ); ?>" value="<?php echo esc_attr( $news_domains ); ?>">
		<span><em><?php esc_html_e( 'Put in the comma-separated list of domains you are interested to check the news from.', 'api-integration' ); ?></em></span>
	</p>
	<div class="clear"></div>
	<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
		<label for="news_date_from"><?php esc_html_e( 'Date From', 'api-integration' ); ?>&nbsp;<span class="required">*</span></label>
		<?php /* translators: 1: %s: seven days past date */ ?>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="news_date_from" id="news_date_from" placeholder="<?php echo esc_html( sprintf( __( 'Example: %1$s', 'api-integration' ), gmdate( 'Y-m-d', strtotime( '-7 days' ) ) ) ); ?>" value="<?php echo esc_attr( $news_date_from ); ?>" required>
		<span><em><?php esc_html_e( 'News date from.', 'api-integration' ); ?></em></span>
	</p>
	<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
		<label for="news_date_to"><?php esc_html_e( 'Date To', 'api-integration' ); ?>&nbsp;<span class="required">*</span></label>
		<?php /* translators: 1: %s: current date */ ?>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="news_date_to" id="news_date_to" placeholder="<?php echo esc_html( sprintf( __( 'Example: %1$s', 'api-integration' ), gmdate( 'Y-m-d' ) ) ); ?>" value="<?php echo esc_attr( $news_date_to ); ?>" required>
		<span><em><?php esc_html_e( 'News date to.', 'api-integration' ); ?></em></span>
	</p>
	<div class="clear"></div>
	<p>
		<?php wp_nonce_field( 'customer_news_preferences', 'ai_customer_news_preferences_nonce' ); ?>
		<input type="hidden" name="_wp_http_referer" value="<?php echo esc_html( $server_arr['REQUEST_URI'] ); ?>">
		<button type="submit" class="woocommerce-Button button" name="save_customer_news_preferences" value="<?php esc_html_e( 'Save changes', 'api-integration' ); ?>"><?php esc_html_e( 'Save changes', 'api-integration' ); ?></button>
		<input type="hidden" name="action" value="save_customer_news_preferences">
	</p>
</form>
