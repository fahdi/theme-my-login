<?php
/*
Plugin Name: Theme My Login 6.0
Plugin URI: http://www.jfarthing.com/extend/plugins/theme-my-login
Description: Themes the WordPress login, registration and forgot password pages according to your theme.
Version: 6.0-alpha
Author: Jeff Farthing
Author URI: http://www.jfarthing.com
Text Domain: theme-my-login
*/

/**
 * Holds the absolute location of Theme My Login
 *
 * @since 6.0
 */
define( 'TML_ABSPATH', dirname( __FILE__ ) );

/**
 * Holds the name of the Theme My Login directory
 *
 * @since 6.0
 */
define( 'TML_DIRNAME', basename( TML_ABSPATH ) );

/**
 * For developers, setting this to true will output useful debug information
 * such as memory usage at specific hooks.
 *
 * @since 6.0
 */
define( 'TML_DEBUG', false );

// Require a few needed files
require_once( TML_ABSPATH . '/includes/class-theme-my-login.php' );
require_once( TML_ABSPATH . '/includes/class-theme-my-login-template.php' );
require_once( TML_ABSPATH . '/includes/class-theme-my-login-widget.php' );

/**
 * Theme My Login object
 * @global object $theme_my_login_object
 * @since 6.0
 */
$theme_my_login_object =& new Theme_My_Login();

/**
 * Holds the reference to @see $theme_my_login_object
 * Use this global for interfacing
 * @global object $theme_my_login
 * @since 1.0
 */
$theme_my_login =& $theme_my_login_object;

// Load active modules
foreach ( $theme_my_login->get_active_and_valid_modules() as $module )
	include_once( $module );
unset( $module );

do_action_ref_array( 'tml_modules_loaded', array( &$theme_my_login ) );

if ( is_admin() ) {
	require_once( TML_ABSPATH . '/admin/class-theme-my-login-admin.php' );
	/**
	 * Theme My Login Admin object
	 * @global object $theme_my_login_admin
	 * @since 6.0
	 */
	$theme_my_login_admin =& new Theme_My_Login_Admin( $theme_my_login );
	
	do_action_ref_array( 'tml_admin_load', array( &$theme_my_login ) );
}

if ( defined( 'TML_DEBUG' ) && TML_DEBUG )
	include_once( TML_ABSPATH . '/includes/class-theme-my-login-debug.php' );

?>