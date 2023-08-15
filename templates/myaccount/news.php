<?php
/**
 * This file is used for templating the news listing for customers.
 *
 * @since 1.0.0
 * @package Api_Integration
 * @subpackage Api_Integration/inc/public/templates
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// Enqueue the required assets.
wp_enqueue_style( 'api-integration-jquery-ui-style' );

$customer_id          = get_current_user_id();
$news_items           = get_transient( "ai_newsapi_news_{$customer_id}" ); // Fetch the news data from the cache.
$customer_preferences = ai_get_customer_preferences(); // Get the customer preferences.
$per_page             = get_option( 'ai_news_per_page' ); // News per page.
$news_interest        = ( ! empty( $customer_preferences['news_interest'] ) ) ? $customer_preferences['news_interest'] : '';
$news_domains         = ( ! empty( $customer_preferences['news_domains'] ) ) ? $customer_preferences['news_domains'] : '';
$news_date_from       = ( ! empty( $customer_preferences['news_date_from'] ) ) ? $customer_preferences['news_date_from'] : '';
$news_date_to         = ( ! empty( $customer_preferences['news_date_to'] ) ) ? $customer_preferences['news_date_to'] : '';
$server_arr           = wp_unslash( $_SERVER );

// See if there are news items in the cache.
if ( false === $news_items || empty( $news_items ) ) :
	$news_items = ai_get_news(); // Shoot the API to get news items.

	/**
	 * Cache the response data.
	 * This cached data will be used to display the news items.
	 */
	if ( false !== $news_items ) :
		set_transient( "ai_newsapi_news_{$customer_id}", wp_json_encode( $news_items ), ( 60 * 60 * 4 ) );
	endif;
endif;

// Get the news items into sliced array to serve the pagination.
$news_items = json_decode( $news_items, true );
$news_items = array_slice( $news_items, 0, $per_page );

// If there are news.
$has_news = ( false === $news_items || false === $customer_preferences ) ? false : $news_items;
?>
<!-- NEWS PREFERENCES -->
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
<?php
if ( $has_news ) :
	?>
	<h3><?php esc_html_e( 'New Items', 'api-integration' ); ?></h3>
	<table class="news-listing-table">
		<thead>
			<tr>
				<th class="table__header-news-image"><span class="nobr"></span></th>
				<th class="table__header-news-title"><span class="nobr"><?php esc_html_e( 'Title', 'api-integration' ); ?></span></th>
				<th class="table__header-news-description"><span class="nobr"><?php esc_html_e( 'Description', 'api-integration' ); ?></span></th>
				<th class="table__header-news-date"><span class="nobr"><?php esc_html_e( 'Date', 'api-integration' ); ?></span></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $news_items as $news_item ) :
				$news_item = (array) $news_item;
				echo wp_kses_post( cai_get_news_row_html( $news_item ) );
			endforeach;
			?>
		</tbody>
	</table>
	<div class="news-pagination">
		<a href="#" class="prev non-clickable" title="<?php esc_html_e( 'Prev', 'api-integration' ); ?>"><?php esc_html_e( 'Prev', 'api-integration' ); ?></a>
		<a href="#" class="next" title="<?php esc_html_e( 'Next', 'api-integration' ); ?>"><?php esc_html_e( 'Next', 'api-integration' ); ?></a>
		<input type="hidden" id="current-news-items-page" value="1" />
		<input type="hidden" id="pagination-section" value="customer-portal" />
	</div>
<?php else : ?>

	<?php wc_print_notice( __( 'The news could not be fetched from the API.', 'api-integration' ) . ' <a class="woocommerce-Button button" href="mailto:' . get_option( 'admin_email' ) . '">' . __( 'Contact administrator', 'api-integration' ) . '</a>', 'notice' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment ?>

<?php endif; ?>
