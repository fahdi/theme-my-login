<?php
/*
Plugin Name: Theme My Login
Plugin URI: http://webdesign.jaedub.com/wordpress-plugins/theme-my-login-plugin
Description: Themes the WordPress login, register, forgot password and profile pages to look like the rest of your website.
Version: 2.0
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
    Completely revamped plugin to use page template, no more specifying template files & HTML
*/

if (!class_exists('ThemeMyLogin')) {
    class ThemeMyLogin {

        var $options = array();

        function ThemeMyLogin() {
            $this->__construct();
        }

        function __construct() {
            register_activation_hook ( __FILE__, array( &$this, 'Activate' ) );
            register_deactivation_hook ( __FILE__, array( &$this, 'Deactivate' ) );

            add_action('init', array(&$this, 'Init'));
            add_action('admin_menu', array(&$this, 'AddAdminPage'));

            add_filter('wp_title', array(&$this, 'WPTitle'));
            add_filter('the_title', array(&$this, 'TheTitle'));
            add_filter('the_content', array(&$this, 'TheContent'));
            
            add_filter('wp_list_pages_excludes', array(&$this, 'ListPagesExcludes'));

            if ($_GET['profile'] == true) {
                add_action('wp_head', array(&$this, 'ProfileJS'));
                add_action('wp_head', array(&$this, 'ProfileCSS'));
                wp_enqueue_script('jquery');
            }
        }

        function Activate() {
            if (get_option('tml_options'))
                delete_option('tml_options');
                
            $theme_my_login = get_page_by_title('Login');
            if (!$theme_my_login) {
                $insert = array(
                'post_title' => 'Login',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => 1,
                'post_content' => 'Please do not edit or remove me!'
                );

                $theme_my_login = wp_insert_post($insert);
            } else $theme_my_login = $theme_my_login->ID;
            
            $this->SetOption( 'page_id', $theme_my_login );
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
            $this->options['version']           = '2.0';
            $this->options['chk_uninstall']     = 0;
            $this->options['page_id']           = '0';
            $this->options['login_redirect']    = 'wp-admin/';
            $this->options['logout_redirect']   = 'wp-login.php?loggedout=true';
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

            $url = get_permalink( $this->GetOption( 'page_id' ) );

            if ($wp_rewrite->using_permalinks())
                return $url . '?';
            else
                return $url . '&';
        }

        function Init() {
            global $pagenow;
            
            $this->LoadOptions();

            $url = $this->QueryURL();

            if ( is_admin() && current_user_can('edit_posts') === false && !isset($_POST['from']) && $_POST['from'] != 'profile' ) {
                $redirect_to = $url . 'profile=true';
                if ($_GET['updated'] == true)
                    $redirect_to = $redirect_to . '&updated=true';
                wp_safe_redirect($redirect_to);
                die();
            }

            switch ($pagenow) {
                case 'wp-register.php':
                case 'wp-login.php':
                    if (isset($_GET))
                        $count = 1;
                        foreach($_GET as $key => $value) {
                            if (strpos($url, '?') !== false)
                                if ($count == 1)
                                    $url .= $key . '=' . $value;
                                else
                                    $url .= '&' . $key . '=' . $value;
                            else
                                $url .= '?' . $key . '=' . $value;
                                
                            $count++;
                        }
                        wp_safe_redirect($url);
                        die();
                    break;
            }
        }
        
        function TheContent($content) {
            if (is_page($this->GetOption('page_id'))) {
                if (isset($_GET['profile']))
                    $this->DoProfile();
                else
                    $this->DoLogin();
            } else return $content;
        }

        function WPTitle($title) {
            if ( strpos($title, 'Theme My Login') !== false ) {
                if (isset($_GET['profile']))
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
            if ( strpos($title, 'Theme My Login') !== false ) {
                if (isset($_GET['profile']))
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

        function DoHeader($message = '', $wp_error = '') {
            global $error;

            if ( empty($wp_error) )
                $wp_error = new WP_Error();
            ?>

            <div id="login">

            <?php

            if ( !empty( $message ) ) echo apply_filters('login_message', $message) . "\n";

            // Incase a plugin uses $error rather than the $errors object
            if ( !empty( $error ) ) {
                $wp_error->add('error', $error);
                unset($error);
            }

            if ( $wp_error->get_error_code() ) {
                $errors = '';
                $messages = '';
                foreach ( $wp_error->get_error_codes() as $code ) {
                    $severity = $wp_error->get_error_data($code);
                    foreach ( $wp_error->get_error_messages($code) as $error ) {
                        if ( 'message' == $severity )
                            $messages .= '    ' . $error . "<br />\n";
                        else
                            $errors .= '    ' . $error . "<br />\n";
                    }
                }
                if ( !empty($errors) )
                    echo '<div id="login_error">' . apply_filters('login_errors', $errors) . "</div>\n";
                if ( !empty($messages) )
                    echo '<p class="message">' . apply_filters('login_messages', $messages) . "</p>\n";

            }
        }

        function DoLogin() {
            
            $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
            $errors = new WP_Error();

            if ( isset($_GET['key']) )
                $action = 'resetpass';

            nocache_headers();

            if ( defined('RELOCATE') ) { // Move flag is set
                if ( isset( $_SERVER['PATH_INFO'] ) && ($_SERVER['PATH_INFO'] != $_SERVER['PHP_SELF']) )
                    $_SERVER['PHP_SELF'] = str_replace( $_SERVER['PATH_INFO'], '', $_SERVER['PHP_SELF'] );

                $schema = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
                if ( dirname($schema . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']) != get_option('siteurl') )
                    update_option('siteurl', dirname($schema . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']) );
            }

            //Set a cookie now to see if they are supported by the browser.
            setcookie(TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN);
            if ( SITECOOKIEPATH != COOKIEPATH )
                setcookie(TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN);

            $http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
            switch ($action) :

            case 'logout' :
                if ($wp_version > '2.6')
                    check_admin_referer('log-out');
                wp_logout();

                if ($this->GetOption('logout_redirect')) {
                    $redirect_to = $this->GetOption('logout_redirect');
                } else {
                    if ( isset( $_REQUEST['redirect_to'] ) )
                        $redirect_to = $_REQUEST['redirect_to'];
                    else
                        $redirect_to = 'wp-login.php';
                }

                wp_safe_redirect($redirect_to);
                exit();
                break;

            case 'lostpassword' :
            case 'retrievepassword' :
                include 'includes/lost-password.php';
                break;

            case 'resetpass' :
            case 'rp' :
            
                if (!function_exists('reset_password')) :
                function reset_password($key) {
                    global $wpdb;
                    
                    require('includes/wp271-functions.php');

                    $key = preg_replace('/[^a-z0-9]/i', '', $key);

                    if ( empty( $key ) )
                        return new WP_Error('invalid_key', __('Invalid key'));

                    $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s", $key));
                    if ( empty( $user ) )
                        return new WP_Error('invalid_key', __('Invalid key'));

                    do_action('password_reset', $user);

                    // Generate something random for a password...
                    $new_pass = wp_generate_password();
                    wp_set_password($new_pass, $user->ID);
                    $message  = sprintf(__('Username: %s'), $user->user_login) . "\r\n";
                    $message .= sprintf(__('Password: %s'), $new_pass) . "\r\n";
                    $message .= site_url('wp-login.php', 'login') . "\r\n";

                    if (  !wp_mail($user->user_email, sprintf(__('[%s] Your new password'), get_option('blogname')), $message) )
                        die('<p>' . __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') . '</p>');

                    if ( !function_exists('wp_password_change_notification') ) :
                    function wp_password_change_notification(&$user) {
                        if ( $user->user_email != get_option('admin_email') ) {
                            $message = sprintf(__('Password Lost and Changed for user: %s'), $user->user_login) . "\r\n";
                            wp_mail(get_option('admin_email'), sprintf(__('[%s] Password Lost/Changed'), get_option('blogname')), $message);
                        }
                    }
                    endif;

                    wp_password_change_notification($user);

                    return true;
                }
                endif;
                
                $errors = reset_password($_GET['key']);

                if ( ! is_wp_error($errors) ) {
                    wp_redirect('wp-login.php?checkemail=newpass');
                    exit();
                }

                wp_redirect('wp-login.php?action=lostpassword&error=invalidkey');
                exit();

                break;

            case 'register' :
                include 'includes/register.php';
                break;

            case 'login' :
            default:
                include 'includes/login.php';
                break;
            endswitch;
        }
        
        function DoProfile() {
            include 'includes/profile.php';
        }
        
        function ProfileJS ( ) {
        ?>
            <script type="text/javascript">
                function update_nickname ( ) {

                    var nickname = jQuery('#nickname').val();
                    var display_nickname = jQuery('#display_nickname').val();

                    if ( nickname == '' ) {
                        jQuery('#display_nickname').remove();
                    }
                    jQuery('#display_nickname').val(nickname).html(nickname);

                }

                jQuery(function($) {
                    $('#pass1').keyup( check_pass_strength )
                    $('.color-palette').click(function(){$(this).siblings('input[name=admin_color]').attr('checked', 'checked')});
                } );

                jQuery(document).ready( function() {
                    jQuery('#pass1,#pass2').attr('autocomplete','off');
                    jQuery('#nickname').blur(update_nickname);
                });
            </script>
        <?php
        }

        function ProfileCSS ( ) {
        ?>
            <style type="text/css">
            table.form-table th, table.form-table td {
                padding: 0;
            }
            table.form-table th {
                width: 150px;
                vertical-align: text-top;
                text-align: left;
            }
            p.message {
                padding: 3px 5px;
                background-color: lightyellow;
                border: 1px solid yellow;
            }
            #display_name {
                width: 250px;
            }
            .field-hint {
                display: block;
                clear: both;
            }
            </style>
        <?php
        }
    }
}

//instantiate the class
if (class_exists('ThemeMyLogin')) {
    $ThemeMyLogin = new ThemeMyLogin();
}

?>
