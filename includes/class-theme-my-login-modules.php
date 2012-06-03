<?php
/**
 * Holds the Theme My Login Modules class
 *
 * @package Theme_My_Login
 * @since 6.3
 */

if ( !class_exists( 'Theme_My_Login_Modules' ) ) :
/*
 * Theme My Login Modules class
 *
 * This class contains properties and methods common to modules.
 *
 * @since 6.3
 */
class Theme_My_Login_Modules extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_modules';

	/**
	 * Returns active and valid modules
	 *
	 * @since 6.3
	 * @access public
	 */
	public function get_active_and_valid_modules() {
		$modules = array();
		$active_modules = apply_filters( 'tml_active_modules', $this->get_options() );
		foreach ( (array) $active_modules as $module ) {
			// check the $plugin filename
			// Validate plugin filename	
			if ( !validate_file( $module ) // $module must validate as file
				|| '.php' == substr( $module, -4 ) // $module must end with '.php'
				|| file_exists( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module )	// $module must exist
				)
			$modules[] = WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module;
		}
		return $modules;
	}

	/**
	 * Determine if $module is an active module
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $module Filename of module to check
	 * @return bool True if $module is active, false if not
	 */
	public function is_module_active( $module ) {
		$active_modules = apply_filters( 'tml_active_modules', $this->get_options() );
		return in_array( $module, (array) $active_modules );
	}
}
endif; // Class exists

