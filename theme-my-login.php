<?php
/*
Plugin Name: Theme My Login
Plugin URI: http://www.jfarthing.com/wordpress-plugins/theme-my-login/
Description: Themes the WordPress login, registration and forgot password pages according to your theme.
Version: 6.0.4
Author: Jeff Farthing
Author URI: http://www.jfarthing.com
Text Domain: theme-my-login
*/

/*
 * = Future Ideas =
 * ----------------------------------------------------------------------
 * Allow separate permalinks for login, register and lostpassword
 * Allow users to delete themselves
 * Add option to forward all "wp-login.php" requests to TML login page
 */

// Allow custom functions file
if ( file_exists( WP_PLUGIN_DIR . '/theme-my-login-custom.php' ) )
	include_once( WP_PLUGIN_DIR . '/theme-my-login-custom.php' );

/**
 * Holds the absolute location of Theme My Login
 *
 * @since 6.0
 */
if ( !defined( 'TML_ABSPATH' ) )
	define( 'TML_ABSPATH', dirname( __FILE__ ) );

/**
 * Holds the name of the Theme My Login directory
 *
 * @since 6.0
 */
if ( !defined( 'TML_DIRNAME' ) )
	define( 'TML_DIRNAME', basename( TML_ABSPATH ) );

/**
 * For developers, setting this to true will output useful debug information
 * such as memory usage at specific hooks.
 *
 * @since 6.0
 */
if ( !defined( 'TML_DEBUG' ) )
	define( 'TML_DEBUG', false );

// Require a few needed files
require_once( TML_ABSPATH . '/includes/class-theme-my-login.php' );
require_once( TML_ABSPATH . '/includes/class-theme-my-login-template.php' );
require_once( TML_ABSPATH . '/includes/class-theme-my-login-module.php' );
require_once( TML_ABSPATH . '/includes/class-theme-my-login-widget.php' );

/**
 * Theme My Login object
 * @global object $theme_my_login_object
 * @since 6.0
 */
$GLOBALS['theme_my_login_object'] =& new Theme_My_Login();

/**
 * Holds the reference to @see $theme_my_login_object
 * Use this global for interfacing
 * @global object $theme_my_login
 * @since 1.0
 */
$GLOBALS['theme_my_login'] =& $GLOBALS['theme_my_login_object'];

// Load active modules
foreach ( $GLOBALS['theme_my_login']->get_active_and_valid_modules() as $module )
	include_once( $module );
unset( $module );

do_action( 'tml_modules_loaded' );

if ( is_admin() ) {
	require_once( TML_ABSPATH . '/admin/class-theme-my-login-admin.php' );
	/**
	 * Theme My Login Admin object
	 * @global object $theme_my_login_admin
	 * @since 6.0
	 */
	$GLOBALS['theme_my_login_admin'] =& new Theme_My_Login_Admin();
}

if ( defined( 'TML_DEBUG' ) && TML_DEBUG )
	include_once( TML_ABSPATH . '/includes/class-theme-my-login-debug.php' );

if ( !function_exists( 'theme_my_login' ) ) :
/**
 * Displays a TML instance
 *
 * @see Theme_My_Login::shortcode() for $args parameters
 * @since 6.0
 *
 * @param string|array $args Template tag arguments
 */
function theme_my_login( $args = '' ) {
	echo $GLOBALS['theme_my_login']->shortcode( wp_parse_args( $args ) );
}
endif;

?>