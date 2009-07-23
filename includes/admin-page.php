<?php

global $wp_roles;
$user_roles = $wp_roles->get_names();

if ( $_POST ) {

    check_admin_referer('theme-my-login');

    $this->SetOption('uninstall', $_POST['uninstall']);
    $this->SetOption('show_page', $_POST['show_page']);
    $this->SetOption('login_title', stripslashes($_POST['login_title']));
    $this->SetOption('register_title', stripslashes($_POST['register_title']));
    $this->SetOption('register_msg', stripslashes($_POST['register_msg']));
    $this->SetOption('register_complete', stripslashes($_POST['register_complete']));
    $this->SetOption('password_title', stripslashes($_POST['password_title']));
    $this->SetOption('password_msg', stripslashes($_POST['password_msg']));
    $this->SetOption('widget_allow_register', $_POST['widget_allow_register']);
    $this->SetOption('widget_allow_password', $_POST['widget_allow_password']);
    foreach ($user_roles as $role => $value) {
        $dashboard_url[$role] = $_POST['widget_dashboard_url'][$role];
        $profile_url[$role] = $_POST['widget_profile_url'][$role];
    }
    $this->SetOption('widget_dashboard_url', $dashboard_url);
    $this->SetOption('widget_profile_url', $profile_url);
    $this->SaveOptions();

    if (isset($_POST['uninstall']))
        $success = __('To complete uninstall, deactivate this plugin. If you do not wish to uninstall, please uncheck the "Complete Uninstall" checkbox.', 'theme-my-login');
    else
        $success =__('Settings saved.', 'theme-my-login');
}

$dashboard_url = $this->GetOption('widget_dashboard_url');
$profile_url = $this->GetOption('widget_profile_url');

?>

<div class="updated" style="background:aliceblue; border:1px solid lightblue">
    <p><?php _e('If you like this plugin, please help keep it up to date by <a href="http://www.jfarthing.com/donate">donating through PayPal</a>!', 'theme-my-login'); ?></p>
</div>

<div class="wrap">
<?php if ( isset($success) && strlen($success) > 0 ) { ?>
    <div id="message" class="updated fade">
        <p><strong><?php echo $success; ?></strong></p>
    </div>
<?php } ?>
    <div id="icon-options-general" class="icon32"><br /></div>
    <h2><?php _e('Theme My Login Settings', 'theme-my-login'); ?></h2>

    <form action="" method="post" id="tml-settings">
    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('theme-my-login'); ?>

    <h3><?php _e('General Settings', 'theme-my-login'); ?></h3>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e('Plugin', 'theme-my-login'); ?></th>
            <td>
                <input name="uninstall" type="checkbox" id="uninstall" value="1" <?php if ($this->GetOption('uninstall')) { echo 'checked="checked"'; } ?> />
                <label for="uninstall"><?php _e('Uninstall', 'theme-my-login'); ?></label>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Page List', 'theme-my-login'); ?></th>
            <td>
                <input name="show_page" type="checkbox" id="show_page" value="1" <?php if ($this->GetOption('show_page')) { echo 'checked="checked"'; } ?> />
                <label for="show_page"><?php _e('Show Login Page', 'theme-my-login'); ?></label>
            </td>
        </tr>
    </table>

    <h3><?php _e('Template Settings', 'theme-my-login'); ?></h3>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="register_title"><?php _e('Register Title', 'theme-my-login'); ?></label></th>
            <td>
                <input name="register_title" type="text" id="register_title" value="<?php echo( htmlspecialchars ( $this->GetOption('register_title') ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="register_msg"><?php _e('Register Message', 'theme-my-login'); ?></label></th>
            <td>
                <input name="register_msg" type="text" id="register_msg" value="<?php echo( htmlspecialchars ( $this->GetOption('register_msg') ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="register_complete"><?php _e('Registration Complete Message', 'theme-my-login'); ?></label></th>
            <td>
                <input name="register_complete" type="text" id="register_complete" value="<?php echo( htmlspecialchars ( $this->GetOption('register_complete') ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="login_title"><?php _e('Login Title', 'theme-my-login'); ?></label></th>
            <td>
                <input name="login_title" type="text" id="login_title" value="<?php echo( htmlspecialchars ( $this->GetOption('login_title') ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="password_title"><?php _e('Lost Password Title', 'theme-my-login'); ?></label></th>
            <td>
                <input name="password_title" type="text" id="password_title" value="<?php echo( htmlspecialchars ( $this->GetOption('password_title') ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="password_msg"><?php _e('Lost Password Message', 'theme-my-login'); ?></label></th>
            <td>
                <input name="password_msg" type="text" id="password_msg" value="<?php echo( htmlspecialchars ( $this->GetOption('password_msg') ) ); ?>" class="regular-text" />
            </td>
        </tr>
    </table>
    
    <h3><?php _e('Widget Settings', 'theme-my-login'); ?></h3>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e('Registration', 'theme-my-login'); ?></th>
            <td>
                <input name="widget_allow_register" type="checkbox" id="widget_allow_register" value="1" <?php if ($this->GetOption('widget_allow_register')) { echo 'checked="checked"'; } ?> />
                <label for="widget_allow_register"><?php _e('Allow Registration in Widget', 'theme-my-login'); ?></label>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Lost Password', 'theme-my-login'); ?></th>
            <td>
                <input name="widget_allow_password" type="checkbox" id="widget_allow_password" value="1" <?php if ($this->GetOption('widget_allow_password')) { echo 'checked="checked"'; } ?> />
                <label for="widget_allow_password"><?php _e('Allow Password Recovery in Widget', 'theme-my-login'); ?></label>
            </td>
        </tr>
    </table>
    <h4><?php _e('Dashboard URL'); ?></h4>
    <p class="setting-description">Leave blank for default</p>
    <table class="form-table">
        <?php foreach ($user_roles as $role => $value) : ?>
        <tr valign="top">
            <th scope="row"><?php echo ucwords($role); ?></th>
            <td>
                <input name="widget_dashboard_url[<?php echo $role; ?>]" type="text" id="widget_dashboard_url[<?php echo $role; ?>]" value="<?php echo $dashboard_url[$role]; ?>" class="regular-text" />
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <h4><?php _e('Profile URL'); ?></h4>
    <p class="setting-description">Leave blank for default</p>
    <table class="form-table">
        <?php foreach ($user_roles as $role => $value) : ?>
        <tr valign="top">
            <th scope="row"><?php echo ucwords($role); ?></th>
            <td>
                <input name="widget_profile_url[<?php echo $role; ?>]" type="text" id="widget_profile_url[<?php echo $role; ?>]" value="<?php echo $profile_url[$role]; ?>" class="regular-text" />
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
    </form>
</div>
