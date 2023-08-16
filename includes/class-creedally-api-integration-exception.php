<?php
/**
 * The file that defines the custom exception handler for this plugin.
 *
 * A class definition that holds the code that would save the code flow in case of errors.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    CreedAlly_Api_Integration
 * @subpackage CreedAlly_Api_Integration/includes
 */

/**
 * The file that defines the custom exception handler for this plugin.
 *
 * A class definition that holds the code that would save the code flow in case of errors.
 *
 * @since      1.0.0
 * @package    CreedAlly_Api_Integration
 * @subpackage CreedAlly_Api_Integration/includes
 * @author     CreedAlly_Api_Integration Verma <adarsh.srmcem@gmail.com>
 */
class CreedAlly_Api_Integration_Exception extends Exception {
	/**
	 * The error message thrown from the exception handler.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      CreedAlly_Api_Integration_Exception    $error_message    The error message thrown from the exception handler.
	 */
	protected $error_message;

	/**
	 * Exception class constructor.
	 *
	 * @param string $error_message Exception error message.
	 * @since 1.0.0
	 */
	public function __construct( $error_message ) {
		$this->error_message = $error_message;
	}

	/**
	 * Return the exception error message.
	 *
	 * @return string
	 */
	public function errorMessage() {
		/* translators: 1: %s: error message line number, 2: error message file path, 3: error message, 4: strong tag opened, 5: strong tag closed */
		return sprintf( __( 'Error on line %1$s in %2$s: %4$s%3$s%5$s. News API could not be processed.', 'api-integration' ), $this->getLine(), $this->getFile(), $this->error_message, '<strong>', '</strong>' );
	}
}
