<?php

function jkf_tml_default_settings($empty = false) {
    $options = array(
        'show_page' => 1,
		'rewrite_links' => 1,
        'enable_css' => 1,
        'enable_template_tag' => 0,
        'enable_widget' => 0,
        'active_modules' => array()
        );
    return apply_filters('tml_default_settings', $options);
}

function jkf_tml_get_instance() {
    static $instance = 0;
    ++$instance;
    return "tml-$instance";
}

function jkf_tml_get_current_url($query = '') {
    $schema = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
    $self =  $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    $keys = array('instance', 'action', 'checkemail', 'error', 'loggedout', 'registered', 'redirect_to', 'updated', 'key', '_wpnonce');
    $url = remove_query_arg($keys, $self);

    if ( !empty($query) ) {
        $query = wp_parse_args($query);
        $url = add_query_arg($query, $url);
    }

    return $url;
}

function jkf_tml_get_css($file = 'theme-my-login.css') {
    if ( file_exists(get_stylesheet_directory() . "/$file") )
        $css_file = get_stylesheet_directory_uri() . "/$file";
    elseif ( file_exists(get_template_directory() . "/$file") )
        $css_file = get_template_directory_uri() . "/$file";
    else
        $css_file = plugins_url("/theme-my-login/$file");

    wp_enqueue_style('theme-my-login', $css_file);
}

function jkf_tml_load_active_modules() {
	global $theme_my_login;
	
	$current_modules = apply_filters( 'tml_active_modules', $theme_my_login->options['active_modules'] );
	if ( is_array($current_modules) ) {
		foreach ( $current_modules as $module ) {
			// check the $plugin filename
			// Validate plugin filename	
			if ( validate_file($module) // $module must validate as file
				|| '.php' != substr($module, -4) // $module must end with '.php'
				|| !file_exists(TML_MODULE_DIR . '/' . $module)	// $module must exist
				)
				continue;

			include_once(TML_MODULE_DIR . '/' . $module);
		}
		unset($module);
	}
	unset($current_modules);

	do_action('tml_modules_loaded');
}

function jkf_tml_is_module_active($module) {
    global $theme_my_login;
	$current = apply_filters('tml_active_modules', $theme_my_login->options['active_modules']);
    return in_array($module, $current);
}

function jkf_tml_update_option() {
	global $theme_my_login;
	
	$args = func_get_args();
	if ( !is_array($args) )
		return false;
		
	$value = array_shift($args);

	$option = 'options';
	foreach ( $args as $arg ) {
		$option .= "['$arg']";
	}
	eval("\$theme_my_login->{$option} = \$value;");
	return true;
}

function jkf_tml_delete_option() {
	global $theme_my_login;
	
	$args = func_get_args();
	if ( !is_array($args) )
		return false;

	$option = 'options';
	foreach ( $args as $arg ) {
		$option .= "['$arg']";
	}
	eval("unset(\$theme_my_login->{$option});");
	return true;
}

function jkf_tml_get_option() {
	global $theme_my_login;
	
	$args = func_get_args();
	if ( !is_array($args) )
		return false;

	$option = $theme_my_login->options;
	foreach ( $args as $arg ) {
		if ( !isset($option[$arg]) )
			return $option;
		$option = $option[$arg];
	}
	return $option;
}

function jkf_tml_save_options($sanitize = true) {
	global $theme_my_login;
	if ( !$sanitize )
		define('TML_EDITING_MODULES', true);
	$result = update_option('theme_my_login', $theme_my_login->options);
	if ( !$sanitize )
		define('TML_EDITING_MODULES', false);
	return $result;
}

function jkf_tml_set_error($error, $code = '', $data = '') {
	global $theme_my_login;
	if ( empty($code) )
		$code = 'tml_error';
	if ( is_a($error, 'WP_Error') )
		$theme_my_login->errors = $error;
	elseif ( is_a($theme_my_login->errors, 'WP_Error') )
		$theme_my_login->errors->add($code, $error, $data);
	else
		$theme_my_login->errors = new WP_Error($code, $error, $data);
}

function jkf_tml_get_error($code = '') {
	global $theme_my_login;
	if ( is_a($theme_my_login->errors, 'WP_Error') )
		return $theme_my_login->errors->get_error_message($code);
	return false;
}

?>