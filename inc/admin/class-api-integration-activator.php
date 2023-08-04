<?php
/**
 * The file that defines the activator class of the plugin.
 *
 * A class definition that holds the code that would execute on plugin activation.
 *
 * @link       https://github.com/vermadarsh/
 * @since      0.1.0
 *
 * @package    Api_Integration
 * @subpackage Api_Integration/inc/admin
 */

/**
 * The activation class.
 *
 * A class definition that holds the code that would execute on plugin activation.
 *
 * @since      0.1.0
 * @package    Api_Integration
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class Api_Integration_Activator {
	/**
	 * Enqueue scripts for admin end.
	 */
	public static function run() {
		// Redirect to plugin settings.
		add_option( 'ai_do_activation_redirect', 1 );
	}
}
