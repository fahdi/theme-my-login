<?php

class ThemeMyLoginWidget extends WP_Widget {

    function ThemeMyLoginWidget(){
        $widget_ops = array('classname' => 'widget_theme_my_login', 'description' => __('A login form for your blog.', 'theme-my-login') );
        $this->WP_Widget('theme-my-login', __('Theme My Login', 'theme-my-login'), $widget_ops);
    }

    function widget($args, $instance){
        global $ThemeMyLogin, $wp_version, $user_ID, $current_user, $login_errors;

        get_currentuserinfo();

        extract ($args);

        if (!is_page($ThemeMyLogin->GetOption('page_id'))) {
            if ($user_ID != '' && $instance['show_logged_in']) {
                $user_role = reset($current_user->roles);
                $dashboard_url = $ThemeMyLogin->GetOption('widget_dashboard_url');
                $profile_url = $ThemeMyLogin->GetOption('widget_profile_url');
                $user_dashboard_url = (empty($dashboard_url[$user_role])) ? site_url('wp-admin/', 'admin') : $dashboard_url[$user_role];
                $user_profile_url = (empty($profile_url[$user_role])) ? site_url('wp-admin/profile.php', 'admin') : $profile_url[$user_role];
                echo $before_widget . $before_title . __('Welcome', 'theme-my-login') . ', ' . $current_user->display_name . $after_title . "\n";
                if ($instance['show_gravatar']) :
                    echo '<div class="theme-my-login-avatar">' . get_avatar( $user_ID, $size = $instance['gravatar_size'] ) . '</div>' . "\n";
                endif;
                do_action('theme_my_login_avatar', $current_user);
                echo '<ul class="theme-my-login-links">' . "\n";
                if ($instance['dashboard_link_' . $user_role]) :
                    echo '<li><a href="' . $user_dashboard_url . '">' . __('Dashboard') . '</a></li>' . "\n";
                endif;
                if ($instance['profile_link_' . $user_role]) :
                    echo '<li><a href="' . $user_profile_url . '">' . __('Profile') . '</a></li>' . "\n";
                endif;
                do_action('theme_my_login_links', $user_role);
                $redirect = wp_guess_url();
                if (version_compare($wp_version, '2.7', '>='))
                    echo '<li><a href="' . wp_logout_url($redirect) . '">' . __('Log Out') . '</a></li>' . "\n";
                else
                    echo '<li><a href="' . site_url('wp-login.php?action=logout&redirect_to='.$redirect, 'login') . '">' . __('Log Out') . '</a></li>' . "\n";
                echo '</ul>' . "\n";
                echo $after_widget . "\n";
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
                echo $after_widget . "\n";
            }
        }
    }

    function update($new_instance, $old_instance){
        global $wp_roles;
        $user_roles = $wp_roles->get_names();
        
        $instance = $old_instance;
        $instance['show_logged_in'] = (empty($new_instance['show_logged_in'])) ? false : true;
        $instance['show_gravatar'] = (empty($new_instance['show_gravatar'])) ? false : true;
        $instance['gravatar_size'] = absint($new_instance['gravatar_size']);
        foreach ($user_roles as $role => $value) {
            $instance['dashboard_link_' . $role] = (empty($new_instance['dashboard_link_' . $role])) ? false : true;
            //$instance['dashboard_url_' . $role] = trim($new_instance['dashboard_url_' . $role]);
            $instance['profile_link_' . $role] = (empty($new_instance['profile_link_' . $role])) ? false : true;
            //$instance['profile_url_' . $role] = trim($new_instance['profile_url_' . $role]);
        }

        return $instance;
    }

    function form($instance){
        global $wp_roles;
        $user_roles = $wp_roles->get_names();

        //Defaults
        $defaults['show_logged_in'] = 1;
        $defaults['show_gravatar'] = 1;
        $defaults['gravatar_size'] = 50;
        foreach ($user_roles as $role => $value) {
            $defaults['dashboard_link_' . $role] = 1;
            $defaults['dashboard_url_' . $role] = 'wp-admin/';
            $defaults['profile_link_' . $role] = 1;
            $defaults['profile_url_' . $role] = 'wp-admin/profile.php';
        }
        $instance = wp_parse_args( (array) $instance, (array) $defaults );

        $is_checked = (empty($instance['show_logged_in'])) ? '' : 'checked="checked" ';
        echo '<p><input name="' . $this->get_field_name('show_logged_in') . '" type="checkbox" id="' . $this->get_field_id('show_logged_in') . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id('show_logged_in') . '">' . __('Show When Logged In', 'theme-my-login') . '</label></p>' . "\n";
        $is_checked = (empty($instance['show_gravatar'])) ? '' : 'checked="checked" ';
        echo '<p><input name="' . $this->get_field_name('show_gravatar') . '" type="checkbox" id="' . $this->get_field_id('show_gravatar') . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id('show_gravatar') . '">' . __('Show Gravatar', 'theme-my-login') . '</label></p>' . "\n";
        echo '<p>' . __('Gravatar Size', 'theme-my-login') . ': <input name="' . $this->get_field_name('gravatar_size') . '" type="text" id="' . $this->get_field_id('gravatar_size') . '" value="' . $instance['gravatar_size'] . '" size="3" /> <label for="' . $this->get_field_id('gravatar_size') . '"></label></p>' . "\n";
        echo '<p>' . __('Dashboard Link', 'theme-my-login') . ':<br />' . "\n";
        foreach ($user_roles as $role => $value) {
            $is_checked = (empty($instance['dashboard_link_' . $role])) ? '' : 'checked="checked" ';
            echo '<input name="' . $this->get_field_name('dashboard_link_' . $role) . '" type="checkbox" id="' . $this->get_field_id('dashboard_link_' . $role) . '" value="1" ' . $is_checked . '/> ' . ucwords($role) . '<br />' . "\n";
            //echo '<input name="' . $this->get_field_name('dashboard_url_' . $role) . '" type="text" id="' . $this->get_field_id('dashboard_url_' . $role) . '" value="' . $instance['dashboard_url_' . $role] . '" class="widefat" /><br />' . "\n";
        }
        echo '</p>';
        echo '<p>' . __('Profile Link', 'theme-my-login') . ':<br />' . "\n";
        foreach ($user_roles as $role => $value) {
            $is_checked = (empty($instance['profile_link_' . $role])) ? '' : 'checked="checked" ';
            echo '<input name="' . $this->get_field_name('profile_link_' . $role) . '" type="checkbox" id="' . $this->get_field_id('profile_link_' . $role) . '" value="1" ' . $is_checked . '/> ' . ucwords($role) . '<br />' . "\n";
            //echo '<input name="' . $this->get_field_name('profile_url_' . $role) . '" type="text" id="' . $this->get_field_id('profile_url_' . $role) . '" value="' . $instance['profile_url_' . $role] . '" class="widefat" /><br />' . "\n";
        }
        echo '</p>';
    }

}// END class

function ThemeMyLoginWidgetInit() {
    register_widget('ThemeMyLoginWidget');
}
add_action('widgets_init', 'ThemeMyLoginWidgetInit');

?>
