<?php

/**
 * Handles sending password retrieval email to user.
 *
 * @uses $wpdb WordPress Database object
 *
 * @return bool|WP_Error True: when finish. WP_Error on error
 */
function retrieve_password() {
    global $wpdb, $ThemeMyLogin;

    $errors = new WP_Error();

    if ( empty( $_POST['user_login'] ) && empty( $_POST['user_email'] ) )
        $errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or e-mail address.'));

    if ( strpos($_POST['user_login'], '@') ) {
        $user_data = get_user_by_email(trim($_POST['user_login']));
        if ( empty($user_data) )
            $errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.'));
    } else {
        $login = trim($_POST['user_login']);
        $user_data = get_userdatabylogin($login);
    }

    do_action('lostpassword_post');

    if ( $errors->get_error_code() )
        return $errors;

    if ( !$user_data ) {
        $errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or e-mail.'));
        return $errors;
    }

    // redefining user_login ensures we return the right case in the email
    $user_login = $user_data->user_login;
    $user_email = $user_data->user_email;

    do_action('retreive_password', $user_login);  // Misspelled and deprecated
    do_action('retrieve_password', $user_login);

    $allow = apply_filters('allow_password_reset', true, $user_data->ID);

    if ( ! $allow )
        return new WP_Error('no_password_reset', __('Password reset is not allowed for this user'));
    else if ( is_wp_error($allow) )
        return $allow;

    $key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
    if ( empty($key) ) {
        // Generate something random for a key...
        $key = wp_generate_password(20, false);
        do_action('retrieve_password_key', $user_login, $key);
        // Now insert the new md5 key into the db
        $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
    }
    
    // The blogname option is escaped with esc_html on the way into the database in sanitize_option
    // we want to reverse this for the plain text arena of emails.
    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    
    $replace_this = array('/%blogname%/', '/%siteurl%/', '/%reseturl%/', '/%user_login%/', '/%user_email%/', '/%key%/', '/%user_ip%/');
    $replace_with = array($blogname, get_option('siteurl'), site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login'), $user_login, $user_email, $key, $_SERVER['REMOTE_ADDR']);
    $title = $ThemeMyLogin->options['retrieve_pass_email']['subject'];
    $message = $ThemeMyLogin->options['retrieve_pass_email']['message'];
    
    if ( !empty($title) )
        $title = preg_replace($replace_this, $replace_with, $title);
    else
        $title = sprintf(__('[%s] Password Reset', 'theme-my-login'), $blogname);

    if ( !empty($message) )
        $message = preg_replace($replace_this, $replace_with, $message);
    else {
        $message = __('Someone has asked to reset the password for the following site and username.') . "\r\n\r\n";
        $message .= get_option('siteurl') . "\r\n\r\n";
        $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
        $message .= __('To reset your password visit the following address, otherwise just ignore this email and nothing will happen.') . "\r\n\r\n";
        $message .= site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . "\r\n";
    }

    $title = apply_filters('retrieve_password_title', $title);
    $message = apply_filters('retrieve_password_message', $message, $key);

    if ( $message && !$ThemeMyLogin->sendEmail($user_email, $title, $message) )
        die('<p>' . __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') . '</p>');

    return true;
}

/**
 * Handles resetting the user's password.
 *
 * @uses $wpdb WordPress Database object
 *
 * @param string $key Hash to validate sending user's password
 * @return bool|WP_Error
 */
function reset_password($key, $login) {
    global $wpdb, $ThemeMyLogin;

    $key = preg_replace('/[^a-z0-9]/i', '', $key);

    if ( empty( $key ) || !is_string( $key ) )
        return new WP_Error('invalid_key', __('Invalid key'));

    if ( empty($login) || !is_string($login) )
        return new WP_Error('invalid_key', __('Invalid key'));

    $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login));
    if ( empty( $user ) )
        return new WP_Error('invalid_key', __('Invalid key'));

    // Generate something random for a password...
    $new_pass = wp_generate_password();

    do_action('password_reset', $user, $new_pass);

    wp_set_password($new_pass, $user->ID);
    update_usermeta($user->ID, 'default_password_nag', true); //Set up the Password change nag.
    
    // The blogname option is escaped with esc_html on the way into the database in sanitize_option
    // we want to reverse this for the plain text arena of emails.
    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    
    $replace_this = array('/%blogname%/', '/%siteurl%/', '/%user_login%/', '/%user_email%/', '/%user_pass%/', '/%user_ip%/');
    $replace_with = array($blogname, get_option('siteurl'), $user->user_login, $user->user_email, $new_pass, $_SERVER['REMOTE_ADDR']);
    $title = $ThemeMyLogin->options['reset_pass_email']['subject'];
    $message = $ThemeMyLogin->options['reset_pass_email']['message'];
    
    if ( !empty($title) )
        $title = preg_replace($replace_this, $replace_with, $title);
    else
        $title = sprintf(__('[%s] Your new password'), $blogname);
    
    if ( !empty($message) )
        $message = preg_replace($replace_this, $replace_with, $message);
    else {
        $message  = sprintf(__('Username: %s'), $user->user_login) . "\r\n";
        $message .= sprintf(__('Password: %s'), $new_pass) . "\r\n";
        $message .= site_url('wp-login.php', 'login') . "\r\n";
    }

    $title = apply_filters('password_reset_title', $title);
    $message = apply_filters('password_reset_message', $message, $new_pass);

    if ( $message && !$ThemeMyLogin->sendEmail($user->user_email, $title, $message) )
          die('<p>' . __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') . '</p>');

    if ( !$ThemeMyLogin->options['reset_pass_email']['admin_disable'] )
        wp_password_change_notification($user);

    return true;
}

/**
 * Handles registering a new user.
 *
 * @param string $user_login User's username for logging in
 * @param string $user_email User's email address to send password and add
 * @return int|WP_Error Either user's ID or error on failure.
 */
function register_new_user($user_login, $user_email) {
    $errors = new WP_Error();

    $user_login = sanitize_user( $user_login );
    $user_email = apply_filters( 'user_registration_email', $user_email );

    // Check the username
    if ( $user_login == '' )
        $errors->add('empty_username', __('<strong>ERROR</strong>: Please enter a username.'));
    elseif ( !validate_username( $user_login ) ) {
        $errors->add('invalid_username', __('<strong>ERROR</strong>: This username is invalid.  Please enter a valid username.'));
        $user_login = '';
    } elseif ( username_exists( $user_login ) )
        $errors->add('username_exists', __('<strong>ERROR</strong>: This username is already registered, please choose another one.'));

    // Check the e-mail address
    if ($user_email == '') {
        $errors->add('empty_email', __('<strong>ERROR</strong>: Please type your e-mail address.'));
    } elseif ( !is_email( $user_email ) ) {
        $errors->add('invalid_email', __('<strong>ERROR</strong>: The email address isn&#8217;t correct.'));
        $user_email = '';
    } elseif ( email_exists( $user_email ) )
        $errors->add('email_exists', __('<strong>ERROR</strong>: This email is already registered, please choose another one.'));

    do_action('register_post', $user_login, $user_email, $errors);

    $errors = apply_filters( 'registration_errors', $errors, $user_login, $user_email );

    if ( $errors->get_error_code() )
        return $errors;

    $user_pass = wp_generate_password();
    $user_id = wp_create_user( $user_login, $user_pass, $user_email );
    if ( !$user_id ) {
        $errors->add('registerfail', sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !'), get_option('admin_email')));
        return $errors;
    }

    wp_new_user_notification($user_id, $user_pass);

    return $user_id;
}

/**
 * Handles activating a new user by user email confirmation.
 *
 * @param string $key Hash to validate sending confirmation email
 * @param string $login User's username for logging in
 * @param bool $newpass Whether or not to assign a new password
 * @return bool|WP_Error
 */
function activate_new_user($key, $login, $newpass = false) {
    global $wpdb;

    $key = preg_replace('/[^a-z0-9]/i', '', $key);

    if ( empty($key) || !is_string($key) )
        return new WP_Error('invalid_key', __('Invalid key'));

    if ( empty($login) || !is_string($login) )
        return new WP_Error('invalid_key', __('Invalid key'));

    $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login));
    if ( empty( $user ) )
        return new WP_Error('invalid_key', __('Invalid key'));
        
    $wpdb->update($wpdb->users, array('user_activation_key' => ''), array('user_login' => $login) );
    
    $user_object = new WP_User($user->ID);
    $user_object->set_role(get_option('default_role'));
    unset($user_object);
    
    $pass = __('Same as when you signed up.', 'theme-my-login');
    if ( $newpass ) {
        $pass = wp_generate_password();
        wp_set_password($pass, $user->ID);
    }
    
    wp_new_user_notification($user->ID, $pass);
    
    return true;
}

/**
 * Handles activating a new user by admin approval.
 *
 * @param string $id User's ID
 * @param bool $newpass Whether or not to assign a new password
 * @return bool Returns false if not a valid user
 */
function approve_new_user($id, $newpass = false) {
    global $wpdb, $ThemeMyLogin;
    
    $id = (int) $id;
    
    $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE ID = %d", $id));
    if ( empty( $user ) )
        return false;

    $wpdb->update($wpdb->users, array('user_activation_key' => ''), array('ID' => $id) );

    $user_object = new WP_User($user->ID);
    $user_object->set_role(get_option('default_role'));
    unset($user_object);

    $pass = __('Same as when you signed up.', 'theme-my-login');
    if ( $newpass ) {
        $pass = wp_generate_password();
        wp_set_password($pass, $user->ID);
    }
    
    // The blogname option is escaped with esc_html on the way into the database in sanitize_option
    // we want to reverse this for the plain text arena of emails.
    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

    $replace_this = array('/%blogname%/', '/%siteurl%/', '/%user_login%/', '/%user_email%/', '/%user_pass%/');
    $replace_with = array($blogname, get_option('siteurl'), $user->user_login, $user->user_email, $pass);
    $subject = $ThemeMyLogin->options['user_approval_email']['subject'];
    $message = $ThemeMyLogin->options['user_approval_email']['message'];

    if ( !empty($subject) )
        $subject = preg_replace($replace_this, $replace_with, $subject);
    else
        $subject = sprintf(__('[%s] Registration Approved', 'theme-my-login'), $blogname);
        
    if ( !empty($message) )
        $message = preg_replace($replace_this, $replace_with, $message);
    else {
        $message  = sprintf(__('You have been approved access to %s', 'theme-my-login'), $blogname) . "\r\n\r\n";
        $message .= sprintf(__('Username: %s', 'theme-my-login'), $user->user_login) . "\r\n";
        $message .= sprintf(__('Password: %s', 'theme-my-login'), $pass) . "\r\n";
        $message .= "\r\n";
        $message .= site_url('wp-login.php', 'login') . "\r\n";
    }
    $ThemeMyLogin->sendEmail($user->user_email, $subject, $message);

    wp_new_user_notification($user->ID, $pass);

    return true;
}

?>