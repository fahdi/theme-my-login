<?php
/*
Plugin Name: Custom User Links
Description: Enabling this module will initialize custom user links. You will then have to configure the settings via the "User Links" tab.
*/

add_action('tml_init', 'jkf_tml_custom_user_links_init');
function jkf_tml_custom_user_links_init() {
	add_filter('tml_user_links', 'jkf_tml_custom_user_links');
}

function jkf_tml_custom_user_links($links) {
	global $theme_my_login;
	
	if ( !is_user_logged_in() )
		return $links;
	
	$current_user = wp_get_current_user();
	$user_role = reset($current_user->roles);
	
	$links = isset($theme_my_login->options['user_links'][$user_role]) ? (array) $theme_my_login->options['user_links'][$user_role] : array();
	return $links;
}

add_action('tml_admin_init', 'jkf_tml_custom_user_links_admin_init');
function jkf_tml_custom_user_links_admin_init() {
	global $wp_roles;
    require_once (TML_MODULE_DIR . '/custom-user-links/admin/admin.php');
	add_action('tml_admin_menu', 'jkf_tml_custom_user_links_admin_menu');
	add_filter('tml_save_settings', 'jkf_tml_custom_user_links_save_settings');
	add_action('tml_settings_page', 'jkf_tml_custom_user_links_admin_styles');
	foreach ( $wp_roles->get_names() as $role => $label ) {
		add_action('wp_ajax_add-' . $role . '-link', 'jkf_tml_custom_user_links_add_user_link_ajax');
		add_action('wp_ajax_delete-' . $role . '-link', 'jkf_tml_custom_user_links_delete_user_link_ajax');
	}
}

add_action('activate_custom-user-links/custom-user-links.php', 'jkf_tml_custom_user_links_activate');
function jkf_tml_custom_user_links_activate() {
	global $theme_my_login;
	
	if ( isset($theme_my_login->options['user_links']) && is_array($theme_my_login->options['user_links']) )
		$theme_my_login->options['user_links'] = array_merge(jkf_tml_custom_user_links_default_settings(), $theme_my_login->options['user_links']);
	else
		$theme_my_login->options['user_links'] = jkf_tml_custom_user_links_default_settings();
		
	update_option('theme_my_login', $theme_my_login->options);
}

function jkf_tml_custom_user_links_default_settings() {
	global $wp_roles;
	
	$user_roles = $wp_roles->get_names();
	foreach ( $user_roles as $role => $label ) {
		$options[$role] = array(
            array('title' => __('Dashboard'), 'url' => admin_url()),
            array('title' => __('Profile'), 'url' => admin_url('profile.php'))
		);
	}
    return $options;
}

?>