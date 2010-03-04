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
	if ( !is_user_logged_in() )
		return $links;

	$current_user = wp_get_current_user();
	$user_role = reset($current_user->roles);
	
	$links = jkf_tml_get_option('user_links', $user_role);
	if ( !is_array($links) || empty($links) )
		$links = array();
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
	$current = jkf_tml_get_option('user_links');
	$default = jkf_tml_custom_user_links_default_settings();
	
	if ( is_array($current) )
		jkf_tml_update_option(array_merge($default, $current), 'user_links');
	else
		jkf_tml_update_option($default, 'user_links');
	
	unset($current, $default);
}

function jkf_tml_custom_user_links_default_settings() {
	global $wp_roles;
	foreach ( $wp_roles->get_names() as $role => $label ) {
		$options[$role] = array(
            array('title' => __('Dashboard'), 'url' => admin_url()),
            array('title' => __('Profile'), 'url' => admin_url('profile.php'))
		);
	}
    return $options;
}

?>