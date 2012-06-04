<?php
/**
 * Holds Theme My Login Custom Permalinks Admin class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Custom_Permalinks
 * @since 6.3
 */

if ( !class_exists( 'Theme_My_Login_Custom_Permalinks_Admin' ) ) :
/**
 * Theme My Login Custom Permalinks class
 *
 * Adds the ability to set permalinks for default actions.
 *
 * @since 6.3
 */
class Theme_My_Login_Custom_Permalinks_Admin extends Theme_My_Login_Abstract {
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
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'load-tml_page_theme_my_login_custom_permalinks', array( &$this, 'load_settings_page' ) );
	}

	/**
	 * Adds "Permalinks" to the Theme My Login menu
	 *
	 * @since 6.3
	 * @access public
	 */
	public function admin_menu() {
		add_submenu_page(
			'theme_my_login',
			__( 'Theme My Login Custom Permalinks Settings', 'theme-my-login' ),
			__( 'Permalinks', 'theme-my-login' ),
			'manage_options',
			$this->options_key,
			array( &$this, 'settings_page' )
		);

		add_settings_section( 'general', null, '__return_false', $this->options_key );

		$actions = array(
			'login'        => __( 'Login', 'theme-my-login' ),
			'register'     => __( 'Register', 'theme-my-login' ),
			'lostpassword' => __( 'Lost Password', 'theme-my-login' )
		);
		foreach ( $actions as $action => $name ) {
			add_settings_field( $action, $name, array( &$this, 'settings_field_permalink' ), $this->options_key, 'general', array(
				'action' => $action
			) );
		}
	}

	/**
	 * Registers options group
	 *
	 * This is used because register_setting() isn't available until the "admin_init" hook.
	 *
	 * @since 6.3
	 * @access public
	 */
	public function admin_init() {
		register_setting( $this->options_key, $this->options_key,  array( &$this, 'save_settings' ) );

		// Flush rewrite rules if slugs have been updated
		if ( $this->get_option( 'flush_rules' ) ) {
			// Flush rewrite rules
			flush_rewrite_rules();
			// Unset the option
			$this->delete_option( 'flush_rules' );
			// Update the options in the DB
			$this->save_options();
		}
	}

	public function load_settings_page() {
		global $wp_rewrite;

		if ( ! $wp_rewrite->using_permalinks() )
			add_settings_error( $this->options_key, 'permalinks_disabled', sprintf( __( '<strong>ERROR</strong>: You must <a href="%s">enable permalinks</a> in order for these settings to be applied.', 'theme-my-login' ), admin_url( 'options-permalink.php' ) ) );

	}

	/**
	 * Renders the settings page
	 *
	 * @since 6.0
	 * @access public
	 */
	public function settings_page( $args = '' ) {
		Theme_My_Login_Admin::display_settings_page( array(
			'title'         => __( 'Theme My Login Custom Permalinks Settings', 'theme-my-login' ),
			'options_group' => $this->options_key,
			'options_page'  => $this->options_key
		) );
	}

	/**
	 * Sanitizes module settings
	 *
	 * This is the callback for register_setting()
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string|array $settings Settings passed in from filter
	 * @return string|array Sanitized settings
	 */
	public function save_settings( $settings ) {
		// Flush permalinks if they have changed
		foreach ( $settings as $action => &$slug ) {
			$slug = sanitize_title( $slug );
			if ( $slug !== $this->get_option( $action ) ) {
				// Save an option to flush after "init", where the new rules are added
				$settings['flush_rules'] = true;
				break;
			}
		}
		return $settings;
	}

	/**
	 * Outputs HTML for "Permalinks" settings tab
	 *
	 * @since 6.2
	 * @access public
	 */
	public function settings_field_permalink( $args = '' ) {
		extract( $args );
		?>
		<input name="<?php echo $this->options_key; ?>[<?php echo $action; ?>]" type="text" id="<?php echo $this->options_key; ?>_<?php echo $action; ?>" value="<?php echo $this->get_option( $action ); ?>" class="regular-text" />
		<?php
	}
}

/**
 * Holds the reference to Theme_My_Login_Custom_Permalinks_Admin object
 * @global object $theme_my_login_custom_permalinks_admin
 * @since 6.3
 */
$theme_my_login_custom_permalinks_admin = new Theme_My_Login_Custom_Permalinks_Admin;

endif; // Class exists

