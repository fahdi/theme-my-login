<?php
/*
Plugin Name: Custom Passwords
Description: Enabling this module will initialize and enable custom passwords. There are no other settings for this module.
*/

add_action('tml_init', 'jkf_tml_custom_pass_init');
function jkf_tml_custom_pass_init() {
	include_once( TML_MODULE_DIR . '/custom-passwords/hook-functions.php' );
	require_once( TML_MODULE_DIR . '/custom-passwords/functions.php' );
	// Password registration
	add_action('register_form', 'jkf_tml_custom_pass_form');
	add_filter('registration_errors', 'jkf_tml_custom_pass_errors');
	add_filter('user_registration_pass', 'jkf_tml_custom_pass_set_pass');
	// Password reset
	add_action('login_form_resetpass', 'jkf_tml_custom_pass_reset_form');
	add_action('login_form_rp', 'jkf_tml_custom_pass_reset_form');
	add_action('login_action_resetpass', 'jkf_tml_custom_pass_reset_action');
	add_action('login_action_rp', 'jkf_tml_custom_pass_reset_action');
	// Template messages
	add_filter('login_message', 'jkf_tml_custom_pass_login_message');
	add_filter('lostpassword_message', 'jkf_tml_custom_pass_lostpassword_message');
	// Redirection
	add_filter('register_redirect', 'jkf_tml_custom_pass_register_redirect');
	add_filter('resetpass_redirect', 'jkf_tml_custom_pass_resetpass_redirect');
}

?>