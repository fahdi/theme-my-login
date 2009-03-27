<?php

require('wp271-functions.php');

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

$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);

if ( $http_post ) {
    $errors = retrieve_password();
    if ( !is_wp_error($errors) ) {
        wp_redirect('wp-login.php?checkemail=confirm');
        exit();
    }
}

if ( isset($_GET['error']) && 'invalidkey' == $_GET['error'] ) $errors->add('invalidkey', __('Sorry, that key does not appear to be valid.'));

do_action('lost_password');
$this->DoHeader('<p class="message">' . __('Please enter your username or e-mail address. You will receive a new password via e-mail.') . '</p>', $errors);

$user_login = isset($_POST['user_login']) ? stripslashes($_POST['user_login']) : '';

?>

    <form name="lostpasswordform" id="lostpasswordform" action="" method="post">
    <p>
        <label><?php _e('Username or E-mail:') ?><br />
        <input type="text" name="user_login" id="user_login" class="input" value="<?php echo attribute_escape($user_login); ?>" size="20" tabindex="10" /></label>
    </p>
    <?php do_action('lostpassword_form'); ?>
    <p class="submit"><input type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Get New Password'); ?>" tabindex="100" /></p>
    </form>

    <ul class="nav">
        <li><a href="<?php echo site_url('wp-login.php', 'login') ?>"><?php _e('Log in') ?></a></li>
        <?php if (get_option('users_can_register')) : ?>
        <li><a href="<?php echo site_url('wp-login.php?action=register', 'login') ?>"><?php _e('Register') ?></li>
        <?php endif; ?>
    </ul>

</div>
