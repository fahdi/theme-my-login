<?php

function jkf_tml_user_mod_user_moderation($user_id) {
    global $theme_my_login, $wpdb;
    
	require_once (TML_MODULE_DIR . '/user-moderation/includes/functions.php');
	
	// Disable original notification
	add_filter('new_user_admin_notification', create_function('', "return false;"), 100);
	add_filter('new_user_notification', create_function('', "return false;"), 100);
	
    $user = new WP_User($user_id);
    $user->set_role('pending');
    if ( 'email' == $theme_my_login->options['moderation']['type'] ) {
        $key = wp_generate_password(20, false);
        $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user->user_login));
		jkf_tml_user_mod_new_user_activation_notification($user_id, $key);
    } elseif ( 'admin' == $theme_my_login->options['moderation']['type'] ) {
		jkf_tml_user_mod_new_user_approval_admin_notification($user_id);
	}
}

function jkf_tml_user_mod_authenticate($user, $username, $password) {
    global $theme_my_login, $wpdb;

    if ( is_a($user, 'WP_User') ) {
        $user_role = reset($user->roles);
        if ( 'pending' == $user_role ) {
            if ( 'email' == $theme_my_login->options['moderation']['type'] )
                return new WP_Error('pending', __('<strong>ERROR</strong>: You have not yet confirmed your e-mail address.', 'theme-my-login'));
            else
                return new WP_Error('pending', __('<strong>ERROR</strong>: Your registration has not yet been approved.', 'theme-my-login'));
        }
    }
    return $user;
}

function jkf_tml_user_mod_user_activation() {
    global $theme_my_login;
    
    require_once( TML_MODULE_DIR . '/user-moderation/includes/functions.php' );
	
    $newpass = ( in_array('custom-passwords/custom-passwords.php', $theme_my_login->options['active_modules']) ) ? 0 : 1;
    $errors = jkf_tml_user_mod_activate_new_user($_GET['key'], $_GET['login'], $newpass);

    if ( !is_wp_error($errors) ) {
        $redirect_to = site_url('wp-login.php?activation=complete');
        if ( 'tml-page' != $theme_my_login->request_instance )
            $redirect_to = jkf_tml_get_current_url('activation=complete&instance=' . $theme_my_login['request_instance']);
        wp_redirect($redirect_to);
        exit();
    }

    $redirect_to = site_url('wp-login.php?activation=invalidkey');
    if ( 'tml-page' != $theme_my_login->request_instance )
        $redirect_to = jkf_tml_get_current_url('activation=invalidkey&instance=' . $theme_my_login['request_instance']);
    wp_redirect($redirect_to);
    exit();
}

function jkf_tml_user_mod_allow_password_reset($allow, $user_id) {
    $user = new WP_User($user_id);
    $user_role = reset($user->roles);
    if ( 'pending' == $user_role )
        $allow = false;
    return $allow;
}

function jkf_tml_user_mod_register_redirect($redirect_to) {
	global $theme_my_login;
	$redirect_to = site_url('wp-login.php');
	if ( 'tml-page' != $theme_my_login->request_instance )
		$redirect_to = jkf_tml_get_current_url('instance=' . $theme_my_login->request_instance);

	if ( 'email' == $theme_my_login->options['moderation']['type'] )
		$redirect_to = add_query_arg('pending', 'activation', $redirect_to);
	elseif ( 'admin' == $theme_my_login->options['moderation']['type'] )
		$redirect_to = add_query_arg('pending', 'approval', $redirect_to);
	return $redirect_to;
}

function jkf_tml_user_mod_login_message($message) {
	global $theme_my_login;
	
	if ( isset($_GET['pending']) && 'activation' == $_GET['pending'] )
		$message = __('Your registration was successful but you must now confirm your email address before you can log in. Please check your email and click on the link provided.', 'theme-my-login');
	elseif ( isset($_GET['pending']) && 'approval' == $_GET['pending'] )
		$message = __('Your registration was successful but you must now be approved by an administrator before you can log in. You will be notified by e-mail once your account has been reviewed.', 'theme-my-login');
	elseif ( isset($_GET['activation']) && 'complete' == $_GET['activation'] ) {
		if ( in_array('custom-passwords/custom-passwords.php', $theme_my_login->options['active_modules']) )
			$message = __('Your account has been activated. You may now log in.', 'theme-my-login');
		else
			$message = __('Your account has been activated. Please check your e-mail for your password.', 'theme-my-login');
	}
	
	// Set invalid key error here rather than create a new function
	if ( $theme_my_login->request_instance == $theme_my_login->current_instance['instance_id'] && isset($_GET['activation']) && 'invalidkey' == $_GET['activation'] )
		$theme_my_login->errors->add('invalid_key', __('<strong>ERROR</strong>: Sorry, that key does not appear to be valid.'));
		
	return $message;
}

?>