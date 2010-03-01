<?php
/*
Plugin Name: Custom E-mail
Description: Enabling this module will initialize custom e-mails. You will then have to configure the settings via the "E-mail" tab.
*/

if ( function_exists('wp_new_user_notification') )
	add_action('admin_notices', 'jkf_tml_custom_email_new_user_notification_override_notice');
if ( function_exists('wp_password_change_notification') )
	add_action('admin_notices', 'jkf_tml_custom_email_password_change_notification_override_notice');

add_action('tml_init', 'jkf_tml_custom_email_init');
function jkf_tml_custom_email_init() {
	require_once( TML_MODULE_DIR . '/custom-email/includes/hook-functions.php' );
	add_action('retrieve_password', 'jkf_tml_custom_email_retrieve_pass_filters');
	add_action('password_reset', 'jkf_tml_custom_email_reset_pass_filters', 10, 2);
	add_action('register_post', 'jkf_tml_custom_email_new_user_filters', 10, 2);
}

add_action('tml_admin_init', 'jkf_tml_custom_email_admin_init');
function jkf_tml_custom_email_admin_init() {
    require_once( TML_MODULE_DIR . '/custom-email/admin/admin.php' );
	add_action('tml_admin_menu', 'jkf_tml_custom_email_admin_menu');
	add_filter('tml_save_settings', 'jkf_tml_custom_email_save_settings');
}

add_action('activate_custom-email/custom-email.php', 'jkf_tml_custom_email_activate');
function jkf_tml_custom_email_activate() {
	$current = jkf_tml_get_option('email');
	$default = jkf_tml_custom_email_default_settings();
	
	if ( is_array($current) )
		jkf_tml_update_option(array_merge($default, $current), 'email');
	else
		jkf_tml_update_option($current, 'email');
		
	unset($current, $default);
	jkf_tml_save_options();
}

function jkf_tml_custom_email_default_settings() {
	$options = array(
		'mail_from' => '',
		'mail_from_name' => '',
		'mail_content_type' => '',
		'new_user' => array(
			'title' => '',
			'message' => '',
			'admin_disable' => 0
			),
		'retrieve_pass' => array(
			'title' => '',
			'message' => ''
			),
		'reset_pass' => array(
			'title' => '',
			'message' => '',
			'admin_disable' => 0
			)
		);
	return $options;
}

function jkf_tml_custom_email_new_user_notification_override_notice() {
	$message = __('<strong>WARNING</strong>: The function <em>wp_new_user_notification</em> has already been overriden by another plugin. ', 'theme-my-login');
	$message .= __('Some features of the <em>Custom E-mails</em> module will not function properly.', 'theme-my-login');
	echo '<div class="error"><p>' . $message . '</p></div>';
}

function jkf_tml_custom_email_password_change_notification_override_notice() {
	$message = __('<strong>WARNING</strong>: The function <em>wp_password_change_notification</em> has already been overriden by another plugin. ', 'theme-my-login');
	$message .= __('Some features of the <em>Custom E-mails</em> module will not function properly.', 'theme-my-login');
	echo '<div class="error"><p>' . $message . '</p></div>';
}

?>