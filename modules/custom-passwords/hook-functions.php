<?php

function jkf_tml_custom_pass_form($instance_id) {
?>
<p><label for="pass1-<?php echo $instance_id; ?>"><?php _e('Password:', 'theme-my-login');?></label>
<input autocomplete="off" name="pass1" id="pass1-<?php echo $instance_id; ?>" class="input" size="20" value="" type="password" /></p>
<p><label for="pass2-<?php echo $instance_id; ?>"><?php _e('Confirm Password:', 'theme-my-login');?></label>
<input autocomplete="off" name="pass2" id="pass2-<?php echo $instance_id; ?>" class="input" size="20" value="" type="password" /></p>
<?php
}

function jkf_tml_custom_pass_errors($errors = '') {
    if ( empty($_POST['pass1']) || $_POST['pass1'] == '' || empty($_POST['pass2']) || $_POST['pass2'] == '' ) {
        $errors->add('empty_password', __('<strong>ERROR</strong>: Please enter a password.'));
    } elseif ( $_POST['pass1'] !== $_POST['pass2'] ) {
        $errors->add('password_mismatch', __('<strong>ERROR</strong>: Your passwords do not match.'));
    } elseif ( strlen($_POST['pass1']) < 6 ) {
        $errors->add('password_length', __('<strong>ERROR</strong>: Your password must be at least 6 characters in length.'));
    } else {
        $_POST['user_pw'] = $_POST['pass1'];
    }	
    return $errors;
}

function jkf_tml_custom_pass_set_pass($user_pass) {
    if ( isset($_POST['user_pw']) && !empty($_POST['user_pw']) )
        $user_pass = $_POST['user_pw'];
    return $user_pass;
}

function jkf_tml_custom_pass_reset_action() {	
	$user = jkf_tml_custom_pass_validate_reset_key($_GET['key'], $_GET['login']);
	if ( is_wp_error($user) ) {
       $redirect_to = site_url('wp-login.php?action=lostpassword&error=invalidkey');
        if ( 'tml-page' != jkf_tml_get_var('request_instance') )
            $redirect_to = jkf_tml_get_current_url('action=lostpassword&error=invalidkey&instance=' . jkf_tml_get_var('request_instance'));
        wp_redirect($redirect_to);
        exit();
	}
	
	if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
		$errors = jkf_tml_custom_pass_reset_pass();
        if ( ! is_wp_error($errors) ) {
            $redirect_to = site_url('wp-login.php?resetpass=complete');
            if ( 'tml-page' != jkf_tml_get_var('request_instance') )
                $redirect_to = jkf_tml_get_current_url('resetpass=complete&instance=' . jkf_tml_get_var('request_instance'));
            wp_redirect($redirect_to);
            exit();
        } else jkf_tml_set_error($errors);
	}
}

function jkf_tml_custom_pass_reset_form($instance_id) {	
	$message = apply_filters('resetpass_message', __('Please enter a new password.', 'theme-my-login'));
	
	jkf_tml_get_header($message);
	
	if ( ! jkf_tml_get_error('invalid_key') ) {
	?>
    <form name="resetpasswordform" id="resetpasswordform-<?php echo $instance_id; ?>" action="<?php echo esc_url(jkf_tml_get_current_url('action=rp&key=' . $_GET['key'] . '&login=' . $_GET['login'] . '&instance=' . $instance_id)); ?>" method="post">
		<p>
			<label for="pass1-<?php echo $instance_id; ?>"><?php _e('New Password:', 'theme-my-login');?></label>
			<input autocomplete="off" name="pass1" id="pass1-<?php echo $instance_id; ?>" class="input" size="20" value="" type="password" />
		</p>
		<p>
			<label for="pass2-<?php echo $instance_id; ?>"><?php _e('Confirm Password:', 'theme-my-login');?></label>
			<input autocomplete="off" name="pass2" id="pass2-<?php echo $instance_id; ?>" class="input" size="20" value="" type="password" />
		</p>
        <?php do_action('resetpassword_form', $instance_id); ?>
        <p class="submit">
            <input type="submit" name="wp-submit" id="wp-submit-<?php echo $instance_id; ?>" value="<?php _e('Change Password', 'theme-my-login'); ?>" />
        </p>
    </form>
<?php
	}
	jkf_tml_get_footer(true, true, false);
}

function jkf_tml_custom_pass_register_redirect($redirect_to) {
	$request_instace = jkf_tml_get_var('request_instance');
	$redirect_to = site_url('wp-login.php?registration=complete');
	if ( 'tml-page' != $request_instance )
		$redirect_to = jkf_tml_get_current_url('registration=complete&instance=' . $request_instance);	
	return $redirect_to;
}

function jkf_tml_custom_pass_resetpass_redirect($redirect_to) {
	$request_instace = jkf_tml_get_var('request_instance');
	$redirect_to = site_url('wp-login.php?resetpass=complete');
	if ( 'tml-page' != $request_instance )
		$redirect_to = jkf_tml_get_current_url('resetpass=complete&instance=' . $request_instance);	
	return $redirect_to;
}

function jkf_tml_custom_pass_login_message($message) {
	if ( isset($_GET['action']) && 'register' == $_GET['action'] )
		$message = '';
	elseif ( isset($_GET['registration']) && 'complete' == $_GET['registration'] )
		$message = __('Registration complete. You may now log in.', 'theme-my-login');
	elseif ( isset($_GET['resetpass']) && 'complete' == $_GET['resetpass'] )
		$message = __('Your password has been saved. You may now log in.', 'theme-my-login');
	return $message;
}

function jkf_tml_custom_pass_lostpassword_message($message) {
	$message = __('Please enter your username or e-mail address. You will recieve an e-mail with a link to reset your password.', 'theme-my-login');
	return $message;
}

?>