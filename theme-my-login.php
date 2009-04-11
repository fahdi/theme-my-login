<?php
/*
Plugin Name: Theme My Login
Plugin URI: http://webdesign.jaedub.com/wordpress-plugins/theme-my-login-plugin
Description: Themes the WordPress login, register, forgot password and profile pages to look like the rest of your website.
Version: 2.0.7
Author: Jae Dub
Author URI: http://webdesign.jaedub.com

Version History

1.0.0 - 2009-03-13
    Initial release version
1.0.1 - 2009-03-14
    Made backwards compatible to WordPress 2.5+
1.1.0 - 2009-03-14
    Added custom profile to completely hide the back-end from subscribers
1.1.1 - 2009-03-16
    Prepared plugin for internationalization and fixed a PHP version bug
1.1.2 - 2009-03-20
    Updated to allow customization of text below registration form
1.2.0 - 2009-03-26
    Added capability to customize page titles for all pages affected by plugin
2.0.0 - 2009-03-27
    Completely rewrote plugin to use page template, no more specifying template files & HTML
2.0.1 - 2009-03-30
    Fixed a bug that redirected users who were not yet logged in to profile page
2.0.2 - 2009-03-31
    Fixed a bug that broke registration and broke other plugins using the_content filter
2.0.3 - 2009-04-02
    Fixed various reported bugs and cleaned up code
2.0.4 - 2009-04-03
    Fixed a bug regarding relative URL's in redirection
2.0.5 - 2009-04-04
    Fixed a bug with default redirection and hid the login form from logged in users
2.0.6 - 2009-04-08
    Added the option to turn on/off subscriber profile theming
2.0.7 - 2009-04-10
    Fixed a bug that broke the Featured Content plugin
*/


global $wp_version;

if ($wp_version < '2.6')
    include 'includes/compat.php';

if (!class_exists('ThemeMyLogin')) {
    class ThemeMyLogin {

        var $version = '2.0.7';
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
            
            if ($this->GetOption('login_redirect') == 'wp-admin/')
                $this->SetOption('login_redirect', admin_url());
            if ($this->GetOption('logout_redirect') == 'wp-login.php?loggedout=true')
                $this->SetOption('logout_redirect', site_url('wp-login.php?loggedout=true', 'login'));
            
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
            $this->options['chk_uninstall']     = 0;
            $this->options['version']           = $this->version;
            $this->options['page_id']           = '0';
            $this->options['login_redirect']    = admin_url();
            $this->options['logout_redirect']   = site_url('wp-login.php?loggedout=true', 'login');
            $this->options['login_title']       = '%blogname% &rsaquo; Log In';
            $this->options['login_text']        = 'Log In';
            $this->options['register_title']    = '%blogname% &rsaquo; Register';
            $this->options['register_text']     = 'Register';
            $this->options['register_msg']      = 'A password will be e-mailed to you.';
            $this->options['password_title']    = '%blogname% &rsaquo; Lost Password';
            $this->options['password_text']     = 'Lost Password';
            $this->options['profile_title']     = '%blogname% &rsaquo; Profile';
            $this->options['profile_text']      = 'Your Profile';
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
            include 'includes/admin-page.php';
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
                        $redirect_to = admin_url('profile.php');
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
            $page_id = $wp->query_vars['page_id'];
            
            if ($this->GetOption('page_id') == $page_id) {
                if ($this->GetOption('theme_profile') && $_GET['profile'] && $_REQUEST['action'] == 'update' && is_user_logged_in())
                    include 'includes/profile-actions.php';
                else
                    include 'includes/wp-login-actions.php';
            }
        }
        
        function TheContent($content) {
            if (strpos($content, '[theme-my-login]') !== false) {
                if ($this->GetOption('theme_profile') && $_GET['profile'] && is_user_logged_in())
                    return $this->DisplayProfile();
                else
                    return $this->DisplayLogin();
            } else {
                return $content;
            }
        }
        
        function DisplayProfile() {
            include 'includes/profile-form.php';
        }
        
        function DisplayLogin() {
            include 'includes/wp-login-forms.php';
        }
        
        function WPHead() {
            echo '<!-- Theme My Login Version ' . $this->version . ' -->' . "\n";
        }

        function WPTitle($title) {
            if (is_page($this->GetOption('page_id'))) {
                if ($_GET['profile'] && is_user_logged_in())
                    return str_replace('%blogname%', get_option('blogname'), $this->GetOption('profile_title'));
                    
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
                if ($_GET['profile'] && is_user_logged_in())
                    return $this->GetOption('profile_text');

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
