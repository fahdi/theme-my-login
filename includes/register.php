<?php

if ( $_POST ) {
    $user_login = $_POST['user_login'];
    $user_email = $_POST['user_email'];
}

$this->DoHeader('', $this->errors);
?>

    <form name="registerform" id="registerform" action="<?php echo get_permalink($this->GetOption('page_id')); ?>?action=register" method="post">
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
