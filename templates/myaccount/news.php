<?php
/**
 * This file is used for templating the news listing for customers.
 *
 * @since 1.0.0
 * @package CreedAlly_Api_Integration
 * @subpackage CreedAlly_Api_Integration/templates/myaccount
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// Enqueue the required assets.
wp_enqueue_style( 'api-integration-jquery-ui-style' );

$news_items = array();

// See if the news throw error.
try {
	if ( ! class_exists( 'CreedAlly_Api_Integration_News' ) ) :
		/* translators: 1: %s: exception error message */
		throw new CreedAlly_Api_Integration_Exception( sprintf( __( 'Invalid class: %1$s', 'api-integration' ), 'CreedAlly_Api_Integration_News' ) );
	endif;

	$news_items = CreedAlly_Api_Integration_News::get();
} catch ( CreedAlly_Api_Integration_Exception $excep ) {
	// Display custom message.
	echo wp_kses_post( $excep->errorMessage() );
}

$news_preferences = cai_get_customer_preferences(); // Get the customer preferences.
$has_news         = ( false === $news_items || false === $news_preferences ) ? false : $news_items;

// Include the news preferences template.
cai_get_template( 'myaccount/news-preferences.php' );

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
