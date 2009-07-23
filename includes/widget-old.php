<?php

class ThemeMyLoginWidget {

    function ThemeMyLoginWidget() {
        add_action('plugins_loaded', array(&$this, 'PluginsLoaded'));
    }

    function PluginsLoaded() {
        wp_register_sidebar_widget('theme-my-login', 'Theme My Login', array(&$this, 'Widget'), array('description' => 'A login form for your blog'));
        wp_register_widget_control('theme-my-login', 'Theme My Login', array(&$this, 'WidgetControl'));
    }

    function Widget($args) {
        global $ThemeMyLogin, $wp_version, $user_ID, $current_user, $login_errors;

        get_currentuserinfo();

        extract ($args);

        if (!is_page($ThemeMyLogin->GetOption('page_id'))) {
            if ($user_ID != '' && $ThemeMyLogin->GetOption('widget_show_logged_in')) {
                $user_role = reset($current_user->roles);
                $dashboard_link = $ThemeMyLogin->GetOption('widget_dashboard_link');
                $dashboard_url = $ThemeMyLogin->GetOption('widget_dashboard_url');
                $profile_link = $ThemeMyLogin->GetOption('widget_profile_link');
                $profile_url = $ThemeMyLogin->GetOption('widget_profile_url');
                $user_dashboard_url = (empty($dashboard_url[$user_role])) ? site_url('wp-admin/', 'admin') : $dashboard_url[$user_role];
                $user_profile_url = (empty($profile_url[$user_role])) ? site_url('wp-admin/profile.php', 'admin') : $profile_url[$user_role];
                echo $before_widget . $before_title . __('Welcome', 'theme-my-login') . ', ' . $current_user->display_name . $after_title . "\n";
                if ($ThemeMyLogin->GetOption('widget_show_gravatar') == true) :
                    echo '<div class="theme-my-login-avatar">' . get_avatar( $user_ID, $size = $ThemeMyLogin->GetOption('widget_gravatar_size') ) . '</div>' . "\n";
                endif;
                do_action('theme_my_login_avatar', $current_user);
                echo '<ul class="theme-my-login-links">' . "\n";
                if ($dashboard_link[$user_role] == true) :
                    echo '<li><a href="' . $user_dashboard_url . '">' . __('Dashboard') . '</a></li>' . "\n";
                endif;
                if ($profile_link[$user_role] == true) :
                    echo '<li><a href="' . $user_profile_url . '">' . __('Profile') . '</a></li>' . "\n";
                endif;
                do_action('theme_my_login_links', $user_role);
                $redirect = wp_guess_url();
                if (version_compare($wp_version, '2.7', '>='))
                    echo '<li><a href="' . wp_logout_url($redirect) . '">' . __('Log Out') . '</a></li>' . "\n";
                else
                    echo '<li><a href="' . site_url('wp-login.php?action=logout&redirect_to='.$redirect, 'login') . '">' . __('Log Out') . '</a></li>' . "\n";
                echo '</ul>' . "\n";
            } elseif (empty($user_ID)) {
                switch ($_GET['action']) {
                    case 'register':
                        $title = $ThemeMyLogin->GetOption('register_title');
                    break;
                    case 'lostpassword':
                    case 'retrievepassword':
                    case 'resetpass':
                    case 'rp':
                        $title = $ThemeMyLogin->GetOption('password_title');
                    break;
                    case 'login':
                    default:
                        $title = $ThemeMyLogin->GetOption('login_title');
                    break;
                }
                echo $before_widget . $before_title . $title . $after_title . "\n";
                $type = 'widget';
                require (WP_PLUGIN_DIR . '/theme-my-login/includes/wp-login-forms.php');
            }
            echo $after_widget . "\n";
        }
    }

    function WidgetControl() {
        global $ThemeMyLogin, $wp_roles;
        $user_roles = $wp_roles->get_names();

        if ( $_POST['tml_submit'] ) {

            foreach ($user_roles as $role => $value) {
                $dashboard_link[$role] = isset($_POST['tml_dashboard_link'][$role]) ? true : false;
                $profile_link[$role] = isset($_POST['tml_profile_link'][$role]) ? true : false;
            }
            $ThemeMyLogin->SetOption('widget_show_logged_in', $_POST['tml_show_widget']);
            $ThemeMyLogin->SetOption('widget_show_gravatar', $_POST['tml_show_gravatar']);
            $ThemeMyLogin->SetOption('widget_gravatar_size', absint($_POST['tml_gravatar_size']));
            $ThemeMyLogin->SetOption('widget_dashboard_link', $dashboard_link);
            $ThemeMyLogin->SetOption('widget_profile_link', $profile_link);
            $ThemeMyLogin->SaveOptions();

        }

        $show_widget = $ThemeMyLogin->GetOption('widget_show_logged_in');
        $show_gravatar = $ThemeMyLogin->GetOption('widget_show_gravatar');
        $dashboard_link = $ThemeMyLogin->GetOption('widget_dashboard_link');
        $profile_link = $ThemeMyLogin->GetOption('widget_profile_link');

        ?>

<p>
    <label for="tml_show_widget">Show Widget When Logged In:</label>
    <select name="tml_show_widget" id="tml_show_widget"><option value="1" <?php if ($show_widget == true) echo 'selected="selected"'; ?>>Yes</option><option value="0" <?php if ($show_widget == false) echo 'selected="selected"'; ?>>No</option></select>
</p>

<p>
    <label for="tml_show_gravatar">Show Gravatar:</label>
    <select name="tml_show_gravatar" id="tml_show_gravatar"><option value="1" <?php if ($show_gravatar == true) echo 'selected="selected"'; ?>>Yes</option><option value="0" <?php if ($show_gravatar == false) echo 'selected="selected"'; ?>>No</option></select>
</p>

<p>
    <label for="tml_gravatar_size">Gravatar Size:</label>
    <input name="tml_gravatar_size" type="text" id="tml_gravatar_size" value="<?php echo absint($ThemeMyLogin->GetOption('widget_gravatar_size')); ?>" size="2" />
</p>

<p>
    <label for="tml_dashboard_link">Dashboard Link:</label><br />
    <?php foreach ($user_roles as $role => $value) : ?>
    <input name="tml_dashboard_link[<?php echo $role; ?>]" type="checkbox" id="tml_dashboard_link[<?php echo $role; ?>]" value="1"<?php if ($dashboard_link[$role] == true) { echo 'checked="checked"'; } ?> /> <?php echo ucwords($role); ?><br />
    <?php endforeach; ?>
</p>

<p>
    <label for="tml_profile_link">Profile Link:</label><br />
    <?php foreach ($user_roles as $role => $value) : ?>
    <input name="tml_profile_link[<?php echo $role; ?>]" type="checkbox" id="tml_profile_link[<?php echo $role; ?>]" value="1"<?php if ($profile_link[$role] == true) { echo 'checked="checked"'; } ?> /> <?php echo ucwords($role); ?><br />
    <?php endforeach; ?>
</p>

<p>
    <input type="hidden" id="tml_submit" name="tml_submit" value="1" />
</p>

        <?php
    }
} // End class

if (class_exists('ThemeMyLoginWidget')) {
    $ThemeMyLoginWidget = new ThemeMyLoginWidget();
}

?>
