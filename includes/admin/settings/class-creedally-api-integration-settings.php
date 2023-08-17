<?php
/**
 * The admin-settings of the plugin.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    CreedAlly_Api_Integration
 * @subpackage CreedAlly_Api_Integration/includes/admin/settings
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Return, if the class already exists.
 */
if ( class_exists( 'CreedAlly_Api_Integration_Settings', false ) ) {
	return new CreedAlly_Api_Integration_Settings();
}

/**
 * Settings class for keeping data sync with marketplace.
 */
class CreedAlly_Api_Integration_Settings extends WC_Settings_Page {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'api-integration';
		$this->label = __( 'News API Integration', 'api-integration' );

		parent::__construct();
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			'' => __( 'General', 'api-integration' ),
		);

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::save_fields( $settings );

		if ( $current_section ) {
			do_action( 'woocommerce_update_options_' . $this->id . '_' . $current_section );
		}
	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section Current section name.
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		$settings = $this->cai_general_settings_fields();

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
	}

	/**
	 * Return the fields for general settings.
	 *
	 * @return array
	 */
	public function cai_general_settings_fields() {

		return apply_filters(
			'creedally_api_integration_settings',
			apply_filters(
				'creedally_api_integration_general_settings',
				array(
					array(
						'title' => __( 'General Settings', 'api-integration' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'ai_general_settings_title',
					),
					array(
						'title'       => __( 'API Key', 'api-integration' ),
						'desc'        => __( 'This holds the API key to fetch the news from newsapi.org.', 'api-integration' ),
						'desc_tip'    => true,
						'id'          => 'ai_news_api_key',
						'placeholder' => '10c******************',
						'type'        => 'text',
					),
					array(
						'title'       => __( 'API Endpoint', 'api-integration' ),
						'desc'        => __( 'This holds the API endpoint to fetch the news from newsapi.org.', 'api-integration' ),
						'desc_tip'    => true,
						'id'          => 'ai_news_api_endpoint',
						'placeholder' => 'https://newsapi.org/v2/',
						'type'        => 'url',
					),
					array(
						'title'             => __( 'No. of News Per Page', 'api-integration' ),
						'desc'              => __( 'This holds the number of news items to be displayed per page.', 'api-integration' ),
						'desc_tip'          => true,
						'id'                => 'ai_news_per_page',
						'placeholder'       => __( 'Example: 5', 'api-integration' ),
						'type'              => 'number',
						'custom_attributes' => array(
							'min'  => 1,
							'max'  => 20,
							'step' => 1,
						),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'ai_general_settings_end',
					),

				)
			)
		);
	}
}

return new CreedAlly_Api_Integration_Settings();
