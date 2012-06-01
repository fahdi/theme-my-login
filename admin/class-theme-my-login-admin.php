<?php
/**
 * Holds the Theme My Login class
 *
 * @package Theme_My_Login
 */

if ( !class_exists( 'Theme_My_Login_Admin' ) ) :
/**
 * Theme My Login Admin class
 *
 * @since 6.0
 */
class Theme_My_Login_Admin {
	/**
	 * Holds TML menu array
	 *
	 * @since 6.0
	 * @access protected
	 * @var array
	 */
	protected $menu;

	/**
	 * Holds TML submenu array
	 *
	 * @since 6.0
	 * @access protected
	 * @var array
	 */
	protected $submenu;

	/**
	 * Constructor
	 *
	 * @since 6.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_notices', array( &$this, 'module_errors' ) );
		add_action( 'load-settings_page_theme-my-login', array( &$this, 'load_settings_page' ) );

		register_activation_hook( WP_PLUGIN_DIR . '/theme-my-login/theme-my-login.php', array( 'Theme_My_Login_Admin', 'install' ) );
		register_uninstall_hook( WP_PLUGIN_DIR . '/theme-my-login/theme-my-login.php', array( 'Theme_My_Login_Admin', 'uninstall' ) );
	}

	/**
	 * Adds "Theme My Login" to the WordPress "Settings" menu
	 *
	 * @since 6.0
	 * @access public
	 */
	public function admin_menu() {
		// Create our settings link in the default WP "Settings" menu
		add_options_page(
			__( 'Theme My Login', 'theme-my-login' ),
			__( 'Theme My Login', 'theme-my-login' ),
			'manage_options',
			'theme-my-login',
			array( &$this, 'display_settings_page' )
			);
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
		// Register our settings in the global "whitelist_settings"
		register_setting( 'theme_my_login', 'theme_my_login',  array( &$this, 'save_settings' ) );

		// Create a hook for modules to use
		do_action_ref_array( 'tml_admin_init', array( &$this ) );
	}

	/**
	 * Outputs HTML for module errors
	 *
	 * @since 6.0
	 * @access public
	 */
	public function module_errors() {
		global $theme_my_login;

		$module_errors = $theme_my_login->get_option( 'module_errors' );
		// If we have errors to display
		if ( $module_errors && current_user_can( 'manage_options' ) ) {
			// Display them
			echo '<div class="error">';
			foreach ( (array) $module_errors as $module => $error ) {
				echo '<p><strong>' . sprintf( __( 'ERROR: The module "%1$s" could not be activated (%2$s).', 'theme-my-login' ), $module, $error ) . '</strong></p>';
			}
			echo '</div>';
			// Unset the error array
			$theme_my_login->delete_option( 'module_errors' );
			// Update the options in the DB
			$theme_my_login->save();
		}
	}

	/**
	 * Loads admin styles and scripts
	 *
	 * @since 6.0
	 * @access public
	 */
	public function load_settings_page() {
		global $theme_my_login, $user_ID;

		// Flush rewrite rules if slugs have been updated
		if ( $theme_my_login->get_option( 'flush_rules' ) )
			flush_rewrite_rules();

		// Enqueue neccessary scripts and styles
		wp_enqueue_style( 'theme-my-login-admin', plugins_url( '/theme-my-login/admin/css/theme-my-login-admin.css' ) );
		wp_enqueue_script( 'theme-my-login-admin', plugins_url( '/theme-my-login/admin/js/theme-my-login-admin.js' ), array( 'jquery-ui-tabs' ) );

		// Set the correct admin style according to user setting (Only supports default admin schemes)
		$admin_color = get_user_meta( $user_ID, 'admin_color', true );
		$stylesheet = ( 'classic' == $admin_color ) ? 'colors-classic.css' : 'colors-fresh.css';
		wp_enqueue_style( 'theme-my-login-colors-fresh', plugins_url( '/theme-my-login/admin/css/' . $stylesheet ) );
	}

	/**
	 * Outputs the main TML admin
	 *
	 * @since 6.0
	 * @access public
	 */
	public function display_settings_page() {
		global $wp_rewrite;

		// Default menu
		$this->add_menu_page( __('General', 'theme-my-login' ), 'tml-options' );
		$this->add_submenu_page( 'tml-options', __( 'Basic', 'theme-my-login' ), 'tml-options-basic', array( &$this, 'display_basic_settings' ) );
		$this->add_submenu_page( 'tml-options', __( 'Modules', 'theme-my-login' ), 'tml-options-modules', array( &$this, 'display_module_settings' ) );
		if ( $wp_rewrite->using_permalinks() )
			$this->add_submenu_page( 'tml-options', __( 'Permalinks', 'theme-my-login' ), 'tml-options-permalinks', array( &$this, 'display_permalink_settings' ) );

		// Allow plugins to add to menu
		do_action_ref_array( 'tml_admin_menu', array( &$this ) );
		?>
<div class="wrap">
    <?php screen_icon( 'options-general' ); ?>
    <h2><?php esc_html_e( 'Theme My Login Settings', 'theme-my-login' ); ?></h2>

    <form action="options.php" method="post">
    <?php settings_fields( 'theme_my_login' ); ?>

	<div style="display:none;">
		<p><input type="submit" name="submit" value="<?php esc_attr_e( 'Save Changes', 'theme-my-login' ) ?>" /></p>
	</div>

    <div id="tml-container">

        <ul>
            <?php foreach ( $this->menu as $menu ) {
                echo '<li><a href="#' . $menu[1] . '">' . $menu[0] . '</a></li>' . "\n";
            } ?>
        </ul>

        <?php foreach ( $this->menu as $menu ) {
            echo '<div id="' . $menu[1] . '" class="' . $menu[1] . '">' . "\n";
            if ( isset( $this->submenu[$menu[1]] ) ) {
                echo '<ul>' . "\n";
                foreach ( $this->submenu[$menu[1]] as $submenu ) {
                    echo '<li><a href="#' . $submenu[1] . '">' . $submenu[0] . '</a></li>' . "\n";



                }
                echo '</ul>' . "\n";

                foreach ( $this->submenu[$menu[1]] as $submenu ) {
                    echo '<div id="' . $submenu[1] . '" class="' . $menu[1] . '">' . "\n";
					if ( has_action( $submenu[2] ) ) {
						do_action( 'load-' . $submenu[2] );
						call_user_func_array( 'do_action', array_merge( (array) $submenu[2], (array) $submenu[3] ) );
					} else {
						if ( validate_file( $submenu[1] ) )
							return false;

						if ( ! ( file_exists( WP_PLUGIN_DIR . '/' . $submenu[1] ) && is_file( WP_PLUGIN_DIR . '/' . $submenu[1] ) ) )
							return false;

						do_action( 'load-' . $submenu[1] );
						include ( WP_PLUGIN_DIR . '/' . $submenu[1] );
					}
                    echo '</div>' . "\n";
                }
            } else {
				if ( has_action( $menu[2] ) ) {
					do_action( 'load-' . $menu[2] );
					call_user_func_array( 'do_action', array_merge( (array) $menu[2], (array) $menu[3] ) );
				} else {
					if ( validate_file( $menu[1] ) )
						return false;

					if ( ! ( file_exists( WP_PLUGIN_DIR . '/' . $menu[1] ) && is_file( WP_PLUGIN_DIR . '/' . $menu[1] ) ) )
						return false;

					do_action( 'load-' . $menu[1] );
					include ( WP_PLUGIN_DIR . '/' . $menu[1] );
				}
            }
            echo '</div>' . "\n";
        } ?>

    </div>
	
    <p><input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'theme-my-login' ) ?>" /></p>
    </form>

</div>
<?php
	}

	/**
	 * Outputs HTML for "Basic" settings tab
	 *
	 * @since 6.0
	 * @access public
	 */
	public function display_basic_settings() {
		global $theme_my_login; ?>
<table class="form-table">
    <tr valign="top">
        <th scope="row"><label for="theme_my_login_page_id"><?php _e( 'Page ID', 'theme-my-login' ); ?></label></th>
        <td>
            <input name="theme_my_login[page_id]" type="text" id="theme_my_login_page_id" value="<?php echo (int) $theme_my_login->get_option( 'page_id' ); ?>" class="small-text" />
            <p class="description"><?php _e( 'This should be the ID of the WordPress page that includes the [theme-my-login] shortcode. By default, this page is titled "Login".', 'theme-my-login' ); ?></p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><?php _e( 'Pagelist', 'theme-my-login' ); ?></th>
        <td>
            <input name="theme_my_login[show_page]" type="checkbox" id="theme_my_login_show_page" value="1"<?php checked( 1, $theme_my_login->get_option( 'show_page' ) ); ?> />
            <label for="theme_my_login_show_page"><?php _e( 'Show Page In Pagelist', 'theme-my-login' ); ?></label>
            <p class="description"><?php _e( 'Enable this setting to add login/logout links to the pagelist generated by functions like wp_list_pages() and wp_page_menu().', 'theme-my-login' ); ?></p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><?php _e( 'Stylesheet', 'theme-my-login' ); ?></th>
        <td>
            <input name="theme_my_login[enable_css]" type="checkbox" id="theme_my_login_enable_css" value="1"<?php checked( 1, $theme_my_login->get_option( 'enable_css' ) ); ?> />
            <label for="theme_my_login_enable_css"><?php _e( 'Enable "theme-my-login.css"', 'theme-my-login' ); ?></label>
            <p class="description"><?php _e( 'In order to keep changes between upgrades, you can store your customized "theme-my-login.css" in your current theme directory.', 'theme-my-login' ); ?></p>
        </td>
    </tr>
    <tr valign="top">
    	<th scope="row"><?php _e( 'E-mail Login', 'theme-my-login' ); ?></th>
    	<td>
    		<input name="theme_my_login[email_login]" type="checkbox" id="theme_my_login_email_login" value="1"<?php checked( 1, $theme_my_login->get_option( 'email_login' ) ); ?> />
    		<label for="theme_my_login_email_login"><?php _e( 'Enable e-mail address login', 'theme-my-login' ); ?></label>
    		<p class="description"><?php _e( 'Allows users to login using their e-mail address in place of their username.', 'theme-my-login' ); ?></p>
    	</td>
    </tr>
    <?php do_action( 'tml_settings_basic' ); ?>
</table><?php
	}

	/**
	 * Outputs HTML for "Module" settings tab
	 *
	 * @since 6.0
	 * @access public
	 */
	public function display_module_settings() {
		global $theme_my_login;

		$all_modules = get_plugins( '/theme-my-login/modules' );
		$active_modules = (array) $theme_my_login->get_option( 'active_modules' );
	?>
<table class="form-table">
    <tr valign="top">
        <th scope="row"><?php _e( 'Modules', 'theme-my-login' ); ?></th>
        <td>
            <?php if ( !empty( $all_modules ) ) : foreach ( $all_modules as $module_file => $module_data ) : ?>
            <input name="theme_my_login_modules[]" type="checkbox" id="theme_my_login_modules_<?php echo $module_file; ?>" value="<?php echo $module_file; ?>"<?php checked( 1, in_array( $module_file, (array) $active_modules ) ); ?> />
            <label for="theme_my_login_modules_<?php echo $module_file; ?>"><?php printf( __( 'Enable %s', 'theme-my-login' ), $module_data['Name'] ); ?></label><br />
            <?php if ( $module_data['Description'] ) echo '<p class="description">' . $module_data['Description'] . '</p>'; ?>
            <?php endforeach; else : _e( 'No modules found.', 'theme-my-login' ); endif; ?>
        </td>
    </tr>
    <?php do_action( 'tml_settings_modules' ); ?>
</table>
<?php
	}

	/**
	 * Outputs HTML for "Permalinks" settings tab
	 *
	 * @since 6.2
	 * @access public
	 */
	public function display_permalink_settings() {
		global $theme_my_login;

		$actions = array(
			'login' => __( 'Login', 'theme-my-login' ),
			'register' => __( 'Register', 'theme-my-login' ),
			'lostpassword' => __( 'Lost Password', 'theme-my-login' )
		); ?>
<table class="form-table">
	<?php foreach ( $actions as $action => $label ) : ?>
	<tr valign="top">
		<th scope="row"><label for="theme_my_login_permalinks_<?php echo $action; ?>"><?php echo $label; ?></label></th>
		<td>
			<input name="theme_my_login[permalinks][<?php echo $action; ?>]" type="text" id="theme_my_login_permalinks_<?php echo $action; ?>" value="<?php echo $theme_my_login->get_option( array( 'permalinks', $action ) ); ?>" class="regular-text" />
		</td>
	</tr>
	<?php endforeach;
	do_action( 'tml_settings_permalinks' ); ?>
</table><?php
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
		global $theme_my_login;

		// Sanitize new settings
		$settings['page_id'] = absint( $settings['page_id'] );
		$settings['show_page'] = isset( $settings['show_page'] );
		$settings['enable_css'] = isset( $settings['enable_css'] );
		$settings['email_login'] = isset( $settings['email_login'] );

		$modules = isset( $_POST['theme_my_login_modules'] ) ? $_POST['theme_my_login_modules'] : array();

		// If we have modules to activate
		if ( $activate = array_diff( (array) $modules, (array) $theme_my_login->get_option( 'active_modules' ) ) ) {
			// Attempt to activate them
			$result = $this->activate_modules( $activate );
			// Check for WP_Error
			if ( is_wp_error( $result ) ) {
				// Loop through each module in the WP_Error object
				foreach ( $result->get_error_data( 'modules_invalid' ) as $module => $wp_error ) {
					// Store the module and error message to a temporary array which will be passed to 'admin_notices'
					if ( is_wp_error( $wp_error ) )
						$theme_my_login->options['module_errors'][$module] = $wp_error->get_error_message();
				}
			}
		}

		// If we have modules to deactivate
		if ( $deactivate = array_diff( (array) $theme_my_login->get_option( 'active_modules' ), $modules ) ) {
			// Deactive them
			$this->deactivate_modules( $deactivate );
		}

		// Flush permalinks if they have changed
		if ( isset( $settings['permalinks'] ) ) {
			foreach ( $settings['permalinks'] as $action => $slug ) {
				if ( $slug !== $theme_my_login->get_option( array( 'permalinks', $action ) ) ) {
					$settings['flush_rules'] = true;
					break;
				}
			}
		}

		// Merge current settings
		$settings = Theme_My_Login::array_merge_recursive( $theme_my_login->options, $settings );

		// Allow plugins/modules to add/modify settings
		$settings = apply_filters( 'tml_save_settings', $settings );

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

		$current = (array) $theme_my_login->get_option( 'active_modules' );
		if ( !$theme_my_login->is_module_active( $module ) ) {
			//ob_start();
			@include ( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module );
			$current[] = $module;
			sort( $current );
			do_action( 'tml_activate_module', trim( $module ) );
			$theme_my_login->set_option( 'active_modules', $current );
			do_action_ref_array( 'tml_activate_' . trim( $module ), array( &$theme_my_login ) );
			do_action( 'tml_activated_module', trim( $module ) );
			//ob_end_clean();
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

		$current = (array) $theme_my_login->get_option( 'active_modules' );

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

		$theme_my_login->set_option( 'active_modules', $current );
		$theme_my_login->save();
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
	 * Adds a tab in the TML admin menu
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $menu_title The text to be used for the menu
	 * @param string $menu_slug The slug name to refer to this menu by (should be unique for this menu)
	 * @param callback $function The function to be called to output the content for this page.
	 * @param array $function_args Arguments to pass in to callback function
	 * @param int $position The position in the menu order this one should appear
	 */
	public function add_menu_page( $menu_title, $menu_slug, $function = '', $function_args = array(), $position = NULL ) {
		$menu_slug = plugin_basename( $menu_slug );

		$hookname = get_plugin_page_hookname( $menu_slug, '' );
		$hookname = preg_replace( '|[^a-zA-Z0-9_:.]|', '-', $hookname );
		if ( !empty( $function ) && !empty( $hookname ) )
			add_action( $hookname, $function );

		$new_menu = array( $menu_title, $menu_slug, $hookname, $function_args );

		if ( NULL === $position )
			$this->menu[] = $new_menu;
		else
			$this->menu[$position] = $new_menu;

		return $hookname;
	}

	/**
	 * Adds a subtab to a tab in the TML admin menu
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $parent_slug The slug name for the parent menu (or the file name of a standard WordPress admin page)
	 * @param string $menu_title The text to be used for the menu
	 * @param string $menu_slug The slug name to refer to this menu by (should be unique for this menu)
	 * @param callback $function The function to be called to output the content for this page.
	 * @param array $function_args Arguments to pass in to callback function
	 */
	public function add_submenu_page( $parent_slug, $menu_title, $menu_slug, $function = '', $function_args = array() ) {
		$menu_slug = plugin_basename( $menu_slug );
		$parent = plugin_basename( $parent_slug );

		$count = ( isset( $this->submenu[$parent_slug] ) && is_array( $this->submenu[$parent_slug] ) ) ? count( $this->submenu[$parent_slug] ) + 1 : 1;

		$hookname = get_plugin_page_hookname( $parent_slug . '-' . $count, '' );
		$hookname = preg_replace( '|[^a-zA-Z0-9_:.]|', '-', $hookname );
		if ( !empty( $function ) && !empty( $hookname ) )
			add_action( $hookname, $function, 10, count( $function_args ) );

		$this->submenu[$parent_slug][] = array( $menu_title, $menu_slug, $hookname, $function_args );

		return $hookname;
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
		global $theme_my_login;

		// Declare page_id to avoid notices
		$page_id = 0;

		// Current version
		$version = $theme_my_login->get_option( 'version' );

		// 4.4 upgrade
		if ( version_compare( $version, '4.4', '<' ) ) {
			remove_role( 'denied' );
		}
		// 6.0 upgrade
		if ( version_compare( $version, '6.0', '<' ) ) {

		}
		// 6.3 upgrade
		if ( version_compare( $version, '6.3', '<' ) ) {
			$theme_my_login->delete_option( 'initial_nag' );
		}

		// Get existing page ID
		$page_id = $theme_my_login->get_option( 'page_id' );

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
		$theme_my_login->set_option( 'version', $plugin_data['Version'] );
		$theme_my_login->set_option( 'page_id', (int) $page_id );
		$theme_my_login->save();

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
		global $theme_my_login;

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
		wp_delete_post( $theme_my_login->get_option( 'page_id' ) );

		// Delete options
		delete_option( 'theme_my_login' );
		delete_option( 'widget_theme-my-login' );
	}
}
endif; // Class exists

