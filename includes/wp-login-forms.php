<?php

require (WP_PLUGIN_DIR . '/theme-my-login/includes/wp-login-functions.php');

$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) :

case 'lostpassword' :
case 'retrievepassword' :
    do_action('lost_password');
    login_header('<p class="message">' . __('Please enter your username or e-mail address. You will receive a new password via e-mail.') . '</p>', $this->errors);

    $user_login = isset($_POST['user_login']) ? stripslashes($_POST['user_login']) : '';
?>

<form name="lostpasswordform" id="lostpasswordform" action="<?php echo ssl_or_not($this->QueryURL().'action=lostpassword') ?>" method="post">
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
    <li><a href="<?php echo site_url('wp-login.php?action=register', 'login') ?>"><?php _e('Register') ?></a></li>
    <?php endif; ?>
</ul>

</div>

<?php
break;

case 'register' :
    $user_login = isset($_POST['user_login']) ? $_POST['user_login'] : '';
    $user_email = isset($_POST['user_email']) ? $_POST['user_email'] : '';
    login_header('', $this->errors);
?>

<form name="registerform" id="registerform" action="<?php echo ssl_or_not($this->QueryURL().'action=register') ?>" method="post">
    <p>
        <label><?php _e('Username') ?><br />
        <input type="text" name="user_login" id="user_login" class="input" value="<?php echo attribute_escape(stripslashes($user_login)); ?>" size="20" tabindex="10" /></label>
    </p>
    <p>
        <label><?php _e('E-mail') ?><br />
        <input type="text" name="user_email" id="user_email" class="input" value="<?php echo attribute_escape(stripslashes($user_email)); ?>" size="25" tabindex="20" /></label>
    </p>
<?php do_action('register_form'); ?>
    <p id="reg_passmail"><?php _e($this->GetOption('register_msg')) ?></p>
    <p class="submit"><input type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Register'); ?>" tabindex="100" /></p>
</form>

<ul class="nav">
    <li><a href="<?php echo site_url('wp-login.php', 'login') ?>"><?php _e('Log in') ?></a></li>
    <li><a href="<?php echo site_url('wp-login.php?action=lostpassword', 'login') ?>" title="<?php _e('Password Lost and Found') ?>"><?php _e('Lost your password?') ?></a></li>
</ul>

</div>
<?php
break;

case 'login' :
default :
    if ( isset( $_REQUEST['redirect_to'] ) ) {
        $redirect_to = $_REQUEST['redirect_to'];
        // Redirect to https if user wants ssl
        if ( $secure_cookie && false !== strpos($redirect_to, 'wp-admin') )
            $redirect_to = preg_replace('|^http://|', 'https://', $redirect_to);
    } else {
        $redirect_to = $this->GetOption('login_redirect');
    }

    // Clear errors if loggedout is set.
    if ( !empty($_GET['loggedout']) )
        $errors = new WP_Error();

    // If cookies are disabled we can't log in even with a valid user+pass
    if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
        $this->errors->add('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress."));

    // Some parts of this script use the main login form to display a message
    if        ( isset($_GET['loggedout']) && TRUE == $_GET['loggedout'] )            $this->errors->add('loggedout', __('You are now logged out.'), 'message');
    elseif    ( isset($_GET['registration']) && 'disabled' == $_GET['registration'] )    $this->errors->add('registerdisabled', __('User registration is currently not allowed.'));
    elseif    ( isset($_GET['checkemail']) && 'confirm' == $_GET['checkemail'] )    $this->errors->add('confirm', __('Check your e-mail for the confirmation link.'), 'message');
    elseif    ( isset($_GET['checkemail']) && 'newpass' == $_GET['checkemail'] )    $this->errors->add('newpass', __('Check your e-mail for your new password.'), 'message');
    elseif    ( isset($_GET['checkemail']) && 'registered' == $_GET['checkemail'] )    $this->errors->add('registered', __('Registration complete. Please check your e-mail.'), 'message');

    login_header('', $this->errors);
    
    if ( isset($_POST['log']) )
        $user_login = ( 'incorrect_password' == $this->errors->get_error_code() || 'empty_password' == $this->errors->get_error_code() ) ? attribute_escape(stripslashes($_POST['log'])) : '';
?>
<?php if ( !isset($_GET['checkemail']) || !in_array( $_GET['checkemail'], array('confirm', 'newpass') ) ) : ?>
<form name="loginform" id="loginform" action="<?php echo ssl_or_not($this->QueryURL().'action=login') ?>" method="post">
    <p>
        <label><?php _e('Username') ?><br />
        <input type="text" name="log" id="user_login" class="input" value="<?php echo isset($user_login) ? $user_login : ''; ?>" size="20" tabindex="10" /></label>
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
<?php
break;

endswitch;
?>
