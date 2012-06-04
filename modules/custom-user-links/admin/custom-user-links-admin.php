<?php
/**
 * Holds Theme My Login Custom User Links Admin class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Custom_User_Links
 * @since 6.0
 */

if ( !class_exists( 'Theme_My_Login_Custom_User_Links_Admin' ) ) :
/**
 * Theme My Login Custom User Links Admin class
 *
 * @since 6.0
 */
class Theme_My_Login_Custom_User_Links_Admin extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_user_links';

	/**
	 * Loads the module
	 *
	 * Called by Theme_My_Login_Abstract::__construct()
	 *
	 * @see Theme_My_Login_Abstract::__construct()
	 * @since 6.0
	 * @access protected
	 */
	protected function load() {
		add_action( 'tml_activate_custom-user-links/custom-user-links.php',  array( &$this, 'activate' ) );
		add_action( 'tml_uninstall_custom-user-links/custom-user-links.php', array( &$this, 'uninstall' ) );
	
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );

		add_action( 'load-tml_page_theme_my_login_user_links', array( &$this, 'load_settings_page' ) );

		add_action( 'wp_ajax_add-user-link',    array( &$this, 'add_user_link_ajax' ) );
		add_action( 'wp_ajax_delete-user-link', array( &$this, 'delete_user_link_ajax' ) );
	}

	/**
	 * Returns default options
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @return array Default options
	 */
	public function default_options() {
		return Theme_My_login_Custom_User_Links::default_options();
	}

	/**
	 * Activates the module
	 *
	 * Callback for "tml_activate_custom-user-links/custom-user-links.php" hook in method Theme_My_Login_Admin::activate_module()
	 *
	 * @see Theme_My_Login_Admin::activate_module()
	 * @since 6.0
	 * @access public
	 */
	public function activate() {
		$this->save_options();
	}

	/**
	 * Uninstalls the module
	 *
	 * Callback for "tml_uninstall_custom-user-links/custom-user-links.php" hook in method Theme_My_Login_Admin::uninstall()
	 *
	 * @see Theme_My_Login_Admin::uninstall()
	 * @since 6.3
	 * @access public
	 */
	public function uninstall() {
		delete_option( $this->options_key );
	}

	/**
	 * Adds "User Links" to Theme My Login menu
	 *
	 * @since 6.0
	 * @access public
	 */
	public function admin_menu() {
		global $wp_roles;

		add_submenu_page(
			'theme_my_login',
			__( 'Theme My Login Custom User Links Settings', 'theme-my-login' ),
			__( 'User Links', 'theme-my-login' ),
			'manage_options',
			$this->options_key,
			array( &$this, 'settings_page' )
		);

		foreach ( $wp_roles->get_names() as $role => $role_name ) {
			if ( 'pending' != $role )
				add_settings_section( $role, translate_user_role( $role_name ), array( &$this, 'settings_section_role' ), $this->options_key );
		}
	}

	/**
	 * Registers options group
	 *
	 * Callback for "admin_init" hook
	 *
	 * @since 6.3
	 * @access public
	 */
	public function admin_init() {
		register_setting( $this->options_key, $this->options_key, array( &$this, 'save_settings' ) );
	}

	/**
	 * Sanitizes settings
	 *
	 * Callback for register_setting()
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $settings Settings passed in from filter
	 * @return string|array Sanitized settings
	 */
	public function save_settings( $settings ) {
		// Bail-out if doing AJAX because it has it's own saving routine
		if ( defined('DOING_AJAX') && DOING_AJAX )
			return $settings;
		// Handle updating/deleting of links
		if ( isset( $_POST['user_links'] ) && is_array( $_POST['user_links'] ) && !empty( $_POST['user_links'] ) ) {
			foreach ( $_POST['user_links'] as $role => $links ) {
				foreach ( $links as $key => $link ) {
					$clean_title = wp_kses( $link['title'], null );
					$clean_url = wp_kses( $link['url'], null );
					$links[$key] = array( 'title' => $clean_title, 'url' => $clean_url );
					if ( ( empty( $clean_title ) && empty( $clean_url ) ) || ( isset( $_POST['delete_user_link'][$role][$key] ) ) )
						unset( $links[$key] );
				}
				$settings[$role] = array_values( $links );
			}
		}
		// Handle new links
		if ( isset( $_POST['new_user_link'] ) && is_array( $_POST['new_user_link'] ) && !empty( $_POST['new_user_link'] ) ) {
			foreach ( $_POST['new_user_link'] as $role => $link ) {
				$clean_title = wp_kses( $link['title'], null );
				$clean_url = wp_kses( $link['url'], null );
				if ( !empty( $clean_title ) && !empty( $clean_url ) )
					$settings[$role][] = array( 'title' => $clean_title, 'url' => $clean_url );
			}
		}
		// Reset link keys
		foreach ( $settings as $role => $links ) {
			$settings[$role] = array_values( $links );
		}
		return $settings;
	}

	/**
	 * Renders settings page
	 *
	 * Callback for add_submenu_page()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_page() {
		Theme_My_Login_Admin::settings_page( array(
			'title'       => __( 'Theme My Login Custom User Links Settings', 'theme-my-login' ),
			'options_key' => $this->options_key
		) );
	}

	/**
	 * Loads admin styles and scripts
	 *
	 * Callback for "load-settings_page_theme-my-login" hook in file "wp-admin/admin.php"
	 *
	 * @since 6.0
	 * @access public
	 */
	public function load_settings_page() {
		wp_enqueue_style(  'tml-custom-user-links-admin', plugins_url( 'theme-my-login/modules/custom-user-links/admin/css/custom-user-links-admin.css' ) );
		wp_enqueue_script( 'tml-custom-user-links-admin', plugins_url( 'theme-my-login/modules/custom-user-links/admin/js/custom-user-links-admin.js' ), array( 'wp-lists', 'jquery-ui-sortable' ) );
	}

	/**
	 * Outputs user links admin menu for specified role
	 *
	 * Callback for add_settings_section()
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param array $args Arguments passed in by add_settings_section()
	 */
	public function settings_section_role( $args ) {
		$role = $args['id'];

		$links = $this->get_option( $role );
		if ( empty($links) )
			$links = array();
		?>
	<div id="ajax-response-<?php echo $role; ?>" class="ajax-response"></div>

	<table id="<?php echo $role; ?>-link-table"<?php if ( empty( $links ) ) echo ' style="display: none;"'; ?> class="sortable user-links">
		<thead>
		<tr>
			<th class="left"><?php _e( 'Title', 'theme-my-login' ); ?></th>
			<th><?php _e( 'URL', 'theme-my-login' ); ?></th>
			<th></th>
		</tr>
		</thead>
		<tbody id="<?php echo $role; ?>-link-list" class="list:user-link">
		<?php if ( empty( $links ) ) {
			echo '<tr><td></td></tr>';
		} else {
			$count = 0;
			foreach ( $links as $key => $link ) {
				$link['id'] = $key;
				echo $this->get_link_row( $link, $role, $count );
			}
		} ?>
		</tbody>
	</table>

	<table id="new-<?php echo $role; ?>-link" class="new-link">
	<tbody>
	<tr>
		<td class="left"><input id="new_user_link[<?php echo $role; ?>][title]" name="new_user_link[<?php echo $role; ?>][title]" type="text" tabindex="8" size="20" /></td>
		<td class="center"><input id="new_user_link[<?php echo $role; ?>][url]" name="new_user_link[<?php echo $role; ?>][url]" type="text" tabindex="8" size="20" /></td>
		<td class="submit">
			<input type="submit" id="add_new_user_link_<?php echo $role; ?>" name="add_new_user_link[<?php echo $role; ?>]" class="add:<?php echo $role; ?>-link-list:new-<?php echo $role; ?>-link" tabindex="9" value="<?php esc_attr_e( 'Add link', 'theme-my-login' ) ?>" />
			<?php wp_nonce_field( 'add-user-link', '_ajax_nonce', false ); ?>
		</td>
	</tr>
	</tbody>
	</table>
<?php
	}

	/**
	 * Outputs a link row to the table
	 *
	 * @since 6.0
	 * @access protected
	 *
	 * @param array $link Link data
	 * @param string $role Name of user role
	 * @param int $count Reference to counter variable
	 * @return sring Link row
	 */
	protected function get_link_row( $link, $role, &$count ) {
		$r = '';
		++ $count;
		if ( $count % 2 )
			$style = 'alternate';
		else
			$style = '';

		$link = (object) $link;

		$delete_nonce = wp_create_nonce( 'delete-user-link_' . $link->id );
		$update_nonce = wp_create_nonce( 'add-user-link' );

		$r .= "\n\t<tr id='$role-link-$link->id' class='$style'>";
		$r .= "\n\t\t<td class='left'><label class='screen-reader-text' for='user_links[$role][$link->id][title]'>" . __( 'Title', 'theme-my-login' ) . "</label><input name='user_links[$role][$link->id][title]' id='user_links[$role][$link->id][title]' tabindex='6' type='text' size='20' value='$link->title' />";
		$r .= wp_nonce_field( 'change-user-link', '_ajax_nonce', false, false );
		$r .= "</td>";

		$r .= "\n\t\t<td class='center'><label class='screen-reader-text' for='user_links[$role][$link->id][url]'>" . __( 'URL', 'theme-my-login' ) . "</label><input name='user_links[$role][$link->id][url]' id='user_links[$role][$link->id][url]' tabindex='6' type='text' size='20' value='$link->url' /></td>";

		$r .= "\n\t\t<td class='submit'><input name='delete_user_link[$role][$link->id]' type='submit' class='delete:$role-link-list:$role-link-$link->id::_ajax_nonce=$delete_nonce deletelink' tabindex='6' value='". esc_attr__( 'Delete' ) ."' />";
		$r .= "\n\t\t<input name='updatelink' type='submit' class='add:$role-link-list:$role-link-$link->id::_ajax_nonce=$update_nonce updatelink' tabindex='6' value='". esc_attr__( 'Update' ) ."' /></td>\n\t</tr>";
		return $r;
	}

	/**
	 * AJAX handler for adding/updating a link
	 *
	 * Callback for "wp_ajax_add-user-link" hook in file "wp-admin/admin-ajax.php"
	 *
	 * @since 6.0
	 * @access public
	 */
	public function add_user_link_ajax() {
		if ( ! current_user_can( 'manage_options' ) )
			die( '-1' );

		check_ajax_referer( 'add-user-link' );

		$links = $this->get_options();

		$c = 0;
		if ( isset( $_POST['new_user_link'] ) ) {
			// Add a new link
			foreach ( $_POST['new_user_link'] as $role => $link ) {
				// Make sure input isn't empty
				if ( is_array( $link ) && !empty( $link ) ) {
					// Clean the input
					$clean_title = wp_kses( $link['title'], null );
					$clean_url = wp_kses( $link['url'], null );

					// Make sure input isn't empty after cleaning
					if ( empty( $clean_title ) || empty( $clean_url ) )
						die( '1' );

					// Add new link
					$links[$role][] = array( 'title' => $clean_title, 'url' => $clean_url );
					// Save links
					$this->set_options( $links );

					$link_row = array_merge( array( 'id' => max( array_keys( $links[$role] ) ) ), end( $links[$role] ) );

					$x = new WP_Ajax_Response( array(
						'what' => $role . '-link',
						'id' => $link_row['id'],
						'data' => $this->get_link_row( $link_row, $role, $c ),
						'position' => 1,
						'supplemental' => array( 'user_role' => $role )
					) );
				}
			}
		} else {
			// Update a link
			foreach ( $_POST['user_links'] as $role => $link ) {
				// Set the link ID
				$id = key( $link );

				// Clean the input
				$clean_title = wp_kses( $link[$id]['title'], null );
				$clean_url = wp_kses( $link[$id]['url'], null );

				// Make sure the requested link ID exists
				if ( ! isset( $links[$role][$id] ) )
					die( '0' );

				// Update the link if it has changed
				if ( $links[$role][$id]['title'] != $clean_title || $links[$role][$id]['url'] != $clean_url ) {
					$links[$role][$id] = array( 'title' => $clean_title, 'url' => $clean_url );
					$this->set_options( $links );
				}

				$link_row = array_merge( array( 'id' => $id ), $links[$role][$id] );

				$x = new WP_Ajax_Response( array(
					'what' => $role . '-link',
					'id' => $id,
					'old_id' => $id,
					'data' => $this->get_link_row( $link_row, $role, $c ),
					'position' => 0,
					'supplemental' => array( 'user_role' => $role )
				) );
			}
		}

		// Save options
		$this->save_options();

		$x->send();
	}

	/**
	 * AJAX handler for deleting a link
	 *
	 * Callback for "wp_ajax_delete-user-link" hook in file "wp-admin/admin-ajax.php"
	 *
	 * @since 6.0
	 * @access public
	 */
	public function delete_user_link_ajax() {
		global $id;

		if ( ! current_user_can( 'manage_options' ) )
			die( '-1' );

		$user_role = isset( $_POST['user_role'] ) ? $_POST['user_role'] : '';
		if ( empty( $user_role ) )
			die( '0' );

		check_ajax_referer( "delete-user-link_$id" );

		$links = $this->get_options();
		if ( isset( $links[$user_role][$id] ) ) {
			// Delete link
			unset( $links[$user_role][$id] );
			// Save links
			$this->set_options( $links );
			$this->save_options();
			die( '1' );
		}
		die( '0' );
	}
}

/**
 * Holds the reference to Theme_My_Login_Custom_User_Links_Admin object
 * @global object $theme_my_login_custom_user_links_admin
 * @since 6.0
 */
$theme_my_login_custom_user_links_admin = new Theme_My_Login_Custom_User_Links_Admin( 'theme_my_login_custom_user_links' );

endif; // Class exists
