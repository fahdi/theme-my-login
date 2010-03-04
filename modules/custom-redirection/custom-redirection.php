<?php
/*
Plugin Name: Custom Redirection
Description: Enabling this module will initialize custom redirection. You will then have to configure the settings via the "Redirection" tab.
*/

add_action('tml_init', 'jkf_tml_custom_redirect_init');
function jkf_tml_custom_redirect_init() {
	include( TML_MODULE_DIR . '/custom-redirection/hook-functions.php' );
	add_filter('login_redirect', 'jkf_tml_custom_redirect_login', 10, 3);
	add_filter('logout_redirect', 'jkf_tml_custom_redirect_logout', 10, 3);
	add_action('login_form', 'jkf_tml_custom_redirect_login_form');
}

add_action('tml_admin_init', 'jkf_tml_custom_redirect_admin_init');
function jkf_tml_custom_redirect_admin_init() {
    require_once (TML_MODULE_DIR . '/custom-redirection/admin.php');
	add_action('tml_admin_menu', 'jkf_tml_custom_redirect_admin_menu');
}

add_action('activate_custom-redirection/custom-redirection.php', 'jkf_tml_custom_redirection_activate');
function jkf_tml_custom_redirection_activate() {
	$current = jkf_tml_get_option('redirection');
	$default = jkf_tml_custom_redirect_default_settings();	
	
	if ( is_array($current) )
		jkf_tml_update_option(array_merge($default, $current), 'redirection');
	else
		jkf_tml_update_option($default, 'redirection');
	
	unset($current, $default);
}

function jkf_tml_custom_redirect_default_settings() {
	global $wp_roles;	
	foreach ( $wp_roles->get_names() as $role => $label ) {
		$options[$role] = array('login_type' => 'default', 'login_url' => '', 'logout_type' => 'default', 'logout_url' => '');
	}
    return $options;
}
        
?>