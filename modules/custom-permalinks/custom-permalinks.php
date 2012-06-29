<?php
/**
 * Plugin Name: Custom Permalinks
 * Description: Enabling this module will initialize custom permalinks. You will then have to configure the settings via the "Permalinks" tab.
 *
 * Class: Theme_My_Login_Custom_Permalinks
 * Admin Class: Theme_My_Login_Custom_Permalinks_Admin
 *
 * Holds Theme My Login Custom Permalinks class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Custom_Permalinks
 * @since 6.3
 */

if ( ! class_exists( 'Theme_My_Login_Custom_Permalinks' ) ) :
/**
 * Theme My Login Custom Permalinks class
 *
 * Adds the ability to set permalinks for default actions.
 *
 * @since 6.3
 */
class Theme_My_Login_Custom_Permalinks extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_permalinks';

	/**
	 * Loads the module
	 *
	 * @since 6.3
	 * @access protected
	 */
	protected function load() {
		add_action( 'init',                array( &$this, 'init'                )        );
		add_action( 'parse_request',       array( &$this, 'parse_request'       ), 0     );
		add_filter( 'page_link',           array( &$this, 'page_link'           ), 10, 2 );
		add_filter( 'tml_page_link',       array( &$this, 'tml_page_link'       ), 10, 2 );
		add_filter( 'logout_url',          array( &$this, 'logout_url'          ), 10, 2 );
		add_filter( 'rewrite_rules_array', array( &$this, 'rewrite_rules_array' )        );
	}

	/**
	 * Initializes the plugin
	 *
	 * Callback for the "init" action hook
	 *
	 * @since 6.3
	 * @access public
	 */
	public function init() {
		global $wp;

		$wp->add_query_var( 'action' );
	}

	/**
	 * Changes request action based on "action" query var
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param object $wp Reference to global WP object
	 */
	public function parse_request( &$wp ) {
		global $theme_my_login;

		if ( ! empty( $wp->query_vars['action'] ) ) {
			$theme_my_login->request_action = $wp->query_vars['action'];

			$instance =& $theme_my_login->get_instance();
			$instance->set_option( 'default_action', $wp->query_vars['action'] );
		}
	}

	/**
	 * Changes login page link to custom permalink
	 *
	 * Callback for "page_link" filter in get_page_link()
	 *
	 * @see get_page_link()
	 * @since 6.3
	 * @access public
	 *
	 * @param string $link Page link
	 * @param int $id Page ID
	 * @return string Page link
	 */
	function page_link( $link, $id ) {
		global $theme_my_login;

		if ( $theme_my_login->is_login_page( $id ) )
			return $this->tml_page_link( $link );
		return $link;
	}

	/**
	 * Returns link for login page
	 *
	 * Callback for "tml_page_link" filter in Theme_My_Login::get_login_page_link()
	 *
	 * @see Theme_My_Login::get_login_page_link()
	 * @since 6.3
	 * @access public
	 *
	 * @param string $link Page link
	 * @param string|array $query Query arguments to add to link
	 * @return string Login page link with optional $query arguments appended
	 */
	public function tml_page_link( $link, $query = '' ) {
		global $theme_my_login, $wp_rewrite;

		if ( $wp_rewrite->using_permalinks() ) {
			$q = wp_parse_args( $query );

			$action = isset( $q['action'] ) ? $q['action'] : 'login';
			if ( $slug = $this->get_option( $action ) ) {
				unset( $q['action'] );
			} else {
				if ( ! $slug = $this->get_option( 'login' ) )
					return $link;
			}

			$page =& get_page( $theme_my_login->get_option( 'page_id' ) );

			$slug = str_replace( $page->post_name, $slug, get_page_uri( $page->ID ) );

			$link = $wp_rewrite->get_page_permastruct();
			$link = str_replace( '%pagename%', $slug, $link );
			$link = home_url( $link );
			$link = user_trailingslashit( $link, 'page' );

			if ( ! empty( $q ) )
				$link = add_query_arg( $q, $link );
		}

		return $link;
	}

	/**
	 * Filters logout URL to allow for logout permalink
	 *
	 * This is needed because WP doesn't pass the action parameter to site_url
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $logout_url Logout URL
	 * @param string $redirect Redirect URL
	 * @return string Logout URL
	 */
	public function logout_url( $logout_url, $redirect ) {
		$logout_url = site_url( 'wp-login.php?action=logout' );
		if ( $redirect )
			$logout = add_query_arg( 'redirect_to', urlencode( $redirect ), $logout_url );
		$logout_url = wp_nonce_url( $logout_url, 'log-out' );
		return $logout_url;
	}

	/**
	 * Handles permalink rewrite rules
	 *
	 * @since 6.2.2
	 * @access public
	 *
	 * @param array $rules Rewrite rules
	 * @return array Rewrite rules
	 */
	function rewrite_rules_array( $rules ) {
		global $theme_my_login;

		$page =& get_page( $theme_my_login->get_option( 'page_id' ) );

		$page_uri = get_page_uri( $page->ID );

		$tml_rules = array();
		foreach ( $this->get_options() as $action => $slug ) {
			if ( !empty( $slug ) ) {
				$slug = str_replace( $page->post_name, $slug, $page_uri );
				$tml_rules["{$slug}/?$"] = "index.php?page_id={$page->ID}&action={$action}";
			}
		}
		return array_merge( $tml_rules, $rules );
	}
}
endif; // Class exists

