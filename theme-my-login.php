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

// Bailout if we're at the default login
if ( strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false )
	return;

/**
 * Stores the location of the Theme My Login directory
 *
 * @since 6.0
 */
define( 'TML_DIR', dirname( __FILE__ ) );

/**
 * Stores the location of the Theme My Login modules directory
 *
 * @since 5.0
 */
define( 'TML_MODULE_DIR', TML_DIR . '/modules' );

/**
 * For developers, setting this to true will output useful debug information
 * such as memory usage at specific hooks.
 *
 * @since 6.0
 */
define( 'TML_DEBUG', false );

// Load plugin textdomain
load_plugin_textdomain( 'theme-my-login', '', 'theme-my-login/language' );

// Require a few needed files
require_once( TML_DIR . '/includes/class-theme-my-login-base.php' );
require_once( TML_DIR . '/includes/class-theme-my-login.php' );
require_once( TML_DIR . '/includes/class-theme-my-login-template.php' );
require_once( TML_DIR . '/includes/class-theme-my-login-widget.php' );

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
foreach ( $theme_my_login->get_active_modules() as $module )
	include_once( $module );
unset( $module );

do_action_ref_array( 'tml_modules_loaded', array( &$theme_my_login ) );

if ( is_admin() ) {
	require_once( TML_DIR . '/admin/class-theme-my-login-admin.php' );
	/**
	 * Theme My Login Admin object
	 * @global object $theme_my_login_admin
	 * @since 6.0
	 */
	$theme_my_login_admin =& new Theme_My_Login_Admin();
}

if ( defined( 'TML_DEBUG' ) && TML_DEBUG )
	include_once( TML_DIR . '/includes/class-theme-my-login-debug.php' );

?>