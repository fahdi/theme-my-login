<?php
/**
 * Plugin Name: AJAX
 * Description: Enabling this module will initialize and enable AJAX. There are no other settings for this module.
 *
 * Class: Theme_My_Login_Ajax
 *
 * Holds the Theme My Login Ajax class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Ajax
 * @since 6.3
 */

if ( ! class_exists( 'Theme_My_Login_Ajax' ) ) :
/**
 * Theme My Login AJAX module class
 *
 * @since 6.3
 */
class Theme_My_Login_Ajax extends Theme_My_Login_Abstract {
	/**
	 * Holds AJAX actions
	 *
	 * @since 6.3
	 * @access protected
	 * @var array
	 */
	protected $ajax_actions = array( 'login', 'register', 'lostpassword' );

	/**
	 * Loads the module
	 *
	 * @since 6.3
	 * @access protected
	 */
	protected function load() {
		add_action( 'parse_request',      array( &$this, 'parse_request'      ), 11 );
		add_action( 'wp_enqueue_scripts', array( &$this, 'wp_enqueue_scripts' ), 10 );

		add_filter( 'tml_page_link',    array( &$this, 'tml_page_link'    ), 10, 3 );
		add_filter( 'tml_redirect_url', array( &$this, 'tml_redirect_url' ), 10, 2 );
	}

	public function parse_request() {
		global $theme_my_login;

		if ( $theme_my_login->is_login_page() && isset( $_GET['ajax'] ) ) {
			define( 'DOING_AJAX', true );

			$data = $theme_my_login->shortcode( array(
				'gravatar_size' => 100,
				'before_title'  => '<h2>',
				'after_title'   => '</h2>'
			) );

			$x = new WP_Ajax_Response( array(
				'what'   => 'login',
				'action' => $theme_my_login->request_action,
				'data'   => $theme_my_login->errors->get_error_code() ? $theme_my_login->errors : $data,
				'supplemental' => array(
					'html' => $data,
					'success' => is_user_logged_in()
				)
			) );
			$x->send();
			exit;
		}
	}

	public function wp_enqueue_scripts() {
		wp_enqueue_style( 'theme-my-login-ajax', plugins_url( 'theme-my-login/modules/ajax/css/ajax.css' ) );

		wp_enqueue_script( 'theme-my-login-ajax', plugins_url( 'theme-my-login/modules/ajax/js/ajax.js' ), array( 'jquery', 'thickbox', 'wp-ajax-response' ) );
	}

	public function tml_page_link( $link, $action, $query ) {
		if ( did_action( 'parse_request' ) && in_array( $action, $this->ajax_actions ) && isset( $_GET['ajax'] ) )
			$link = add_query_arg( array(
				'ajax'     => 1,
				'instance' => 1
			), $link );
		return $link;
	}

	public function tml_redirect_url( $url, $action ) {
		global $theme_my_login;

		if ( $theme_my_login->is_login_page() && in_array( $theme_my_login->request_action, $this->ajax_actions ) && isset( $_GET['ajax'] ) ) {
			switch ( $action ) {
				case 'lostpassword' :
				case 'retrievepassword' :
					$url = $theme_my_login->get_page_link( 'login', 'checkemail=confirm&ajax=1' );
					break;
				case 'register' :
					$url = $theme_my_login->get_page_link( 'login', 'checkemail=registered&ajax=1' );
					break;
				case 'login' :
					$url = $theme_my_login->get_page_link( 'login', 'ajax=1' );
					break;
			}
			if ( isset( $_GET['instance'] ) )
				$url = add_query_arg( 'instance', $_GET['instance'], $url );
		}
		return $url;
	}
}
endif; // Class exists

