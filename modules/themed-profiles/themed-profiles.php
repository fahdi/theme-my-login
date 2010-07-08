<?php
/*
Plugin Name: Themed Profiles
Description: Enabling this module will initialize and enable themed profiles. There are no other settings for this module.
*/

if ( !class_exists( 'Theme_My_Login_Themed_Profiles' ) ) :
/**
 * Theme My Login Themed Profiles module class
 *
 * Allows users to edit profile on the front-end.
 *
 * @since 6.0
 */
class Theme_My_Login_Themed_Profiles {
	/**
	 * Holds reference to $theme_my_login object
	 *
	 * @since 6.0
	 * @access public
	 * @var object
	 */
	var $theme_my_login;
	
	/**
	 * Redirects profile.php to themed profile page
	 *
	 * Callback for 'init' hook
	 *
	 * @since 6.0
	 * @access public
	 */
	function init() {
		global $pagenow;
		if ( 'profile.php' == $pagenow ) {
			$redirect_to = add_query_arg( 'action', 'profile', $this->theme_my_login->get_login_page_link() );
			$redirect_to = add_query_arg( $_GET, $redirect_to );
			wp_redirect( $redirect_to );
			exit();
		}
	}
	
	/**
	 * Redirects login page to profile if user is logged in
	 *
	 * Callback for 'template_redirect' hook
	 *
	 * @since 6.0
	 * @access public
	 */
	function template_redirect() {
		if ( $this->theme_my_login->is_login_page() && is_user_logged_in() && !( isset( $_REQUEST['action'] ) && in_array($_REQUEST['action'], array( 'profile', 'logout' ) ) ) ){
			$redirect_to = add_query_arg( 'action', 'profile', $this->theme_my_login->get_login_page_link() );
			wp_redirect( $redirect_to );
			exit();
		} elseif ( $this->theme_my_login->is_login_page() && ( isset( $_REQUEST['action'] ) && 'profile' == $_REQUEST['action'] ) && isset( $_REQUEST['instance'] ) ) {
			$redirect_to = remove_query_arg( array( 'instance' ) );
			wp_redirect( $redirect_to );
			exit();
		}
	}
	
	/**
	 * Handles profile action
	 *
	 * Callback for 'login_action_profile' in method Theme_My_Login::the_request()
	 *
	 * @see Theme_My_Login::the_request()
	 * @since 6.0
	 * @access public
	 */
	function profile_action() {
	
		require_once( ABSPATH . 'wp-admin/includes/user.php' );
		require_once( ABSPATH . WPINC . '/registration.php' );
		
		define( 'IS_PROFILE_PAGE', true );
		
		wp_enqueue_style( 'password-strength', plugins_url( 'theme-my-login/modules/themed-profiles/password-strength.css' ) );
		
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.dev' : '';
		
		wp_enqueue_script( 'user-profile', admin_url( "js/user-profile$suffix.js" ), array( 'jquery' ), '', true );
		wp_enqueue_script( 'password-strength-meter', admin_url( "js/password-strength-meter$suffix.js" ), array( 'jquery' ), '', true );
		wp_localize_script( 'password-strength-meter', 'pwsL10n', array(
			'empty' => __( 'Strength indicator', $this->theme_my_login->textdomain ),
			'short' => __( 'Very weak', $this->theme_my_login->textdomain ),
			'bad' => __( 'Weak', $this->theme_my_login->textdomain ),
			/* translators: password strength */
			'good' => _x( 'Medium', 'password strength', $this->theme_my_login->textdomain ),
			'strong' => __( 'Strong', $this->theme_my_login->textdomain ),
			'l10n_print_after' => 'try{convertEntities(pwsL10n);}catch(e){};'
		) );
		
		$current_user = wp_get_current_user();
		
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			check_admin_referer( 'update-user_' . $current_user->ID );

			if ( !current_user_can( 'edit_user', $current_user->ID ) )
				wp_die( __( 'You do not have permission to edit this user.', $this->theme_my_login->textdomain ) );

			do_action( 'personal_options_update', $current_user->ID );

			$errors = edit_user( $current_user->ID );

			if ( !is_wp_error( $errors ) ) {
				$redirect = add_query_arg( array( 'updated' => 'true' ) );
				wp_redirect( $redirect );
				exit();
			}
			
			$this->theme_my_login->errors = $errors;
		}
		
		if ( isset( $_GET['updated'] ) && 'true' == $_GET['updated'] )
			$this->theme_my_login->errors->add( 'profile_updated', __( 'Profile updated.', $this->theme_my_login->textdomain ), 'message' );
	}
	
	/**
	 * Outputs profile form HTML
	 *
	 * Callback for 'login_form_profile' hook in method Theme_My_login_Template::display()
	 *
	 * @see Theme_My_Login_Template::display()
	 * @since 6.0
	 * @access public
	 *
	 * @param object $template Reference to $theme_my_login_template object
	 */
	function get_profile_form( &$template ) {
		$_template = array();
		// Allow template override via shortcode or template tag args
		if ( !empty( $template->options['profile_template'] ) )
			$_template[] = $template->options['profile_template'];
		// Default template
		$_template[] = 'profile-form.php';
		// Load template
		$template->get_template( $_template );
	}
	
	/**
	 * Changes links from profile.php to themed profile page
	 *
	 * Callback for 'site_url' hook
	 *
	 * @see site_url()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $url The generated link
	 * @param string $path The specified path
	 * @param string $orig_scheme The original connection scheme
	 * @return string The filtered link
	 */
	function site_url( $url, $path, $orig_scheme = '' ) {
		if ( strpos( $url, 'profile.php' ) !== false ) {
			$orig_url = $url;
			$url = add_query_arg( 'action', 'profile', $this->theme_my_login->get_login_page_link( '', true ) );
			if ( strpos( $orig_url, '?' ) ) {
				$query = substr( $orig_url, strpos( $orig_url, '?' ) + 1 );
				parse_str( $query, $r );
				$url = add_query_arg( $r, $url );
			}
		}
		return $url;
	}
	
	/**
	 * Changes the page title for themed profile page
	 *
	 * Callback for 'tml_title' hook in method Theme_My_Login_Template::get_page_title()
	 *
	 * @see Theme_My_Login_Template::get_page_title()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $title The current title
	 * @param string $action The requested action
	 * @return string The filtered title
	 */
	function tml_title( $title, $action ) {
		if ( 'profile' == $action && is_user_logged_in() && '' == $this->theme_my_login->request_instance )
			$title = __( 'Your Profile', $this->theme_my_login->textdomain );
		return $title;
	}
	
	/**
	 * Loads global $theme_my_login object into class property
	 *
	 * Callback for 'tml_modules_loaded' in file "theme-my-login.php"
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param object $theme_my_login Reference to global $theme_my_login object
	 */
	function load( &$theme_my_login ) {
		// Create a reference to global $theme_my_login object
		$this->theme_my_login =& $theme_my_login;
		
		add_filter( 'site_url', array( &$this, 'site_url' ), 10, 3 );
		add_filter( 'admin_url', array( &$this, 'site_url' ), 10, 2 );
	}
	
	/**
	 * PHP4 style constructor
	 *
	 * @since 6.0
	 * @access public
	 */
	function Theme_My_Login_Themed_Profiles() {
		$this->__construct();
	}
	
	/**
	 * PHP5 style constructor
	 *
	 * @since 6.0
	 * @access public
	 */
	function __construct() {
		// Load
		add_action( 'tml_modules_loaded', array( &$this, 'load' ) );
		add_filter( 'tml_title', array( &$this, 'tml_title' ), 10, 2 );
		
		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'template_redirect', array( &$this, 'template_redirect' ) );
		
		add_action( 'login_action_profile', array( &$this, 'profile_action' ) );
		add_action( 'login_form_profile', array( &$this, 'get_profile_form' ) );
	}
}

/**
 * Holds the reference to Theme_My_Login_Themed_Profiles object
 * @global object $theme_my_login_themed_profiles
 * @since 6.0
 */
$theme_my_login_themed_profiles = new Theme_My_Login_Themed_Profiles();

endif;

?>