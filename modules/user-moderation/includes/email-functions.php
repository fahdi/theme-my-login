<?php

include_once( TML_MODULE_DIR . '/custom-email/includes/hook-functions.php' );

function jkf_tml_user_mod_custom_email_filters() {
	jkf_tml_custom_email_headers();
	add_filter('user_approval_title', 'jkf_tml_user_mod_user_approval_title');
	add_filter('user_approval_message', 'jkf_tml_user_mod_user_approval_message', 10, 3);
	add_filter('user_denial_title', 'jkf_tml_user_mod_user_denial_title');
	add_filter('user_denial_message', 'jkf_tml_user_mod_user_denial_message', 10, 2);	
}

function jkf_tml_user_mod_user_approval_title($title) {
	global $theme_my_login;
	return empty($theme_my_login->options['email']['user_approval']['title']) ? $title : jkf_tml_custom_email_replace_vars($theme_my_login->options['email']['user_approval']['title'], $user_id);
}

function jkf_tml_user_mod_user_approval_message($message, $new_pass, $user_id) {
	global $theme_my_login;
	$replacements = array(
		'%loginurl%' => site_url('wp-login.php', 'login'),
		'%user_pass%' => $new_pass
		);	
	return empty($theme_my_login->options['email']['user_approval']['message']) ? $message : jkf_tml_custom_email_replace_vars($theme_my_login->options['email']['user_approval']['message'], $user_id, $replacements);
}

function jkf_tml_user_mod_user_denial_title($title) {
	global $theme_my_login;
	return empty($theme_my_login->options['email']['user_denial']['title']) ? $title : jkf_tml_custom_email_replace_vars($theme_my_login->options['email']['user_denial']['title'], $user_id);
}

function jkf_tml_user_mod_user_denial_message($message, $user_id) {
	global $theme_my_login;
	$replacements = array(
		'%loginurl%' => site_url('wp-login.php', 'login'),
		'%user_pass%' => $new_pass
		);	
	return empty($theme_my_login->options['email']['user_denial']['message']) ? $message : jkf_tml_custom_email_replace_vars($theme_my_login->options['email']['user_denial']['message'], $user_id, $replacements);
}

?>