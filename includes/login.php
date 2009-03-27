<?php

require('wp271-functions.php');

$secure_cookie = '';

// If the user wants ssl but the session is not ssl, force a secure cookie.
if ( !empty($_POST['log']) && !force_ssl_admin() ) {
    $user_name = sanitize_user($_POST['log']);
    if ( $user = get_userdatabylogin($user_name) ) {
        if ( get_user_option('use_ssl', $user->ID) ) {
            $secure_cookie = true;
            force_ssl_admin(true);
        }
    }
}

if ( isset( $_REQUEST['redirect_to'] ) ) {
    $redirect_to = $_REQUEST['redirect_to'];
    // Redirect to https if user wants ssl
    if ( $secure_cookie && false !== strpos($redirect_to, 'wp-admin') )
        $redirect_to = preg_replace('|^http://|', 'https://', $redirect_to);
    } else {
        $redirect_to = $this->GetOption('login_redirect');
    }

    if ( !$secure_cookie && is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )
        $secure_cookie = false;

    $user = wp_signon('', $secure_cookie);

    $redirect_to = apply_filters('login_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user);

    if ( !is_wp_error($user) ) {
        // If the user can't edit posts, send them to their profile.
        if ( !$user->has_cap('edit_posts') && ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' ) )
            $redirect_to = admin_url('profile.php');
        wp_safe_redirect($redirect_to);
        exit();
    }

    $errors = $user;
    // Clear errors if loggedout is set.
    if ( !empty($_GET['loggedout']) )
        $errors = new WP_Error();

    // If cookies are disabled we can't log in even with a valid user+pass
    if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
        $errors->add('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress."));

    // Some parts of this script use the main login form to display a message
    if        ( isset($_GET['loggedout']) && TRUE == $_GET['loggedout'] )            $errors->add('loggedout', __('You are now logged out.'), 'message');
    elseif    ( isset($_GET['registration']) && 'disabled' == $_GET['registration'] )    $errors->add('registerdisabled', __('User registration is currently not allowed.'));
    elseif    ( isset($_GET['checkemail']) && 'confirm' == $_GET['checkemail'] )    $errors->add('confirm', __('Check your e-mail for the confirmation link.'), 'message');
    elseif    ( isset($_GET['checkemail']) && 'newpass' == $_GET['checkemail'] )    $errors->add('newpass', __('Check your e-mail for your new password.'), 'message');
    elseif    ( isset($_GET['checkemail']) && 'registered' == $_GET['checkemail'] )    $errors->add('registered', __('Registration complete. Please check your e-mail.'), 'message');

    $this->DoHeader('', $errors);

    if ( isset($_POST['log']) )
        $user_login = ( 'incorrect_password' == $errors->get_error_code() || 'empty_password' == $errors->get_error_code() ) ? attribute_escape(stripslashes($_POST['log'])) : '';
?>

<?php if ( !isset($_GET['checkemail']) || !in_array( $_GET['checkemail'], array('confirm', 'newpass') ) ) : ?>
<form name="loginform" id="loginform" action="<?php echo get_permalink($this->GetOption('page_id')); ?>" method="post">
    <p>
        <label><?php _e('Username') ?><br />
        <input type="text" name="log" id="user_login" class="input" value="<?php echo $user_login; ?>" size="20" tabindex="10" /></label>
    </p>
    <p>
        <label><?php _e('Password') ?><br />
        <input type="password" name="pwd" id="user_pass" class="input" value="" size="20" tabindex="20" /></label>
    </p>
<?php do_action('login_form'); ?>
    <p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" /> <?php _e('Remember Me'); ?></label></p>
    <p class="submit">
        <input type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Log In'); ?>" tabindex="100" />
        <input type="hidden" name="redirect_to" value="<?php echo attribute_escape($redirect_to); ?>" />
        <input type="hidden" name="testcookie" value="1" />
    </p>
</form>
<?php endif; ?>

<ul class="nav">
<?php if ( isset($_GET['checkemail']) && in_array( $_GET['checkemail'], array('confirm', 'newpass') ) ) : ?>
<?php elseif (get_option('users_can_register')) : ?>
<li><a href="<?php echo site_url('wp-login.php?action=register', 'login') ?>"><?php _e('Register') ?></a></li>
<?php endif; ?>
<li><a href="<?php echo site_url('wp-login.php?action=lostpassword', 'login') ?>" title="<?php _e('Password Lost and Found') ?>"><?php _e('Lost your password?') ?></a></li>
</ul>

</div>
