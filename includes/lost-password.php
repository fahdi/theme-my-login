<?php

if ( isset($_GET['error']) && 'invalidkey' == $_GET['error'] ) $this->errors->add('invalidkey', __('Sorry, that key does not appear to be valid.'));

do_action('lost_password');
$this->DoHeader('<p class="message">' . __('Please enter your username or e-mail address. You will receive a new password via e-mail.') . '</p>', $this->errors);

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
