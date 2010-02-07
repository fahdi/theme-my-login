<?php

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
	
	if ( !isset($theme_my_login->options['active_modules']) )
		return false;
		
	$modules = (array) $theme_my_login->options['active_modules'];
        
    foreach ( $modules as $module ) {
        if ( validate_file($module) )
            continue;

        if ( ! ( file_exists(WP_PLUGIN_DIR . "/theme-my-login/modules/$module") && is_file(WP_PLUGIN_DIR . "/theme-my-login/modules/$module") ) )
            continue;
            
        include (WP_PLUGIN_DIR . "/theme-my-login/modules/$module");
    }
}

?>
