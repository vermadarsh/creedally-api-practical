<?php
/**
 * The file that defines the activator class of the plugin.
 *
 * A class definition that holds the code that would execute on plugin activation.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    CreedAlly_Api_Integration
 * @subpackage CreedAlly_Api_Integration/inc
 */

/**
 * The activation class.
 *
 * A class definition that holds the code that would execute on plugin activation.
 *
 * @since      1.0.0
 * @package    CreedAlly_Api_Integration
 * @subpackage CreedAlly_Api_Integration/inc
 * @author     CreedAlly_Api_Integration Verma <adarsh.srmcem@gmail.com>
 */
class CreedAlly_Api_Integration_Activator {
	/**
	 * Enqueue scripts for admin end.
	 */
	public static function run() {
		// Redirect to plugin settings.
		add_option( 'cai_do_activation_redirect', 1 );
	}
}
