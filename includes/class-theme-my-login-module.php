<?php
/**
 * Holds the Theme My Login Module class
 *
 * @package Theme_My_Login
 */

if ( !class_exists( 'Theme_My_Login_Module' ) ) :
/*
 * Theme My Login Module class
 *
 * This class is the base class to be extended by a module.
 *
 * @since 6.0
 */
abstract class Theme_My_Login_Module {
	/**
	 * Constructor
	 *
	 * @since 6.0
	 * @access public
	 */
	public function __construct() {
		$this->load();
	}

	/**
	 * Called when object is constructed
	 *
	 * @since 6.0
	 * @access protected
	 */
	abstract protected function load();
}
endif;

