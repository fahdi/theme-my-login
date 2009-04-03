<?php
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

if ( !function_exists('wp_password_change_notification') ) :
function wp_password_change_notification(&$user) {
    // send a copy of password change notification to the admin
    // but check to see if it's the admin whose password we're changing, and skip this
    if ( $user->user_email != get_option('admin_email') ) {
        $message = sprintf(__('Password Lost and Changed for user: %s'), $user->user_login) . "\r\n";
        wp_mail(get_option('admin_email'), sprintf(__('[%s] Password Lost/Changed'), get_option('blogname')), $message);
    }
}
endif;
?>
