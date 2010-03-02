<?php

function jkf_tml_custom_redirect_login_form($instance_id) {
	wp_original_referer_field(true, 'previous');
	echo "\n";
}

function jkf_tml_custom_redirect_login($redirect_to, $request, $user) {
	global $pagenow;

	if ( 'wp-login.php' == $pagenow )
		return $redirect_to;
	
	// Bailout if this isn't a login
	if ( 'POST' != $_SERVER['REQUEST_METHOD'] )
		return $redirect_to;

	$orig_redirect = $redirect_to;

	// Determine the correct referer
	$http_referer = isset($_REQUEST['_wp_original_http_referer']) ? $_REQUEST['_wp_original_http_referer'] : $_SERVER['HTTP_REFERER'];
	if ( strpos($http_referer, get_option('home')) === false )
		$http_referer = get_option('home');
	
	// User is logged in
	if ( is_object($user) && !is_wp_error($user) ) {
		$user_role = reset($user->roles);
		$redirection = jkf_tml_get_option('redirection', $user_role);
		if ( 'default' == $redirection['login_type'] )
			$redirect_to = $orig_redirect;
		elseif ( 'referer' == $redirection['login_type'] )
			$redirect_to = $http_referer;
		else {
			$redirect_to = $redirection['login_url'];
			// Allow a few user specific variables
			$replace = array('%user_id%' => $user->ID, '%user_login%' => $user->user_login);
			$redirect_to = str_replace(array_keys($replace), array_values($replace), $redirect_to);
		}
	}
	
	if ( isset($request) && admin_url() != $request )
		$redirect_to = $request;
	
	return $redirect_to;
}

function jkf_tml_custom_redirect_logout($redirect_to, $request, $user) {

	$orig_redirect = $redirect_to;
	
	// Determine the correct referer
	$http_referer = isset($_REQUEST['_wp_original_http_referer']) ? $_REQUEST['_wp_original_http_referer'] : $_SERVER['HTTP_REFERER'];
	$http_referer = remove_query_arg(array('instance', 'action', 'checkemail', 'error', 'loggedout', 'registered', 'redirect_to', 'updated', 'key', '_wpnonce'), $http_referer);
	if ( strpos($http_referer, get_option('home')) === false )
		$http_referer = get_option('home');	

	if ( is_object($user) && !is_wp_error($user) ) {
		$user_role = reset($user->roles);
		$redirection = jkf_tml_get_option('redirection', $user_role);
		if ( 'default' == $redirection['logout_type'] )
			$redirect_to = $orig_redirect;
		elseif ( 'referer' == $redirection['logout_type'] )
			$redirect_to = $http_referer;
		else {
			$redirect_to = $redirection['logout_url'];
			// Allow a few user specific variables
			$replace = array('%user_id%' => $user->ID, '%user_login%' => $user->user_login);
			$redirect_to = str_replace(array_keys($replace), array_values($replace), $redirect_to);
		}
	}
	
	if ( strpos($redirect_to, 'wp-admin') !== false )
		$redirect_to = add_query_arg('loggedout', 'true', get_permalink(jkf_tml_get_option('page_id')));

	return $redirect_to;
}

?>