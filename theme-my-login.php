<?php
/*
Plugin Name: Theme My Login
Plugin URI: http://www.jfarthing.com/wordpress-plugins/theme-my-login-plugin
Description: Themes the WordPress login, registration and forgot password pages according to your theme.
Version: 5.0-beta
Author: Jeff Farthing
Author URI: http://www.jfarthing.com
Text Domain: theme-my-login
*/

// Bailout if we're at the default login
if ( 'wp-login.php' == $pagenow )
	return;

// Set the default module directory
if ( !defined('TML_MODULE_DIR') )
    define('TML_MODULE_DIR', WP_PLUGIN_DIR . '/theme-my-login/modules');

// Require global configuration class file
require_once( WP_PLUGIN_DIR . '/theme-my-login/includes/class.php' );

// Initialize global configuration class
$theme_my_login = new Theme_My_Login();

// Require general plugin functions file
require_once( WP_PLUGIN_DIR . '/theme-my-login/includes/functions.php' );

// Load the plugin textdomain
load_plugin_textdomain('theme-my-login', '', 'theme-my-login/language');

// Load active modules
jkf_tml_load_active_modules();

// Include admin-functions.php for install/uninstall process
if ( is_admin() ) {
    require_once( WP_PLUGIN_DIR . '/theme-my-login/admin/includes/admin.php' );
    require_once( WP_PLUGIN_DIR . '/theme-my-login/admin/includes/module.php' );
	
    register_activation_hook(__FILE__, 'jkf_tml_install');
    register_uninstall_hook(__FILE__, 'jkf_tml_uninstall');
	
	add_action('admin_init', 'jkf_tml_admin_init');
    add_action('admin_menu', 'jkf_tml_admin_menu');
	
	if ( function_exists('wp_new_user_notification') )
		add_action('admin_notices', 'jkf_tml_new_user_notification_override_notice');
	if ( function_exists('wp_password_change_notification') )
		add_action('admin_notices', 'jkf_tml_password_change_notification_override_notice');
}

// Load pluggable functions after modules (in case a module needs to override a function)
require_once( WP_PLUGIN_DIR . '/theme-my-login/includes/pluggable-functions.php' );

add_action('plugins_loaded', 'jkf_tml_load');
function jkf_tml_load() {
	require_once( WP_PLUGIN_DIR . '/theme-my-login/includes/hook-functions.php' );
	
    do_action('tml_load');

    add_action('template_redirect', 'jkf_tml_template_redirect');
    
    add_filter('the_title', 'jkf_tml_the_title', 10, 2);
    add_filter('single_post_title', 'jkf_tml_single_post_title');
	
	if ( jkf_tml_get_option('rewrite_links') )
		add_filter('site_url', 'jkf_tml_site_url', 10, 3);
	
	if ( jkf_tml_get_option('show_page') )
		add_filter('get_pages', 'jkf_tml_get_pages', 10, 2);
	else
		add_filter('wp_list_pages_excludes', 'jkf_tml_list_pages_excludes');
    
	add_shortcode('theme-my-login', 'jkf_tml_shortcode');
    
    if ( jkf_tml_get_option('enable_widget') ) {
        require_once( WP_PLUGIN_DIR . '/theme-my-login/includes/widget.php' );
		add_action('widgets_init', 'jkf_tml_register_widget');
		function jkf_tml_register_widget() {
			return register_widget("Theme_My_Login_Widget");
		}
    }
}

function jkf_tml_template_redirect() {
    if ( is_page(jkf_tml_get_option('page_id')) || jkf_tml_get_option('enable_template_tag') || is_active_widget(false, null, 'theme-my-login') ) {
	
		jkf_tml_set_error();
	
		do_action('tml_init');

        if ( jkf_tml_get_option('enable_css') )
            jkf_tml_get_css();
            
        require_once( WP_PLUGIN_DIR . '/theme-my-login/includes/login-actions.php' );
    }
}

// Template tag
function theme_my_login($args = '') {
	if ( ! jkf_tml_get_option('enable_template_tag') )
		return false;		
	$args = wp_parse_args($args);
	echo jkf_tml_shortcode($args);
}

?>