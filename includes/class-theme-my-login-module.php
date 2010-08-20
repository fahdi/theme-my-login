<?php
/**
 * Holds the Theme My Login Module class
 *
 * @package Theme My Login
 * @subpackage Modules
 */

if ( !class_exists( 'Theme_My_Login_Module' ) ) :
/*
 * Theme My Login Module class
 *
 * This class is the base class to be extended by a module.
 *
 * @since 6.0
 */
class Theme_My_Login_Module {
	/**
	 * Holds reference to global $theme_my_login object
	 *
	 * @since 6.0
	 * @access public
	 * @var object
	 */
	var $theme_my_login;

	/**
	 * Called when object is constructed
	 *
	 * @since 6.0
	 * @access public
	 */
	function load() {
		// This function should be overridden by the module extend class
	}

	/**
	 * PHP4 style constructor
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param object $theme_my_login Reference to global $theme_my_login object
	 */
	function Theme_My_Login_Module() {
		$this->__construct();
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param object $theme_my_login Reference to global $theme_my_login object
	 */
	function __construct() {
		$this->theme_my_login =& $GLOBALS['theme_my_login'];
		$this->load();
	}
}

endif; // Class exists

?>