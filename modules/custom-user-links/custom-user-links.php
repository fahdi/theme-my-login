<?php
/**
 * Plugin Name: Custom User Links
 * Description: Enabling this module will initialize custom user links. You will then have to configure the settings via the "User Links" tab.
 *
 * Holds Theme My Login Custom User Links class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Custom_User_Links
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_Custom_User_Links' ) ) :
/**
 * Theme My Login Custom User Links module class
 *
 * Adds the ability to define custom links to display to a user when logged in based upon their "user role".
 *
 * @since 6.0
 */
class Theme_My_Login_Custom_User_Links extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @var string
	 */
	protected $options_key = 'theme_my_login_user_links';

	/**
	 * Returns default options
	 *
	 * @since 6.3
	 *
	 * @return array Default options
	 */
	public static function default_options() {
		global $wp_roles;

		if ( empty( $wp_roles ) )
			$wp_roles = new WP_Roles;

		$options = array();
		foreach ( $wp_roles->get_names() as $role => $role_name ) {
			if ( 'pending' != $role ) {
				$options[$role] = array(
					array(
						'title' => __( 'Dashboard' ),
						'url'   => admin_url()
					),
					array(
						'title' => __( 'Profile' ),
						'url'   => admin_url( 'profile.php' )
					)
				);
			}
		}
		return $options;
	}

	/**
	 * Constructor
	 *
	 * @since 6.0
	 */
	public function __construct() {
		// Load options
		$this->load_options();

		add_filter( 'tml_user_links', array( $this, 'get_user_links' ) );

		// Load admin
		if ( is_admin() ) {
			require_once( WP_PLUGIN_DIR . '/theme-my-login/modules/custom-user-links/admin/custom-user-links-admin.php' );

			$this->admin = new Theme_My_Login_Custom_User_Links_Admin;
		}
	}

	/**
	 * Gets the user links for the current user's role
	 *
	 * Callback for "tml_user_links" hook in method Theme_My_Login_Template::display()
	 *
	 * @see Theme_My_Login_Template::display()
	 * @since 6.0
	 *
	 * @param array $links Default user links
	 * @return array New user links
	 */
	public function get_user_links( $links = array() ) {
		if ( ! is_user_logged_in() )
			return $links;

		$current_user = wp_get_current_user();
		if ( is_multisite() && empty( $current_user->roles ) )
			$current_user->roles = array( 'subscriber' );

		foreach( (array) $current_user->roles as $role ) {
			if ( $links = $this->get_option( $role ) );
				break;
		}

		// Define and allow filtering of replacement variables
		$replacements = apply_filters( 'tml_custom_user_links_variables', array(
			'%user_id%'  => $current_user->ID,
			'%username%' => $current_user->user_nicename
		) );

		// Replace variables in link
		foreach ( (array) $links as $key => $link ) {
			$links[$key]['url'] = Theme_My_Login_Common::replace_vars( $link['url'], $current_user->ID, $replacements );
		}

		return $links;
	}
}

/**
 * Load the Custom User Links module
 */
Theme_My_Login::get_object()->load_module( 'custom-user-links', 'Theme_My_Login_Custom_User_Links' );

endif; // Class exists
