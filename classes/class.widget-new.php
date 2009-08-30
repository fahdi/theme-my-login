<?php

if ( !class_exists('ThemeMyLoginWidget') ) :
class ThemeMyLoginWidget extends WP_Widget {

    function ThemeMyLoginWidget(){
        $widget_ops = array('classname' => 'widget_theme_my_login', 'description' => __('A login form for your blog.', 'theme-my-login') );
        $this->WP_Widget('theme-my-login', __('Theme My Login', 'theme-my-login'), $widget_ops);
    }

    function widget($args, $instance){
        global $ThemeMyLogin;
        
        $new_args['widget'] = array_merge($args, $instance);

        echo $ThemeMyLogin->ThemeMyLoginShortcode($new_args);
    }

    function update($new_instance, $old_instance){
        
        $instance = $old_instance;
        $instance['default_action'] = $new_instance['default_action'];
        $instance['show_logged'] = (empty($new_instance['show_logged'])) ? false : true;
        $instance['show_title'] = (empty($new_instance['show_title'])) ? false : true;
        $instance['show_links'] = (empty($new_instance['show_links'])) ? false: true;
        $instance['show_gravatar'] = (empty($new_instance['show_gravatar'])) ? false : true;
        $instance['gravatar_size'] = absint($new_instance['gravatar_size']);
        $instance['registration'] = (empty($new_instance['registration'])) ? false : true;
        $instance['lostpassword'] = (empty($new_instance['lostpassword'])) ? false : true;

        return $instance;
    }

    function form($instance){
        global $wp_roles;
        $user_roles = $wp_roles->get_names();

        //Defaults
        $defaults['default_action'] = 'login';
        $defaults['show_logged'] = 1;
        $defaults['show_title'] = 1;
        $defaults['show_links'] = 1;
        $defaults['show_gravatar'] = 1;
        $defaults['gravatar_size'] = 50;
        $defaults['registration'] = 1;
        $defaults['lostpassword'] = 1;

        $instance = wp_parse_args( (array) $instance, (array) $defaults );
        $actions = array('login' => 'Login', 'register' => 'Register', 'lostpassword' => 'Lost Password');
        echo '<p>Default Action<br /><select name="' . $this->get_field_name('default_action') . '" id="' . $this->get_field_id('default_action') . '">';
        foreach ($actions as $action => $title) {
            $is_selected = ($instance['default_action'] == $action) ? ' selected="selected"' : '';
            echo '<option value="' . $action . '"' . $is_selected . '>' . $title . '</option>';
        }
        echo '</select></p>' . "\n";
        $is_checked = (empty($instance['show_logged'])) ? '' : 'checked="checked" ';
        echo '<p><input name="' . $this->get_field_name('show_logged') . '" type="checkbox" id="' . $this->get_field_id('show_logged') . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id('show_logged') . '">' . __('Show When Logged In', 'theme-my-login') . '</label></p>' . "\n";
        $is_checked = (empty($instance['show_title'])) ? '' : 'checked="checked" ';
        echo '<p><input name="' . $this->get_field_name('show_title') . '" type="checkbox" id="' . $this->get_field_id('show_title') . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id('show_title') . '">' . __('Show Title', 'theme-my-login') . '</label></p>' . "\n";
        $is_checked = (empty($instance['show_links'])) ? '' : 'checked="checked" ';
        echo '<p><input name="' . $this->get_field_name('show_links') . '" type="checkbox" id="' . $this->get_field_id('show_links') . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id('show_links') . '">' . __('Show Action Links', 'theme-my-login') . '</label></p>' . "\n";
        $is_checked = (empty($instance['show_gravatar'])) ? '' : 'checked="checked" ';
        echo '<p><input name="' . $this->get_field_name('show_gravatar') . '" type="checkbox" id="' . $this->get_field_id('show_gravatar') . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id('show_gravatar') . '">' . __('Show Gravatar', 'theme-my-login') . '</label></p>' . "\n";
        echo '<p>' . __('Gravatar Size', 'theme-my-login') . ': <input name="' . $this->get_field_name('gravatar_size') . '" type="text" id="' . $this->get_field_id('gravatar_size') . '" value="' . $instance['gravatar_size'] . '" size="3" /> <label for="' . $this->get_field_id('gravatar_size') . '"></label></p>' . "\n";
        $is_checked = (empty($instance['registration'])) ? '' : 'checked="checked" ';
        echo '<p><input name="' . $this->get_field_name('registration') . '" type="checkbox" id="' . $this->get_field_id('registration') . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id('registration') . '">' . __('Allow Registration', 'theme-my-login') . '</label></p>' . "\n";
        $is_checked = (empty($instance['lostpassword'])) ? '' : 'checked="checked" ';
        echo '<p><input name="' . $this->get_field_name('lostpassword') . '" type="checkbox" id="' . $this->get_field_id('lostpassword') . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id('lostpassword') . '">' . __('Allow Password Recovery', 'theme-my-login') . '</label></p>' . "\n";
    }

}// END class
endif;

if ( !function_exists('ThemeMyLoginWidgetInit') ) :
function ThemeMyLoginWidgetInit() {
    register_widget('ThemeMyLoginWidget');
}
endif;
add_action('widgets_init', 'ThemeMyLoginWidgetInit');

?>
