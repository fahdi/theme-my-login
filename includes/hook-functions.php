<?php

function wdbj_tml_the_title($title, $post_id = '') {
	$tml_page = wdbj_tml_get_option('page_id');
    if ( is_admin() && ! is_page($tml_page) )
        return $title;
    if ( $tml_page == $post_id ) {
        require_once (WP_PLUGIN_DIR . '/theme-my-login/includes/template-functions.php');
        $action = ( 'tml-page' == wdbj_tml_get_var('request_instance') ) ? wdbj_tml_get_var('request_action') : 'login';
        $title = wdbj_tml_get_title($action);
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
	if ( !wdbj_tml_get_option('show_page') )
		$exclude_array[] = wdbj_tml_get_option('page_id');
	return $exclude_array;
}

function wdbj_tml_page_link($link, $id) {
	if ( $id == wdbj_tml_get_option('page_id') ) {
		if ( is_user_logged_in() )
			$link = wp_nonce_url(add_query_arg('action', 'logout', $link), 'log-out');
	}
	return $link;
}

function wdbj_tml_get_pages($pages, $attributes) {
	$tml_page = wdbj_tml_get_option('page_id');
	if ( is_admin() && ! is_page($tml_page) )
		return $pages;
	
	// Change to logout link if user is logged in
	add_filter('page_link', 'wdbj_tml_page_link', 10, 2);
	
	// It sucks there's not really a better way to do this
	if ( wdbj_tml_get_option('show_page') ) {
		foreach ( $pages as $page ) {
			if ( $page->ID == $tml_page ) {
				if ( is_user_logged_in() )
					$page->post_title = __('Log out');
				else
					$page->post_title = __('Log In');
			}
		}
	}
	return $pages;
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

?>
