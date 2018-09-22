<?php
/**
 *	Kalium WordPress Theme
 *	
 *	Laborator.co
 *	www.laborator.co 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Visual_Composer {
	
	/**
	 * Required plugin/s for this class
	 */
	public static $plugins = array( 'js_composer/js_composer.php' );
	
	/**
	 * Class instructor, define necesarry actions
	 */
	public function __construct() {
		$this->template_redirect_priority = 100;
	}
	
	/**
	 * Deregister isotope
	 *
	 * @type action
	 */
	public function template_redirect() {
		wp_deregister_script( 'isotope' );
	}
}