<?php

if ( !function_exists('ThemeMyLoginWidget') ) :
class ThemeMyLoginWidget {

    function ThemeMyLoginWidget() {
        add_action('plugins_loaded', array(&$this, 'PluginsLoaded'));
    }

    function PluginsLoaded() {
        wp_register_sidebar_widget('theme-my-login', 'Theme My Login', array(&$this, 'Widget'), array('description' => 'A login form for your blog'));
        wp_register_widget_control('theme-my-login', 'Theme My Login', array(&$this, 'WidgetControl'));
    }

    function Widget($args) {
        global $ThemeMyLogin;
        echo $ThemeMyLogin->ThemeMyLoginShortcode($args);
    }

    function WidgetControl() {
        global $ThemeMyLogin;

        if ( $_POST['tml_submit'] ) {

            $widget['default_action'] = $_POST['tml_default_action'];
            $ThemeMyLogin->options['logged_in_widget'] = (empty($_POST['tml_show_logged'])) ? false : true;
            $ThemeMyLogin->options['show_title'] = (empty($_POST['tml_show_title'])) ? false : true;
            $ThemeMyLogin->options['show_log_link'] = (empty($_POST['tml_show_log_link'])) ? false: true;
            $ThemeMyLogin->options['show_reg_link'] = (empty($_POST['tml_show_reg_link'])) ? false: true;
            $ThemeMyLogin->options['show_pass_link'] = (empty($_POST['tml_show_pass_link'])) ? false: true;
            $ThemeMyLogin->options['show_gravatar'] = (empty($_POST['tml_show_gravatar'])) ? false : true;
            $ThemeMyLogin->options['gravatar_size'] = absint($_POST['tml_gravatar_size']);
            $ThemeMyLogin->options['register_widget'] = (empty($_POST['tml_register_widget'])) ? false : true;
            $ThemeMyLogin->options['lost_pass_widget'] = (empty($_POST['tml_lost_pass_widget'])) ? false : true;
            $ThemeMyLogin->SaveOptions();
            
        }

        $ThemeMyLogin->options = $ThemeMyLogin->GetOption('widget');

        $actions = array('login' => 'Login', 'register' => 'Register', 'lostpassword' => 'Lost Password');
        echo '<p>Default Action<br /><select name="tml_default_action" id="tml_default_action">';
        foreach ($actions as $action => $title) {
            $is_selected = ($ThemeMyLogin->options['default_action'] == $action) ? ' selected="selected"' : '';
            echo '<option value="' . $action . '"' . $is_selected . '>' . $title . '</option>';
        }
        echo '</select></p>' . "\n";
        $is_checked = (empty($ThemeMyLogin->options['show_logged'])) ? '' : 'checked="checked" ';
        echo '<p><input name="tml_show_logged" type="checkbox" id="tml_show_logged" value="1" ' . $is_checked . '/> <label for="tml_show_logged">' . __('Show When Logged In', 'theme-my-login') . '</label></p>' . "\n";
        $is_checked = (empty($ThemeMyLogin->options['show_title'])) ? '' : 'checked="checked" ';
        echo '<p><input name="tml_show_title" type="checkbox" id="tml_show_title" value="1" ' . $is_checked . '/> <label for="tml_show_title">' . __('Show Title', 'theme-my-login') . '</label></p>' . "\n";
        $is_checked = (empty($ThemeMyLogin->options['show_login_link'])) ? '' : 'checked="checked" ';
        echo '<p><input name="tml_show_login_link" type="checkbox" id="tml_show_login_link" value="1" ' . $is_checked . '/> <label for="tml_show_log_link">' . __('Show Login Link', 'theme-my-login') . '</label></p>' . "\n";
        $is_checked = (empty($ThemeMyLogin->options['show_reg_link'])) ? '' : 'checked="checked" ';
        echo '<p><input name="tml_show_reg_link" type="checkbox" id="tml_show_reg_link" value="1" ' . $is_checked . '/> <label for="tml_show_reg_link">' . __('Show Registration Link', 'theme-my-login') . '</label></p>' . "\n";
        $is_checked = (empty($ThemeMyLogin->options['show_pass_link'])) ? '' : 'checked="checked" ';
        echo '<p><input name="tml_show_pass_link" type="checkbox" id="tml_show_pass_link" value="1" ' . $is_checked . '/> <label for="tml_show_pass_link">' . __('Show Lost Password Link', 'theme-my-login') . '</label></p>' . "\n";
        $is_checked = (empty($ThemeMyLogin->options['show_gravatar'])) ? '' : 'checked="checked" ';
        echo '<p><input name="tml_show_gravatar" type="checkbox" id="tml_show_gravatar" value="1" ' . $is_checked . '/> <label for="tml_show_gravatar">' . __('Show Gravatar', 'theme-my-login') . '</label></p>' . "\n";
        echo '<p>' . __('Gravatar Size', 'theme-my-login') . ': <input name="tml_gravatar_size" type="text" id="tml_gravatar_size" value="' . $ThemeMyLogin->options['gravatar_size'] . '" size="3" /> <label for="tml_gravatar_size"></label></p>' . "\n";
        $is_checked = (empty($ThemeMyLogin->options['register_widget'])) ? '' : 'checked="checked" ';
        echo '<p><input name="tml_register_widget" type="checkbox" id="tml_register_widget" value="1" ' . $is_checked . '/> <label for="tml_register_widget">' . __('Allow Registration', 'theme-my-login') . '</label></p>' . "\n";
        $is_checked = (empty($ThemeMyLogin->options['lost_pass_widget'])) ? '' : 'checked="checked" ';
        echo '<p><input name="tml_lost_pass_widget" type="checkbox" id="tml_lost_pass_widget" value="1" ' . $is_checked . '/> <label for="tml_lost_pass_widget">' . __('Allow Password Recovery', 'theme-my-login') . '</label></p>' . "\n";
        echo '<p><input type="hidden" id="tml_submit" name="tml_submit" value="1" /></p>' . "\n";

    }
    
}
endif;

if (class_exists('ThemeMyLoginWidget')) {
    $ThemeMyLoginWidget = new ThemeMyLoginWidget();
}

?>
