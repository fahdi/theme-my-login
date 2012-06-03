<?php
/**
 * Plugin Name: Custom Permalinks
 * Description: Enabling this module will initialize custom permalinks. You will then have to configure the settings via the "Permalinks" tab.
 *
 * Holds Theme My Login Custom Permalinks class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Custom_Permalinks
 * @since 6.3
 */

if ( !class_exists( 'Theme_My_Login_Custom_Permalinks' ) ) :
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
	protected $options_key = 'theme_my_login_custom_permalinks';

	/**
	 * Loads the module
	 *
	 * @since 6.3
	 * @access protected
	 */
	protected function load() {
		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'parse_request', array( &$this, 'parse_request' ), 0 );
		add_filter( 'page_link', array( &$this, 'page_link' ), 10, 2 );
		add_filter( 'tml_page_link', array( &$this, 'tml_page_link' ), 10, 2 );
		add_filter( 'tml_redirect_url', array( &$this, 'tml_redirect_url' ), 10, 2 );
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
		global $wp, $theme_my_login;

		$wp->add_query_var( 'action' );
		
		$page_id = $theme_my_login->get_option( 'page_id' );

		foreach ( $this->get_options() as $action => $slug ) {
			if ( !empty( $slug ) )
				add_rewrite_rule( "$slug/?$", "index.php?page_id=$page_id&action=$action", 'top' );
		}
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

		if ( ! empty( $wp->query_vars['action'] ) )
			$theme_my_login->request_action = $wp->query_vars['action'];
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
					$slug = get_post_field( 'post_name', $theme_my_login->get_option( 'page_id' ) );
			}
			$link = $wp_rewrite->get_page_permastruct();
			$link = str_replace( '%pagename%', $slug, $link );
			$link = home_url( $link );
			$link = user_trailingslashit( $link, 'page' );

			if ( !empty( $q ) )
				$link = add_query_arg( $q, $link );
		}

		return $link;
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
}

/**
 * Holds the reference to Theme_My_Login_Custom_Permalinks object
 * @global object $theme_my_login_custom_permalinks
 * @since 6.3
 */
$theme_my_login_custom_permalinks = new Theme_My_Login_Custom_Permalinks;

endif; // Class exists

if ( is_admin() )
	include_once( WP_PLUGIN_DIR . '/theme-my-login/modules/custom-permalinks/admin/custom-permalinks-admin.php' );

