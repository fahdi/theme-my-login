<?php
/*
Plugin Name: Theme My Login
Plugin URI: http://www.jfarthing.com/wordpress-plugins/theme-my-login-plugin
Description: Themes the WordPress login, registration and forgot password pages according to your theme.
Version: 4.1.1
Author: Jeff Farthing
Author URI: http://www.jfarthing.com
Text Domain: theme-my-login
*/

global $wp_version;

require_once ('classes/class.plugin-shell.php');
require_once ('classes/class.wp-login.php');

if ($wp_version < '2.6') {
    require (WP_PLUGIN_DIR . '/theme-my-login/includes/compat.php');
}

if (!class_exists('ThemeMyLogin')) {
    class ThemeMyLogin extends WPPluginShell {

        var $version = '4.1.1';
        var $options = array();
        var $permalink = '';
        var $instances = 0;

        function ThemeMyLogin() {
            global $wp_version;
            
            $this->SetPluginTitle('Theme My Login');
            
            load_plugin_textdomain($this->plugin_textdomain, '/wp-content/plugins/theme-my-login/language');

            register_activation_hook ( __FILE__, array( &$this, 'Activate' ) );
            register_deactivation_hook ( __FILE__, array( &$this, 'Deactivate' ) );
            
            $this->AddAdminPage('options', __('Theme My Login', 'theme-my-login'), __('Theme My Login', 'theme-my-login'), 8, '/theme-my-login/includes/admin-page.php');

            $this->AddAction('init');
            $this->AddAction('admin_init');
            $this->AddAction('template_redirect');

            $this->AddAction('register_form');
            $this->AddAction('registration_errors');

            $this->AddFilter('wp_head');
            $this->AddFilter('wp_title');
            $this->AddFilter('the_title');
            $this->AddFilter('wp_list_pages');
            $this->AddFilter('wp_list_pages_excludes');
            $this->AddFilter('login_redirect', 'LoginRedirect', 10, 3);
            $this->AddFilter('site_url', 'SiteURL', 10, 2);
            $this->AddFilter('retrieve_password_title', 'RetrievePasswordTitle', 10, 2);
            $this->AddFilter('retrieve_password_message', 'RetrievePasswordMessage', 10, 3);
            $this->AddFilter('password_reset_title', 'PasswordResetTitle', 10, 2);
            $this->AddFilter('password_reset_message', 'PasswordResetMessage', 10, 3);
            
            $this->AddShortcode('theme-my-login-page');
            $this->AddShortcode('theme-my-login');
            
            $this->LoadOptions();
            
            if ( !isset($this->options['page_id']) || empty($this->options['page_id']) ) {
                $login_page = get_page_by_title('Login');
                $this->options['general']['page_id'] = ( $login_page ) ? $login_page->ID : 0;
                $this->SaveOptions();
            }
            
            $this->SetMailFrom($this->options['general']['from_email'], $this->options['general']['from_name']);
            
            $this->WPPluginShell();
            
        }

        function Activate() {
            $insert = array(
                'post_title' => 'Login',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => 1,
                'post_content' => '[theme-my-login-page]',
                'comment_status' => 'closed',
                'ping_status' => 'closed'
                );
                
            $theme_my_login = get_page_by_title('Login');
            if ( !$theme_my_login ) {
                $page_id = wp_insert_post($insert);
            } else {
                $page_id = $theme_my_login->ID;
                $insert['ID'] = $page_id;
                $insert['post_content'] = str_replace('[theme-my-login]', '[theme-my-login-page]', $theme_my_login->post_content);
                wp_update_post($insert);
            }
            
            $opts = get_option('theme_my_login');
            if ( $opts ) {
                if ( is_array($opts) ) {
                    if ( isset($opts['version']) && version_compare($opts['version'], '4.0', '<') ) {
                        delete_option('theme_my_login');
                        delete_option('widget_theme-my-login');
                    }
                }
            }
            
            $options = array('page_id' => $page_id, 'version' => $this->version);
            $this->SetOption('general', $options);
            $this->SaveOptions();
        }

        function Deactivate() {
            if ( $this->options['general']['uninstall'] ) {
                delete_option('theme_my_login');
                delete_option('widget_theme-my-login');
                wp_delete_post($this->options['general']['page_id']);
            }
        }

        function InitOptions($save = false) {
            
            $this->options['general']['page_id']        = 0;
            $this->options['general']['uninstall']      = 0;
            $this->options['general']['defaults']       = 0;
            $this->options['general']['show_page']      = 0;
            $this->options['general']['custom_pass']    = 0;
            $this->options['general']['from_name']      = '';
            $this->options['general']['from_email']     = '';
            
            $this->options['titles']['welcome']         = __('Welcome') . ', %display_name%';
            $this->options['titles']['login']           = __('Log In');
            $this->options['titles']['register']        = __('Register');
            $this->options['titles']['lostpassword']    = __('Lost Password');
            $this->options['titles']['logout']          = __('Log Out');
            
            $this->options['messages']['register']      = __('A password will be e-mailed to you.');
            $this->options['messages']['success']       = __('Registration complete. Please check your e-mail.');
            $this->options['messages']['lostpassword']  = __('Please enter your username or e-mail address. You will receive a new password via e-mail.');
            
            $this->options['widget']['default_action']  = 'login';
            $this->options['widget']['show_title']      = 1;
            $this->options['widget']['show_links']      = 1;
            $this->options['widget']['registration']    = 1;
            $this->options['widget']['lostpassword']    = 1;
            $this->options['widget']['show_logged']     = 1;
            $this->options['widget']['show_gravatar']   = 1;
            $this->options['widget']['gravatar_size']   = 50;
            $this->options['widget']['before_widget']   = '<li>';
            $this->options['widget']['after_widget']    = '</li>';
            $this->options['widget']['before_title']    = '<h2>';
            $this->options['widget']['after_title']     = '</h2>';
            
            $this->options['emails']['retrievepassword']['subject']         = '';
            $this->options['emails']['retrievepassword']['message']         = '';
            $this->options['emails']['resetpassword']['subject']            = '';
            $this->options['emails']['resetpassword']['message']            = '';
            $this->options['emails']['resetpassword']['admin-disable']      = 0;
            $this->options['emails']['newregistration']['subject']          = '';
            $this->options['emails']['newregistration']['message']          = '';
            $this->options['emails']['newregistration']['admin-disable']    = 0;
            $this->options['emails']['newregistration']['user-disable']     = 0;

            global $wp_roles;
            if ( empty($wp_roles) )
                $wp_roles = new WP_Roles();
                
            $user_roles = $wp_roles->get_names();
            foreach ( $user_roles as $role => $title ) {
                $this->options['links'][$role][] = array('title' => 'Dashboard', 'url' => admin_url());
                $this->options['links'][$role][] = array('title' => 'Profile', 'url' => admin_url('profile.php'));
                $this->options['redirects'][$role] = array('login_url' => '', 'logout_url' => '');
            }
            
            if ( $save )
                $this->SaveOptions();
        }
        
        function Init() {
            global $pagenow;
            
            $this->permalink = get_permalink($this->options['general']['page_id']);

            switch ( $pagenow ) {
                case 'wp-register.php':
                case 'wp-login.php':
                    $redirect_to = add_query_arg($_GET, $this->permalink);
                    wp_redirect($redirect_to);
                    exit();
                break;
            }
            
            if ( file_exists(WP_PLUGIN_DIR . '/theme-my-login/theme-my-login.css') )
                $this->AddStyle('theme-my-login', WP_PLUGIN_URL . '/theme-my-login/theme-my-login.css');
            else
                $this->AddStyle('theme-my-login', WP_PLUGIN_URL . '/theme-my-login/css/theme-my-login.css');
                
        }
        
        function AdminInit() {
            global $user_ID, $wp_version, $pagenow;
            
            if ( 'options-general.php' == $pagenow && (isset($_GET['page']) && 'theme-my-login/includes/admin-page.php' == $_GET['page']) ) {
            
                if ( version_compare($wp_version, '2.8', '>=') ) {
                    $this->AddAdminScript('jquery-ui-tabs');
                } else {
                    global $wp_scripts;

                    if ( empty($wp_scripts) )
                        $wp_scripts = new WP_Scripts();

                    $wp_scripts->dequeue('jquery');
                    $wp_scripts->dequeue('jquery-ui-core');
                    $wp_scripts->dequeue('jquery-ui-tabs');
                    $wp_scripts->remove('jquery');
                    $wp_scripts->remove('jquery-ui-core');
                    $wp_scripts->remove('jquery-ui-tabs');
                    $this->AddAdminScript('jquery', WP_PLUGIN_URL . '/theme-my-login/js/jquery/jquery.js', false, '1.7.2');
                    $this->AddAdminScript('jquery-ui-core', WP_PLUGIN_URL . '/theme-my-login/js/jquery/ui.core.js', array('jquery'), '1.7.2');
                    $this->AddAdminScript('jquery-ui-tabs', WP_PLUGIN_URL . '/theme-my-login/js/jquery/ui.tabs.js', array('jquery', 'jquery-ui-core'), '1.7.2');
                }

                $this->AddAdminScript('theme-my-login-admin', WP_PLUGIN_URL . '/theme-my-login/js/theme-my-login-admin.js.php');
            
                $this->AddAdminStyle('theme-my-login-admin', WP_PLUGIN_URL . '/theme-my-login/css/theme-my-login-admin.css.php');
            
                if ( version_compare($wp_version, '2.8', '>=') ) {
                    $admin_color = get_usermeta($user_ID, 'admin_color');
                    if ( 'classic' == $admin_color ) {
                        $this->AddAdminStyle('jquery-colors-classic', WP_PLUGIN_URL . '/theme-my-login/css/wp-colors-classic/wp-colors-classic.css');
                    } else {
                        $this->AddAdminStyle('jquery-colors-fresh', WP_PLUGIN_URL . '/theme-my-login/css/wp-colors-fresh/wp-colors-fresh.css');
                    }
                } elseif ( version_compare($wp_version, '2.7', '>=') ) {
                    $this->AddAdminStyle('jquery-colors-fresh', WP_PLUGIN_URL . '/theme-my-login/css/wp-colors-fresh/wp-colors-fresh.css');
                } elseif ( version_compare($wp_version, '2.5', '>=') ) {
                    $this->AddAdminStyle('jquery-colors-classic', WP_PLUGIN_URL . '/theme-my-login/css/wp-colors-classic/wp-colors-classic.css');
                }

                if ( 'page.php' == $pagenow && (isset($_REQUEST['post']) && $this->options['general']['page_id'] == $_REQUEST['post']) )
                    add_action('admin_notices', array(&$this, 'PageEditNotice'));
            }
        }
        
        function PageEditNotice() {
            echo '<div class="error"><p>' . __('NOTICE: This page is integral to the operation of Theme My Login. <strong>DO NOT</strong> edit the title or remove the short code from the contents.') . '</p></div>';
        }

        function TemplateRedirect() {
            global $WPLogin;

            if ( is_page($this->options['general']['page_id']) ) {
                $action = ( isset($_GET['action']) ) ? $_GET['action'] : '';
                if ( is_user_logged_in() && 'logout' != $action ) {
                    wp_redirect(get_bloginfo('home'));
                    exit();
                }
            }

            if ( !is_admin() )
                $WPLogin = new WPLogin('theme-my-login', $this->options);
        }
        
        function RegisterForm($instance) {
            if ( isset($this->options['general']['custom_pass']) && true == $this->options['general']['custom_pass'] ) {
                ?>
            <p><label><?php _e('Password:');?> <br />
            <input autocomplete="off" name="pass1" id="pass1-<?php echo $instance; ?>" class="input" size="20" value="" type="password" /></label><br />
            <label><?php _e('Confirm Password:');?> <br />
            <input autocomplete="off" name="pass2" id="pass2-<?php echo $instance; ?>" class="input" size="20" value="" type="password" /></label></p>
                <?php
            }
        }

        function RegistrationErrors($errors){
            if ( isset($this->options['general']['custom_pass']) && true == $this->options['general']['custom_pass'] ) {
                if (empty($_POST['pass1']) || $_POST['pass1'] == '' || empty($_POST['pass2']) || $_POST['pass2'] == ''){
                    $errors->add('empty_password', __('<strong>ERROR</strong>: Please enter a password.'));
                } elseif ($_POST['pass1'] !== $_POST['pass2']){
                    $errors->add('password_mismatch', __('<strong>ERROR</strong>: Your passwords do not match.'));
                } elseif (strlen($_POST['pass1'])<6){
                    $errors->add('password_length', __('<strong>ERROR</strong>: Your password must be at least 6 characters in length.'));
                } else {
                    $_POST['user_pw'] = $_POST['pass1'];
                }
            }

            return $errors;
        }

        function WPHead() {
            if ( !is_admin() && $this->instances > 0 )
                do_action('login_head');
        }

        function WPTitle($title) {
            global $WPLogin;
            
            if ( is_page($this->options['general']['page_id']) ) {
            
                $titles = $this->GetOption('titles');

                $action = ( isset($WPLogin->options['action']) ) ? $WPLogin->options['action'] : '';
                if ( 'tml-main' == $WPLogin->instance || empty($WPLogin->instance) )
                    $action = $WPLogin->action;

                if ( is_user_logged_in() )
                    return str_replace('Login', $titles['logout'], $title);
                    
                switch ($action) {
                    case 'register':
                        return str_replace('Login', $titles['register'], $title);
                        break;
                    case 'lostpassword':
                    case 'retrievepassword':
                    case 'resetpass':
                    case 'rp':
                        return str_replace('Login', $titles['lostpassword'], $title);
                        break;
                    case 'login':
                    default:
                        return str_replace('Login', $titles['login'], $title);
                }
            } return $title;
        }
        
        function TheTitle($title) {
            global $WPLogin;
            
            if ( is_admin() )
                return $title;
            
            if ( $title == 'Login' ) {
            
                 $titles = $this->GetOption('titles');

                if ( is_user_logged_in() )
                    return $titles['logout'];

                $action = ( isset($WPLogin->options['action']) ) ? $WPLogin->options['action'] : '';
                if ( 'tml-main' == $WPLogin->instance || empty($WPLogin->instance) )
                    $action = $WPLogin->action;
                    
                switch ($action) {
                    case 'register':
                        return $titles['register'];
                        break;
                    case 'lostpassword':
                    case 'retrievepassword':
                    case 'resetpass':
                    case 'rp':
                        return $titles['lostpassword'];
                        break;
                    case 'login':
                    default:
                        return $titles['login'];
                }
            } return $title;
        }
        
        function WPListPages($pages) {
            global $wp_version, $WPLogin;
            
            if ( $this->options['general']['show_page'] && is_user_logged_in() ) {
                $redirect = $WPLogin->GuessURL();
                $logout_url = ( version_compare($wp_version, '2.7', '>=') ) ? wp_logout_url($redirect) : site_url('wp-login.php?action=logout&redirect_to='.$redirect, 'login');
                $pages = str_replace($this->permalink, $logout_url, $pages);
            }

            return $pages;
        }
        
        function WPListPagesExcludes($excludes) {
            if ( !$this->options['general']['show_page'] )
                $excludes[] = $this->options['general']['page_id'];

            return $excludes;
        }
        
        function LoginRedirect($redirect_to, $request, $user) {
            global $pagenow;

            $schema = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
            $self =  $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            if ( empty($redirect_to) || admin_url() == $redirect_to) {
                if ( empty($request) )
                    $redirect_to = ( 'wp-login.php' == $pagenow ) ? $_SERVER['HTTP_REFERER'] : $self;
                else
                    $redirect_to = $request;
            }

            if ( is_object($user) && !is_wp_error($user) ) {
                $user_role = array_shift($user->roles);
                $redirects = $this->GetOption('redirects');
                if ( '' != $redirects[$user_role]['login_url'] )
                    $redirect_to = $redirects[$user_role]['login_url'];
            }
            return $redirect_to;
        }
        
        function SiteURL($url, $path) {
            global $wp_rewrite;
            
            if ( preg_match('/wp-login.php/', $url) ) {
                $parsed_url = parse_url($url);
                if ( isset($parsed_url['query']) )
                    $url = $wp_rewrite->using_permalinks() ? $this->permalink.'?'.$parsed_url['query'] : $this->permalink.'&'.$parsed_url['query'];
                else
                    $url = $this->permalink;
            }
            return $url;
        }
        
        function RetrievePasswordTitle($title, $user) {
            if ( !empty($this->options['emails']['retrievepassword']['subject']) ) {
                $replace_this = array('/%blogname%/', '/%siteurl%/', '/%reseturl%/', '/%user_login%/', '/%user_email%/', '/%user_ip%/');
                $replace_with = array(get_option('blogname'), get_option('siteurl'), site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login'), $user->user_login, $user->user_email, $_SERVER['REMOTE_ADDR']);
                $title = preg_replace($replace_this, $replace_with, $this->options['emails']['retrievepassword']['subject']);
            }
            return $title;
        }
        
        function RetrievePasswordMessage($message, $key, $user) {
            if ( !empty($this->options['emails']['retrievepassword']['message']) ) {
                $replace_this = array('/%blogname%/', '/%siteurl%/', '/%reseturl%/', '/%user_login%/', '/%user_email%/', '/%key%/', '/%user_ip%/');
                $replace_with = array(get_option('blogname'), get_option('siteurl'), site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login'), $user->user_login, $user->user_email, $key, $_SERVER['REMOTE_ADDR']);
                $message = preg_replace($replace_this, $replace_with, $this->options['emails']['retrievepassword']['message']);
            }
            return $message;
        }
        
        function PasswordResetTitle($title, $user) {
            if ( !empty($this->options['emails']['resetpassword']['subject']) ) {
                $replace_this = array('/%blogname%/', '/%siteurl%/', '/%user_login%/', '/%user_email%/', '/%user_ip%/');
                $replace_with = array(get_option('blogname'), get_option('siteurl'), $user->user_login, $user->user_email, $_SERVER['REMOTE_ADDR']);
                $title = preg_replace($replace_this, $replace_with, $this->options['emails']['resetpassword']['subject']);
            }
            return $title;
        }
        
        function PasswordResetMessage($message, $new_pass, $user) {
            if ( !empty($this->options['emails']['resetpassword']['message']) ) {
                $replace_this = array('/%blogname%/', '/%siteurl%/', '/%user_login%/', '/%user_email%/', '/%user_pass%/', '/%user_ip%/');
                $replace_with = array(get_option('blogname'), get_option('siteurl'), $user->user_login, $user->user_email, $new_pass, $_SERVER['REMOTE_ADDR']);
                $message = preg_replace($replace_this, $replace_with, $this->options['emails']['resetpassword']['message']);
            }
            return $message;
        }

        function ThemeMyLoginShortcode($args = array()) {
            global $WPLogin;

            $args = wp_parse_args($args);

            $options = $this->options;
            foreach ( $args as $key => $value ) {
                if ( !is_array($value) ) {
                    if ( in_array($key, array('welcome', 'login', 'register', 'lostpassword', 'logout')) )
                        $options['titles'][$key] = $value;
                    elseif ( in_array($key, array('register', 'success', 'lostpassword')) )
                        $options['messages'][$key] = $value;
                    elseif ( in_array($key, array('instance', 'default_action', 'show_title', 'show_links', 'registration', 'lostpassword', 'show_logged', 'show_gravatar', 'gravatar_size', 'before_widget', 'after_widget', 'before_title', 'after_title')) )
                        $options['widget'][$key] = $value;
                } else {
                    foreach ( $value as $k => $v )
                        $options[$key][$k] = $v;
                }
            }

            $instance = ( isset($options['widget']['instance']) ) ? $options['widget']['instance'] : $this->NewInstance();

            return $WPLogin->Display($instance, $options);
        }
        
        
        function ThemeMyLoginPageShortcode($args = array()) {
            $args['widget']['instance'] = 'tml-main';
            $args['widget']['default_action'] = 'login';
            $args['widget']['show_title'] = '0';
            $args['widget']['before_widget'] = '';
            $args['widget']['after_widget'] = '';
            return $this->ThemeMyLoginShortcode($args);
        }
        
        function NewInstance() {
            $this->instances++;
            return 'tml-' . $this->instances;
        }
    }
}

if (class_exists('ThemeMyLogin')) {
    global $wp_version;
    
    $ThemeMyLogin = new ThemeMyLogin();

    if ( version_compare($wp_version, '2.8', '>=') ) {
        require_once (WP_PLUGIN_DIR . '/theme-my-login/classes/class.widget-new.php');
    } else {
        require_once (WP_PLUGIN_DIR . '/theme-my-login/classes/class.widget-old.php');
    }
    
    if ( !function_exists('theme_my_login') ) :
    function theme_my_login($args = '') {
        global $ThemeMyLogin;
        
        echo $ThemeMyLogin->ThemeMyLoginShortcode($args);
    }
    endif;

    if ( !function_exists('wp_new_user_notification') ) :
    function wp_new_user_notification($user_id, $plaintext_pass = '') {
        global $ThemeMyLogin, $wp_version;
        
        $user = new WP_User($user_id);
        
        $user_login = stripslashes($user->user_login);
        $user_email = stripslashes($user->user_email);
        
        if ( !$ThemeMyLogin->options['emails']['newregistration']['admin-disable'] ) {
            $message  = sprintf(__('New user registration on your blog %s:'), get_option('blogname')) . "\r\n\r\n";
            $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
            $message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

            @wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), get_option('blogname')), $message);
        }

        if ( empty($plaintext_pass) )
            return;

        if ( !$ThemeMyLogin->options['emails']['newregistration']['user-disable'] ) {
            $subject = $ThemeMyLogin->options['emails']['newregistration']['subject'];
            $message = $ThemeMyLogin->options['emails']['newregistration']['message'];
            $replace_this = array('/%blogname%/', '/%siteurl%/', '/%user_login%/', '/%user_email%/', '/%user_pass%/', '/%user_ip%/');
            $replace_with = array(get_option('blogname'), get_option('siteurl'), $user->user_login, $user->user_email, $plaintext_pass, $_SERVER['REMOTE_ADDR']);
            
            if ( !empty($subject) )
                $subject = preg_replace($replace_this, $replace_with, $subject);
            else
                $subject = sprintf(__('[%s] Your username and password'), get_option('blogname'));
            if ( !empty($message) )
                $message = preg_replace($replace_this, $replace_with, $message);
            else {
                $message  = sprintf(__('Username: %s'), $user_login) . "\r\n";
                $message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
                $message .= ( version_compare($wp_version, '2.7', '>=') ) ? wp_login_url() . "\r\n" : site_url('wp-login.php', 'login') . "\r\n";
            }

            wp_mail($user_email, $subject, $message);
        }

    }
    endif;

}

?>
