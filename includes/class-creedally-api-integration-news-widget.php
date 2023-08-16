<?php
/**
 * The widget for showing the news items.
 *
 * @since      1.0.0
 *
 * @package    Api_Integration
 * @subpackage Api_Integration/inc/admin
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * The core plugin widget class.
 *
 * Class to manage the calendar widget settings and frontend display.
 *
 * @since      1.0.0
 * @package    Api_Integration
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class CreedAlly_Api_Integration_News_Widget extends WP_Widget {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'creedally-api-integration-news-items-widget', // Widget base ID.
			__( 'CreedAlly API Integration: News Items', 'api-integration' ), // Widget name will appear in UI.
			array(
				'description' => __( 'This widget offered by API Integration plugin, shows the popular news items.', 'api-integration' ), // Widget description.
			)
		);
	}

	/**
	 * Frontend template of the widget.
	 * Shows the popular news items.
	 *
	 * @param array $args Holds the widget arguments.
	 * @param array $instance Holds the widget settings data.
	 */
	public function widget( $args, $instance ) {
		$news_items = array();

		// See if the news throw error.
		try {
			if ( ! class_exists( 'CreedAlly_Api_Integration_News' ) ) {
				throw new CreedAlly_Api_Integration_Exception( sprintf( __( 'Invalid class: %1$s', 'api-integration' ), 'CreedAlly_Api_Integration_News' ) );
			}

			$news_items = CreedAlly_Api_Integration_News::get();
		}

		catch ( CreedAlly_Api_Integration_Exception $excep ) {
			// Display custom message.
			echo wp_kses_post( $excep->errorMessage() );
		}

		$widget_title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$widget_desc  = ( ! empty( $instance['description'] ) ) ? $instance['description'] : '';

		// Display the news listing widget.
		?>
		<div class="api-integration-widget-container">
			<?php
			// Print the widget title.
			if ( ! empty( $widget_title ) ) {
				echo wp_kses_post( '<h2 class="widget-title">' . $widget_title . '</h2>' );
			}

			// Print the widget description.
			if ( ! empty( $widget_desc ) ) {
				echo wp_kses(
					'<p class="description">' . $widget_desc . '</p>',
					array(
						'p' => array(
							'class' => array(),
						),
					)
				);
			}

			if ( ! empty( $news_items ) && is_array( $news_items ) ) {
				?>
				<div class="api-integration-news-container widget-container">
					<?php foreach ( $news_items as $news_item ) {
						$news_item = (array) $news_item;
						echo wp_kses_post( cai_get_news_widget_section_html( $news_item ) );
						} ?>
				</div>
				<div class="api-integration-news-pagination">
					<div class="news-pagination">
						<a href="#" class="prev non-clickable" title="<?php esc_html_e( 'Prev', 'api-integration' ); ?>"><?php esc_html_e( 'Prev', 'api-integration' ); ?></a>
						<a href="#" class="next" title="<?php esc_html_e( 'Next', 'api-integration' ); ?>"><?php esc_html_e( 'Next', 'api-integration' ); ?></a>
						<input type="hidden" id="current-news-items-page" value="1" />
						<input type="hidden" id="pagination-section" value="widget" />
					</div>
				</div>
				<?php
			} else {
				echo wp_kses(
					'<p class="api-integration-no-news-items">' . __( 'There are no news items.', 'api-integration' ) . '</p>',
					array(
						'p' => array(
							'class' => array(),
						),
					)
				);
			}
			?>
		</div>
		<?php
	}

	/**
	 * Widget admin settings.
	 *
	 * @param array $instance Widget instance.
	 */
	public function form( $instance ) {
		$title       = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'New title', 'api-integration' );
		$description = ( ! empty( $instance['description'] ) ) ? $instance['description'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'api-integration' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo wp_kses_post( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"><?php esc_html_e( 'Description:', 'api-integration' ); ?></label>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'description' ) ); ?>"><?php echo wp_kses_post( $description ); ?></textarea>
		</p>
		<?php
	}

	/**
	 * Updating widget replacing old instances with new.
	 *
	 * @param array $new_instance New instance.
	 * @param array $old_instance Old instance.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                = array();
		$instance['title']       = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['description'] = ( ! empty( $new_instance['description'] ) ) ? $new_instance['description'] : '';

		return $instance;
	}
}
