<?php

if ( $_POST ) {
    if ( !current_user_can('manage_options') )
        die( __('Cheatin&#8217; huh?') );

    check_admin_referer('tml-settings');
    $chk_uninstall = isset($_POST['chk_uninstall']) ? true : false;
    $theme_profile = isset($_POST['theme_profile']) ? true : false;
    $this->SetOption('chk_uninstall', $chk_uninstall);
    $this->SetOption('subscr_login_redirect', stripslashes($_POST['subscr_login_redirect']));
    $this->SetOption('contrb_login_redirect', stripslashes($_POST['contrb_login_redirect']));
    $this->SetOption('author_login_redirect', stripslashes($_POST['author_login_redirect']));
    $this->SetOption('editor_login_redirect', stripslashes($_POST['editor_login_redirect']));
    $this->SetOption('admin_login_redirect', stripslashes($_POST['admin_login_redirect']));
    $this->SetOption('logout_redirect', stripslashes($_POST['logout_redirect']));
    $this->SetOption('theme_profile', $theme_profile);
    $this->SetOption('login_title', stripslashes($_POST['login_title']));
    $this->SetOption('login_text', stripslashes($_POST['login_text']));
    $this->SetOption('register_title', stripslashes($_POST['register_title']));
    $this->SetOption('register_text', stripslashes($_POST['register_text']));
    $this->SetOption('register_msg', stripslashes($_POST['register_msg']));
    $this->SetOption('password_title', stripslashes($_POST['password_title']));
    $this->SetOption('password_text', stripslashes($_POST['password_text']));
    $this->SetOption('profile_title', stripslashes($_POST['profile_title']));
    $this->SetOption('profile_text', stripslashes($_POST['profile_text']));
    $this->SaveOptions();

    if ($chk_uninstall)
        $success = "To complete uninstall, deactivate this plugin. If you do not wish to uninstall, please uncheck the 'Complete Uninstall' checkbox.";
    else
        $success = "Settings saved.";
}
?>

<div class="updated" style="background:aliceblue; border:1px solid lightblue">
    <p><?php _e('If you like this plugin, please help keep it up to date by <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3836253">donating through PayPal</a>!'); ?></p>
</div>

<div class="wrap">
<?php if ( isset($success) && strlen($success) > 0 ) { ?>
    <div id="message" class="updated fade">
        <p><strong><?php _e($success); ?></strong></p>
    </div>
<?php } ?>
    <div id="icon-options-general" class="icon32"><br /></div>
    <h2><?php _e('Theme My Login Settings'); ?></h2>

    <form action="" method="post" id="tml-settings">
    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('tml-settings'); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="chk_uninstall"><?php _e('Complete Uninstall?'); ?></label></th>
            <td>
                <input name="chk_uninstall" type="checkbox" id="chk_uninstall" value="1" <?php if ($this->GetOption('chk_uninstall')) { echo 'checked="checked"'; } ?> />
                <span class="setting-description"><?php _e('Check here and then disable plugin to completely uninstall.'); ?></span>
            </td>
        </tr>
    </table>
    <h3><?php _e('Redirection Settings'); ?></h3>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="subscr_login_redirect"><?php _e('Subscriber Login Redirect'); ?></label></th>
            <td>
                <input name="subscr_login_redirect" type="text" id="subscr_login_redirect" value="<?php echo( htmlspecialchars ( $this->GetOption('subscr_login_redirect') ) ); ?>" class="regular-text" size="100" />
                <span class="setting-description"><?php _e('Must be an absolute URL.'); ?></span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="contrb_login_redirect"><?php _e('Contributor Login Redirect'); ?></label></th>
            <td>
                <input name="contrb_login_redirect" type="text" id="contrb_login_redirect" value="<?php echo( htmlspecialchars ( $this->GetOption('contrb_login_redirect') ) ); ?>" class="regular-text" size="75" />
                <span class="setting-description"><?php _e('Must be an absolute URL.'); ?></span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="login_redirect"><?php _e('Author Login Redirect'); ?></label></th>
            <td>
                <input name="author_login_redirect" type="text" id="author_login_redirect" value="<?php echo( htmlspecialchars ( $this->GetOption('author_login_redirect') ) ); ?>" class="regular-text" size="75" />
                <span class="setting-description"><?php _e('Must be an absolute URL.'); ?></span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="editor_login_redirect"><?php _e('Editor Login Redirect'); ?></label></th>
            <td>
                <input name="editor_login_redirect" type="text" id="editor_login_redirect" value="<?php echo( htmlspecialchars ( $this->GetOption('editor_login_redirect') ) ); ?>" class="regular-text" size="75" />
                <span class="setting-description"><?php _e('Must be an absolute URL.'); ?></span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="admin_login_redirect"><?php _e('Administrator Login Redirect'); ?></label></th>
            <td>
                <input name="admin_login_redirect" type="text" id="admin_login_redirect" value="<?php echo( htmlspecialchars ( $this->GetOption('admin_login_redirect') ) ); ?>" class="regular-text" size="75" />
                <span class="setting-description"><?php _e('Must be an absolute URL.'); ?></span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="logout_redirect"><?php _e('Redirect on Logout'); ?></label></th>
            <td>
                <input name="logout_redirect" type="text" id="logout_redirect" value="<?php echo( htmlspecialchars ( $this->GetOption('logout_redirect') ) ); ?>" class="regular-text" size="75" />
                <span class="setting-description"><?php _e('Must be an absolute URL.'); ?></span>
            </td>
        </tr>
    </table>

    <h3><?php _e('Template Settings'); ?></h3>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="theme_profile"><?php _e('Theme Subscriber\'s Profile?'); ?></label></th>
            <td>
                <input name="theme_profile" type="checkbox" id="theme_profile" value="1" <?php if ($this->GetOption('theme_profile')) { echo 'checked="checked"'; } ?> />
                <span class="setting-description"><?php _e('Check here to theme subscriber\'s profile. This is known to cause issues with plugins that have a user administration menu.'); ?></span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="register_title"><?php _e('Register Page Title'); ?></label></th>
            <td>
                <input name="register_title" type="text" id="register_title" value="<?php echo( htmlspecialchars ( $this->GetOption('register_title') ) ); ?>" class="regular-text" />
                <span class="setting-description">You can use %blogname% for your blog name.'</span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="register_text"><?php _e('Register Text'); ?></label></th>
            <td>
                <input name="register_text" type="text" id="register_text" value="<?php echo( htmlspecialchars ( $this->GetOption('register_text') ) ); ?>" class="regular-text" />
                <span class="setting-description"><?php _e('This will appear above the registration form.'); ?></span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="register_msg"><?php _e('Register Message'); ?></label></th>
            <td>
                <input name="register_msg" type="text" id="register_msg" value="<?php echo( htmlspecialchars ( $this->GetOption('register_msg') ) ); ?>" class="regular-text" />
                <span class="setting-description"><?php _e('This will appear below the registration form.'); ?></span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="login_title"><?php _e('Login Page Title'); ?></label></th>
            <td>
                <input name="login_title" type="text" id="login_title" value="<?php echo( htmlspecialchars ( $this->GetOption('login_title') ) ); ?>" class="regular-text" />
                <span class="setting-description">You can use %blogname% for your blog name.'</span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="login_text"><?php _e('Login Text'); ?></label></th>
            <td>
                <input name="login_text" type="text" id="login_text" value="<?php echo( htmlspecialchars ( $this->GetOption('login_text') ) ); ?>" class="regular-text" />
                <span class="setting-description"><?php _e('This will appear above the login form.'); ?></span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="password_title"><?php _e('Lost Password Page Title'); ?></label></th>
            <td>
                <input name="password_title" type="text" id="password_title" value="<?php echo( htmlspecialchars ( $this->GetOption('password_title') ) ); ?>" class="regular-text" />
                <span class="setting-description">You can use %blogname% for your blog name.'</span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="password_text"><?php _e('Lost Password Text'); ?></label></th>
            <td>
                <input name="password_text" type="text" id="password_text" value="<?php echo( htmlspecialchars ( $this->GetOption('password_text') ) ); ?>" class="regular-text" />
                <span class="setting-description"><?php _e('This will appear above the lost password form.'); ?></span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="profile_title"><?php _e('Profile Page Title'); ?></label></th>
            <td>
                <input name="profile_title" type="text" id="profile_title" value="<?php echo( htmlspecialchars ( $this->GetOption('profile_title') ) ); ?>" class="regular-text" />
                <span class="setting-description">You can use %blogname% for your blog name.'</span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="profile_text"><?php _e('Profile Text'); ?></label></th>
            <td>
                <input name="profile_text" type="text" id="profile_text" value="<?php echo( htmlspecialchars ( $this->GetOption('profile_text') ) ); ?>" class="regular-text" />
                <span class="setting-description"><?php _e('This will appear above the users profile.'); ?></span>
            </td>
        </tr>
    </table>
    <p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
    </form>
</div>
