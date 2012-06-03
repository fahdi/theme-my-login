<?php
/**
 * Holds the Theme My Login Modules Admin class
 *
 * @package Theme_My_Login
 * @since 6.3
 */

if ( !class_exists( 'Theme_My_Login_Modules_Admin' ) ) :
/**
 * Theme My Login Modules Admin class
 *
 * @since 6.3
 */
class Theme_My_Login_Modules_Admin extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_modules';

	/**
	 * Loads object
	 *
	 * @since 6.3
	 * @access public
	 */
	protected function load() {
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
	}

	/**
	 * Builds plugin admin menu and pages
	 *
	 * @since 6.3
	 * @access public
	 */
	public function admin_menu() {
		add_submenu_page(
			'theme_my_login',
			__( 'Theme My Login Modules', 'theme-my-login' ),
			__( 'Modules', 'theme-my-login' ),
			'manage_options',
			$this->options_key,
			array( &$this, 'display_settings_page' )
		);

		// Modules section
		add_settings_section( 'modules', __( 'Modules', 'theme-my-login' ), '__return_false', $this->options_key );

		// Modules fields
		foreach ( get_plugins( '/theme-my-login/modules' ) as $path => $details ) {
			add_settings_field( sanitize_title( $details['Name'] ), $details['Name'], array( &$this, 'settings_field_module' ), $this->options_key, 'modules', array(
				'name'        => $details['Name'],
				'description' => $details['Description'],
				'path'        => $path
			) );
		}
	}

	/**
	 * Registers settings
	 *
	 * This is used because register_setting() isn't available until the "admin_init" hook.
	 *
	 * @since 6.3
	 * @access public
	 */
	public function admin_init() {
		register_setting( $this->options_key, $this->options_key,  array( &$this, 'save_settings' ) );
	}

	/**
	 * Renders the settings page
	 *
	 * @since 6.3
	 * @access public
	 */
	public function display_settings_page() {
		Theme_My_Login_Admin::display_settings_page( array(
			'title'         => __( 'Theme My Login Modules', 'theme-my-login' ),
			'options_group' => $this->options_key,
			'options_page'  => $this->options_key
		) );
	}

	/**
	 * Renders Module settings fields
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_field_module( $args ) {
		$id = sanitize_title( $args['name'] );
		?>
		<input name="<?php echo $this->options_key; ?>[]" type="checkbox" id="<?php echo $this->options_key; ?>_<?php echo $id; ?>" value="<?php echo $args['path']; ?>"<?php checked( 1, in_array( $args['path'], (array) $this->get_options() ) ); ?> />
		<label for="<?php echo $this->options_key; ?>_<?php echo $id; ?>"><?php printf( __( 'Enable %s', 'theme-my-login' ), $args['name'] ); ?></label><br />
		<?php if ( $args['description'] ) : ?>
		<p class="description"><?php echo $args['description']; ?></p>
		<?php endif;
	}

	/**
	 * Sanitizes settings
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
		// Get selected modules
		$modules = isset( $_POST['theme_my_login_modules'] ) ? $_POST['theme_my_login_modules'] : array();

		// If we have modules to activate
		if ( $activate = array_diff( (array) $modules, (array) $this->get_options() ) ) {
			// Attempt to activate them
			$result = $this->activate_modules( $activate );
			// Check for WP_Error
			if ( is_wp_error( $result ) ) {
				// Loop through each module in the WP_Error object
				foreach ( $result->get_error_data( 'modules_invalid' ) as $module => $wp_error ) {
					// Store the module and error message to a temporary array which will be passed to 'admin_notices'
					if ( is_wp_error( $wp_error ) )
						add_settings_error( $this->options_key, $wp_error->get_error_code(), $wp_error->get_error_message() );
				}
			}
		}

		// If we have modules to deactivate
		if ( $deactivate = array_diff( (array) $this->get_options(), $modules ) ) {
			// Deactive them
			$this->deactivate_modules( $deactivate );
		}

		return $this->options;
	}

	/**
	 * Activates a module
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $module Module to activate
	 * @return null|WP_Error True on success, WP_Error on error
	 */
	public function activate_module( $module ) {
		global $theme_my_login_modules;

		$module = plugin_basename( trim( $module ) );
		$valid = $this->validate_module( $module );
		if ( is_wp_error( $valid ) )
			return $valid;

		$current = (array) $this->get_options();
		if ( !$theme_my_login_modules->is_module_active( $module ) ) {
			ob_start();
			@include ( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module );
			$current[] = $module;
			sort( $current );
			do_action( 'tml_activate_module', trim( $module ) );
			$this->set_options( $current );
			do_action( 'tml_activate_' . trim( $module ) );
			do_action( 'tml_activated_module', trim( $module ) );
			ob_end_clean();
		}
		return null;
	}

	/**
	 * Activates one or more module(s)
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string|array $modules Module(s) to activate
	 * @return bool|WP_Error True on succes, WP_Error on error
	 */
	public function activate_modules( $modules ) {
		if ( !is_array( $modules ) )
			$modules = array( $modules );

		$errors = array();
		foreach ( (array) $modules as $module ) {
			$result = $this->activate_module( $module );
			if ( is_wp_error( $result ) )
				$errors[$module] = $result;
		}

		if ( !empty( $errors ) )
			return new WP_Error( 'modules_invalid', __( 'One of the modules is invalid.', 'theme-my-login' ), $errors );

		return true;
	}

	/**
	 * Deactivates one or more module(s)
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string|array $plugins Module(s) to deactivate
	 * @param bool $silent If true, prevents calling deactivate hook
	 */
	public function deactivate_modules( $modules, $silent = false ) {
		global $theme_my_login_modules;

		$current = (array) $this->get_options();

		if ( !is_array( $modules ) )
			$modules = array( $modules );

		foreach ( $modules as $module ) {
			$module = plugin_basename( $module );
			if( !$theme_my_login_modules->is_module_active( $module ) )
				continue;

			if ( !$silent )
				do_action( 'tml_deactivate_module', trim( $module ) );

			$key = array_search( $module, (array) $current );

			if ( false !== $key )
				array_splice( $current, $key, 1 );

			if ( !$silent ) {
				do_action( 'tml_deactivate_' . trim( $module ) );
				do_action( 'tml_deactivated_module', trim( $module ) );
			}
		}

		$this->set_options( $current );
	}

	/**
	 * Validates a module
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $module Module path
	 * @return int|WP_Error 0 on success, WP_Error on failure.
	 */
	public static function validate_module( $module ) {
		if ( validate_file( $module ) )
			return new WP_Error( 'module_invalid', __( 'Invalid module path.', 'theme-my-login' ) );
		if ( !file_exists( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module ) )
			return new WP_Error( 'module_not_found', __( 'Module file does not exist.', 'theme-my-login' ) );

		$installed_modules = get_plugins( '/theme-my-login/modules' );
		if ( !isset( $installed_modules[$module] ) )
			return new WP_Error( 'no_module_header', __( 'The module does not have a valid header.', 'theme-my-login' ) );
		return 0;
	}
}
endif; // Class exists

