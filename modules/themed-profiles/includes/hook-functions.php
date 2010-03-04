<?php

function wdbj_tml_themed_profiles_site_url($url, $path, $orig_scheme = '') {
	global $wp_rewrite, $current_user;
	
	if ( is_user_logged_in() ) {
		if ( strpos('profile.php', $url) !== false ) {
			$orig_url = $url;
			$url = get_permalink(wdbj_tml_get_option('page_id'));
			if ( strpos($orig_url, '?') ) {
				$query = substr($orig_url, strpos($orig_url, '?') + 1);
				parse_str($query, $r);
				$url = add_query_arg($r, $url);
			}
		}
	}
	return $url;
}

function wdbj_tml_themed_profiles_title($title, $action) {
	if ( 'profile' == $action || is_user_logged_in() )
		$title = 'Your Profile';
	return $title;
}

?>