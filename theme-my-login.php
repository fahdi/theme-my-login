<?php
/*
Plugin Name: Theme My Login
Plugin URI: http://www.jfarthing.com/wordpress-plugins/theme-my-login-plugin
Description: Themes the WordPress login, registration and forgot password pages according to your theme.
Version: 5.0-pre-alpha
Author: Jeff Farthing
Author URI: http://www.jfarthing.com
Text Domain: theme-my-login
*/

if ( !defined('TML_MODULE_DIR') )
    define('TML_MODULE_DIR', WP_PLUGIN_DIR . '/theme-my-login/modules');

require_once (WP_PLUGIN_DIR . '/theme-my-login/includes/functions.php');
require_once (WP_PLUGIN_DIR . '/theme-my-login/includes/hook-functions.php');

load_plugin_textdomain('theme-my-login', '', 'theme-my-login/language');

// Include admin-functions.php for install/uninstall process
if ( defined('WP_ADMIN') && true == WP_ADMIN ) {
    require_once (WP_PLUGIN_DIR . '/theme-my-login/admin/includes/admin.php');
    require_once (WP_PLUGIN_DIR . '/theme-my-login/admin/includes/module.php');
    register_activation_hook(__FILE__, 'jkf_tml_install');
    register_uninstall_hook(__FILE__, 'jkf_tml_uninstall');
}

function jkf_tml_default_settings($empty = false) {
    $options = array(
        'show_page' => 1,
		'rewrite_links' => 1,
        'enable_css' => 1,
        'enable_template_tag' => 0,
        'enable_widget' => 0,
        'active_modules' => array()
        );
    return apply_filters('tml_default_settings', $options);
}

// Main action hook that will spawn all other hooks/filter
add_action('plugins_loaded', 'jkf_tml_load');
function jkf_tml_load() {
    global $theme_my_login;

    $theme_my_login = (object) array(
        'options' => get_option('theme_my_login', jkf_tml_default_settings()),
        'errors' => '',
        'request_action' => isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login',
        'request_instance' => isset($_REQUEST['instance']) ? $_REQUEST['instance'] : 'tml-page',
        'current_instance' => '',
        'redirect_to' => ''
        );
    
	jkf_tml_load_active_modules();
	
    do_action('tml_load', $theme_my_login);
    
    if ( defined('WP_ADMIN') && true == WP_ADMIN ) {
        add_action('admin_init', 'jkf_tml_admin_init');
        add_action('admin_menu', 'jkf_tml_admin_menu');
    }

    add_action('template_redirect', 'jkf_tml_template_redirect');
    
    add_filter('the_title', 'jkf_tml_the_title', 10, 2);
    add_filter('single_post_title', 'jkf_tml_single_post_title');
	
	if ( $theme_my_login->options['rewrite_links'] )
		add_filter('site_url', 'jkf_tml_site_url', 10, 3);
	
	if ( $theme_my_login->options['show_page'] ) {
		add_filter('wp_list_pages_excludes', 'jkf_tml_list_pages_excludes');
		add_filter('page_link', 'jkf_tml_page_link', 10, 2);
		add_filter('get_pages', 'jkf_tml_get_pages', 10, 2);
	}
    
    add_shortcode('theme-my-login', 'jkf_tml_shortcode');
    
    if ( $theme_my_login->options['enable_widget'] ) {
        require_once (WP_PLUGIN_DIR . '/theme-my-login/includes/widget.php');
        add_action('widgets_init', create_function('', 'return register_widget("Theme_My_Login_Widget");'));
    }
}

function jkf_tml_template_redirect() {
    global $theme_my_login;
	
	do_action('tml_init');
        
    if ( is_page($theme_my_login->options['page_id']) || is_active_widget(false, null, 'theme-my-login') || $theme_my_login->options['enable_template_tag'] ) {

        if ( $theme_my_login->options['enable_css'] )
            jkf_tml_get_css();
            
        require_once (WP_PLUGIN_DIR . '/theme-my-login/includes/login-actions.php');
    }
}

?>