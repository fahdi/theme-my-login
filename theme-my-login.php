<?php
/*
Plugin Name: Theme My Login
Plugin URI: http://www.jfarthing.com/wordpress-plugins/theme-my-login-plugin
Description: Themes the WordPress login, registration and forgot password pages according to your theme.
Version: 3.3
Author: Jeff Farthing
Author URI: http://www.jfarthing.com
Text Domain: theme-my-login
*/

global $wp_version;

if ($wp_version < '2.6') {
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

        var $version = '3.3';
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

            global $wp_roles;
            if (empty($wp_roles))
                $wp_roles = new WP_Roles();
                
            $user_roles = $wp_roles->get_names();
            foreach ($user_roles as $role => $title) {
                $dashboard_link[$role] = 1;
                $profile_link[$role] = 1;
                $dashboard_url[$role] = '';
                $profile_url[$role] = '';
            }
            $this->options['widget_dashboard_link'] = $dashboard_link;
            $this->options['widget_profile_link'] = $profile_link;
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
            global $login_errors;
            
            if (strpos($content, '[theme-my-login]') !== false)
                return str_replace('[theme-my-login]', $this->DisplayLogin(), $content);
            else
                return $content;
        }
        
        function DisplayLogin($type = 'page', $action = '') {
            global $login_errors;

            $login_forms = WP_PLUGIN_DIR . '/theme-my-login/includes/wp-login-forms.php';
            if (is_file($login_forms)) {
                ob_start();
                include $login_forms;
                $contents = ob_get_contents();
                ob_end_clean();
                return $contents;
            }
            return false;
        }
        
        function WPHead() {
            echo '<!-- Theme My Login Version ' . $this->version . ' -->' . "\n";
            echo '<link rel="stylesheet" type="text/css" href="' . WP_PLUGIN_URL . '/theme-my-login/theme-my-login.css" />' . "\n";
            do_action('login_head');
        }

        function WPTitle($title) {
            if ( is_page($this->GetOption('page_id')) ) {
                    
                $action = (empty($_REQUEST['action'])) ? 'login' : $_REQUEST['action'];

                switch ($action) {
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
        
        function TheTitle($title, $action = '') {
            if ($title == 'Login') {

                if (empty($action))
                    $action = (empty($_REQUEST['action'])) ? 'login' : $_REQUEST['action'];
                    
                switch ($action) {
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
    
    function theme_my_login($args = array()) {
        global $ThemeMyLogin, $wp_version, $user_ID, $current_user, $login_errors, $wp_roles;

        if (empty($wp_roles))
            $wp_roles = new WP_Roles();

        $user_roles = $wp_roles->get_names();

        //Defaults
        $defaults['before_widget'] = '<li>';
        $defaults['after_widget'] = '</li>';
        $defaults['before_title'] = '<h2>';
        $defaults['after_title'] = '</h2>';
        $defaults['action'] = 'login';
        $defaults['show_logged_in'] = 1;
        $defaults['show_gravatar'] = 1;
        $defaults['gravatar_size'] = 50;
        foreach ($user_roles as $role => $value) {
            $defaults['dashboard_link_' . $role] = 1;
            $defaults['profile_link_' . $role] = 1;
        }
        
        $args = wp_parse_args( $args, (array) $defaults );

        if ($user_ID != '' && $args['show_logged_in']) {
            get_currentuserinfo();
            $user_role = reset($current_user->roles);
            $dashboard_url = $ThemeMyLogin->GetOption('widget_dashboard_url');
            $profile_url = $ThemeMyLogin->GetOption('widget_profile_url');
            $user_dashboard_url = (empty($dashboard_url[$user_role])) ? site_url('wp-admin/', 'admin') : $dashboard_url[$user_role];
            $user_profile_url = (empty($profile_url[$user_role])) ? site_url('wp-admin/profile.php', 'admin') : $profile_url[$user_role];
            echo $args['before_widget'] . $args['before_title'] . __('Welcome', 'theme-my-login') . ', ' . $current_user->display_name . $args['after_title'] . "\n";
            if ($args['show_gravatar']) :
                echo '<div class="theme-my-login-avatar">' . get_avatar( $user_ID, $size = $args['gravatar_size'] ) . '</div>' . "\n";
            endif;
            do_action('theme_my_login_avatar', $current_user);
            echo '<ul class="theme-my-login-links">' . "\n";
            if ($args['dashboard_link_' . $user_role]) :
                echo '<li><a href="' . $user_dashboard_url . '">' . __('Dashboard') . '</a></li>' . "\n";
            endif;
            if ($args['profile_link_' . $user_role]) :
                echo '<li><a href="' . $user_profile_url . '">' . __('Profile') . '</a></li>' . "\n";
            endif;
            do_action('theme_my_login_links', $user_role);
            $redirect = wp_guess_url();
            if (version_compare($wp_version, '2.7', '>='))
                echo '<li><a href="' . wp_logout_url($redirect) . '">' . __('Log Out') . '</a></li>' . "\n";
            else
                echo '<li><a href="' . site_url('wp-login.php?action=logout&redirect_to='.$redirect, 'login') . '">' . __('Log Out') . '</a></li>' . "\n";
            echo '</ul>' . "\n";
            echo $args['after_widget'] . "\n";
        } elseif (empty($user_ID)) {
            $action = (empty($_GET['action'])) ? (empty($args['action'])) ? '' : $args['action'] : $_GET['action'];
            echo $args['before_widget'] . $args['before_title'] . $ThemeMyLogin->TheTitle('Login', $action) . $args['after_title'] . "\n";
            echo $ThemeMyLogin->DisplayLogin('widget', $action);
            echo $args['after_widget'] . "\n";
        }
    }
}

?>
