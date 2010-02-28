<?php

function jkf_tml_the_title($title, $post_id = '') {
    if ( is_admin() )
        return $title;
    if ( jkf_tml_get_option('page_id') == $post_id ) {
        require_once (WP_PLUGIN_DIR . '/theme-my-login/includes/template-functions.php');
        $action = ( 'tml-page' == jkf_tml_get_var('request_instance') ) ? jkf_tml_get_var('request_action') : 'login';
        $title = jkf_tml_get_title($action);
    }
    return $title;
}

function jkf_tml_single_post_title($title) {
    if ( is_page(jkf_tml_get_option('page_id')) ) {
        require_once (WP_PLUGIN_DIR . '/theme-my-login/includes/template-functions.php');
        $action = ( 'tml-page' == jkf_tml_get_var('request_instance') ) ? jkf_tml_get_var('request_action') : 'login';
        $title = jkf_tml_get_title($action);
    }
    return $title;
}

function jkf_tml_site_url($url, $path, $orig_scheme) {
    if ( strpos($url, 'wp-login.php') !== false && !isset($_REQUEST['interim-login']) ) {
        $orig_url = $url;
        $url = get_permalink(jkf_tml_get_option('page_id'));
        if ( strpos($orig_url, '?') ) {
            $query = substr($orig_url, strpos($orig_url, '?') + 1);
            parse_str($query, $r);
            $url = add_query_arg($r, $url);
        }
    }
    return $url;
}

function jkf_tml_list_pages_excludes($exclude_array) {
	if ( !jkf_tml_get_option('show_page') )
		$exclude_array[] = jkf_tml_get_option('page_id');
	return $exclude_array;
}

function jkf_tml_page_link($link, $id) {
	if ( $id == jkf_tml_get_option('page_id') ) {
		if ( is_user_logged_in() )
			$link = wp_nonce_url(add_query_arg('action', 'logout', $link), 'log-out');
	}
	return $link;
}

function jkf_tml_get_pages($pages, $attributes) {
	if ( is_admin() )
		return $pages;
	
	// Change to logout link if user is logged in
	add_filter('page_link', 'jkf_tml_page_link', 10, 2);
	
	// It sucks there's not really a better way to do this
	if ( jkf_tml_get_option('show_page') ) {
		foreach ( $pages as $page ) {
			if ( $page->ID == jkf_tml_get_option('page_id') ) {
				if ( is_user_logged_in() )
					$page->post_title = __('Log out');
				else
					$page->post_title = __('Log In');
			}
		}
	}
	return $pages;
}

function jkf_tml_shortcode($atts = '') {
    require_once( WP_PLUGIN_DIR . '/theme-my-login/includes/template-functions.php' );
    
    if ( empty($atts['instance_id']) )
        $atts['instance_id'] = jkf_tml_get_new_instance();

    if ( jkf_tml_get_var('request_instance') == $atts['instance_id'] )
        $atts['is_active'] = 1;

    jkf_tml_set_var(shortcode_atts(jkf_tml_get_display_options(), $atts), 'current_instance');
    return jkf_tml_display();
}

?>