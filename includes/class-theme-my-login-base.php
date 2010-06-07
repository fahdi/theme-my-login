<?php
/**
 * Holds the Theme My Login base class
 *
 * @package Theme My Login
 */
 
if ( !class_exists( 'Theme_My_Login_Base' ) ) :
/*
 * Theme My Login base class
 *
 * This class contains properties and methods common to both the front-end and back-end.
 *
 * @since 6.0
 */
class Theme_My_Login_Base {
	/**
	 * Holds TML options
	 *
	 * @since 6.0
	 * @access public
	 * @var array
	 */
	var $options = array();
	
	/**
	 * Holds WP_Error() object
	 *
	 * @since 6.0
	 * @access public
	 * @var object
	 */
	var $errors;
	
	/**
	 * Rewrites URL's containing wp-login.php created by site_url()
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $url The URL
	 * @param string $path The path specified
	 * @param string $orig_scheme The current connection scheme (HTTP/HTTPS)
	 * @return string The modified URL
	 */
	function site_url( $url, $path, $orig_scheme ) {
		if ( strpos( $url, 'wp-login.php' ) !== false && !isset( $_REQUEST['interim-login'] ) ) {
			$orig_url = $url;
			$url = trailingslashit( $this->page_link );
			if ( strpos( $orig_url, '?' ) ) {
				$query = substr( $orig_url, strpos( $orig_url, '?' ) + 1 );
				parse_str( $query, $r );
				$url = add_query_arg( $r, $url );
			}
		}
		return $url;
	}
	
	/**
	 * Returns active TML modules
	 *
	 * Returns all valid modules specified via $this->options['active_modules']
	 *
	 * @since 6.0
	 * @access public
	 */
	function get_active_modules() {
		$modules = array();
		$active_modules = apply_filters( 'tml_active_modules', $this->options['active_modules'] );
		foreach ( (array) $active_modules as $module ) {
			// check the $plugin filename
			// Validate plugin filename	
			if ( !validate_file( $module ) // $module must validate as file
				|| '.php' == substr( $module, -4 ) // $module must end with '.php'
				|| file_exists( TML_MODULE_DIR . '/' . $module )	// $module must exist
				)
			$modules[] = $module;
		}
		return $modules;
	}
	
	/**
	 * Determine if $module is an active TML module
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $module Filename of module to check
	 * @return bool True if $module is active, false if not
	 */
	function is_module_active( $module ) {
		$active_modules = apply_filters( 'tml_active_modules', $this->options['active_modules'] );
		return in_array( $module, (array) $active_modules );
	}
	
	/**
	 * Initializes TML options
	 *
	 * @since 6.0
	 * @access public
	 */
	function init_options() {
		$this->options = apply_filters( 'tml_init_options', array(
			'page_id' => 0,
			'show_page' => 1,
			'enable_css' => 1,
			'active_modules' => array(),
			'initial_nag' => 1
		) );
	}
	
	/**
	 * Loads TML options
	 *
	 * @since 6.0
	 * @access public
	 */
	function load_options( $return = false ) {
	
		$this->init_options();
		
		$options = get_option( 'theme_my_login' );
		
		if ( is_array( $options ) ) {
			$this->options = array_merge( $this->options, $options );
		} else {
			update_option( 'theme_my_login', $this->options );
		}
		
		if ( $return )
			return $this->options;
	}
	
	/**
	 * Saves TML options
	 *
	 * @since 6.0
	 * @access public
	 */
	function save_options() {
		$options = get_option( 'theme_my_login' );
		if ( $options != $this->options )
			update_option( 'theme_my_login', $this->options );
	}
	
	/**
	 * Retrieve a TML option
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $option Name of option to retrieve
	 * @param mixed $default optional. Default value to return if $option is not set
	 * @return mixed Value of requested option or $default if option is not set
	 */
	function get_option( $option, $default = false ) {
		if ( isset( $this->options[$option] ) )
			return $this->options[$option];
		else
			return $default;
	}
	
	/**
	 * Set a TML option
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $option Name of option to set
	 * @param mixed $value Value of new option
	 * @param bool $save True will save to DB
	 */
	function set_option( $option, $value = '', $save = false ) {
		$this->options[$option] = $value;
		if ( $save )
			$this->save_options();
	}
	
	/**
	 * Dummy method called by __construct to be overridden by sub-class
	 *
	 * This method should just be used to attach callbacks to hooks.
	 *
	 * @since 6.0
	 * @access public
	 */
	function load() {
		// Override me!
	}
	
	/**
	 * Dummy method called by _init() to be overridden by sub-class
	 *
	 * This function is called on the 'init' action and can be used
	 * within the sub-class instead of creating a new hook on 'init'.
	 *
	 * @since 6.0
	 * @access public
	 */
	function init() {
		// Override me!
	}
	
	/**
	 * Sets up class properties on 'init' action
	 *
	 * @since 6.0
	 * @access private
	 */
	function _init() {
		$this->errors = new WP_Error();
		$this->page_link = get_page_link( $this->options['page_id'] );
		$this->init();
	}
	
	/**
	 * Adds scripts to $wp_scripts object
	 *
	 * @since 6.0
	 * @access public
	 */
	function default_scripts( $wp_scripts ) {
		$wp_scripts->add( 'jquery-shake', plugins_url( 'theme-my-login/js/jquery.shake.js' ), array( 'jquery' ) );
	}
	
	/**
	 * PHP4 style constructor
	 *
	 * @since 6.0
	 * @access public
	 */
	function Theme_My_Login_Base() {
		$this->__construct();
	}
	
	/**
	 * PHP5 style constructor
	 *
	 * @since 6.0
	 * @access public
	 */
	function __construct() {
		add_filter( 'site_url', array( &$this, 'site_url' ), 10, 3 );
		
		add_action( 'init', array( &$this, '_init' ) );
		add_action( 'wp_default_scripts', array( &$this, 'default_scripts' ) );

		$this->load_options();
		$this->load();
	}
}
endif;

?>