<?php

global $wp_version;

if ($wp_version < '2.6')
    include 'compat.php';
    
require('wp-login-functions.php');

if ( force_ssl_admin() && !is_ssl() ) {
    if ( 0 === strpos($_SERVER['REQUEST_URI'], 'http') ) {
        wp_redirect(preg_replace('|^http://|', 'https://', $_SERVER['REQUEST_URI']));
        exit();
    } else {
        wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
    }
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$this->errors = new WP_Error();

if ( isset($_GET['key']) )
    $action = 'resetpass';

nocache_headers();

header('Content-Type: '.get_bloginfo('html_type').'; charset='.get_bloginfo('charset'));

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
switch ($action) {
case 'logout' :
    if ($wp_version >= '2.6')
        check_admin_referer('log-out');
    wp_logout();

    $redirect_to = 'wp-login.php?loggedout=true';
    if ( isset( $_REQUEST['redirect_to'] ) )
        $redirect_to = $_REQUEST['redirect_to'];

    wp_safe_redirect($redirect_to);
    exit();
    break;
case 'lostpassword' :
case 'retrievepassword' :
    if ( $http_post ) {
        $this->errors = retrieve_password();
        if ( !is_wp_error($this->errors) ) {
            wp_redirect('wp-login.php?checkemail=confirm');
            exit();
        }
    }

    if ( isset($_GET['error']) && 'invalidkey' == $_GET['error'] )
        $this->errors->add('invalidkey', __('Sorry, that key does not appear to be valid.'));
    break;
case 'resetpass' :
case 'rp' :
    $this->errors = reset_password($_GET['key']);

    if ( ! is_wp_error($this->errors) ) {
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
case 'login' :
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
} // end action switch
?>
