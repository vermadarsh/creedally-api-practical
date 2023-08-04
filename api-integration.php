<?php
/**
 * Plugin Name:     CreedAlly Practical: API Integration
 * Plugin URI:      https://github.com/vermadarsh/creedally-api-practical
 * Description:     This plugin serves the practical test from CreedAlly.
 * Author:          Adarsh Verma
 * Author URI:      https://github.com/vermadarsh/
 * Text Domain:     api-integration
 * Version:         0.1.0
 * License:         GPL-2.0+
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package         Api_Integration
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 0.1.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AI_PLUGIN_VERSION', '0.1.0' );

// Plugin path.
if ( ! defined( 'AI_PLUGIN_PATH' ) ) {
	define( 'AI_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

// Plugin URL.
if ( ! defined( 'AI_PLUGIN_URL' ) ) {
	define( 'AI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// WooCommerce logs directory path.
if ( ! defined( 'AIWC_LOG_DIR_PATH' ) ) {
	$uploads_dir = wp_upload_dir();
	define( 'AIWC_LOG_DIR_PATH', $uploads_dir['basedir'] . '/wc-logs/' );
}

// WooCommerce logs directory url.
if ( ! defined( 'AIWC_LOG_DIR_URL' ) ) {
	$uploads_dir = wp_upload_dir();
	define( 'AIWC_LOG_DIR_URL', $uploads_dir['baseurl'] . '/wc-logs/' );
}

/**
 * This code runs during the plugin activation.
 * This code is documented in inc/class-api-integration-activator.php
 */
function activate_api_integration() {
	require 'inc/admin/class-api-integration-activator.php';
	Api_Integration_Activator::run();
}

register_activation_hook( __FILE__, 'activate_api_integration' );

/**
 * This code runs during the plugin deactivation.
 * This code is documented in inc/class-api-integration-deactivator.php
 */
function deactivate_api_integration() {
	require 'inc/admin/class-api-integration-deactivator.php';
	Api_Integration_Deactivator::run();
}

register_deactivation_hook( __FILE__, 'deactivate_api_integration' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_api_integration_plugin() {
	// This function is used to write reusable functions.
	require_once 'inc/api-integration-functions.php';

	// The core plugin class that is used to define admin-specific hooks.
	require_once 'inc/admin/class-api-integration-admin.php';
	new Api_Integration_Admin();

	// The core plugin class that is used to define public-specific hooks.
	require_once 'inc/public/class-api-integration-public.php';
	new Api_Integration_Public();
}

/**
 * This initiates the plugin.
 * Checks for the required plugins to be installed and active.
 */
function ai_plugins_loaded_callback() {
	$active_plugins = get_option( 'active_plugins' ); // Active plugins.
	$is_wc_active   = in_array( 'woocommerce/woocommerce.php', $active_plugins, true );

	// If the dependant plugin isn't active, throw admin notice.
	if ( false === $is_wc_active ) {
		add_action( 'admin_notices', 'ai_admin_notices_callback' );
	} else {
		run_api_integration_plugin();
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ai_plugin_actions_callback' );
	}
}

add_action( 'plugins_loaded', 'ai_plugins_loaded_callback' );

/**
 * This function is called to show admin notices for any required plugin not active || installed.
 */
function ai_admin_notices_callback() {
	$this_plugin_data = get_plugin_data( __FILE__ );
	$this_plugin      = $this_plugin_data['Name'];
	$wc_plugin        = 'WooCommerce';
	?>
	<div class="error">
		<p>
			<?php /* translators: 1: %s: string tag open, 2: %s: strong tag close, 3: %s: this plugin, 4: %s: woocommerce plugin */ ?>
			<?php echo wp_kses_post( sprintf( __( '%1$s%3$s%2$s is ineffective as it requires %1$s%4$s%2$s to be installed and active.', 'api-integration' ), '<strong>', '</strong>', esc_html( $this_plugin ), esc_html( $wc_plugin ) ) ); ?>
		</p>
	</div>
	<?php
}

/**
 * This function adds custom plugin actions.
 *
 * @param array $links Links array.
 * @return array
 * @since 0.1.0
 */
function ai_plugin_actions_callback( $links ) {
	$this_plugin_links = array(
		'<a title="' . __( 'Settings', 'api-integration' ) . '" href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=api-integration' ) ) . '">' . __( 'Settings', 'api-integration' ) . '</a>',
	);

	return array_merge( $this_plugin_links, $links );
}
