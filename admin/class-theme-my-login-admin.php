<?php
/**
 * Holds the Theme My Login Admin class
 *
 * @package Theme_My_Login
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_Admin' ) ) :
/**
 * Theme My Login Admin class
 *
 * @since 6.0
 */
class Theme_My_Login_Admin {
	/**
	 * Constructor
	 *
	 * @since 6.4
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		register_uninstall_hook( WP_PLUGIN_DIR . '/theme-my-login/theme-my-login.php', array( 'Theme_My_Login_Admin', 'uninstall' ) );
	}

	/**
	 * Builds plugin admin menu and pages
	 *
	 * @since 6.0
	 * @access public
	 */
	public function admin_menu() {
		add_menu_page(
			__( 'Theme My Login Settings', 'theme-my-login' ),
			__( 'TML', 'theme-my-login' ),
			'manage_options',
			'theme_my_login',
			array( 'Theme_My_Login_Admin', 'settings_page' )
		);

		add_submenu_page(
			'theme_my_login',
			__( 'General', 'theme-my-login' ),
			__( 'General', 'theme-my-login' ),
			'manage_options',
			'theme_my_login',
			array( 'Theme_My_Login_Admin', 'settings_page' )
		);

		add_submenu_page(
			'theme_my_login',
			__( 'TML Pages', 'theme-my-login' ),
			__( 'Pages',     'theme-my-login' ),
			'manage_options',
			'edit.php?post_type=tml_page'
		);

		// General section
		add_settings_section( 'general',    __( 'General', 'theme-my-login'    ), '__return_false', 'theme_my_login' );
		add_settings_section( 'modules',    __( 'Modules', 'theme-my-login'    ), '__return_false', 'theme_my_login' );

		// General fields
		add_settings_field( 'enable_css',  __( 'Stylesheet',   'theme-my-login' ), array( &$this, 'settings_field_enable_css'  ), 'theme_my_login', 'general' );
		add_settings_field( 'email_login', __( 'E-mail Login', 'theme-my-login' ), array( &$this, 'settings_field_email_login' ), 'theme_my_login', 'general' );
		add_settings_field( 'modules',     __( 'Modules',      'theme-my-login' ), array( &$this, 'settings_field_modules'     ), 'theme_my_login', 'modules' );
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

		if ( version_compare( Theme_My_Login::get_object()->version, Theme_My_Login::version, '<' ) )
			$this->install();
	}

	/**
	 * Renders the settings page
	 *
	 * @since 6.0
	 * @access public
	 */
	public static function settings_page( $args = '' ) {
		extract( wp_parse_args( $args, array(
			'title'       => __( 'Theme My Login Settings', 'theme-my-login' ),
			'options_key' => 'theme_my_login'
		) ) );
		?>
		<div id="<?php echo $options_key; ?>" class="wrap">
			<?php screen_icon( 'options-general' ); ?>
			<h2><?php echo esc_html( $title ); ?></h2>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( $options_key );
					do_settings_sections( $options_key );
					submit_button();
				?>
			</form>
		</div>
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
		<input name="theme_my_login[enable_css]" type="checkbox" id="theme_my_login_enable_css" value="1"<?php checked( Theme_My_Login::get_object()->enable_css ); ?> />
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
		<input name="theme_my_login[email_login]" type="checkbox" id="theme_my_login_email_login" value="1"<?php checked( Theme_My_Login::get_object()->email_login ); ?> />
		<label for="theme_my_login_email_login"><?php _e( 'Enable e-mail address login', 'theme-my-login' ); ?></label>
		<p class="description"><?php _e( 'Allows users to login using their e-mail address in place of their username.', 'theme-my-login' ); ?></p>
    	<?php
	}

	/**
	 * Renders Modules settings field
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_field_modules() {
		foreach ( get_plugins( '/theme-my-login/modules' ) as $path => $data ) {
			$id = sanitize_key( $data['Name'] );
		?>
		<input name="theme_my_login[active_modules][]" type="checkbox" id="theme_my_login_active_modules_<?php echo $id; ?>" value="<?php echo $path; ?>"<?php checked( in_array( $path, (array) Theme_My_Login::get_object()->active_modules ) ); ?> />
		<label for="theme_my_login_active_modules_<?php echo $id; ?>"><?php printf( __( 'Enable %s', 'theme-my-login' ), $data['Name'] ); ?></label><br />
		<?php if ( $data['Description'] ) : ?>
		<p class="description"><?php echo $data['Description']; ?></p>
		<?php endif;
		}
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
		$settings['enable_css']     = ! empty( $settings['enable_css']   );
		$settings['email_login']    = ! empty( $settings['email_login']  );
		$settings['active_modules'] = isset( $settings['active_modules'] ) ? (array) $settings['active_modules'] : array();

		// If we have modules to activate
		if ( $activate = array_diff( $settings['active_modules'], (array) Theme_My_Login::get_object()->active_modules ) ) {
			foreach ( $activate as $module ) {
				if ( file_exists( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module ) )
					include_once( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module );
				do_action( 'tml_activate_' . $module );
			}
		}

		// If we have modules to deactivate
		if ( $deactivate = array_diff( (array) Theme_My_Login::get_object()->active_modules, $settings['active_modules'] ) ) {
			foreach ( $deactivate as $module ) {
				do_action( 'tml_deactivate_' . $module );
			}
		}

		$settings = array_intersect_key( $settings, Theme_My_Login::default_options() );

		return $settings;
	}

	/**
	 * Installs TML
	 *
	 * @since 6.0
	 * @access private
	 */
	private function install() {
		global $wpdb;

		// Check if options exist
		if ( $options = get_option( 'theme_my_login' ) ) {
			// Current version
			$version = empty( $options['version'] ) ? Theme_My_Login::version : $options['version'];

			// Check if legacy page exists
			if ( ! empty( $options['page_id'] ) ) {
				$page = get_page( $options['page_id'] );
			} else {
				$page = get_page_by_title( 'Login' );
			}

			// 4.4 upgrade
			if ( version_compare( $version, '4.4', '<' ) ) {
				remove_role( 'denied' );
			}

			// 6.0 upgrade
			if ( version_compare( $version, '6.0', '<' ) ) {
				// Replace shortcode
				if ( $page ) {
					$page->post_content = str_replace( '[theme-my-login-page]', '[theme-my-login]', $page->post_content );
					wp_update_post( $page );
				}
			}

			// 6.3 upgrade
			if ( version_compare( $version, '6.3.3', '<' ) ) {
				// Move options to their own rows
				foreach ( $options as $key => $value ) {
					if ( in_array( $key, array( 'active_modules' ) ) )
						continue;

					if ( ! is_array( $value ) )
						continue;

					update_option( "theme_my_login_{$key}", $value );
				}

				// Maybe create login page?
				if ( $page ) {
					// Make sure the page is not in the trash
					if ( 'trash' == $page->post_status )
						wp_untrash_post( $page->ID );

					// Change to new post type
					set_post_type( $page->ID, 'tml_page' );

					update_post_meta( $page->ID, '_tml_action', 'login' );
				}
			}

			// 6.4 upgrade
			if ( version_compare( $version, '6.4', '<' ) ) {
				// Merge module options back into the main options. Heh.
				$modules = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'theme_my_login_%'" );
				foreach ( $modules as $module ) {
					$option_name  = str_replace( 'theme_my_login_', '', $module->option_name );
					$option_value = maybe_unserialize( $module->option_value );
					$options[$option_name] = $option_value;
				}
			}

			// Activate modules in case they need to be upgraded
			foreach ( (array) $options['active_modules'] as $module ) {
				if ( file_exists( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module ) )
					include_once( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module );
				do_action( 'tml_activate_' . $module );
			}
		} else {
			$options = Theme_My_Login::default_options();
		}

		// Setup default pages
		foreach ( Theme_My_Login::default_pages() as $action => $title ) {
			if ( ! $page_id = Theme_My_Login::get_page_id( $action ) ) {
				$page_id = wp_insert_post( array(
					'post_title'     => $title,
					'post_status'    => 'publish',
					'post_type'      => 'tml_page',
					'post_content'   => '[theme-my-login]',
					'comment_status' => 'closed',
					'ping_status'    => 'closed'
				) );
				update_post_meta( $page_id, '_tml_action', $action );
			}
		}

		// Generate permalinks
		flush_rewrite_rules( false );

		// Set current version
		$options['version'] = Theme_My_Login::version;

		// Update options
		update_option( 'theme_my_login', $options );
	}

	/**
	 * Wrapper for multisite uninstallation
	 *
	 * @since 6.1
	 * @access public
	 */
	public static function uninstall() {
		global $wpdb;

		if ( is_multisite() ) {
			if ( isset( $_GET['networkwide'] ) && ( $_GET['networkwide'] == 1 ) ) {
				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::_uninstall();
				}
				restore_current_blog();
				return;
			}	
		}
		self::_uninstall();
	}

	/**
	 * Uninstalls TML
	 *
	 * @since 6.0
	 * @access protected
	 */
	protected static function _uninstall() {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		// Run module uninstall hooks
		$modules = get_plugins( '/theme-my-login/modules' );
		foreach ( array_keys( $modules ) as $module ) {
			$module = plugin_basename( trim( $module ) );

			if ( file_exists( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module ) )
				@include ( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module );

			do_action( 'tml_uninstall_' . $module );
		}

		// Delete the pages
		$pages = get_posts( array( 'post_type' => 'tml_page', 'post_status' => 'any', 'posts_per_page' => -1 ) );
		foreach ( $pages as $page ) {
			wp_delete_post( $page->ID );
		}

		// Delete options
		delete_option( 'theme_my_login' );
		delete_option( 'widget_theme-my-login' );
	}
}
endif; // Class exists

