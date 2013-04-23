<?php
/**
 * Holds Theme My Login Themed Profiles Admin class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Themed_Profiles
 * @since 6.2
 */

if ( ! class_exists( 'Theme_My_Login_Themed_Profiles_Admin' ) ) :
/**
 * Theme My Login Themed Profiles Admin class
 *
 * @since 6.2
 */
class Theme_My_Login_Themed_Profiles_Admin extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @var string
	 */
	protected $options_key = 'theme_my_login_themed_profiles';

	/**
	 * Returns default options
	 *
	 * @since 6.3
	 */
	public static function default_options() {
		return Theme_My_Login_Themed_Profiles::default_options();
	}

	/**
	 * Constructor
	 *
	 * @since 6.4
	 */
	public function __construct() {
		// Load options
		$this->load_options();

		add_action( 'tml_activate_themed-profiles/themed-profiles.php',  array( $this, 'activate'  ) );
		add_action( 'tml_uninstall_themed-profiles/themed-profiles.php', array( $this, 'uninstall' ) );

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	/**
	 * Activates the module
	 *
	 * Callback for "tml_activate_themed-profiles/themed-profiles.php" hook in method Theme_My_Login_Modules_Admin::activate_module()
	 *
	 * @see Theme_My_Login_Modules_Admin::activate_module()
	 * @since 6.0
	 */
	public function activate() {
		wp_cache_delete( 'profile', 'tml_page_ids' );
		if ( ! $page_id = Theme_My_Login::get_page_id( 'profile' ) ) {
			$page_id = wp_insert_post( array(
				'post_title'     => __( 'Your Profile' ),
				'post_status'    => 'publish',
				'post_type'      => 'tml_page',
				'post_content'   => '[theme-my-login]',
				'comment_status' => 'closed',
				'ping_status'    => 'closed'
			) );
			update_post_meta( $page_id, '_tml_action', 'profile' );

			flush_rewrite_rules( false );
		}
	}

	/**
	 * Uninstalls the module
	 *
	 * Callback for "tml_uninstall_themed-profiles/themed-profiles.php" hook in method Theme_My_Login_Admin::uninstall()
	 *
	 * @see Theme_My_Login_Admin::uninstall()
	 * @since 6.3
	 */
	public function uninstall() {
		delete_option( $this->options_key );
	}

	/**
	 * Adds "Themed Profiles" tab to Theme My Login menu
	 *
	 * Callback for "admin_menu" hook
	 *
	 * @since 6.3
	 */
	public function admin_menu() {
		add_submenu_page(
			'theme_my_login',
			__( 'Theme My Login Themed Profiles Settings', 'theme-my-login' ),
			__( 'Themed Profiles', 'theme-my-login' ),
			'manage_options',
			$this->options_key,
			array( $this, 'settings_page' )
		);

		add_settings_section( 'general', null, '__return_false', $this->options_key );

		add_settings_field( 'themed_profiles', __( 'Themed Profiles',       'theme-my-login' ), array( $this, 'settings_field_themed_profiles'       ), $this->options_key, 'general' );
		add_settings_field( 'restrict_admin',  __( 'Restrict Admin Access', 'theme-my-login' ), array( $this, 'settings_field_restrict_admin_access' ), $this->options_key, 'general' );
	}

	/**
	 * Registers options group
	 *
	 * Callback for "admin_init" hook
	 *
	 * @since 6.3
	 */
	public function admin_init() {
		register_setting( $this->options_key, $this->options_key, array( &$this, 'save_settings' ) );
	}

	/**
	 * Renders settings page
	 *
	 * Callback for add_submenu_page()
	 *
	 * @since 6.3
	 */
	public function settings_page() {
		Theme_My_Login_Admin::settings_page( array(
			'title'       => __( 'Theme My Login Themed Profiles Settings', 'theme-my-login' ),
			'options_key' => $this->options_key
		) );
	}

	/**
	 * Renders Themed Profiles settings field
	 *
	 * @since 6.3
	 */
	public function settings_field_themed_profiles() {
		global $wp_roles;

		foreach ( $wp_roles->get_names() as $role => $role_name ) {
			if ( 'pending' == $role )
				continue;
			?>
            <input name="<?php echo $this->options_key; ?>[<?php echo $role; ?>][theme_profile]" type="checkbox" id="<?php echo $this->options_key; ?>_<?php echo $role; ?>_theme_profile" value="1"<?php checked( $this->get_option( array( $role, 'theme_profile' ) ) ); ?> />
            <label for="<?php echo $this->options_key; ?>_<?php echo $role; ?>_theme_profile"><?php echo $role_name; ?></label><br />
    		<?php 
    	}
	}

	/**
	 * Renders Restrict Admin Access settings field
	 *
	 * @since 6.3
	 */
	public function settings_field_restrict_admin_access() {
		global $wp_roles;

		foreach ( $wp_roles->get_names() as $role => $role_name ) {
			if ( 'pending' == $role )
				continue;
			?>
			<input name="<?php echo $this->options_key; ?>[<?php echo $role; ?>][restrict_admin]" type="checkbox" id="<?php echo $this->options_key; ?>_<?php echo $role; ?>_restrict_admin" value="1"<?php checked( $this->get_option( array( $role, 'restrict_admin' ) ) ); ?><?php if ( 'administrator' == $role ) echo ' disabled="disabled"'; ?> />
			<label for="<?php echo $this->options_key; ?>_<?php echo $role; ?>_restrict_admin"><?php echo $role_name; ?></label><br />
			<?php
		}
	}

	/**
	 * Sanitizes settings
	 *
	 * Callback for register_setting()
	 *
	 * @since 6.2
	 *
	 * @param array $settings Settings passed in from filter
	 * @return array Sanitized settings
	 */
	public function save_settings( $settings ) {
		global $wp_roles;

		foreach( $wp_roles->get_names() as $role => $role_name ) {
			if ( 'pending' != $role ) {
				$settings[$role] = array(
					'theme_profile'  => ! empty( $settings[$role]['theme_profile']  ),
					'restrict_admin' => ! empty( $settings[$role]['restrict_admin'] )
				);
			}
		}
		return $settings;
	}
}
endif; // Class exists
