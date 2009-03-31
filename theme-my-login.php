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
    Completely rewrote plugin to use page template, no more specifying template files & HTML
*/

if (!class_exists('ThemeMyLogin')) {
    class ThemeMyLogin {

        var $options = array();
        var $is_login = false;
        var $errors = '';

        function ThemeMyLogin() {
            $this->__construct();
        }

        function __construct() {
            register_activation_hook ( __FILE__, array( &$this, 'Activate' ) );
            register_deactivation_hook ( __FILE__, array( &$this, 'Deactivate' ) );

            add_action('init', array(&$this, 'Init'));
            add_action('admin_menu', array(&$this, 'AddAdminPage'));
            
            add_action('wp_print_scripts', array(&$this, 'DoLogin'));

            add_filter('wp_title', array(&$this, 'WPTitle'));
            add_filter('the_title', array(&$this, 'TheTitle'));
            
            add_filter('wp_list_pages_excludes', array(&$this, 'ListPagesExcludes'));
            
            if ($_GET['show'] == 'profile') {
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
                'post_content' => 'Please do not edit or remove me!',
                'commen_status' => 'closed',
                'ping_status' => 'closed'
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

            $url = get_permalink( $this->GetOption('page_id') );

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
                $url = $url . 'show=profile';
                if ($_GET['updated'] == true)
                    $url = $url . '&updated=true';
                wp_safe_redirect($url);
                exit;
            }

            switch ($pagenow) {
                case 'wp-register.php':
                case 'wp-login.php':
                    if (isset($_GET)) :
                        $count = 1;
                        foreach($_GET as $key => $value) :
                            if (strpos($url, '?') !== false) :
                                if ($count == 1)
                                    $url .= $key . '=' . $value;
                                else
                                    $url .= '&' . $key . '=' . $value;
                            else :
                                $url .= '?' . $key . '=' . $value;
                            endif;
                            $count++;
                        endforeach;
                    else :
                        $url = get_permalink( $this->GetOption('page_id') );
                    endif;
                    wp_safe_redirect($url);
                    exit;
                    break;
            }
            
            $this->errors = new WP_Error();
            
            $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
            $http_post = ('POST' == $_SERVER['REQUEST_METHOD']);

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
            setcookie(TEST_COOKIE, 'Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN);
            if ( SITECOOKIEPATH != COOKIEPATH )
                setcookie(TEST_COOKIE, 'Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN);
            
            if (isset($_POST['show']) && $_POST['show'] == 'profile') {
                if ( !$user_id ) {
                    $current_user = wp_get_current_user();
                    $user_id = $current_user->ID;
                    if (!$user_id) {
                        wp_redirect('wp-login.php');
                        exit;
                    }
                }
            }
            
            switch ($action) :
            case 'logout':
                $this->Logout();
                break;
            case 'lostpassword':
            case 'retrievepassword':
                require('includes/compat.php');
                if ( $http_post ) {
                    $this->errors = retrieve_password();
                    if ( !is_wp_error($this->errors) ) {
                        wp_redirect('wp-login.php?checkemail=confirm');
                        exit();
                    }
                }
                break;
            case 'register':
                require('includes/compat.php');
                if ( !get_option('users_can_register') ) {
                    wp_redirect('wp-login.php?registration=disabled');
                    exit();
                }

                $user_login = '';
                $user_email = '';
                if ( $http_post ) {
                    require_once( ABSPATH . WPINC . '/registration.php');
    
                    $user_login = $_POST['user_login'];
                    $user_email = $_POST['user_email'];
                    $this->errors = register_new_user($user_login, $user_email);
                    if ( !is_wp_error($this->errors) ) {
                        wp_redirect('wp-login.php?checkemail=registered');
                        exit();
                    }
                }
                break;
            case 'login':
                $secure_cookie = '';

                // If the user wants ssl but the session is not ssl, force a secure cookie.
                if ( !empty($_POST['log']) && !force_ssl_admin() ) {
                    $user_name = sanitize_user($_POST['log']);
                    if ( $user = get_userdatabylogin($user_name) ) {
                        if ( get_user_option('use_ssl', $user->ID) ) {
                            $secure_cookie = true;
                            force_ssl_admin(true);
                        }
                    }
                }

                if ( isset( $_REQUEST['redirect_to'] ) ) {
                    $redirect_to = $_REQUEST['redirect_to'];
                    // Redirect to https if user wants ssl
                    if ( $secure_cookie && false !== strpos($redirect_to, 'wp-admin') )
                        $redirect_to = preg_replace('|^http://|', 'https://', $redirect_to);
                } else {
                    $redirect_to = $this->GetOption('login_redirect');
                }

                if ( !$secure_cookie && is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )
                    $secure_cookie = false;

                $user = wp_signon('', $secure_cookie);

                $redirect_to = apply_filters('login_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user);

                if ( !is_wp_error($user) ) {
                    // If the user can't edit posts, send them to their profile.
                    if ( !$user->has_cap('edit_posts') && ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' ) )
                        $redirect_to = admin_url('profile.php');
                    wp_safe_redirect($redirect_to);
                    exit();
                }
                
                $this->errors = $user;
                
                break;
                
            case 'update':
                if (isset($_POST['from']) && $_POST['from'] == 'profile') {
                
                    if ( !$user_id ) {
                        $current_user = wp_get_current_user();
                        $user_id = $current_user->ID;
                        if (!$user_id) {
                            wp_redirect('wp-login.php');
                            exit;
                        }
                    }

                    //include ABSPATH . '/wp-admin/includes/misc.php';
                    include ABSPATH . '/wp-admin/includes/user.php';
                    include ABSPATH . 'wp-includes/registration-functions.php';
                    
                    check_admin_referer('update-user_' . $user_id);

                    if ( !current_user_can('edit_user', $user_id) )
                        wp_die(__('You do not have permission to edit this user.'));

                    do_action('personal_options_update');

                    $this->errors = edit_user($user_id);

                    if ( !is_wp_error( $this->errors ) ) {
                        $redirect = 'wp-admin/profile.php?updated=true';
                        $redirect = add_query_arg('wp_http_referer', urlencode($wp_http_referer), $redirect);
                        wp_redirect($redirect);
                        exit;
                    }
                }
                break;
            endswitch;
        }
        
        function DoLogin() {
            global $wp_query;
            
            if ((is_page()) && ($wp_query->post->ID == $this->GetOption('page_id'))) :
            
                $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
                $http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
                
                if ( isset($_GET['key']) )
                    $action = 'resetpass';
                    
                if ($_GET['show'] == 'profile') {
                    add_filter('the_content', array(&$this, 'Profile'));
                } else {

                    switch ($action) {
                    case 'lostpassword' :
                    case 'retrievepassword' :
                        add_filter('the_content', array(&$this, 'LostPassword'));
                        break;
                    case 'resetpass' :
                    case 'rp' :
                        $this->ResetPass();
                        break;
                    case 'register' :
                        add_filter('the_content', array(&$this, 'Register'));
                        break;
                    case 'login' :
                    default:
                        add_filter('the_content', array(&$this, 'Login'));
                        break;
                    }
                }
            endif;
        }

        function WPTitle($title) {
            if (is_page($this->GetOption('page_id'))) {
                if ($_GET['show'] == 'profile')
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
                if ($_GET['show'] == 'profile')
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
        
        function Logout() {
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
        }
        
        function LostPassword() {
            include 'includes/lost-password.php';
        }
        
        function ResetPass() {
            if (!function_exists('reset_password')) :
            function reset_password($key) {
                global $wpdb;

                require('includes/compat.php');

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
        }
        
        function Register() {
            include 'includes/register.php';
        }
        
        function Login() {
            include 'includes/login.php';
        }
        
        function Profile() {
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
