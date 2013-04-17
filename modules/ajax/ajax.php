<?php
/**
 * Plugin Name: AJAX
 * Description: Enabling this module will initialize and enable AJAX. There are no other settings for this module.
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
class Theme_My_Login_Ajax {
	/**
	 * Constructor
	 *
	 * @since 6.3
	 */
	public function __construct() {
		add_action( 'template_redirect',  array( $this, 'template_redirect'  ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

		add_filter( 'tml_action_url',         array( $this, 'tml_action_url'         ),  100, 3 );
		add_filter( 'tml_redirect_url',       array( $this, 'tml_redirect_url'       ),  100, 2 );
		add_filter( 'page_css_class',         array( $this, 'page_css_class'         ),   10, 2 );
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'wp_setup_nav_menu_item' )          );
	}

	/**
	 * Returns default AJAX actions
	 *
	 * @since 6.3
	 *
	 * @return array AJAX actions
	 */
	public static function default_actions() {
		$actions = array( 'login', 'register', 'lostpassword' );
		if ( is_multisite() )
			$actions[] = 'activate';
		return apply_filters( 'tml_ajax_actions', $actions );
	}

	/**
	 * Handles AJAX response
	 *
	 * @since 6.3
	 */
	public function template_redirect() {

		$theme_my_login = Theme_My_Login::get_object();

		if ( Theme_My_Login::is_tml_page() && isset( $_GET['ajax'] ) ) {
			define( 'DOING_AJAX', true );

			send_origin_headers();

			@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
			@header( 'X-Robots-Tag: noindex' );

			send_nosniff_header();
			nocache_headers();

			$instance =& $theme_my_login->get_instance();

			$instance->set_option( 'default_action', $theme_my_login->request_action );
			$instance->set_option( 'gravatar_size', 75 );
			$instance->set_option( 'before_title', '<h2>' );
			$instance->set_option( 'after_title', '</h2>' );

			if ( is_user_logged_in() )
				wp_send_json_success( $instance->display() );

			wp_send_json_error( $instance->display() );
		}
	}

	/**
	 * Enqueues styles and scripts
	 *
	 * @since 6.3
	 */
	public function wp_enqueue_scripts() {
		wp_enqueue_style( 'theme-my-login-ajax', plugins_url( 'theme-my-login/modules/ajax/css/ajax.css' ) );

		wp_enqueue_script( 'theme-my-login-ajax', plugins_url( 'theme-my-login/modules/ajax/js/ajax.js' ), array( 'jquery' ) );
	}

	/**
	 * Adds ajax parameter to TML action URL's
	 *
	 * Callback for "tml_action_url" filter
	 *
	 * @since 6.3
	 *
	 * @param string $url The action URL
	 * @param string $action The action
	 * @param int $instance The instance
	 * @return string The action URL
	 */
	public function tml_action_url( $url, $action, $instance ) {
		if ( Theme_My_Login::is_tml_page() && in_array( $action, self::default_actions() ) && isset( $_GET['ajax'] ) )
			$url = Theme_My_Login::get_page_link( $action, 'ajax=1' );
		return $url;
	}

	/**
	 * Adds ajax parameter to TML redirect URL's
	 *
	 * Callback for "tml_redirect_url" filter
	 *
	 * @since 6.3
	 *
	 * @param string $url The redirect URL
	 * @param string $action The action
	 * @return string The redirect URL
	 */
	public function tml_redirect_url( $url, $action ) {
		if ( Theme_My_Login::is_tml_page() && in_array( $action, self::default_actions() ) && isset( $_GET['ajax'] ) ) {
			switch ( $action ) {
				case 'lostpassword' :
				case 'retrievepassword' :
				case 'register' :
					$url = add_query_arg( 'ajax', 1, $url );
					break;
				case 'login' :
					$url = Theme_My_Login::get_page_link( 'login', 'ajax=1' );
					break;
			}
		}
		return $url;
	}

	/**
	 * Adds CSS class to TML pages
	 *
	 * @since 6.3
	 *
	 * @param array $classes CSS classes
	 * @param object $page Post object
	 * @return array CSS classes
	 */
	public function page_css_class( $classes, $page ) {
		if ( ! is_user_logged_in() && Theme_My_Login::is_tml_page( '', $page->ID ) )
			$classes[] = 'tml_ajax_link';
		return $classes;
	}

	/**
	 * Adds CSS class to TML pages
	 *
	 * @since 6.3
	 *
	 * @param object $menu_item Nav menu item
	 * @return object Nav menu item
	 */
	public function wp_setup_nav_menu_item( $menu_item ) {
		if ( ! is_user_logged_in() && Theme_My_Login::is_tml_page( '', $menu_item->object_id ) )
			$menu_item->classes[] = 'tml_ajax_link';
		return $menu_item;
	}
}

/**
 * Load the AJAX module
 *
 */
Theme_My_Login::get_object()->load_module( 'ajax', 'Theme_My_Login_Ajax' );

endif; // Class exists
