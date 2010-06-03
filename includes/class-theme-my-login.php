<?php

if ( !class_exists( 'Theme_My_Login' ) ) :
/*
 * Theme My Login class
 *
 * This class contains properties and methods common to the front-end.
 *
 * @since 6.0
 */
class Theme_My_Login extends Theme_My_Login_Base {
	/**
	 * Total instances of TML
	 *
	 * @since 6.0
	 * @access public
	 * @var int
	 */
	var $count = 0;
	
	/**
	 * Current instance being requested via HTTP GET or POST
	 *
	 * @since 6.0
	 * @access public
	 * @var int
	 */
	var $request_instance;
	
	/**
	 * Current action being requested via HTTP GET or POST
	 *
	 * @since 6.0
	 * @access public
	 * @var string
	 */
	var $request_action;
	
	/**
	 * Flag used within wp_list_pages() to make the_title() filter work properly
	 *
	 * @since 6.0
	 * @access public
	 * @var bool
	 */
	var $doing_pagelist = false;
	
	/**
	 * Proccesses the request
	 *
	 * @since 6.0
	 * @access public
	 */
	function the_request() {
		$errors =& $this->errors;
		$action =& $this->request_action;
		$instance =& $this->request_instance;
		
		do_action_ref_array( 'tml_request', array( &$this ) );
		
		if ( $this->options['enable_css'] )
			wp_enqueue_style( 'theme-my-login', $this->get_stylesheet(), false, $this->options['version'] );

		// Set a cookie now to see if they are supported by the browser.
		setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN );
		if ( SITECOOKIEPATH != COOKIEPATH )
			setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN );
			
		// allow plugins to override the default actions, and to add extra actions if they want
		if ( has_filter( 'login_action_' . $action ) ) {
			do_action_ref_array( 'login_action_' . $action, array( &$this ) );
		} else {
			$http_post = ( 'POST' == $_SERVER['REQUEST_METHOD'] );
			switch ( $action ) {
				case 'logout' :
					check_admin_referer( 'log-out' );
					
					$redirect_to = apply_filters( 'logout_redirect', site_url( 'wp-login.php?loggedout=true' ), isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '' );

					wp_logout();

					wp_safe_redirect( $redirect_to );
					exit();
					break;
				case 'lostpassword' :
				case 'retrievepassword' :
					if ( $http_post ) {
						require_once( TML_DIR . '/includes/login-functions.php' );
						$errors = tml_retrieve_password();
						if ( !is_wp_error( $errors ) ) {
							$redirect_to = !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : $this->get_current_url( 'checkemail=confirm&instance=' . $instance );
							wp_safe_redirect( $redirect_to );
							exit();
						}
					}

					if ( isset( $_REQUEST['error'] ) && 'invalidkey' == $_REQUEST['error'] )
						$errors->add( 'invalidkey', __( 'Sorry, that key does not appear to be valid.', 'theme-my-login' ) );
					break;
				case 'resetpass' :
				case 'rp' :
					require_once( TML_DIR . '/includes/login-functions.php' );
					$errors = tml_reset_password( $_GET['key'], $_GET['login'] );

					if ( !is_wp_error( $errors ) ) {
						$redirect_to = apply_filters( 'resetpass_redirect', $this->get_current_url( 'checkemail=newpass&instance=' . $instance ) );
						wp_safe_redirect( $redirect_to );
						exit();
					}

					$redirect_to = $this->get_current_url( 'action=lostpassword&error=invalidkey&instance=' . $instance );
					wp_redirect( $redirect_to );
					exit();
					break;
				case 'register' :
					if ( function_exists( 'is_multisite' ) && is_multisite() ) {
						// Multisite uses wp-signup.php
						wp_redirect( apply_filters( 'wp_signup_location', get_bloginfo('wpurl') . '/wp-signup.php' ) );
						exit;
					}
					
					if ( !get_option( 'users_can_register' ) ) {
						wp_redirect( $this->get_current_url( 'registration=disabled' ) );
						exit();
					}

					$user_login = '';
					$user_email = '';
					if ( $http_post ) {
						require_once( ABSPATH . WPINC . '/registration.php' );
						require_once( TML_DIR . '/includes/login-functions.php' );

						$user_login = $_POST['user_login'];
						$user_email = $_POST['user_email'];
						
						$errors = tml_register_new_user( $user_login, $user_email );
						if ( !is_wp_error( $errors ) ) {
							$redirect_to = !empty( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : $this->get_current_url('checkemail=registered&instance=' . $instance );
							$redirect_to = apply_filters( 'register_redirect', $redirect_to );
							wp_redirect( $redirect_to );
							exit();
						}
					}
					break;
				case 'login' :
				default:
					$secure_cookie = '';
					$interim_login = isset( $_REQUEST['interim-login'] );

					// If the user wants ssl but the session is not ssl, force a secure cookie.
					if ( !empty( $_POST['log'] ) && !force_ssl_admin() ) {
						$user_name = sanitize_user( $_POST['log'] );
						if ( $user = get_userdatabylogin( $user_name ) ) {
							if ( get_user_option( 'use_ssl', $user->ID ) ) {
								$secure_cookie = true;
								force_ssl_admin( true );
							}
						}
					}

					if ( isset( $_REQUEST['redirect_to'] ) && !empty( $_REQUEST['redirect_to'] ) ) {
						$redirect_to = $_REQUEST['redirect_to'];
						// Redirect to https if user wants ssl
						if ( $secure_cookie && false !== strpos( $redirect_to, 'wp-admin' ) )
							$redirect_to = preg_replace( '|^http://|', 'https://', $redirect_to );
					} else {
						$redirect_to = admin_url();
					}
					
					$reauth = empty( $_REQUEST['reauth'] ) ? false : true;

					// If the user was redirected to a secure login form from a non-secure admin page, and secure login is required but secure admin is not, then don't use a secure
					// cookie and redirect back to the referring non-secure admin page.  This allows logins to always be POSTed over SSL while allowing the user to choose visiting
					// the admin via http or https.
					if ( !$secure_cookie && is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos( $redirect_to, 'https' ) ) && ( 0 === strpos( $redirect_to, 'http' ) ) )
						$secure_cookie = false;
						
					if ( $http_post ) {
						$user = wp_signon( '', $secure_cookie );

						$redirect_to = apply_filters( 'login_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user );

						if ( !is_wp_error( $user ) && !$reauth ) {
							// If the user can't edit posts, send them to their profile.
							if ( !$user->has_cap( 'edit_posts' ) && ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ) )
								$redirect_to = admin_url( 'profile.php' );
							wp_safe_redirect( $redirect_to );
							exit();
						}

						$errors = $user;
					}
					// Clear errors if loggedout is set.
					if ( !empty( $_GET['loggedout'] ) || $reauth )
						$errors = new WP_Error();

					// If cookies are disabled we can't log in even with a valid user+pass
					if ( isset( $_POST['testcookie'] ) && empty( $_COOKIE[TEST_COOKIE] ) )
						$errors->add( 'test_cookie', __( '<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href="http://www.google.com/cookies.html">enable cookies</a> to use WordPress.', 'theme-my-login' ) );

					// Clear any stale cookies.
					if ( $reauth )
						wp_clear_auth_cookie();
					break;
			} // end switch
		} // endif has_filter()
		
		// Some parts of this script use the main login form to display a message
		if		( isset( $_GET['loggedout'] ) && TRUE == $_GET['loggedout'] )
			$errors->add( 'loggedout', __( 'You are now logged out.', 'theme-my-login' ), 'message' );
		elseif	( isset( $_GET['registration'] ) && 'disabled' == $_GET['registration'] )
			$errors->add( 'registerdisabled', __( 'User registration is currently not allowed.', 'theme-my-login' ) );
		elseif	( isset( $_GET['checkemail'] ) && 'confirm' == $_GET['checkemail'] )
			$errors->add( 'confirm', __( 'Check your e-mail for the confirmation link.', 'theme-my-login' ), 'message' );
		elseif	( isset( $_GET['checkemail'] ) && 'newpass' == $_GET['checkemail'] )
			$errors->add( 'newpass', __( 'Check your e-mail for your new password.', 'theme-my-login' ), 'message' );
		elseif	( isset( $_GET['checkemail'] ) && 'registered' == $_GET['checkemail'] )
			$errors->add( 'registered', __( 'Registration complete. Please check your e-mail.', 'theme-my-login' ), 'message' );
		elseif	( $interim_login )
			$errors->add( 'expired', __( 'Your session has expired. Please log-in again.', 'theme-my-login' ), 'message' );
	}

	/**
	 * Changes the_title() to reflect the current action
	 *
	 * @since 6.0
	 * @acess public
	 *
	 * @param string $title The current post title
	 * @param int $post_id The current post ID
	 * @return string The modified post title
	 */
	function the_title( $title, $post_id = '' ) {
		if ( is_admin() && !defined( 'IS_PROFILE_PAGE' ) )
			return $title;
		if ( $this->options['page_id'] == $post_id ) {
			if ( $this->doing_pagelist ) {
				$title = is_user_logged_in() ? __( 'Log Out', 'theme-my-login' ) : __( 'Log In', 'theme-my-login' );
			} else {
				$action = ( 'page' == $this->request_instance ) ? $this->request_action : 'login';
				$title = Theme_My_Login_Template::get_title( $action );
			}
		}
		return $title;
	}
	
	/**
	 * Changes single_post_title() to reflect the current action
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $title The current post title
	 * @return string The modified post title
	 */
	function single_post_title( $title ) {
		if ( is_page( $this->options['page_id'] ) ) {
			$action = ( 'page' == $this->request_instance ) ? $this->request_action : 'login';
			$title = Theme_My_Login_Template::get_title( $action );
		}
		return $title;
	}
	
	/**
	 * Excludes TML page if set in the admin
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param array $exclude_array Array of excluded pages
	 * @return array Modified array of excluded pages
	 */
	function list_pages_excludes( $exclude_array ) {
		// This makes the_title() filter work properly
		$this->doing_pagelist = true;
		
		$exclude_array = (array) $exclude_array;
		if ( !$this->options['show_page'] )
			$exclude_array[] = $this->options['page_id'];
		return $exclude_array;
	}
	
	/**
	 * Filters the output of wp_list_pages()
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $output The generated HTML output
	 * @return string The modified HTML output
	 */
	function wp_list_pages( $output ) {
		// The second part to make the_title() filter work properly
		$this->doing_pagelist = false;
		return $output;
	}
	
	/**
	 * Changes permalink to logout link if user is logged in
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $link The link
	 * @param int $id The current post ID
	 * @return string The modified link
	 */
	function page_link( $link, $id ) {
		if ( !$this->doing_pagelist )
			return $link;
		if ( $id == $this->options['page_id'] ) {
			if ( is_user_logged_in() && ( !isset( $_REQUEST['action'] ) || 'logout' != $_REQUEST['action'] ) )
				$link = wp_nonce_url( add_query_arg( 'action', 'logout', $link ), 'log-out' );
		}
		return $link;
	}

	/**
	 * Handler for 'theme-my-login' shortcode
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $atts Attributes passed from the shortcode
	 * @return string HTML output from Theme_My_Login_Template->display()
	 */
	function shortcode( $atts = '' ) {
	
		if ( isset( $atts['instance_id'] ) )
			$atts['instance'] = $atts['instance_id'];
		
		if ( !isset( $atts['instance'] ) )
			$atts['instance'] = $this->get_new_instance();
			
		if ( $this->request_instance == $atts['instance'] )
			$atts['is_active'] = 1;
		
		$template =& new Theme_My_Login_Template( $atts, $this->errors );
		
		return $template->display();
	}
	
	/**
	 * Handler for 'theme-my-login-page' shortcode
	 *
	 * Essentially a wrapper for the 'theme-my-login' shortcode.
	 * Works by automatically setting some attributes to make the shortcode work properly for the main login page
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $atts Attributes passed from the shortcode
	 * @return string HTML output from Theme_My_Login_Template->display()
	 */
	function page_shortcode( $atts = '' ) {
		if ( !is_array( $atts ) )
			$atts = array();

		$atts['instance'] = 'page';
		
		if ( !isset( $atts['show_title'] ) )
			$atts['show_title'] = 0;
		if ( !isset( $atts['before_widget'] ) )
			$atts['before_widget'] = '';
		if ( !isset( $atts['after_widget'] ) )
			$atts['after_widget'] = '';
			
		return $this->shortcode( $atts );
	}

	/**
	 * Incremenets $this->count and returns it
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @return int New value of $this->count
	 */
	function get_new_instance() {
		$this->count++;
		return $this->count;
	}
	
	/**
	 * Returns current URL
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $path Optionally append path to the current URL
	 * @return string URL with optional path appended
	 */
	function get_current_url( $path = '' ) {
		$url = remove_query_arg( array( 'instance', 'action', 'checkemail', 'error', 'loggedout', 'registered', 'redirect_to', 'updated', 'key', '_wpnonce' ) );
		if ( !empty( $path ) ) {
			$path = wp_parse_args( $path );
			$url = add_query_arg( $path, $url );
		}
		return $url;
	}
	
	/**
	 * Enqueues the specified sylesheet
	 *
	 * First looks in theme/template directories for the stylesheet, falling back to plugin directory
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $file Filename of stylesheet to load
	 * @return string Path to stylesheet
	 */
	function get_stylesheet( $file = 'theme-my-login.css' ) {
		if ( file_exists( get_stylesheet_directory() . '/' . $file ) )
			$stylesheet = get_stylesheet_directory_uri() . '/' . $file;
		elseif ( file_exists( get_template_directory() . '/' . $file ) )
			$stylesheet = get_template_directory_uri() . '/' . $file;
		else
			$stylesheet = plugins_url( '/theme-my-login/' . $file );
		return $stylesheet;
	}
	
	/**
	 * Attaches class methods to WordPress hooks
	 *
	 * @since 6.0
	 * @access public
	 */
	function load() {
		$this->request_action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';
		$this->request_instance = isset( $_REQUEST['instance'] ) ? $_REQUEST['instance'] : 'page';
		
		add_action( 'parse_request', array( &$this, 'the_request' ) );
		
		add_filter( 'the_title', array( &$this, 'the_title' ), 10, 2 );
		add_filter( 'single_post_title', array( &$this, 'single_post_title' ) );
		
		add_filter( 'page_link', array( &$this, 'page_link' ), 10, 2 );
		
		add_filter( 'wp_list_pages_excludes', array( &$this, 'list_pages_excludes' ) );
		add_filter( 'wp_list_pages', array( &$this, 'wp_list_pages' ) );
		
		add_shortcode( 'theme-my-login-page', array( &$this, 'page_shortcode' ) );
		add_shortcode( 'theme-my-login', array( &$this, 'shortcode' ) );
	}
}
endif;

?>