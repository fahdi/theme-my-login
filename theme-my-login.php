<?php
/*
Plugin Name: Theme My Login
Plugin URI: http://www.jfarthing.com/extend/wordpress-plugins/theme-my-login/
Description: Themes the WordPress login, registration and forgot password pages according to your theme.
Version: 6.3-alpha
Author: Jeff Farthing
Author URI: http://www.jfarthing.com
Text Domain: theme-my-login
*/

// Allow custom functions file
if ( file_exists( WP_PLUGIN_DIR . '/theme-my-login-custom.php' ) )
	include_once( WP_PLUGIN_DIR . '/theme-my-login-custom.php' );

// Require a few needed files
require_once( WP_PLUGIN_DIR . '/theme-my-login/includes/class-theme-my-login-common.php' );
require_once( WP_PLUGIN_DIR . '/theme-my-login/includes/class-theme-my-login-abstract.php' );
require_once( WP_PLUGIN_DIR . '/theme-my-login/includes/class-theme-my-login.php' );
require_once( WP_PLUGIN_DIR . '/theme-my-login/includes/class-theme-my-login-template.php' );
require_once( WP_PLUGIN_DIR . '/theme-my-login/includes/class-theme-my-login-widget.php' );

/**
 * Theme My Login object
 * @global object $theme_my_login_object
 * @since 6.0
 */
$GLOBALS['theme_my_login'] = new Theme_My_Login;

/**
 * Include active module files
 */
foreach ( $GLOBALS['theme_my_login']->get_option( 'active_modules', array() ) as $module ) {
	include_once( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module );
}
unset( $module );

if ( is_admin() ) {
	require_once( WP_PLUGIN_DIR . '/theme-my-login/admin/class-theme-my-login-admin.php' );
	/**
	 * Theme My Login Admin object
	 * @global object $theme_my_login_admin
	 * @since 6.0
	 */
	$GLOBALS['theme_my_login_admin'] = new Theme_My_Login_Admin;

	/**
	 * Include active module admin files
	 */
	foreach ( $GLOBALS['theme_my_login']->get_option( 'active_modules', array() ) as $module ) {
		$admin_file = dirname( $module ) . '/admin/' . basename( $module, '.php' ) . '-admin.php';
		if ( file_exists( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $admin_file ) )
			include_once( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $admin_file );
	}
	unset( $module );
}

if ( is_multisite() ) {
	require_once( WP_PLUGIN_DIR . '/theme-my-login/includes/class-theme-my-login-ms-signup.php' );
	/**
	 * Theme My Login MS Signup object
	 * @global object $theme_my_login_ms_signup
	 * @since 6.1
	 */
	$GLOBALS['theme_my_login_ms_signup'] = new Theme_My_Login_MS_Signup;
}

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
	global $theme_my_login;
	echo $theme_my_login->shortcode( wp_parse_args( $args ) );
}
endif;

?>
