<?php
/**
 * Holds the Theme My Login Admin class
 *
 * @package Theme_My_Login
 * @since 6.0
 */

if ( !class_exists( 'Theme_My_Login_Admin' ) ) :
/**
 * Theme My Login Admin class
 *
 * @since 6.0
 */
class Theme_My_Login_Admin extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login';

	/**
	 * Returns default options
	 *
	 * @since 6.3
	 * @access public
	 */
	public function default_options() {
		return Theme_My_Login::default_options();
	}

	/**
	 * Loads object
	 *
	 * @since 6.3
	 * @access public
	 */
	protected function load() {
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_notices', array( &$this, 'module_errors' ) );

		register_activation_hook( WP_PLUGIN_DIR . '/theme-my-login/theme-my-login.php', array( 'Theme_My_Login_Admin', 'install' ) );
		register_uninstall_hook( WP_PLUGIN_DIR . '/theme-my-login/theme-my-login.php', array( 'Theme_My_Login_Admin', 'uninstall' ) );
	}

	/**
	 * Builds plugin admin menu and pages
	 *
	 * @since 6.0
	 * @access public
	 */
	public function admin_menu() {
		global $wp_rewrite;

		add_menu_page(
			__( 'Theme My Login Settings', 'theme-my-login' ),
			__( 'TML', 'theme-my-login' ),
			'manage_options',
			'theme_my_login',
			array( &$this, 'display_settings_page' )
		);
		add_submenu_page(
			'theme_my_login',
			__( 'General', 'theme-my-login' ),
			__( 'General', 'theme-my-login' ),
			'manage_options',
			'theme_my_login',
			array( &$this, 'display_settings_page' )
		);

		// General section
		add_settings_section( 'general', __( 'General', 'theme-my-login' ), '__return_false',  'theme_my_login' );

		// General fields
		add_settings_field( 'page_id',     __( 'Page ID', 'theme-my-login' ),      array( &$this, 'settings_field_page_id' ),     'theme_my_login', 'general' );
		add_settings_field( 'show_page',   __( 'Pagelist', 'theme-my-login' ),     array( &$this, 'settings_field_show_page' ),   'theme_my_login', 'general' );
		add_settings_field( 'enable_css',  __( 'Stylesheet', 'theme-my-login' ),   array( &$this, 'settings_field_enable_css' ),  'theme_my_login', 'general' );
		add_settings_field( 'email_login', __( 'E-mail Login', 'theme-my-login' ), array( &$this, 'settings_field_email_login' ), 'theme_my_login', 'general' );

		// Modules section
		add_settings_section( 'modules', __( 'Modules', 'theme-my-login' ), '__return_false', 'theme_my_login' );

		// Modules fields
		foreach ( get_plugins( '/theme-my-login/modules' ) as $path => $details ) {
			add_settings_field( sanitize_title( $details['Name'] ), $details['Name'], array( &$this, 'settings_field_module' ), 'theme_my_login', 'modules', array(
				'name'        => $details['Name'],
				'description' => $details['Description'],
				'path'        => $path
			) );
		}
	}

	/**
	 * Registers TML settings
	 *
	 * This is used because register_setting() isn't available until the "admin_init" hook.
	 *
	 * @since 6.0
	 * @access public
	 */
	public function admin_init() {
		register_setting( 'theme_my_login', 'theme_my_login',  array( &$this, 'save_settings' ) );
	}

	/**
	 * Outputs HTML for module errors
	 *
	 * @since 6.0
	 * @access public
	 */
	public function module_errors() {
		$module_errors = $this->get_option( 'module_errors' );
		// If we have errors to display
		if ( $module_errors && current_user_can( 'manage_options' ) ) {
			// Display them
			echo '<div class="error">';
			foreach ( (array) $module_errors as $module => $error ) {
				echo '<p><strong>' . sprintf( __( 'ERROR: The module "%1$s" could not be activated (%2$s).', 'theme-my-login' ), $module, $error ) . '</strong></p>';
			}
			echo '</div>';
			// Unset the error array
			$this->delete_option( 'module_errors' );
			// Update the options in the DB
			$this->save_options();
		}
	}

	/**
	 * Renders the settings page
	 *
	 * @since 6.0
	 * @access public
	 */
	public function display_settings_page( $args = '' ) {
		extract( wp_parse_args( $args, array(
			'title'        => __( 'Theme My Login Settings', 'theme-my-login' ),
			'options_group' => 'theme_my_login',
			'options_page'  => 'theme_my_login'
		) ) );
		?>
		<div class="wrap">
			<?php screen_icon( 'options-general' ); ?>
			<h2><?php echo esc_html( $title ); ?></h2>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( $options_group );
					do_settings_sections( $options_page );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Renders Page ID settings field
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_field_page_id() {
		?>
		<input name="theme_my_login[page_id]" type="text" id="theme_my_login_page_id" value="<?php echo (int) $this->get_option( 'page_id' ); ?>" class="small-text" />
		<p class="description"><?php _e( 'This should be the ID of the WordPress page that includes the [theme-my-login] shortcode. By default, this page is titled "Login".', 'theme-my-login' ); ?></p>
        <?php
	}

	/**
	 * Renders Pagelist settings field
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_field_show_page() {
		?>
		<input name="theme_my_login[show_page]" type="checkbox" id="theme_my_login_show_page" value="1"<?php checked( 1, $this->get_option( 'show_page' ) ); ?> />
		<label for="theme_my_login_show_page"><?php _e( 'Show Page In Pagelist', 'theme-my-login' ); ?></label>
		<p class="description"><?php _e( 'Enable this setting to add login/logout links to the pagelist generated by functions like wp_list_pages() and wp_page_menu().', 'theme-my-login' ); ?></p>
		<?php
	}

	/**
	 * Renders Stylesheet settings field
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_field_enable_css() {
		?>
		<input name="theme_my_login[enable_css]" type="checkbox" id="theme_my_login_enable_css" value="1"<?php checked( 1, $this->get_option( 'enable_css' ) ); ?> />
		<label for="theme_my_login_enable_css"><?php _e( 'Enable "theme-my-login.css"', 'theme-my-login' ); ?></label>
		<p class="description"><?php _e( 'In order to keep changes between upgrades, you can store your customized "theme-my-login.css" in your current theme directory.', 'theme-my-login' ); ?></p>
        <?php
	}

	/**
	 * Renders E-mail Login settings field
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_field_email_login() {
		?>
		<input name="theme_my_login[email_login]" type="checkbox" id="theme_my_login_email_login" value="1"<?php checked( 1, $this->get_option( 'email_login' ) ); ?> />
		<label for="theme_my_login_email_login"><?php _e( 'Enable e-mail address login', 'theme-my-login' ); ?></label>
		<p class="description"><?php _e( 'Allows users to login using their e-mail address in place of their username.', 'theme-my-login' ); ?></p>
    	<?php
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
		<input name="theme_my_login_modules[]" type="checkbox" id="theme_my_login_modules_<?php echo $id; ?>" value="<?php echo $args['path']; ?>"<?php checked( 1, in_array( $args['path'], (array) $this->get_option( 'active_modules', array() ) ) ); ?> />
		<label for="theme_my_login_modules_<?php echo $id; ?>"><?php printf( __( 'Enable %s', 'theme-my-login' ), $args['name'] ); ?></label><br />
		<?php if ( $args['description'] ) : ?>
		<p class="description"><?php echo $args['description']; ?></p>
		<?php endif;
	}

	/**
	 * Sanitizes TML settings
	 *
	 * This is the callback for register_setting()
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $settings Settings passed in from filter
	 * @return string|array Sanitized settings
	 */
	public function save_settings( $settings ) {
		// Sanitize new settings
		$settings['page_id'] = absint( $settings['page_id'] );
		$settings['show_page'] = isset( $settings['show_page'] );
		$settings['enable_css'] = isset( $settings['enable_css'] );
		$settings['email_login'] = isset( $settings['email_login'] );

		$modules = isset( $_POST['theme_my_login_modules'] ) ? $_POST['theme_my_login_modules'] : array();

		// If we have modules to activate
		if ( $activate = array_diff( (array) $modules, (array) $this->get_option( 'active_modules' ) ) ) {
			// Attempt to activate them
			$result = $this->activate_modules( $activate );
			// Check for WP_Error
			if ( is_wp_error( $result ) ) {
				// Loop through each module in the WP_Error object
				foreach ( $result->get_error_data( 'modules_invalid' ) as $module => $wp_error ) {
					// Store the module and error message to a temporary array which will be passed to 'admin_notices'
					if ( is_wp_error( $wp_error ) )
						$this->set_option( array( 'module_errors', $module ), $wp_error->get_error_message() );
				}
			}
		}

		// If we have modules to deactivate
		if ( $deactivate = array_diff( (array) $this->get_option( 'active_modules' ), $modules ) ) {
			// Deactive them
			$this->deactivate_modules( $deactivate );
		}

		// Merge current settings
		$settings = Theme_My_Login_Common::array_merge_recursive( $this->options, $settings );

		return $settings;
	}

	/**
	 * Activates a TML module
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $module Module to activate
	 * @return null|WP_Error True on success, WP_Error on error
	 */
	public function activate_module( $module ) {
		global $theme_my_login;

		$module = plugin_basename( trim( $module ) );
		$valid = $this->validate_module( $module );
		if ( is_wp_error( $valid ) )
			return $valid;

		$current = (array) $this->get_option( 'active_modules' );
		if ( !$theme_my_login->is_module_active( $module ) ) {
			ob_start();
			@include ( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module );
			$current[] = $module;
			sort( $current );
			do_action( 'tml_activate_module', trim( $module ) );
			$this->set_option( 'active_modules', $current );
			do_action_ref_array( 'tml_activate_' . trim( $module ), array( &$theme_my_login ) );
			do_action( 'tml_activated_module', trim( $module ) );
			ob_end_clean();
		}
		return null;
	}

	/**
	 * Activates one or more TML module(s)
	 *
	 * @since 6.0
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
	 * Deactivates one or more TML module(s)
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $plugins Module(s) to deactivate
	 * @param bool $silent If true, prevents calling deactivate hook
	 */
	public function deactivate_modules( $modules, $silent = false ) {
		global $theme_my_login;

		$current = (array) $this->get_option( 'active_modules' );

		if ( !is_array( $modules ) )
			$modules = array( $modules );

		foreach ( $modules as $module ) {
			$module = plugin_basename( $module );
			if( !$theme_my_login->is_module_active( $module ) )
				continue;

			if ( !$silent )
				do_action( 'tml_deactivate_module', trim( $module ) );

			$key = array_search( $module, (array) $current );

			if ( false !== $key )
				array_splice( $current, $key, 1 );

			if ( !$silent ) {
				do_action_ref_array( 'tml_deactivate_' . trim( $module ), array( &$theme_my_login ) );
				do_action( 'tml_deactivated_module', trim( $module ) );
			}
		}

		$this->set_option( 'active_modules', $current );
	}

	/**
	 * Validates a TML module
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $module Module path
	 * @return int|WP_Error 0 on success, WP_Error on failure.
	 */
	public function validate_module( $module ) {
		if ( validate_file( $module ) )
			return new WP_Error( 'module_invalid', __( 'Invalid module path.', 'theme-my-login' ) );
		if ( !file_exists( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module ) )
			return new WP_Error( 'module_not_found', __( 'Module file does not exist.', 'theme-my-login' ) );

		$installed_modules = get_plugins( '/theme-my-login/modules' );
		if ( !isset( $installed_modules[$module] ) )
			return new WP_Error( 'no_module_header', __( 'The module does not have a valid header.', 'theme-my-login' ) );
		return 0;
	}

	/**
	 * Wrapper for multisite installation
	 *
	 * @since 6.1
	 * @access public
	 */
	public function install() {
		global $wpdb;

		if ( is_multisite() ) {
			if ( isset( $_GET['networkwide'] ) && ( $_GET['networkwide'] == 1 ) ) {
				$blogids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs" ) );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					Theme_My_Login_Admin::_install();
				}
				restore_current_blog();
				return;
			}	
		}
		Theme_My_Login_Admin::_install();
	}

	/**
	 * Installs TML
	 *
	 * @since 6.0
	 * @access protected
	 */
	protected function _install() {
		// Declare page_id to avoid notices
		$page_id = 0;

		// Current version
		$version = $this->get_option( 'version' );

		// 4.4 upgrade
		if ( version_compare( $version, '4.4', '<' ) ) {
			remove_role( 'denied' );
		}
		// 6.0 upgrade
		if ( version_compare( $version, '6.0', '<' ) ) {

		}
		// 6.3 upgrade
		if ( version_compare( $version, '6.3', '<' ) ) {
			$this->delete_option( 'initial_nag' );
		}

		// Get existing page ID
		$page_id = $this->get_option( 'page_id' );

		// Check if page exists
		$page = ( $page_id ) ? get_page( $page_id ) : get_page_by_title( 'Login' );

		// Maybe create login page?
		if ( $page ) {
			$page_id = $page->ID;
			// Make sure the page is not in the trash
			if ( 'trash' == $page->post_status )
				wp_untrash_post( $page_id );
		} else {
			$insert = array(
				'post_title' => 'Login',
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_content' => '[theme-my-login]',
				'comment_status' => 'closed',
				'ping_status' => 'closed'
				);
			$page_id = wp_insert_post( $insert );
		}

		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/theme-my-login/theme-my-login.php' );
		$this->set_option( 'version', $plugin_data['Version'] );
		$this->set_option( 'page_id', (int) $page_id );
		$this->save_options();

		return $page_id;
	}

	/**
	 * Wrapper for multisite uninstallation
	 *
	 * @since 6.1
	 * @access public
	 */
	public function uninstall() {
		global $wpdb;

		if ( is_multisite() ) {
			if ( isset( $_GET['networkwide'] ) && ( $_GET['networkwide'] == 1 ) ) {
				$blogids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs" ) );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					$this->_uninstall();
				}
				restore_current_blog();
				return;
			}	
		}
		Theme_My_Login_Admin::_uninstall();
	}

	/**
	 * Uninstalls TML
	 *
	 * @since 6.0
	 * @access protected
	 */
	protected function _uninstall() {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		// Run module uninstall hooks
		$modules = get_plugins( '/theme-my-login/modules' );
		foreach ( array_keys( $modules ) as $module ) {
			$module = plugin_basename( trim( $module ) );

			$valid = Theme_My_Login_Admin::validate_module( $module );
			if ( is_wp_error( $valid ) )
				continue;

			@include ( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module );
			do_action( 'uninstall_' . trim( $module ) );
		}

		// Delete the page
		wp_delete_post( $this->get_option( 'page_id' ) );

		// Delete options
		delete_option( 'theme_my_login' );
		delete_option( 'widget_theme-my-login' );
	}
}
endif; // Class exists

