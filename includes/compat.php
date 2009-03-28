<?php
if ( !function_exists('is_ssl') ) :
function is_ssl() {
    if ( isset($_SERVER['HTTPS']) ) {
        if ( 'on' == strtolower($_SERVER['HTTPS']) )
            return true;
        if ( '1' == $_SERVER['HTTPS'] )
            return true;
    } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
        return true;
    }
    return false;
}
endif;

if ( !function_exists('site_url') ) :
function site_url($path = '', $scheme = null) {
    // should the list of allowed schemes be maintained elsewhere?
    $orig_scheme = $scheme;
    if ( !in_array($scheme, array('http', 'https')) ) {
        if ( ('login_post' == $scheme) && ( force_ssl_login() || force_ssl_admin() ) )
            $scheme = 'https';
        elseif ( ('login' == $scheme) && ( force_ssl_admin() ) )
            $scheme = 'https';
        elseif ( ('admin' == $scheme) && force_ssl_admin() )
            $scheme = 'https';
        else
            $scheme = ( is_ssl() ? 'https' : 'http' );
    }

    $url = str_replace( 'http://', "{$scheme}://", get_option('siteurl') );

    if ( !empty($path) && is_string($path) && strpos($path, '..') === false )
        $url .= '/' . ltrim($path, '/');

    return apply_filters('site_url', $url, $path, $orig_scheme);
}
endif;

if ( !function_exists('admin_url') ) :
function admin_url($path = '') {
    $url = site_url('wp-admin/', 'admin');

    if ( !empty($path) && is_string($path) && strpos($path, '..') === false )
        $url .= ltrim($path, '/');

    return $url;
}
endif;

if ( !function_exists('force_ssl_login') ) :
function force_ssl_login($force = '') {
    static $forced;

    if ( '' != $force ) {
        $old_forced = $forced;
        $forced = $force;
        return $old_forced;
    }

    return $forced;
}
endif;

if ( !function_exists('force_ssl_admin') ) :
function force_ssl_admin($force = '') {
    static $forced;

    if ( '' != $force ) {
        $old_forced = $forced;
        $forced = $force;
        return $old_forced;
    }

    return $forced;
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

if (!function_exists('wp_reset_vars')) :
function wp_reset_vars( $vars ) {
    for ( $i=0; $i<count( $vars ); $i += 1 ) {
        $var = $vars[$i];
        global $$var;

        if (!isset( $$var ) ) {
            if ( empty( $_POST["$var"] ) ) {
                if ( empty( $_GET["$var"] ) )
                    $$var = '';
                else
                    $$var = $_GET["$var"];
            } else {
                $$var = $_POST["$var"];
            }
        }
    }
}
endif;

if (!function_exists('get_user_to_edit')) :
function get_user_to_edit( $user_id ) {
    $user = new WP_User( $user_id );
    $user->user_login   = attribute_escape($user->user_login);
    $user->user_email   = attribute_escape($user->user_email);
    $user->user_url     = clean_url($user->user_url);
    $user->first_name   = attribute_escape($user->first_name);
    $user->last_name    = attribute_escape($user->last_name);
    $user->display_name = attribute_escape($user->display_name);
    $user->nickname     = attribute_escape($user->nickname);
    $user->aim          = isset( $user->aim ) && !empty( $user->aim ) ? attribute_escape($user->aim) : '';
    $user->yim          = isset( $user->yim ) && !empty( $user->yim ) ? attribute_escape($user->yim) : '';
    $user->jabber       = isset( $user->jabber ) && !empty( $user->jabber ) ? attribute_escape($user->jabber) : '';
    $user->description  = isset( $user->description ) && !empty( $user->description ) ? wp_specialchars($user->description) : '';

    return $user;
}
endif;
?>
