<?php

if (!function_exists('theme_my_login_url')) :
function theme_my_login_url($args = array()) {
    $login_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';

    if ($_SERVER["SERVER_PORT"] != "80") {
        $login_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
        $login_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }

    $keys = array('action', 'checkemail', 'error', 'loggedout', 'registered', 'redirect_to', 'updated', 'key');
    $login_url = remove_query_arg($keys, $login_url);

    if (!empty($args)) {
        foreach ($args as $key => $value)
            $login_url = add_query_arg($key, $value, $login_url);
    }

    return $login_url;
}
endif;

if (!function_exists('login_header')) :
function login_header($message = '', $wp_error = '') {
    global $error;

    if ( empty($wp_error) )
        $wp_error = new WP_Error();

    echo '<div id="login">';

    if ( !empty( $message ) ) echo apply_filters('login_message', $message) . "\n";

    // Incase a plugin uses $error rather than the $errors object
    if ( !empty( $error ) ) {
        $wp_error->add('error', $error);
        unset($error);
    }

    if ( $wp_error->get_error_code() ) {
        $errors = '';
        $messages = '';
        foreach ( $wp_error->get_error_codes() as $code ) {
            $severity = $wp_error->get_error_data($code);
            foreach ( $wp_error->get_error_messages($code) as $error ) {
                if ( 'message' == $severity )
                    $messages .= '    ' . $error . "<br />\n";
                else
                    $errors .= '    ' . $error . "<br />\n";
            }
        }
        if ( !empty($errors) )
            echo '<p class="error">' . apply_filters('login_errors', $errors) . "</p>\n";
        if ( !empty($messages) )
            echo '<p class="message">' . apply_filters('login_messages', $messages) . "</p>\n";
    }
}
endif;

if (!function_exists('login_footer')) :
function login_footer($type = 'page') {
    $_GET['action'] = isset($_GET['action']) ? $_GET['action'] : 'login';
    echo '<ul class="links">' . "\n";
    if (in_array($_GET['action'], array('register', 'lostpassword')) || $_GET['action'] == 'login' && isset($_GET['checkemail'])) {
        $url = ($type == 'widget') ? add_query_arg('action', 'login', wp_guess_url()) : site_url('wp-login.php', 'login');
        echo '<li><a href="' . $url . '">' . __('Log in') . '</a></li>' . "\n";
    }
    if (get_option('users_can_register') && $_GET['action'] != 'register') {
        $url = ($type == 'widget') ? add_query_arg('action', 'register', wp_guess_url()) : site_url('wp-login.php?action=register', 'login');
        echo '<li><a href="' . $url . '">' . __('Register') . '</a></li>' . "\n";
    }
    if ($_GET['action'] != 'lostpassword') {
        $url = ($type == 'widget') ? add_query_arg('action', 'lostpassword', wp_guess_url()) : site_url('wp-login.php?action=lostpassword', 'login');
        echo '<li><a href="' . $url . '" title="' . __('Password Lost and Found') . '">' . __('Lost your password?') . '</a></li>' . "\n";
    }
    echo '</ul>' . "\n";
    echo '</div>' . "\n";
}
endif;

if (!function_exists('retrieve_password')) :
function retrieve_password() {
    global $wpdb;

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
        $wpdb->query($wpdb->prepare("UPDATE $wpdb->users SET user_activation_key = %s WHERE user_login = %s", $key, $user_login));
    }
    $message = __('Someone has asked to reset the password for the following site and username.') . "\r\n\r\n";
    $message .= get_option('siteurl') . "\r\n\r\n";
    $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
    $message .= __('To reset your password visit the following address, otherwise just ignore this email and nothing will happen.') . "\r\n\r\n";
    $message .= site_url("wp-login.php?action=rp&key=$key", 'login') . "\r\n";

    if ( !wp_mail($user_email, sprintf(__('[%s] Password Reset'), get_option('blogname')), $message) )
        die('<p>' . __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') . '</p>');

    return true;
}
endif;

if (!function_exists('reset_password')) :
function reset_password($key) {
    global $wpdb;

    $key = preg_replace('/[^a-z0-9]/i', '', $key);

    if ( empty( $key ) )
        return new WP_Error('invalid_key', __('Invalid key'));

    $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s", $key));
    if ( empty( $user ) )
        return new WP_Error('invalid_key', __('Invalid key'));

    do_action('password_reset', $user);

    // Generate something random for a password...
    $new_pass = wp_generate_password();
    wp_set_password($new_pass, $user->ID);
    $message  = sprintf(__('Username: %s'), $user->user_login) . "\r\n";
    $message .= sprintf(__('Password: %s'), $new_pass) . "\r\n";
    $message .= site_url('wp-login.php', 'login') . "\r\n";

    if (  !wp_mail($user->user_email, sprintf(__('[%s] Your new password'), get_option('blogname')), $message) )
        die('<p>' . __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') . '</p>');

    wp_password_change_notification($user);

    return true;
}
endif;

if (!function_exists('register_new_user')) :
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

    $errors = apply_filters( 'registration_errors', $errors );

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
endif;

?>
