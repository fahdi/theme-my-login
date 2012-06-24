<?php
/**
 * Plugin Name: Custom Redirection
 * Description: Enabling this module will initialize custom redirection. You will then have to configure the settings via the "Redirection" tab.
 *
 * Class: Theme_My_Login_Custom_Redirection
 * Admin Class: Theme_My_Login_Custom_Redirection_Admin
 *
 * Holds Theme My Login Custom Redirection class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Custom_Redirection
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_Custom_Redirection' ) ) :
/**
 * Theme My Login Custom Redirection class
 *
 * Adds the ability to redirect users when logging in/out based upon their "user role".
 *
 * @since 6.0
 */
class Theme_My_Login_Custom_Redirection extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_redirection';

	/**
	 * Called on Theme_My_Login_Abstract::__construct
	 *
	 * @since 6.0
	 * @access protected
	 */
	protected function load() {
		add_action( 'tml_login_form',  array( &$this, 'login_form'      )        );
		add_filter( 'login_redirect',  array( &$this, 'login_redirect'  ), 10, 3 );
		add_filter( 'logout_redirect', array( &$this, 'logout_redirect' ), 10, 3 );
	}

	/**
	 * Returns default options
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @return array Default options
	 */
	public function default_options() {
		global $wp_roles;

		if ( empty( $wp_roles ) )
			$wp_roles = new WP_Roles;

		$options = array();
		foreach ( $wp_roles->get_names() as $role => $label ) {
			if ( 'pending' != $role ) {
				$options[$role] = array(
					'login_type' => 'default',
					'login_url' => '',
					'logout_type' => 'default',
					'logout_url' => ''
				);
			}
		}
		return $options;
	}

	/**
	 * Adds "_wp_original_referer" field to login form
	 *
	 * Callback for "tml_login_form" hook in file "login-form.php", included by method Theme_My_Login_Template::display()
	 *
	 * @see Theme_My_Login_Template::display()
	 * @since 6.0
	 * @access public
	 *
	 * @param object $template Reference to $theme_my_login_template object
	 */
	public function login_form( &$template ) {
		$jump_back_to = empty( $template->instance ) ? 'previous' : 'current';
		wp_original_referer_field( true, $jump_back_to );
		echo "\n";
	}

	/**
	 * Handles login redirection
	 *
	 * Callback for "login_redirect" hook in method Theme_My_Login::the_request()
	 *
	 * @see Theme_My_Login::the_request()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $redirect_to Default redirect
	 * @param string $request Requested redirect
	 * @param WP_User|WP_Error WP_User if user logged in, WP_Error otherwise
	 * @return string New redirect
	 */
	public function login_redirect( $redirect_to, $request, $user ) {
		// Determine the correct referer
		if ( ! $http_referer = wp_get_original_referer() )
			$http_referer = wp_get_referer();

		// Remove some arguments that may be present and shouldn't be
		$http_referer = remove_query_arg( array( 'instance', 'action', 'checkemail', 'error', 'loggedout', 'registered', 'redirect_to', 'updated', 'key', '_wpnonce', 'reauth' ), $http_referer );

		// Make sure $user object exists and is a WP_User instance
		if ( ! is_wp_error( $user ) && is_a( $user, 'WP_User' ) ) {
			if ( is_multisite() && empty( $user->roles ) ) {
				$user->roles = array( 'subscriber' );
			}
			$redirection = array( 'login_type' => 'default' );
			foreach ( (array) $user->roles as $role ) {
				if ( $this->get_option( $role ) ) {
					$redirection = $this->get_option( $role );
					break;
				}
			}
			if ( 'referer' == $redirection['login_type'] ) {
				// Send 'em back to the referer
				$redirect_to = $http_referer;
			} elseif ( 'custom' == $redirection['login_type'] ) {
				// Send 'em to the specified URL
				$redirect_to = $redirection['login_url'];
				// Allow a few user specific variables
				$replace = array( '%user_id%' => $user->ID, '%user_login%' => $user->user_login );
				$redirect_to = str_replace( array_keys( $replace ), array_values( $replace ), $redirect_to );
			}
		}

		// If a redirect is requested, it takes precedence
		if ( ! empty( $request ) && admin_url() != $request && admin_url( 'profile.php' ) != $request )
			$redirect_to = $request;

		// Make sure $redirect_to isn't empty
		if ( empty( $redirect_to ) )
			$redirect_to = get_option( 'home' );

		return $redirect_to;
	}

	/**
	 * Handles logout redirection
	 *
	 * Callback for "logout_redirect" hook in method Theme_My_Login::the_request()
	 *
	 * @see Theme_My_Login::the_request()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $redirect_to Default redirect
	 * @param string $request Requested redirect
	 * @param WP_User|WP_Error WP_User if user logged in, WP_Error otherwise
	 * @return string New redirect
	 */
	public function logout_redirect( $redirect_to, $request, $user ) {
		global $theme_my_login;

		// Determine the correct referer
		if ( ! $http_referer = wp_get_original_referer() )
			$http_referer = wp_get_referer();

		// Remove some arguments that may be present and shouldn't be
		$http_referer = remove_query_arg( array( 'instance', 'action', 'checkemail', 'error', 'loggedout', 'registered', 'redirect_to', 'updated', 'key', '_wpnonce' ), $http_referer );

		// Make sure $user object exists and is a WP_User instance
		if ( ! is_wp_error( $user ) && is_a( $user, 'WP_User' ) ) {
			if ( is_multisite() && empty( $user->roles ) ) {
				$user->roles = array( 'subscriber' );
			}
			$redirection = array();
			foreach ( (array) $user->roles as $role ) {
				if ( $this->get_option( $role ) ) {
					$redirection = $this->get_option( $role );
					break;
				}
			}
			if ( 'referer' == $redirection['logout_type'] ) {
				// Send 'em back to the referer
				$redirect_to = $http_referer;
			} elseif ( 'custom' == $redirection['logout_type'] ) {
				// Send 'em to the specified URL
				$redirect_to = $redirection['logout_url'];
				// Allow a few user specific variables
				$replace = array( '%user_id%' => $user->ID, '%user_login%' => $user->user_login );
				$redirect_to = str_replace( array_keys( $replace ), array_values( $replace ), $redirect_to );
			}
		}

		// Make sure $redirect_to isn't empty or pointing to an admin URL (causing an endless loop)
		if ( empty( $redirect_to ) || strpos( $redirect_to, 'wp-admin' ) !== false )
			$redirect_to = $theme_my_login->get_login_page_link( 'loggedout=true' );

		return $redirect_to;
	}
}
endif; // Class exists

