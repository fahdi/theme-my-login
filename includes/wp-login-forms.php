<?php

require_once (WP_PLUGIN_DIR . '/theme-my-login/includes/wp-login-functions.php');

$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) :

case 'lostpassword' :
case 'retrievepassword' :
    do_action('lost_password');
    login_header('<p class="message">' . $this->GetOption('password_msg') . '</p>', $login_errors);

    $user_login = isset($_POST['user_login']) ? stripslashes($_POST['user_login']) : '';
?>

<form name="lostpasswordform" id="lostpasswordform" action="<?php //echo add_query_arg('action', 'lostpassword', $this->permalink) ?>" method="post">
    <p>
        <label><?php _e('Username or E-mail:', 'theme-my-login') ?><br />
        <input type="text" name="user_login" id="user_login" class="input" value="<?php echo attribute_escape($user_login); ?>" size="20" tabindex="10" /></label>
    </p>
<?php do_action('lostpassword_form'); ?>
    <p class="submit">
    <input type="hidden" name="post-from" id="post-from" value="<?php echo $type; ?>" />
    <input type="hidden" name="action" id="action" value="lostpassword" />
    <input type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Get New Password', 'theme-my-login'); ?>" tabindex="100" />
    </p>
</form>

<?php
    login_footer($type);
break;

case 'register' :
    $user_login = isset($_POST['user_login']) ? $_POST['user_login'] : '';
    $user_email = isset($_POST['user_email']) ? $_POST['user_email'] : '';
    login_header('', $login_errors);
?>

<form name="registerform" id="registerform" action="<?php //echo add_query_arg('action', 'register', $this->permalink) ?>" method="post">
    <p>
        <label><?php _e('Username', 'theme-my-login') ?><br />
        <input type="text" name="user_login" id="user_login" class="input" value="<?php echo attribute_escape(stripslashes($user_login)); ?>" size="20" tabindex="10" /></label>
    </p>
    <p>
        <label><?php _e('E-mail', 'theme-my-login') ?><br />
        <input type="text" name="user_email" id="user_email" class="input" value="<?php echo attribute_escape(stripslashes($user_email)); ?>" size="20" tabindex="20" /></label>
    </p>
<?php do_action('register_form'); ?>
    <p id="reg_passmail"><?php _e($this->GetOption('register_msg')) ?></p>
    <p class="submit">
    <input type="hidden" name="post-from" id="post-from" value="<?php echo $type; ?>" />
    <input type="hidden" name="action" id="action" value="register" />
    <input type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Register', 'theme-my-login'); ?>" tabindex="100" />
    </p>
</form>

<?php
    login_footer($type);
break;

case 'login' :
default :

    if ( isset( $_REQUEST['redirect_to'] ) ) {
        $redirect_to = $_REQUEST['redirect_to'];
        // Redirect to https if user wants ssl
        if ( isset($secure_cookie) && false !== strpos($redirect_to, 'wp-admin') )
            $redirect_to = preg_replace('|^http://|', 'https://', $redirect_to);
    } else {
        $redirect_to = admin_url();
    }
    
    $redirect_to = apply_filters('login_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', isset( $user ) ? $user : '');

    // Clear errors if loggedout is set.
    if ( !empty($_GET['loggedout']) )
        $login_errors = new WP_Error();

    // If cookies are disabled we can't log in even with a valid user+pass
    if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
        $login_errors->add('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress.", 'theme-my-login'));

    // Some parts of this script use the main login form to display a message
    if        ( isset($_GET['loggedout']) && TRUE == $_GET['loggedout'] )            $login_errors->add('loggedout', __('You are now logged out.', 'theme-my-login'), 'message');
    elseif    ( isset($_GET['registration']) && 'disabled' == $_GET['registration'] )    $login_errors->add('registerdisabled', __('User registration is currently not allowed.', 'theme-my-login'));
    elseif    ( isset($_GET['checkemail']) && 'confirm' == $_GET['checkemail'] )    $login_errors->add('confirm', __('Check your e-mail for the confirmation link.', 'theme-my-login'), 'message');
    elseif    ( isset($_GET['checkemail']) && 'newpass' == $_GET['checkemail'] )    $login_errors->add('newpass', __('Check your e-mail for your new password.', 'theme-my-login'), 'message');
    elseif    ( isset($_GET['checkemail']) && 'registered' == $_GET['checkemail'] )    $login_errors->add('registered', $this->GetOption('registered_complete'), 'message');

    login_header('', $login_errors);
    
    if ( isset($_POST['log']) )
        $user_login = ( 'incorrect_password' == $login_errors->get_error_code() || 'empty_password' == $login_errors->get_error_code() ) ? attribute_escape(stripslashes($_POST['log'])) : '';
?>
<?php if ( !isset($_GET['checkemail']) || !in_array( $_GET['checkemail'], array('confirm', 'newpass') ) ) : ?>
<form name="loginform" id="loginform" action="" method="post">
    <p>
        <label><?php _e('Username', 'theme-my-login') ?><br />
        <input type="text" name="log" id="user_login" class="input" value="<?php echo isset($user_login) ? $user_login : ''; ?>" size="20" tabindex="10" /></label>
    </p>
    <p>
        <label><?php _e('Password', 'theme-my-login') ?><br />
        <input type="password" name="pwd" id="user_pass" class="input" value="" size="20" tabindex="20" /></label>
    </p>
<?php do_action('login_form'); ?>
    <p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" /> <?php _e('Remember Me', 'theme-my-login'); ?></label></p>
    <p class="submit">
        <input type="hidden" name="post-from" id="post-from" value="<?php echo $type; ?>" />
        <input type="hidden" name="action" id="action" value="login" />
        <input type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Log In', 'theme-my-login'); ?>" tabindex="100" />
        <input type="hidden" name="redirect_to" value="<?php echo attribute_escape($redirect_to); ?>" />
        <input type="hidden" name="testcookie" value="1" />
    </p>
</form>
<?php endif; ?>

<?php
    login_footer($type);
break;

endswitch;
?>
