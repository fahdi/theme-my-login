<?php

global $wp_version;
    
require_once (WP_PLUGIN_DIR . '/theme-my-login/includes/wp-login-functions.php');

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
$post_from = isset($_REQUEST['post-from']) ? $_REQUEST['post-from'] : '';

if ( isset($_GET['key']) )
    $action = 'resetpass';

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
    if ($wp_version >= '2.7')
        check_admin_referer('log-out');
    wp_logout();
    
    $redirect_to = ('widget' == $post_from) ? theme_my_login_url(array('loggedout' => 'true')) : site_url('wp-login.php?loggedout=true', 'login');
    if ( isset( $_REQUEST['redirect_to'] ) )
        $redirect_to = $_REQUEST['redirect_to'];

    wp_safe_redirect($redirect_to);
    exit();
    break;
case 'lostpassword' :
case 'retrievepassword' :
    if ( $http_post ) {
        $login_errors = retrieve_password();
        if ( !is_wp_error($login_errors) ) {
            $redirect_to = ('widget' == $post_from) ? theme_my_login_url(array('checkemail' => 'confirm')) : site_url('wp-login.php?checkemail=confirm', 'login');
            wp_redirect($redirect_to);
            exit();
        }
    }

    if ( isset($_GET['error']) && 'invalidkey' == $_GET['error'] )
        $login_errors->add('invalidkey', __('Sorry, that key does not appear to be valid.'));
    break;
case 'resetpass' :
case 'rp' :
    $login_errors = reset_password($_GET['key']);

    if ( ! is_wp_error($login_errors) ) {
        $redirect_to = ('widget' == $post_from) ? theme_my_login_url(array('checkemail' => 'newpass')) : site_url('wp-login.php?checkemail=newpass', 'login');
        wp_redirect($redirect_to);
        exit();
    }

    $redirect_to = ('widget' == $post_from) ? theme_my_login_url(array('action' => 'lostpassword', 'error' => 'invalidkey')) : site_url('wp-login.php?action=lostpassword&error=invalidkey', 'login');
    wp_redirect($redirect_to);
    exit();
    break;
case 'register' :
    if ( !get_option('users_can_register') ) {
        $redirect_to = ('widget' == $post_from) ? theme_my_login_url(array('registration' => 'disabled')) : site_url('wp-login.php?registration=disabled', 'login');
        wp_redirect($redirect_to);
        exit();
    }
    
    if ( $http_post ) {
        require_once (ABSPATH . WPINC . '/registration.php');

        $user_login = $_POST['user_login'];
        $user_email = $_POST['user_email'];
        $login_errors = register_new_user($user_login, $user_email);
        
        if ( !is_wp_error($login_errors) ) {
            $redirect_to = ('widget' == $post_from) ? theme_my_login_url(array('checkemail' => 'registered')) : site_url('wp-login.php?checkemail=registered', 'login');
            wp_redirect($redirect_to);
            exit();
        }
    }
    break;
case 'login' :
    $secure_cookie = '';

    if (isset($_GET['loggedout']))
        unset($_GET['loggedout']);

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
        $redirect_to = admin_url();
    }

    if ( !$secure_cookie && is_ssl() && force_ssl_login() && !force_ssl_admin() )
        $secure_cookie = false;

    $user = wp_signon('', $secure_cookie);
    
    $redirect_to = apply_filters('login_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user);

    if ( !is_wp_error($user) ) {
        // If the user can't edit posts, send them to their profile.
        if ( !$user->has_cap('edit_posts') && ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ) )
            $redirect_to = admin_url('profile.php');
        wp_safe_redirect($redirect_to);
        exit();
    }

    $login_errors = $user;
    break;
} // end action switch
?>
