<?php
/*
Plugin Name: Custom User Links
Description: Enabling this module will initialize custom user links. You will then have to configure the settings via the "User Links" tab.
*/

if ( !class_exists( 'Theme_My_Login_Custom_User_Links' ) ) :
/**
 * Theme My Login Custom User Links module class
 *
 * Adds the ability to define custom links to display to a user when logged in based upon their "user role".
 *
 * @since 6.0
 */
class Theme_My_Login_Custom_User_Links {
	/**
	 * Holds reference to $theme_my_login object
	 *
	 * @since 6.0
	 * @access public
	 * @var object
	 */
	var $theme_my_login;
	
	/**
	 * Gets the user links for the current user's role
	 *
	 * Callback for 'tml_user_links' hook in method Theme_My_Login_Template::display()
	 *
	 * @see Theme_My_Login_Template::display()
	 * @since 6.0
	 * @access public
	 */
	function get_user_links( $links = array() ) {
	
		if ( !is_user_logged_in() )
			return $links;
			
		$current_user = wp_get_current_user();
		
		$links = $this->theme_my_login->options['user_links'][$current_user->roles[0]];
		if ( !is_array( $links ) || empty( $links ) )
			$links = array();

		// Allow for user_id variable in link
		foreach ( $links as $key => $link ) {
			$links[$key]['url'] = str_replace( '%user_id%', $current_user->ID, $link['url'] );
		}
		
		return $links;
	}
	
	/**
	 * Activates this module
	 *
	 * Callback for 'tml_activate_custom-user-links/custom-user-links.php' hook in method Theme_My_Login_Admin::activate_module()
	 *
	 * @see Theme_My_Login_Admin::activate_module()
	 * @since 6.0
	 * @access public
	 */
	function activate( &$theme_my_login ) {
		if ( !( isset( $theme_my_login->options['user_links'] ) && is_array( $theme_my_login->options['user_links'] ) ) )
			$theme_my_login->options = array_merge( $theme_my_login->options, $this->init_options() );
	}
	
	/**
	 * Initializes options for this module
	 *
	 * Callback for 'tml_init_options' hook in method Theme_My_Login_Base::init_options()
	 *
	 * @see Theme_My_Login_Base::init_options()
	 * @since 6.0
	 * @access public
	 */
	function init_options( $options = array() ) {
		global $wp_roles;
		if ( empty( $wp_roles ) )
			$wp_roles =& new WP_Roles();
		
		$options = (array) $options;
		
		$options['user_links'] = array();
		foreach ( $wp_roles->get_names() as $role => $label ) {
			if ( 'pending' == $role )
				continue;
			$options['user_links'][$role] = array(
				array( 'title' => __( 'Dashboard', $this->theme_my_login->textdomain ), 'url' => admin_url() ),
				array( 'title' => __( 'Profile', $this->theme_my_login->textdomain ), 'url' => admin_url( 'profile.php' ) )
			);
		}
		return $options;
	}
	
	/**
	 * Loads global $theme_my_login object into class property
	 *
	 * Callback for 'tml_modules_loaded' in file "theme-my-login.php"
	 *
	 * @since 6.0
	 * @access public
	 */
	function load( &$theme_my_login ) {
		// Create a reference to global $theme_my_login object
		$this->theme_my_login =& $theme_my_login;
	}
	
	/**
	 * PHP4 style constructor
	 *
	 * @since 6.0
	 * @access public
	 */
	function Theme_My_Login_Custom_User_Links() {
		$this->__construct();
	}
	
	/**
	 * PHP5 style constructor
	 *
	 * @since 6.0
	 * @access public
	 */
	function __construct() {
		add_action( 'tml_activate_custom-user-links/custom-user-links.php', array( &$this, 'activate' ) );
		add_filter( 'tml_init_options', array( &$this, 'init_options' ) );
		add_filter( 'tml_modules_loaded', array( &$this, 'load' ) );
		add_filter( 'tml_user_links', array( &$this, 'get_user_links' ) );
	}
}

/**
 * Holds the reference to Theme_My_Login_Custom_User_Links object
 * @global object $theme_my_login_custom_user_links
 * @since 6.0
 */
$theme_my_login_custom_user_links = new Theme_My_Login_Custom_User_Links();

if ( is_admin() )
	include_once( TML_ABSPATH. '/modules/custom-user-links/admin/custom-user-links-admin.php' );

endif;

?>