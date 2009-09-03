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
        
        $new_args['widget'] = wp_parse_args($args);
        
        echo $ThemeMyLogin->ThemeMyLoginShortcode($new_args);

    }

    function WidgetControl() {
        global $ThemeMyLogin;

        if ( $_POST['tml_submit'] ) {

            $widget['default_action'] = $_POST['tml_default_action'];
            $widget['show_logged'] = (empty($_POST['tml_show_logged'])) ? false : true;
            $widget['show_title'] = (empty($_POST['tml_show_title'])) ? false : true;
            $widget['show_links'] = (empty($_POST['tml_show_links'])) ? false: true;
            $widget['show_gravatar'] = (empty($_POST['tml_show_gravatar'])) ? false : true;
            $widget['gravatar_size'] = absint($_POST['tml_gravatar_size']);
            $widget['registration'] = (empty($_POST['tml_registration'])) ? false : true;
            $widget['lostpassword'] = (empty($_POST['tml_lostpassword'])) ? false : true;
            //$ThemeMyLogin->options['widget'] = array_merge($ThemeMyLogin->options['widget'], $widget);
            $ThemeMyLogin->SetOption('widget', $widget);
            $ThemeMyLogin->SaveOptions();
            
        }

        $widget = $ThemeMyLogin->GetOption('widget');

        $actions = array('login' => 'Login', 'register' => 'Register', 'lostpassword' => 'Lost Password');
        echo '<p>Default Action<br /><select name="tml_default_action" id="tml_default_action">';
        foreach ($actions as $action => $title) {
            $is_selected = ($widget['default_action'] == $action) ? ' selected="selected"' : '';
            echo '<option value="' . $action . '"' . $is_selected . '>' . $title . '</option>';
        }
        echo '</select></p>' . "\n";
        $is_checked = (empty($widget['show_logged'])) ? '' : 'checked="checked" ';
        echo '<p><input name="tml_show_logged" type="checkbox" id="tml_show_logged" value="1" ' . $is_checked . '/> <label for="tml_show_logged">' . __('Show When Logged In', 'theme-my-login') . '</label></p>' . "\n";
        $is_checked = (empty($widget['show_title'])) ? '' : 'checked="checked" ';
        echo '<p><input name="tml_show_title" type="checkbox" id="tml_show_title" value="1" ' . $is_checked . '/> <label for="tml_show_title">' . __('Show Title', 'theme-my-login') . '</label></p>' . "\n";
        $is_checked = (empty($widget['show_links'])) ? '' : 'checked="checked" ';
        echo '<p><input name="tml_show_links" type="checkbox" id="tml_show_links" value="1" ' . $is_checked . '/> <label for="tml_show_links">' . __('Show Action Links', 'theme-my-login') . '</label></p>' . "\n";
        $is_checked = (empty($widget['show_gravatar'])) ? '' : 'checked="checked" ';
        echo '<p><input name="tml_show_gravatar" type="checkbox" id="tml_show_gravatar" value="1" ' . $is_checked . '/> <label for="tml_show_gravatar">' . __('Show Gravatar', 'theme-my-login') . '</label></p>' . "\n";
        echo '<p>' . __('Gravatar Size', 'theme-my-login') . ': <input name="tml_gravatar_size" type="text" id="tml_gravatar_size" value="' . $widget['gravatar_size'] . '" size="3" /> <label for="tml_gravatar_size"></label></p>' . "\n";
        $is_checked = (empty($widget['registration'])) ? '' : 'checked="checked" ';
        echo '<p><input name="tml_registration" type="checkbox" id="tml_registration" value="1" ' . $is_checked . '/> <label for="tml_registration">' . __('Allow Registration', 'theme-my-login') . '</label></p>' . "\n";
        $is_checked = (empty($widget['lostpassword'])) ? '' : 'checked="checked" ';
        echo '<p><input name="tml_lostpassword" type="checkbox" id="tml_lostpassword" value="1" ' . $is_checked . '/> <label for="tml_lostpassword">' . __('Allow Password Recovery', 'theme-my-login') . '</label></p>' . "\n";
        echo '<p><input type="hidden" id="tml_submit" name="tml_submit" value="1" /></p>' . "\n";

    }
    
}
endif;

if (class_exists('ThemeMyLoginWidget')) {
    $ThemeMyLoginWidget = new ThemeMyLoginWidget();
}

?>
