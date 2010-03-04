<?php
/*
Plugin Name: User Moderation
Description: Enabling this module will initialize user moderation. You will then have to configure the settings via the "Moderation" tab.
*/

add_action('tml_init', 'jkf_tml_user_mod_init');
function jkf_tml_user_mod_init() {
	include( TML_MODULE_DIR . '/user-moderation/includes/hook-functions.php' );
	$moderation = jkf_tml_get_option('moderation', 'type');
    if ( in_array($moderation, array('admin', 'email')) ) {
        add_action('user_register', 'jkf_tml_user_mod_user_moderation', 100);
        add_action('authenticate', 'jkf_tml_user_mod_authenticate', 100, 3);
        add_filter('allow_password_reset', 'jkf_tml_user_mod_allow_password_reset', 10, 2);
		add_filter('register_redirect', 'jkf_tml_user_mod_register_redirect', 100);
        if ( 'email' == $moderation ) {
            add_action('login_action_activate', 'jkf_tml_user_mod_user_activation');
			if ( jkf_tml_is_module_active('custom-email/custom-email.php') ) {
				require_once( TML_MODULE_DIR . '/user-moderation/includes/email-functions.php' );
				add_action('register_post', 'jkf_tml_user_mod_custom_email_user_activation_filters');
				add_action('user_activation_post', 'jkf_tml_custom_email_new_user_filters', 10, 2);
			}
		}
    }
}

add_action('template_redirect', 'jkf_tml_user_mod_login_message', 100);
function jkf_tml_user_mod_login_message($message) {
	if ( isset($_GET['pending']) && 'activation' == $_GET['pending'] )
		jkf_tml_set_error('pending_activation', __('Your registration was successful but you must now confirm your email address before you can log in. Please check your email and click on the link provided.', 'theme-my-login'), 'message');
	elseif ( isset($_GET['pending']) && 'approval' == $_GET['pending'] )
		jkf_tml_set_error('pending_approval', __('Your registration was successful but you must now be approved by an administrator before you can log in. You will be notified by e-mail once your account has been reviewed.', 'theme-my-login'), 'message');
	elseif ( isset($_GET['activation']) && 'complete' == $_GET['activation'] ) {
		if ( jkf_tml_is_module_active('custom-passwords/custom-passwords.php') )
			jkf_tml_set_error('activation_complete', __('Your account has been activated. You may now log in.', 'theme-my-login'), 'message');
		else
			jkf_tml_set_error('activation_complete', __('Your account has been activated. Please check your e-mail for your password.', 'theme-my-login'), 'message');
	}

	if ( jkf_tml_get_var('request_instance') == jkf_tml_get_var('current_instance', 'instance_id') && isset($_GET['activation']) && 'invalidkey' == $_GET['activation'] )
		jkf_tml_set_error('invalid_key', __('<strong>ERROR</strong>: Sorry, that key does not appear to be valid.'));
}

add_action('tml_admin_init', 'jkf_tml_user_mod_admin_init');
function jkf_tml_user_mod_admin_init() {
	include( TML_MODULE_DIR . '/user-moderation/admin/admin.php' );

    add_action('tml_admin_menu', 'jkf_tml_user_mod_admin_menu');

	add_action('load-users.php', 'jkf_tml_user_mod_load_users_page');

	if ( jkf_tml_is_module_active('custom-email/custom-email.php') ) {
		require_once( TML_MODULE_DIR . '/user-moderation/includes/email-functions.php' );
		add_action('approve_user', 'jkf_tml_user_mod_custom_email_user_approval_filters');
		add_action('deny_user', 'jkf_tml_user_mod_custom_email_user_denial_filters');
	}
}

add_action('activate_user-moderation/user-moderation.php', 'jkf_tml_user_mod_activate');
function jkf_tml_user_mod_activate() {
	$current = jkf_tml_get_option('moderation');
	$default = jkf_tml_user_mod_default_settings();

	add_role( 'pending', 'Pending', array() );

	if ( is_array($current) )
		jkf_tml_update_option(array_merge($default, $current), 'moderation');
	else
		jkf_tml_update_option($default, 'moderation');

	unset($current, $default);
}

add_action('deactivate_user-moderation/user-moderation.php', 'jkf_tml_user_mod_deactivate');
add_action('uninstall_user-moderation/user-moderation.php', 'jkf_tml_user_mod_deactivate');
function jkf_tml_user_mod_deactivate() {
	remove_role( 'pending' );
}

function jkf_tml_user_mod_default_settings() {
	$options = array(
		'type' => 'none'
		);
	return $options;
}
        
?>