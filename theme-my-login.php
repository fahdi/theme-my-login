<?php
/*
Plugin Name: Theme My Login
Plugin URI: http://webdesign.jaedub.com/wordpress-plugins/theme-my-login-plugin
Description: Themes the WordPress login pages according to your theme. Also allows you to redirect users based on their role upon login.
Version: 2.1
Author: Jae Dub
Author URI: http://webdesign.jaedub.com
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

        var $version = '2.1';
        var $options = array();
        var $errors = '';

        function ThemeMyLogin() {
            $this->__construct();
        }

        function __construct() {
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
            $this->SaveOptions();
        }

        function Deactivate() {
            if ($this->GetOption('chk_uninstall')) {
                delete_option('theme_my_login');
                wp_delete_post($this->GetOption('page_id'));
            }
        }

        # Sets up default options
        function InitOptions() {
            $this->options['chk_uninstall']         = 0;
            $this->options['version']               = $this->version;
            $this->options['page_id']               = '0';
            $this->options['subscr_login_redirect'] = admin_url();
            $this->options['contrb_login_redirect'] = admin_url();
            $this->options['author_login_redirect'] = admin_url();
            $this->options['editor_login_redirect'] = admin_url();
            $this->options['admin_login_redirect']  = admin_url();
            $this->options['logout_redirect']       = site_url('wp-login.php?loggedout=true', 'login');
            $this->options['login_title']           = 'Log In';
            $this->options['login_text']            = 'Log In';
            $this->options['register_title']        = 'Register';
            $this->options['register_text']         = 'Register';
            $this->options['register_msg']          = 'A password will be e-mailed to you.';
            $this->options['password_title']        = 'Lost Password';
            $this->options['password_text']         = 'Lost Password';
            $this->options['profile_title']         = 'Your Profile';
            $this->options['profile_text']          = 'Your Profile';
        }

        # Loads options from database
        function LoadOptions() {

            $this->InitOptions();

            $storedoptions = get_option( 'theme_my_login' );
            if ( $storedoptions && is_array( $storedoptions ) ) {
                foreach ( $storedoptions as $key => $value ) {
                    $this->options[$key] = $value;
                }
            } else update_option( 'theme_my_login', $this->options );
        }

        # Returns option value for given key
        function GetOption( $key ) {
            if ( array_key_exists( $key, $this->options ) ) {
                return $this->options[$key];
            } else return null;
        }

        # Sets the speficied option key to a new value
        function SetOption( $key, $value ) {
            $this->options[$key] = $value;
        }

        # Saves the options to the database
        function SaveOptions() {
            $oldvalue = get_option( 'theme_my_login' );
            if( $oldvalue == $this->options ) {
                return true;
            } else return update_option( 'theme_my_login', $this->options );
        }

        function AddAdminPage(){
            add_submenu_page('options-general.php', __('Theme My Login'), __('Theme My Login'), 'manage_options', __('Theme My Login'), array(&$this, 'AdminPage'));
        }

        function AdminPage(){
            require (WP_PLUGIN_DIR . '/theme-my-login/includes/admin-page.php');
        }
        
        function QueryURL() {
            global $wp_rewrite;

            $permalink = get_permalink( $this->GetOption('page_id') );

            if ($wp_rewrite->using_permalinks())
                return $permalink . '?';
            else
                return $permalink . '&';
        }
        
        function ArgURL($permalink = '', $arg = array()) {
            if (isset($arg)) :
                $count = 1;
                foreach($arg as $key => $value) :
                    if (strpos($permalink, '?') !== false) :
                        if ($count == 1)
                            $permalink .= $key . '=' . $value;
                        else
                            $permalink .= '&' . $key . '=' . $value;
                    else :
                        $permalink .= '?' . $key . '=' . $value;
                    endif;
                    $count++;
                endforeach;
            else :
                $permalink = get_permalink( $this->GetOption('page_id') );
            endif;
            
            return $permalink;
        }
        
        function Init() {
            global $pagenow;

            $this->LoadOptions();
            $permalink = $this->QueryURL();
            
            if ( $this->GetOption('theme_profile') && is_user_logged_in() && is_admin() && current_user_can('edit_posts') === false && !isset($_POST['from']) && $_POST['from'] != 'profile' ) {
                $_GET['profile'] = true;
                $redirect_to = $this->ArgURL($permalink, $_GET);
                wp_safe_redirect($redirect_to);
                exit;
            }

            switch ($pagenow) {
                case 'wp-register.php':
                case 'wp-login.php':
                    if ( is_user_logged_in() && $_REQUEST['action'] != 'logout' )
                        $redirect_to = admin_url();
                    else
                        $redirect_to = $this->ArgURL($permalink, $_GET);
                    wp_safe_redirect($redirect_to);
                    exit;
                    break;
                case 'profile.php':
                    if ( $this->GetOption('theme_profile') && is_user_logged_in() && current_user_can('edit_posts') === false && !isset($_POST['from']) && $_POST['from'] != 'profile' ) {
                        $_GET['profile'] = true;
                        $redirect_to = $this->ArgURL($permalink, $_GET);
                        wp_safe_redirect($redirect_to);
                        exit;
                    }
                    break;
            }
        }

        function ParseRequest() {
            global $wp;
            $is_login = false;
            $page_id = isset($wp->query_vars['page_id']) ? $wp->query_vars['page_id'] : 0;
            $pagename = isset($wp->query_vars['pagename']) ? $wp->query_vars['pagename'] : '';

            if (isset($page_id) && $page_id == $this->GetOption('page_id'))
                $is_login = true;
            elseif (isset($pagename) && $pagename == 'login')
                $is_login = true;
            
            if ($is_login) {
                if ($this->GetOption('theme_profile') && isset($_GET['profile']) && isset($_REQUEST['action']) && $_REQUEST['action'] == 'update' && is_user_logged_in())
                    require (WP_PLUGIN_DIR . '/theme-my-login/includes/profile-actions.php');
                else
                    require (WP_PLUGIN_DIR . '/theme-my-login/includes/wp-login-actions.php');
            }
        }
        
        function TheContent($content) {
            if (strpos($content, '[theme-my-login]') !== false) {
                if ($this->GetOption('theme_profile') && isset($_GET['profile']) && is_user_logged_in())
                    return $this->DisplayProfile();
                else
                    return $this->DisplayLogin();
            } else {
                return $content;
            }
        }
        
        function DisplayProfile() {
            require (WP_PLUGIN_DIR . '/theme-my-login/includes/profile-form.php');
        }
        
        function DisplayLogin() {
            require (WP_PLUGIN_DIR . '/theme-my-login/includes/wp-login-forms.php');
        }
        
        function WPHead() {
            echo '<!-- Theme My Login Version ' . $this->version . ' -->' . "\n";
        }

        function WPTitle($title) {
            if (is_page($this->GetOption('page_id'))) {
                if (isset($_GET['profile']) && is_user_logged_in())
                    return str_replace('%blogname%', get_option('blogname'), $this->GetOption('profile_title'));
                    
                if (!isset($_GET['action']))
                    $_GET['action'] = 'login';
                    
                switch ($_GET['action']) {
                    case 'register':
                        return str_replace('%blogname%', get_option('blogname'), $this->GetOption('register_title'));
                        break;
                    case 'lostpassword':
                    case 'retrievepassword':
                    case 'resetpass':
                    case 'rp':
                        return str_replace('%blogname%', get_option('blogname'), $this->GetOption('password_title'));
                        break;
                    case 'login':
                    default:
                        return str_replace('%blogname%', get_option('blogname'), $this->GetOption('login_title'));
                }
            } return $title;
        }
        
        function TheTitle($title) {
            if ($title == 'Login') {
                if (isset($_GET['profile']) && is_user_logged_in())
                    return $this->GetOption('profile_text');

                if (!isset($_GET['action']))
                    $_GET['action'] = 'login';
                    
                switch ($_GET['action']) {
                    case 'register':
                        return $this->GetOption('register_text');
                        break;
                    case 'lostpassword':
                    case 'retrievepassword':
                    case 'resetpass':
                    case 'rp':
                        return $this->GetOption('password_text');
                        break;
                    case 'login':
                    default:
                        return $this->GetOption('login_text');
                }
            } return $title;
        }
        
        function ListPagesExcludes($excludes) {
            $excludes[] = $this->GetOption( 'page_id' );

            return $excludes;
        }
    }
}

//instantiate the class
if (class_exists('ThemeMyLogin')) {
    $ThemeMyLogin = new ThemeMyLogin();
}

?>
