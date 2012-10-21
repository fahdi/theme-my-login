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
	 * Returns singleton instance
	 *
	 * @since 6.3
	 * @access public
	 * @return object
	 */
	public static function get_object() {
		return parent::get_object( __CLASS__ );
	}

	/**
	 * Loads the module
	 *
	 * @since 6.3
	 * @access protected
	 */
	protected function load() {
		add_filter( 'tml_page_link_slug', array( &$this, 'tml_page_link_slug' ) );
	}

	public function tml_page_link_slug( $action ) {
		if ( $slug = $this->get_option( $action ) )
			return $slug;
		return $action;
	}
}

Theme_My_Login_Custom_Permalinks::get_object();

endif;

if ( is_admin() )
	include_once( dirname( __FILE__ ) . '/admin/custom-permalinks-admin.php' );

