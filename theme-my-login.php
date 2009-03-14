<?php
/*
Plugin Name: Theme My Login
Plugin URI: http://webdesign.jaedub.com/wordpress-plugins/theme-my-login-plugin
Description: This plugin creates custom login and register pages to replace the wp-login and wp-register pages.
Version: 1.0.1
Author: Jae Dub
Author URI: http://webdesign.jaedub.com

Version History

1.0.0 - 2009-03-13
    Initial release version
1.0.1 - 2009-03-14
    Made backwards compatible to WordPress 2.5+
    
*/

if (!class_exists('ThemeMyLogin')) {
    class ThemeMyLogin {
    
        var $options = array();

        function ThemedRegLog() {
            $this->__construct();
        }

        function __construct() {
            register_activation_hook ( __FILE__, array( &$this, 'Activate' ) );
            register_deactivation_hook ( __FILE__, array( &$this, 'Deactivate' ) );
            
            add_action('init', array(&$this, 'Init'));
            add_action('admin_menu', array(&$this, 'AddAdminPage'));
            
            $this->LoadOptions();
        }
        
        function Activate() {

        }
        
        function Deactivate() {
            delete_option('tml_options');
        }
        
        # Sets up default options
        function InitOptions() {
            $this->options['tml_version']           ='1.0';
            $this->options['tml_uninstall']         = 0;
            $this->options['tml_login_redirect']    = 'wp-admin/';
            $this->options['tml_logout_redirect']   = 'wp-login.php?loggedout=true';
            $this->options['tml_header_files']      = array('header.php');
            $this->options['tml_header_html']       = '    <div id="content" class="narrowcolumn">' . "\n";
            $this->options['tml_footer_files']      = array('sidebar.php', 'footer.php');
            $this->options['tml_footer_html']       = '    </div>' . "\n";
            $this->options['tml_login_text']        = 'Log In';
            $this->options['tml_register_text']     = 'Register';
            $this->options['tml_password_text']     = 'Reset Password';
        }

        # Loads options from database
        function LoadOptions() {

            $this->InitOptions();

            $storedoptions = get_option( 'tml_options' );
            if ( $storedoptions && is_array( $storedoptions ) ) {
                foreach ( $storedoptions as $key => $value ) {
                    $this->options[$key] = $value;
                }
            } else update_option( 'tml_options', $this->options );
        }

        # Returns option value for given key
        function GetOption( $key ) {
            $key = "tml_" . $key;
            if ( array_key_exists( $key, $this->options ) ) {
                return $this->options[$key];
            } else return null;
        }

        # Sets the speficied option key to a new value
        function SetOption( $key, $value ) {
            if ( strstr( $key, 'tml_' )!== 0 ) $key = 'tml_' . $key;

            $this->options[$key] = $value;
        }

        # Saves the options to the database
        function SaveOptions() {
            $oldvalue = get_option( 'tml_options' );
            if( $oldvalue == $this->options ) {
                return true;
            } else return update_option( 'tml_options', $this->options );
        }

        function AddAdminPage(){
            add_submenu_page('options-general.php', "Theme My Login", "Theme My Login", 10, "Theme My Login", array(&$this, 'AdminPage'));
        }

        function AdminPage(){
            if ( $_POST ) {
                if ( !current_user_can('manage_options') )
                    die( __( 'Cheatin&#8217; huh?' ) );

                check_admin_referer('tml-settings');
                
                $error = "";
                $header_files = trim(str_replace("\r\n", "\n", stripslashes($_POST['header_files'])));
                $header_files = explode("\n", $header_files);
                foreach((array)$header_files as $header_file) {
                    if ( !file_exists(TEMPLATEPATH . '/' . $header_file) ) {
                        $error .= "<li>The header file {$header_file} doesn't exist in your theme (template) directory, please verify the name and try again.</li>";
                    }
                }
                if ( empty($error) )
                    $this->SetOption('header_files', $header_files);

                $footer_files = trim(str_replace("\r\n", "\n", stripslashes($_POST['footer_files'])));
                $footer_files = explode("\n", $footer_files);
                foreach((array)$footer_files as $footer_file) {
                    if ( !file_exists(TEMPLATEPATH . '/' . $footer_file) ) {
                        $error .= "<li>The footer file {$footer_file} doesn't exist in your theme (template) directory, please verify the name and try again.</li>";
                    }
                }
                if ( empty($error) ) {
                    $this->SetOption('footer_files', $footer_files);
                    $success = "<li>Custom login and registration form options updated successfully!</li>";
                 }

                $this->SetOption('login_text', stripslashes($_POST['login_text']));
                $this->SetOption('register_text', stripslashes($_POST['register_text']));
                $this->SetOption('password_text', stripslashes($_POST['password_text']));
                $this->SetOption('login_redirect', stripslashes($_POST['login_redirect']));
                $this->SetOption('logout_redirect', stripslashes($_POST['logout_redirect']));
                $this->SetOption('header_html', stripslashes($_POST['header_html']));
                $this->SetOption('footer_html', stripslashes($_POST['footer_html']));
                $this->SaveOptions();

                $success = "<li>Settings updated successfully!</li>";

            } //end if

            ?>
            <div class="wrap">
            <?php if ( strlen($success) > 0 ) { ?>
                <div id="message" class="updated fade">
                    <p><strong><?php _e("<ul>{$success}</ul>"); ?></strong></p>
                </div>
            <?php } ?>
                <div id="icon-options-general" class="icon32"><br /></div>
                <h2><?php _e('Theme My Login Settings'); ?></h2>

                <form action="" method="post" id="tml-settings">
            <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('tml-settings'); ?>
                <h3>Redirection Settings</h3>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="login_redirect">Redirect on Login</label></th>
                        <td>
                            <input name="login_redirect" type="text" id="login_redirect" value="<?php echo( htmlspecialchars ( $this->GetOption('login_redirect') ) ); ?>" class="regular-text" />
                            <span class="setting-description">Defaults to 'wp-admin/'.</span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="logout_redirect">Redirect on Logout</label></th>
                        <td>
                            <input name="logout_redirect" type="text" id="logout_redirect" value="<?php echo( htmlspecialchars ( $this->GetOption('logout_redirect') ) ); ?>" class="regular-text" />
                            <span class="setting-description">Defaults to 'wp-login.php?loggedout=true'.</span>
                        </td>
                    </tr>
                </table>

                <h3>Template Settings</h3>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="register_text">Register Text</label></th>
                        <td>
                            <input name="register_text" type="text" id="register_text" value="<?php echo( htmlspecialchars ( $this->GetOption('register_text') ) ); ?>" class="regular-text" />
                            <span class="setting-description">This will appear above the registration form.</span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="login_text">Login Text</label></th>
                        <td>
                            <input name="login_text" type="text" id="login_text" value="<?php echo( htmlspecialchars ( $this->GetOption('login_text') ) ); ?>" class="regular-text" />
                            <span class="setting-description">This will appear above the login form.</span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="password_text">Forgot Password Text</label></th>
                        <td>
                            <input name="password_text" type="text" id="password_text" value="<?php echo( htmlspecialchars ( $this->GetOption('password_text') ) ); ?>" class="regular-text" />
                            <span class="setting-description">This will appear above the forgot password form.</span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="login_redirect">Template Header Files</label></th>
                        <td>
                            <textarea name="header_files" id="header_files" rows="5" cols="50" class="large-text"><?php echo $this->GetOption('header_files') ? htmlspecialchars(implode("\n", $this->GetOption('header_files'))) : ''; ?></textarea>
                            <span class="setting-description">Enter each header file used in your template, one per line. Typically, this is 'header.php'.</span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="header_html">Template HTML After Header</label></th>
                        <td>
                            <textarea name="header_html" id="header_html" rows="5" cols="50" class="large-text"><?php echo $this->GetOption('header_html') ? htmlspecialchars($this->GetOption('header_html')) : ''; ?></textarea>
                            <span class="setting-description">Enter the HTML that appears after the get_header() function and before the page code.</span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="footer_html">Template HTML Before Footer</label></th>
                        <td>
                            <textarea name="footer_html" id="footer_html" rows="5" cols="50" class="large-text"><?php echo $this->GetOption('footer_html') ? htmlspecialchars($this->GetOption('footer_html')) : ''; ?></textarea>
                            <span class="setting-description">Enter footer HTML that appears between the page code and the get_sidebar()/get_footer() functions.</span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="footer_files">Template Footer Files</label></th>
                        <td>
                            <textarea name="footer_files" id="footer_files" rows="5" cols="50" class="large-text"><?php echo $this->GetOption('footer_files') ? htmlspecialchars(implode("\n", $this->GetOption('footer_files'))) : ''; ?></textarea>
                            <span class="setting-description">Enter each footer file used in your template, one per line. Typically, this is 'sidebar.php' and 'footer.php'.</span>
                        </td>
                    </tr>
                </table>
                <p class="submit"><input type="submit" name="Submit" class="button-primary" value="Save Changes" />
                </form>
            </div>
            <?php
        }
        
        function Init() {
            global $pagenow;

            switch ($pagenow) {
                case "wp-login.php":
                case "wp-register.php":
                    $this->DoLogin();
                break;
            }
        }
        
        function DoHeader($title = 'Log In', $message = '', $wp_error = '') {
            global $error;

            if ( empty($wp_error) )
                $wp_error = new WP_Error();
                
            $header_files = $this->GetOption('header_files');
            foreach((array)$header_files as $header_file)
                include(TEMPLATEPATH . '/' . $header_file);

            echo $this->GetOption('header_html');
            ?>
            
            <div id="login">
                <h2><?php _e($title); ?></h2>
                
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
                if ( $http_post ) {
                    $errors = retrieve_password();
                    if ( !is_wp_error($errors) ) {
                        wp_redirect('wp-login.php?checkemail=confirm');
                        exit();
                    }
                }

                if ( isset($_GET['error']) && 'invalidkey' == $_GET['error'] ) $errors->add('invalidkey', __('Sorry, that key does not appear to be valid.'));

                do_action('lost_password');
                $this->DoHeader(__($this->GetOption('password_text')), '<p class="message">' . __('Please enter your username or e-mail address. You will receive a new password via e-mail.') . '</p>', $errors);

                $user_login = isset($_POST['user_login']) ? stripslashes($_POST['user_login']) : '';

            ?>

            <form name="lostpasswordform" id="lostpasswordform" action="<?php echo site_url('wp-login.php?action=lostpassword', 'login_post') ?>" method="post">
                <p>
                    <label><?php _e('Username or E-mail:') ?><br />
                    <input type="text" name="user_login" id="user_login" class="input" value="<?php echo attribute_escape($user_login); ?>" size="20" tabindex="10" /></label>
                </p>
            <?php do_action('lostpassword_form'); ?>
                <p class="submit"><input type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Get New Password'); ?>" tabindex="100" /></p>
            </form>

            <ul class="nav">
            <li><a href="<?php echo site_url('wp-login.php', 'login') ?>"><?php _e('Log in') ?></a></li>
            <?php if (get_option('users_can_register')) : ?>
            <li><a href="<?php echo site_url('wp-login.php?action=register', 'login') ?>"><?php _e('Register') ?></li>
            <?php endif; ?>
            </ul>

        </div>

            <?php
                echo $this->GetOption('footer_html');
                $footer_files = $this->GetOption('footer_files');
                foreach((array)$footer_files as $footer_file)
                    include(TEMPLATEPATH . '/' . $footer_file);

                die();
            break;

            case 'resetpass' :
            case 'rp' :
                $errors = reset_password($_GET['key']);

                if ( ! is_wp_error($errors) ) {
                    wp_redirect('wp-login.php?checkemail=newpass');
                    exit();
                }

                wp_redirect('wp-login.php?action=lostpassword&error=invalidkey');
                exit();

            break;

            case 'register' :
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
                    $errors = register_new_user($user_login, $user_email);
                    if ( !is_wp_error($errors) ) {
                        wp_redirect('wp-login.php?checkemail=registered');
                        exit();
                    }
                }

                $this->DoHeader(__($this->GetOption('register_text')), '', $errors);
            ?>

            <form name="registerform" id="registerform" action="<?php echo site_url('wp-login.php?action=register', 'login_post') ?>" method="post">
                <p>
                    <label><?php _e('Username') ?><br />
                    <input type="text" name="user_login" id="user_login" class="input" value="<?php echo attribute_escape(stripslashes($user_login)); ?>" size="20" tabindex="10" /></label>
                </p>
                <p>
                    <label><?php _e('E-mail') ?><br />
                    <input type="text" name="user_email" id="user_email" class="input" value="<?php echo attribute_escape(stripslashes($user_email)); ?>" size="25" tabindex="20" /></label>
                </p>
            <?php do_action('register_form'); ?>
                <p id="reg_passmail"><?php _e('A password will be e-mailed to you.') ?></p>
                <p class="submit"><input type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Register'); ?>" tabindex="100" /></p>
            </form>

            <ul class="nav">
            <li><a href="<?php echo site_url('wp-login.php', 'login') ?>"><?php _e('Log in') ?></a></li>
            <li><a href="<?php echo site_url('wp-login.php?action=lostpassword', 'login') ?>" title="<?php _e('Password Lost and Found') ?>"><?php _e('Lost your password?') ?></a></li>
            </ul>

        </div>

            <?php
                echo $this->GetOption('footer_html');
                $footer_files = $this->GetOption('footer_files');
                foreach((array)$footer_files as $footer_file)
                    include(TEMPLATEPATH . '/' . $footer_file);

                die();
            break;

            case 'login' :
            default:
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

                $errors = $user;
                // Clear errors if loggedout is set.
                if ( !empty($_GET['loggedout']) )
                    $errors = new WP_Error();

                // If cookies are disabled we can't log in even with a valid user+pass
                if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
                    $errors->add('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress."));

                // Some parts of this script use the main login form to display a message
                if        ( isset($_GET['loggedout']) && TRUE == $_GET['loggedout'] )            $errors->add('loggedout', __('You are now logged out.'), 'message');
                elseif    ( isset($_GET['registration']) && 'disabled' == $_GET['registration'] )    $errors->add('registerdisabled', __('User registration is currently not allowed.'));
                elseif    ( isset($_GET['checkemail']) && 'confirm' == $_GET['checkemail'] )    $errors->add('confirm', __('Check your e-mail for the confirmation link.'), 'message');
                elseif    ( isset($_GET['checkemail']) && 'newpass' == $_GET['checkemail'] )    $errors->add('newpass', __('Check your e-mail for your new password.'), 'message');
                elseif    ( isset($_GET['checkemail']) && 'registered' == $_GET['checkemail'] )    $errors->add('registered', __('Registration complete. Please check your e-mail.'), 'message');

                $this->DoHeader(__($this->GetOption('login_text')), '', $errors);

                if ( isset($_POST['log']) )
                    $user_login = ( 'incorrect_password' == $errors->get_error_code() || 'empty_password' == $errors->get_error_code() ) ? attribute_escape(stripslashes($_POST['log'])) : '';
            ?>

            <?php if ( !isset($_GET['checkemail']) || !in_array( $_GET['checkemail'], array('confirm', 'newpass') ) ) : ?>
            <form name="loginform" id="loginform" action="<?php echo site_url('wp-login.php', 'login_post') ?>" method="post">
                <p>
                    <label><?php _e('Username') ?><br />
                    <input type="text" name="log" id="user_login" class="input" value="<?php echo $user_login; ?>" size="20" tabindex="10" /></label>
                </p>
                <p>
                    <label><?php _e('Password') ?><br />
                    <input type="password" name="pwd" id="user_pass" class="input" value="" size="20" tabindex="20" /></label>
                </p>
            <?php do_action('login_form'); ?>
                <p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" /> <?php _e('Remember Me'); ?></label></p>
                <p class="submit">
                    <input type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Log In'); ?>" tabindex="100" />
                    <input type="hidden" name="redirect_to" value="<?php echo attribute_escape($redirect_to); ?>" />
                    <input type="hidden" name="testcookie" value="1" />
                </p>
            </form>
            <?php endif; ?>

            <ul class="nav">
            <?php if ( isset($_GET['checkemail']) && in_array( $_GET['checkemail'], array('confirm', 'newpass') ) ) : ?>
            <?php elseif (get_option('users_can_register')) : ?>
            <li><a href="<?php echo site_url('wp-login.php?action=register', 'login') ?>"><?php _e('Register') ?></a></li>
            <?php endif; ?>
            <li><a href="<?php echo site_url('wp-login.php?action=lostpassword', 'login') ?>" title="<?php _e('Password Lost and Found') ?>"><?php _e('Lost your password?') ?></a></li>
            </ul>

        </div>

            <?php
                echo $this->GetOption('footer_html');
                $footer_files = $this->GetOption('footer_files');
                foreach((array)$footer_files as $footer_file)
                    include(TEMPLATEPATH . '/' . $footer_file);

                die();
            break;
            endswitch;
        }
    }
}

//instantiate the class
if (class_exists('ThemeMyLogin')) {
    $ThemeMyLogin = new ThemeMyLogin();
}

if ( !function_exists('is_ssl') ) :
function is_ssl() {
    if ( isset($_SERVER['HTTPS']) ) {
        if ( 'on' == strtolower($_SERVER['HTTPS']) )
            return true;
        if ( '1' == $_SERVER['HTTPS'] )
            return true;
    } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
        return true;
    }
    return false;
}
endif;

if ( !function_exists('site_url') ) :
function site_url($path = '', $scheme = null) {
    // should the list of allowed schemes be maintained elsewhere?
    $orig_scheme = $scheme;
    if ( !in_array($scheme, array('http', 'https')) ) {
        if ( ('login_post' == $scheme) && ( force_ssl_login() || force_ssl_admin() ) )
            $scheme = 'https';
        elseif ( ('login' == $scheme) && ( force_ssl_admin() ) )
            $scheme = 'https';
        elseif ( ('admin' == $scheme) && force_ssl_admin() )
            $scheme = 'https';
        else
            $scheme = ( is_ssl() ? 'https' : 'http' );
    }

    $url = str_replace( 'http://', "{$scheme}://", get_option('siteurl') );

    if ( !empty($path) && is_string($path) && strpos($path, '..') === false )
        $url .= '/' . ltrim($path, '/');

    return apply_filters('site_url', $url, $path, $orig_scheme);
}
endif;

if ( !function_exists('admin_url') ) :
function admin_url($path = '') {
    $url = site_url('wp-admin/', 'admin');

    if ( !empty($path) && is_string($path) && strpos($path, '..') === false )
        $url .= ltrim($path, '/');

    return $url;
}
endif;

if ( !function_exists('force_ssl_login') ) :
function force_ssl_login($force = '') {
    static $forced;

    if ( '' != $force ) {
        $old_forced = $forced;
        $forced = $force;
        return $old_forced;
    }

    return $forced;
}
endif;

if ( !function_exists('force_ssl_admin') ) :
function force_ssl_admin($force = '') {
    static $forced;

    if ( '' != $force ) {
        $old_forced = $forced;
        $forced = $force;
        return $old_forced;
    }

    return $forced;
}
endif;

?>
