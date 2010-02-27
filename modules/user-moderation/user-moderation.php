<?php
/*
Plugin Name: User Moderation
Description: Enabling this module will initialize user moderation. You will then have to configure the settings via the "Moderation" tab.
*/

add_action('tml_init', 'jkf_tml_user_mod_init');
function jkf_tml_user_mod_init() {
    global $theme_my_login;
    
	include( TML_MODULE_DIR . '/user-moderation/includes/hook-functions.php' );
	
    if ( in_array($theme_my_login->options['moderation']['type'], array('admin', 'email')) ) {
        add_action('user_register', 'jkf_tml_user_mod_user_moderation', 100);
        add_action('authenticate', 'jkf_tml_user_mod_authenticate', 100, 3);
        add_filter('allow_password_reset', 'jkf_tml_user_mod_allow_password_reset', 10, 2);
		add_filter('register_redirect', 'jkf_tml_user_mod_register_redirect', 100);
		add_filter('login_message', 'jkf_tml_user_mod_login_message', 100);
        if ( 'email' == $theme_my_login->options['moderation']['type'] )
            add_action('login_action_activate', 'jkf_tml_user_mod_user_activation');
		if ( in_array('custom-email/custom-email.php', $theme_my_login->options['active_modules']) )
			add_action('user_activation_post', 'jkf_tml_custom_email_new_user_filters', 10, 2);
    }
}

add_action('tml_admin_init', 'jkf_tml_user_mod_admin_init');
function jkf_tml_user_mod_admin_init() {
    global $theme_my_login;
	
	include( TML_MODULE_DIR . '/user-moderation/admin/admin.php' );
	
    add_action('tml_admin_menu', 'jkf_tml_user_mod_admin_menu');
    add_filter('tml_save_settings', 'jkf_tml_user_mod_save_settings');	
	
	add_action('load-users.php', 'jkf_tml_user_mod_load_users_page');
	
	if ( in_array('custom-email/custom-email.php', $theme_my_login->options['active_modules']) ) {
		require_once( TML_MODULE_DIR . '/user-moderation/includes/email-functions.php' );
		jkf_tml_user_mod_custom_email_filters();
	}
}

add_action('activate_user-moderation/user-moderation.php', 'jkf_tml_user_mod_activate');
function jkf_tml_user_mod_activate() {
	global $theme_my_login, $wp_roles;
	
	if ( ! $wp_roles->is_role('pending') )
		add_role( 'pending', 'Pending', array() );
	
	if ( isset($theme_my_login->options['moderation']) && is_array($theme_my_login->options['moderation']) )
		$theme_my_login->options['moderation'] = array_merge(jkf_tml_user_mod_default_settings(), $theme_my_login->options['moderation']);
	else
		$theme_my_login->options['moderation'] = jkf_tml_user_mod_default_settings();
		
	update_option('theme_my_login', $theme_my_login->options);
}

add_action('deactivate_user-moderation/user-moderation.php', 'jkf_tml_user_mod_deactivate');
add_action('uninstall_user-moderation/user-moderation.php', 'jkf_tml_user_mod_deactivate');
function jkf_tml_user_mod_deactivate() {
	global $wp_roles;
	
	if ( empty($wp_roles) )
		$wp_roles = new WP_Roles();
	
	if ( $wp_roles->is_role('pending') )
		remove_role( 'pending' );
}

function jkf_tml_user_mod_default_settings() {
	$options = array(
		'type' => 'none'
		);
	return $options;
}
        
?>