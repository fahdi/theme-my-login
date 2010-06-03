<?php

if ( !class_exists( 'Theme_My_Login_Module' ) ) :
/*
 * Theme My Login module class
 *
 */
class Theme_My_Login_Module {

	var $menu = array();
	var $submenu = array();
	
	function add_menu_page( $menu_title, $file, $function = '', $function_args = array(), $position = NULL ) {
		global $tml_admin_menu;

		$file = plugin_basename( $file );

		$hookname = get_plugin_page_hookname( $file, '' );
		$hookname = preg_replace( '|[^a-zA-Z0-9_:.]|', '-', $hookname );
		if ( !empty( $function ) && !empty( $hookname ) )
			add_action( $hookname, $function );

		$new_menu = array( $menu_title, $file, $hookname, $function_args );

		if ( NULL === $position )
			$this->menu[] = $new_menu;
		else
			$this->menu[$position] = $new_menu;

		return $hookname;
	}

	function add_submenu_page( $parent, $menu_title, $file, $function = array(), $function_args = '' ) {
		global $tml_admin_submenu;
		
		$file = plugin_basename($file);
		$parent = plugin_basename($parent);
		
		$count = ( isset( $tml_admin_submenu[$parent] ) && is_array( $tml_admin_submenu[$parent] ) ) ? count( $tml_admin_submenu[$parent] ) + 1 : 1;
		
		$hookname = get_plugin_page_hookname( $parent . '-' . $count, '' );
		$hookname = preg_replace( '|[^a-zA-Z0-9_:.]|', '-', $hookname );
		if ( !empty( $function ) && !empty( $hookname ) )
			add_action( $hookname, $function );
		
		$tml_admin_submenu[$parent][] = array( $menu_title, $file, $hookname, $function_args );
		
		return $hookname;
	}
}
endif;

?>