<?php

function wdbj_tml_the_title($title, $post_id = '') {
	global $wdbj_tml_doing_pagelist;
	
    if ( is_admin() && !defined('IS_PROFILE_PAGE') )
        return $title;
		
	// No post ID until WP 3.0!
	if ( empty( $post_id ) ) {
		global $wpdb;
		$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s", $title ) );
	}
		
    if ( wdbj_tml_get_option('page_id')	== $post_id ) {
		if ( $wdbj_tml_doing_pagelist ) {
			$title = is_user_logged_in() ? __('Log Out', 'theme-my-login') : __('Log In', 'theme-my-login');
		} else {
			require_once (WP_PLUGIN_DIR . '/theme-my-login/includes/template-functions.php');
			$action = ( 'tml-page' == wdbj_tml_get_var('request_instance') ) ? wdbj_tml_get_var('request_action') : 'login';
			$title = wdbj_tml_get_title($action);
		}
    }
    return $title;
}

function wdbj_tml_single_post_title($title) {
    if ( is_page(wdbj_tml_get_option('page_id')) ) {
        require_once (WP_PLUGIN_DIR . '/theme-my-login/includes/template-functions.php');
        $action = ( 'tml-page' == wdbj_tml_get_var('request_instance') ) ? wdbj_tml_get_var('request_action') : 'login';
        $title = wdbj_tml_get_title($action);
    }
    return $title;
}

function wdbj_tml_site_url($url, $path, $orig_scheme) {
    if ( strpos($url, 'wp-login.php') !== false && !isset($_REQUEST['interim-login']) ) {
        $orig_url = $url;
        $url = get_permalink(wdbj_tml_get_option('page_id'));
        if ( strpos($orig_url, '?') ) {
            $query = substr($orig_url, strpos($orig_url, '?') + 1);
            parse_str($query, $r);
            $url = add_query_arg($r, $url);
        }
    }
    return $url;
}

function wdbj_tml_list_pages_excludes($exclude_array) {
	global $wdbj_tml_doing_pagelist;
	$wdbj_tml_doing_pagelist = true;
	if ( !wdbj_tml_get_option('show_page') )
		$exclude_array[] = wdbj_tml_get_option('page_id');
	return $exclude_array;
}

function wdbj_tml_list_pages($output) {
	global $wdbj_tml_doing_pagelist;
	$wdbj_tml_doing_pagelist = false;
	return $output;
}

function wdbj_tml_page_link($link, $id) {
	global $wdbj_tml_doing_pagelist;
	if ( !$wdbj_tml_doing_pagelist )
		return $link;
	if ( $id == wdbj_tml_get_option('page_id') ) {
		if ( is_user_logged_in() && ( !isset($_REQUEST['action']) || 'logout' != $_REQUEST['action'] ) )
			$link = wp_nonce_url(add_query_arg('action', 'logout', $link), 'log-out');
	}
	return $link;
}

function wdbj_tml_shortcode($atts = '') {
    require_once( WP_PLUGIN_DIR . '/theme-my-login/includes/template-functions.php' );
    
    if ( empty($atts['instance_id']) )
        $atts['instance_id'] = wdbj_tml_get_new_instance();

    if ( wdbj_tml_get_var('request_instance') == $atts['instance_id'] )
        $atts['is_active'] = 1;

    wdbj_tml_set_var(shortcode_atts(wdbj_tml_get_display_options(), $atts), 'current_instance');
    return wdbj_tml_display();
}

function wdbj_tml_page_shortcode($atts = '') {

	if ( !is_array($atts) )
		$atts = array();

	$atts['instance_id'] = 'tml-page';
	
	if ( !isset($atts['show_title']) )
		$atts['show_title'] = 0;
	if ( !isset($atts['before_widget']) )
		$atts['before_widget'] = '';
	if ( !isset($atts['after_widget']) )
		$atts['after_widget'] = '';
		
	return wdbj_tml_shortcode($atts);
}

?>