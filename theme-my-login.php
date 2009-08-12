<?php
/*
Plugin Name: Theme My Login
Plugin URI: http://www.jfarthing.com/wordpress-plugins/theme-my-login-plugin
Description: Themes the WordPress login, registration and forgot password pages according to your theme.
Version: 3.2.8
Author: Jeff Farthing
Author URI: http://www.jfarthing.com
Text Domain: theme-my-login
*/

global $wp_version;

if ($wp_version < '2.7') {
    if ( !defined('WP_CONTENT_DIR') )
        define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
    if ( !defined('WP_CONTENT_URL') )
        define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
    if ( !defined('WP_PLUGIN_DIR') )
        define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
    if ( !defined('WP_PLUGIN_URL') )
        define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
        
    require (WP_PLUGIN_DIR . '/theme-my-login/includes/compat.php');
}

if (!class_exists('ThemeMyLogin')) {
    class ThemeMyLogin {

        var $version = '3.2.8';
        var $options = array();
        var $permalink = '';

        function ThemeMyLogin() {
            $this->__construct();
        }

        function __construct() {
        
            load_plugin_textdomain('theme-my-login', '/wp-content/plugins/theme-my-login/language');

            register_activation_hook ( __FILE__, array( &$this, 'Activate' ) );
            register_deactivation_hook ( __FILE__, array( &$this, 'Deactivate' ) );
            
            add_action('admin_menu', array(&$this, 'AddAdminPage'));
            add_action('init', array(&$this, 'Init'));
            add_action('parse_request', array(&$this, 'ParseRequest'));
            
            add_filter('wp_head', array(&$this, 'WPHead'));
            add_filter('wp_title', array(&$this, 'WPTitle'));
            add_filter('the_title', array(&$this, 'TheTitle'));
            add_filter('wp_list_pages_excludes', array(&$this, 'ListPagesExcludes'));
            add_filter('the_content', array(&$this, 'TheContent'));
            add_filter('site_url', array(&$this, 'SiteURLFilter'), 10, 2);
            
            $this->LoadOptions();
        }

        function Activate() {
            $theme_my_login = get_page_by_title('Login');
            if ( !$theme_my_login ) {
                $insert = array(
                'post_title' => 'Login',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => 1,
                'post_content' => '[theme-my-login]',
                'comment_status' => 'closed',
                'ping_status' => 'closed'
                );
                $page_id = wp_insert_post($insert);
            } else $page_id = $theme_my_login->ID;
            
            $this->SetOption('page_id', $page_id);
            $this->SetOption('version', $this->version);
            $this->SaveOptions();
        }

        function Deactivate() {
            if ($this->GetOption('uninstall')) {
                delete_option('theme_my_login');
                wp_delete_post($this->GetOption('page_id'));
            }
        }

        function InitOptions() {
            $this->options['uninstall']             = 0;
            $this->options['page_id']               = 0;
            $this->options['show_page']             = 0;
            $this->options['login_title']           = __('Log In');
            $this->options['register_title']        = __('Register');
            $this->options['register_msg']          = __('A password will be e-mailed to you.');
            $this->options['register_complete']     = __('Registration complete. Please check your e-mail.');
            $this->options['password_title']        = __('Lost Password');
            $this->options['password_msg']          = __('Please enter your username or e-mail address. You will receive a new password via e-mail.');
            
            // Widget options
            $this->options['widget_allow_register'] = 1;
            $this->options['widget_allow_password'] = 1;
            $this->options['widget_show_logged_in'] = 1;
            $this->options['widget_show_gravatar'] = 1;
            $this->options['widget_gravatar_size'] = 50;
            $this->options['widget_dashboard_link'] = array('subscriber' => 1, 'contributor' => 1, 'author' => 1, 'editor' => 1, 'administrator' => 1);
            $this->options['widget_profile_link'] = array('subscriber' => 1, 'contributor' => 1, 'author' => 1, 'editor' => 1, 'administrator' => 1);

            $user_roles = array('subscriber', 'contributor', 'author', 'editor', 'administrator');
            foreach ($user_roles as $role) {
                $dashboard_url[$role] = '';
                $profile_url[$role] = '';
            }
            $this->options['widget_dashboard_url'] = $dashboard_url;
            $this->options['widget_profile_url'] = $profile_url;
        }

        function LoadOptions() {

            $this->InitOptions();

            $storedoptions = get_option( 'theme_my_login' );
            if ( $storedoptions && is_array( $storedoptions ) ) {
                foreach ( $storedoptions as $key => $value ) {
                    $this->options[$key] = $value;
                }
            } else update_option( 'theme_my_login', $this->options );
        }

        function GetOption( $key ) {
            if ( array_key_exists( $key, $this->options ) ) {
                return $this->options[$key];
            } else return null;
        }

        function SetOption( $key, $value ) {
            $this->options[$key] = $value;
        }

        function SaveOptions() {
            $oldvalue = get_option( 'theme_my_login' );
            if( $oldvalue == $this->options ) {
                return true;
            } else return update_option( 'theme_my_login', $this->options );
        }

        function AddAdminPage(){
            add_options_page(__('Theme My Login', 'theme-my-login'), __('Theme My Login', 'theme-my-login'), 8, 'theme-my-login', array(&$this, 'AdminPage'));
        }

        function AdminPage(){
            require (WP_PLUGIN_DIR . '/theme-my-login/includes/admin-page.php');
        }
        
        function ParseRequest() {
            global $wp, $login_errors;

            $page_id = isset($wp->query_vars['page_id']) ? $wp->query_vars['page_id'] : 0;
            $pagename = isset($wp->query_vars['pagename']) ? $wp->query_vars['pagename'] : '';

            if ( isset($page_id) && $page_id == $this->GetOption('page_id') || isset($pagename) && strtolower($pagename) == 'login' ) {
                if ( is_user_logged_in() && 'logout' != $_GET['action'] ) {
                    wp_redirect(get_bloginfo('home'));
                    exit;
                }
            }
            if (strpos($_SERVER['REQUEST_URI'], '/wp-admin') === false) {
                $login_errors = new WP_Error();
                require (WP_PLUGIN_DIR . '/theme-my-login/includes/wp-login-actions.php');
            }
        }
        
        function Init() {
            global $pagenow;
            
            $this->permalink = get_permalink($this->GetOption('page_id'));
            
            switch ($pagenow) {
                case 'wp-register.php':
                case 'wp-login.php':
                    $redirect_to = add_query_arg($_GET, $this->permalink);
                    wp_redirect($redirect_to);
                    exit;
                break;
            }
        }
        
        function TheContent($content) {
            if (strpos($content, '[theme-my-login]') !== false)
                return str_replace('[theme-my-login]', $this->DisplayLogin(), $content);
            else
                return $content;
        }
        
        function DisplayLogin($type = 'page') {
            global $login_errors;
            
            require (WP_PLUGIN_DIR . '/theme-my-login/includes/wp-login-forms.php');
        }
        
        function WPHead() {
            echo '<!-- Theme My Login Version ' . $this->version . ' -->' . "\n";
            echo '<link rel="stylesheet" type="text/css" href="' . WP_PLUGIN_URL . '/theme-my-login/theme-my-login.css" />' . "\n";
            echo '<!-- Theme My Login Version ' . $this->version . ' -->' . "\n";
            do_action('login_head');
        }

        function WPTitle($title) {
            if ( is_page($this->GetOption('page_id')) ) {
                    
                if (!isset($_GET['action']))
                    $_GET['action'] = 'login';
                    
                switch ($_GET['action']) {
                    case 'register':
                        return str_replace('Login', $this->GetOption('register_title'), $title);
                        break;
                    case 'lostpassword':
                    case 'retrievepassword':
                    case 'resetpass':
                    case 'rp':
                        return str_replace('Login', $this->GetOption('password_title'), $title);
                        break;
                    case 'login':
                    default:
                        return str_replace('Login', $this->GetOption('login_title'), $title);
                }
            } return $title;
        }
        
        function TheTitle($title) {
            if ($title == 'Login') {

                if (!isset($_GET['action']))
                    $_GET['action'] = 'login';
                    
                switch ($_GET['action']) {
                    case 'register':
                        return $this->GetOption('register_title');
                        break;
                    case 'lostpassword':
                    case 'retrievepassword':
                    case 'resetpass':
                    case 'rp':
                        return $this->GetOption('password_title');
                        break;
                    case 'login':
                    default:
                        return $this->GetOption('login_title');
                }
            } return $title;
        }
        
        function ListPagesExcludes($excludes) {
            if (!$this->GetOption('show_page'))
                $excludes[] = $this->GetOption( 'page_id' );

            return $excludes;
        }
        
        function SiteURLFilter($url, $path) {
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
    }
}

//instantiate the class
if (class_exists('ThemeMyLogin')) {
    global $wp_version;
    
    $ThemeMyLogin = new ThemeMyLogin();
    
    if ($wp_version >= '2.8') {
        require (WP_PLUGIN_DIR . '/theme-my-login/includes/widget-new.php');
    } else {
        require (WP_PLUGIN_DIR . '/theme-my-login/includes/widget-old.php');
    }
}

?>
