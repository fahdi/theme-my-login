<?php
/**
 * Holds Theme My Login Custom E-mail Admin class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Custom_Email
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_Custom_Email_Admin' ) ) :
/**
 * Theme My Login Custom E-mail Admin class
 *
 * @since 6.0
 */
class Theme_My_Login_Custom_Email_Admin extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_email';

	/**
	 * Returns default options
	 *
	 * @since 6.3
	 * @access public
	 */
	public function default_options() {
		return Theme_My_Login_Custom_Email::default_options();
	}

	/**
	 * Activates the module
	 *
	 * Callback for "tml_activate_custom-email/custom-email.php" hook in method Theme_My_Login_Modules_Admin::activate_module()
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
	 * Callback for "tml_uninstall_custom-email/custom-email.php" hook in method Theme_My_Login_Admin::uninstall()
	 *
	 * @see Theme_My_Login_Admin::uninstall()
	 * @since 6.3
	 * @access public
	 */
	public function uninstall() {
		delete_option( $this->options_key );
	}

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
		add_action( 'tml_activate_custom-email/custom-email.php',  array( &$this, 'activate' ) );
		add_action( 'tml_uninstall_custom-email/custom-email.php', array( &$this, 'uninstall' ) );

		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
	}

	/**
	 * Adds "E-mail" tab to Theme My Login menu
	 *
	 * Callback for "admin_menu" hook
	 *
	 * @since 6.0
	 * @access public
	 */
	public function admin_menu() {
		global $theme_my_login_modules;

		add_submenu_page(
			'theme_my_login',
			__( 'Theme My Login Custom E-mail Settings', 'theme-my-login' ),
			__( 'E-mail', 'theme-my-login' ),
			'manage_options',
			$this->options_key,
			array( &$this, 'settings_page' )
		);

		add_settings_section( 'new_user',       __( 'New User', 'theme-my-login' ),          array( &$this, 'settings_section_new_user' ),       $this->options_key );
		add_settings_section( 'new_user_admin', __( 'New User Admin', 'theme-my-login' ),    array( &$this, 'settings_section_new_user_admin' ), $this->options_key );
		add_settings_section( 'retrieve_pass',  __( 'Retrieve Password', 'theme-my-login' ), array( &$this, 'settings_section_retrieve_pass' ),  $this->options_key );
		add_settings_section( 'reset_pass',     __( 'Reset Password', 'theme-my-login' ),    array( &$this, 'settings_section_reset_pass' ),     $this->options_key );
		if ( $theme_my_login_modules->is_module_active( 'user-moderation/user-moderation.php' ) ) {
			add_settings_section( 'user_activation',     __( 'User Activation', 'theme-my-login' ),    array( &$this, 'settings_section_user_activation' ),     $this->options_key );
			add_settings_section( 'user_approval',       __( 'User Approval', 'theme-my-login' ),      array( &$this, 'settings_section_user_approval' ),       $this->options_key );
			add_settings_section( 'user_approval_admin', __( 'User Approval Admin', 'theme-my-login'), array( &$this, 'settings_section_user_approval_admin' ), $this->options_key );
			add_settings_section( 'user_denial',         __( 'User Denial', 'theme-my-login' ),        array( &$this, 'settings_section_user_denial' ),         $this->options_key );
		}
	}

	/**
	 * Registers settings
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
	 * Renders settings page
	 *
	 * This is the callback for add_submenu_page()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_page() {
		return Theme_My_Login_Admin::settings_page( array(
			'title'       => __( 'Theme My Login Custom E-mail Settings', 'theme-my-login' ),
			'options_key' => $this->options_key
		) );
	}

	/**
	 * Sanitizes settings
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
		global $theme_my_login_modules;

		$settings['new_user']['admin_disable']   = isset( $settings['new_user']['admin_disable'] );
		$settings['reset_pass']['admin_disable'] = isset( $settings['reset_pass']['admin_disable'] );

		if ( $theme_my_login_modules->is_module_active( 'user-moderation/user-moderation.php' ) )
			$settings['user_approval']['admin_disable'] = isset( $settings['user_approval']['admin_disable'] );

		$settings = Theme_My_Login_Common::array_merge_recursive( $this->get_options(), $settings );

		return $settings;
	}

	/**
	 * Renders New User Notification settings section
	 *
	 * This is the callback for add_settings_section()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_section_new_user() {
		?>
		<p class="description">
			<?php _e( 'This e-mail will be sent to a new user upon registration.', 'theme-my-login' ); ?>
			<?php _e( 'Please be sure to include the variable %user_pass% if using default passwords or else the user will not know their password!', 'theme-my-login' ); ?>
			<?php _e( 'If any field is left empty, the default will be used instead.', 'theme-my-login' ); ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_mail_from_name"><?php _e( 'From Name', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[new_user][mail_from_name]" type="text" id="<?php echo $this->options_key; ?>_new_user_mail_from_name" value="<?php echo $this->get_option( array( 'new_user', 'mail_from_name' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_mail_from"><?php _e( 'From E-mail', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[new_user][mail_from]" type="text" id="<?php echo $this->options_key; ?>_new_user_mail_from" value="<?php echo $this->get_option( array( 'new_user', 'mail_from' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_mail_content_type"><?php _e( 'E-mail Format', 'theme-my-login' ); ?></label></th>
				<td>
					<select name="<?php echo $this->options_key; ?>[new_user][mail_content_type]" id="<?php echo $this->options_key; ?>_new_user_mail_content_type">
						<option value="plain"<?php selected( $this->get_option( array( 'new_user', 'mail_content_type' ) ), 'plain' ); ?>><?php _e( 'Plain Text', 'theme-my-login' ); ?></option>
						<option value="html"<?php  selected( $this->get_option( array( 'new_user', 'mail_content_type' ) ), 'html' ); ?>><?php  _e( 'HTML', 'theme-my-login' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_title"><?php _e( 'Subject', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[new_user][title]" type="text" id="<?php echo $this->options_key; ?>_new_user_title" value="<?php echo $this->get_option( array( 'new_user', 'title' ) ); ?>" class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_message"><?php _e( 'Message', 'theme-my-login' ); ?></label></th>
				<td>
					<p class="description"><?php _e( 'Available Variables', 'theme-my-login' ); ?>: %blogname%, %siteurl%, %user_login%, %user_email%, %user_pass%, %user_ip%</p>
					<textarea name="<?php echo $this->options_key; ?>[new_user][message]" id="<?php echo $this->options_key; ?>_new_user_message" class="large-text" rows="10"><?php echo $this->get_option( array( 'new_user', 'message' ) ); ?></textarea></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Renders New User Admin Notification settings section
	 *
	 * This is the callback for add_settings_section()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_section_new_user_admin() {
		?>
		<p class="description">
			<?php _e( 'This e-mail will be sent to the e-mail address or addresses (multiple addresses may be separated by commas) specified below, upon new user registration.', 'theme-my-login' ); ?>
			<?php _e( 'If any field is left empty, the default will be used instead.', 'theme-my-login' ); ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_admin_mail_to"><?php _e( 'To', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[new_user][admin_mail_to]" type="text" id="<?php echo $this->options_key; ?>_new_user_admin_mail_to" value="<?php echo $this->get_option( array( 'new_user', 'admin_mail_to' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_admin_mail_from_name"><?php _e( 'From Name', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[new_user][admin_mail_from_name]" type="text" id="<?php echo $this->options_key; ?>_new_user_admin_mail_from_name" value="<?php echo $this->get_option( array( 'new_user', 'admin_mail_from_name' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_admin_mail_from"><?php _e( 'From E-mail', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[new_user][admin_mail_from]" type="text" id="<?php echo $this->options_key; ?>_new_user_admin_mail_from" value="<?php echo $this->get_option( array( 'new_user', 'admin_mail_from' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_admin_mail_content_type"><?php _e( 'E-mail Format', 'theme-my-login' ); ?></label></th>
				<td>
					<select name="<?php echo $this->options_key; ?>[new_user][admin_mail_content_type]" id="<?php echo $this->options_key; ?>_new_user_admin_mail_content_type">
						<option value="plain"<?php selected( $this->get_option( array( 'new_user', 'admin_mail_content_type' ) ), 'plain' ); ?>><?php _e( 'Plain Text', 'theme-my-login' ); ?></option>
						<option value="html"<?php  selected( $this->get_option( array( 'new_user', 'admin_mail_content_type' ) ), 'html' ); ?>><?php  _e( 'HTML', 'theme-my-login' ); ?></option>
					</select>
				</td>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_admin_title"><?php _e( 'Subject', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[new_user][admin_title]" type="text" id="<?php echo $this->options_key; ?>_new_user_admin_title" value="<?php echo $this->get_option( array( 'new_user', 'admin_title' ) ); ?>" class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_admin_message"><?php _e( 'Message', 'theme-my-login' ); ?></label></th>
				<td>
					<p class="description"><?php _e( 'Available Variables', 'theme-my-login' ); ?>: %blogname%, %siteurl%, %user_login%, %user_email%, %user_ip%</p>
					<textarea name="<?php echo $this->options_key; ?>[new_user][admin_message]" id="<?php echo $this->options_key; ?>_new_user_admin_message" class="large-text" rows="10"><?php echo $this->get_option( array( 'new_user', 'admin_message' ) ); ?></textarea>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">&nbsp;</th>
				<td>
					<input name="<?php echo $this->options_key; ?>[new_user][admin_disable]" type="checkbox" id="<?php echo $this->options_key; ?>_new_user_admin_disable" value="1"<?php checked( 1, $this->get_option( array( 'new_user', 'admin_disable' ) ) ); ?> />
					<label for="<?php echo $this->options_key; ?>_new_user_admin_disable"><?php _e( 'Disable Admin Notification', 'theme-my-login' ); ?></label>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Renders Retrieve Password settings section
	 *
	 * This is the callback for add_settings_section()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_section_retrieve_pass() {
		?>
		<p class="description">
			<?php _e( 'This e-mail will be sent to a user when they attempt to recover their password.', 'theme-my-login' ); ?>
			<?php _e( 'Please be sure to include the variable %reseturl% or else the user will not be able to recover their password!', 'theme-my-login' ); ?>
			<?php _e( 'If any field is left empty, the default will be used instead.', 'theme-my-login' ); ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_retrieve_pass_mail_from_name"><?php _e( 'From Name', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[retrieve_pass][mail_from_name]" type="text" id="<?php echo $this->options_key; ?>_retrieve_pass_mail_from_name" value="<?php echo $this->get_option( array( 'retrieve_pass', 'mail_from_name' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_retrieve_pass_mail_from"><?php _e( 'From E-mail', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[retrieve_pass][mail_from]" type="text" id="<?php echo $this->options_key; ?>_retrieve_pass_mail_from" value="<?php echo $this->get_option( array( 'retrieve_pass', 'mail_from' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_retrieve_pass_mail_content_type"><?php _e( 'E-mail Format', 'theme-my-login' ); ?></label></th>
				<td>
					<select name="<?php echo $this->options_key; ?>[retrieve_pass][mail_content_type]" id="<?php echo $this->options_key; ?>_retrieve_pass_mail_content_type">
						<option value="plain"<?php selected( $this->get_option( array( 'retrieve_pass', 'admin_mail_content_type' ) ), 'plain' ); ?>><?php _e( 'Plain Text', 'theme-my-login' ); ?></option>
						<option value="html"<?php  selected( $this->get_option( array( 'retrieve_pass', 'admin_mail_content_type' ) ), 'html' ); ?>><?php  _e( 'HTML', 'theme-my-login' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_retrieve_pass_title"><?php _e( 'Subject', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[retrieve_pass][title]" type="text" id="<?php echo $this->options_key; ?>_retrieve_pass_title" value="<?php echo $this->get_option( array( 'retrieve_pass', 'title' ) ); ?>" class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_retrieve_pass_message"><?php _e( 'Message', 'theme-my-login' ); ?></label></th>
				<td>
					<p class="description"><?php _e( 'Available Variables', 'theme-my-login' ); ?>: %blogname%, %siteurl%, %reseturl%, %user_login%, %user_email%, %user_ip%</p>
					<textarea name="<?php echo $this->options_key; ?>[retrieve_pass][message]" id="<?php echo $this->options_key; ?>_retrieve_pass_message" class="large-text" rows="10"><?php echo $this->get_option( array( 'retrieve_pass', 'message' ) ); ?></textarea>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Renders Reset Password settings section
	 *
	 * This is the callback for add_settings_section()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_section_reset_pass() {
		?>
		<p class="description">
			<?php _e( 'This e-mail will be sent to the e-mail address or addresses (multiple addresses may be separated by commas) specified below, upon user password change.', 'theme-my-login' ); ?>
			<?php _e( 'If any field is left empty, the default will be used instead.', 'theme-my-login' ); ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_reset_pass_admin_mail_to"><?php _e( 'To', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[reset_pass][admin_mail_to]" type="text" id="<?php echo $this->options_key; ?>_reset_pass_admin_mail_to" value="<?php echo $this->get_option( array( 'reset_pass', 'admin_mail_to' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_reset_pass_admin_mail_from_name"><?php _e( 'From Name', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[reset_pass][admin_mail_from_name]" type="text" id="<?php echo $this->options_key; ?>_reset_pass_admin_mail_from_name" value="<?php echo $this->get_option( array( 'reset_pass', 'admin_mail_from_name' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_reset_pass_admin_mail_from"><?php _e( 'From E-mail', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[reset_pass][admin_mail_from]" type="text" id="<?php echo $this->options_key; ?>_reset_pass_admin_mail_from" value="<?php echo $this->get_option( array( 'reset_pass', 'admin_mail_from' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_reset_pass_admin_mail_content_type"><?php _e( 'E-mail Format', 'theme-my-login' ); ?></label></th>
				<td>
					<select name="<?php echo $this->options_key; ?>[reset_pass][admin_mail_content_type]" id="<?php echo $this->options_key; ?>_reset_pass_admin_mail_content_type">
						<option value="plain"<?php selected( $this->get_option( array( 'reset_pass', 'admin_mail_content_type' ) ), 'plain' ); ?>><?php _e( 'Plain Text', 'theme-my-login' ); ?></option>
						<option value="html"<?php  selected( $this->get_option( array( 'reset_pass', 'admin_mail_content_type' ) ), 'html' ); ?>><?php  _e( 'HTML', 'theme-my-login' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_reset_pass_admin_title"><?php _e( 'Subject', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[reset_pass][admin_title]" type="text" id="<?php echo $this->options_key; ?>_reset_pass_admin_title" value="<?php echo $this->get_option( array( 'reset_pass', 'admin_title' ) ); ?>" class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_reset_pass_admin_message"><?php _e( 'Message', 'theme-my-login' ); ?></label></th>
				<td>
					<p class="description"><?php _e( 'Available Variables', 'theme-my-login' ); ?>: %blogname%, %siteurl%, %user_login%, %user_email%, %user_ip%</p>
					<textarea name="<?php echo $this->options_key; ?>[reset_pass][admin_message]" id="<?php echo $this->options_key; ?>_reset_pass_admin_message" class="large-text" rows="10"><?php echo $this->get_option( array( 'reset_pass', 'admin_message' ) ); ?></textarea>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">&nbsp;</th>
				<td>
					<input name="<?php echo $this->options_key; ?>[reset_pass][admin_disable]" type="checkbox" id="<?php echo $this->options_key; ?>_reset_pass_admin_disable" value="1"<?php checked( 1, $this->get_option( array( 'reset_pass', 'admin_disable' ) ) ); ?> />
					<label for="<?php echo $this->options_key; ?>_reset_pass_admin_disable"><?php _e( 'Disable Admin Notification', 'theme-my-login' ); ?></label>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Renders User Activation settings section
	 *
	 * This is the callback for add_settings_section()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_section_user_activation() {
		?>
		<p class="description">
			<?php _e( 'This e-mail will be sent to a new user upon registration when "E-mail Confirmation" is checked for "User Moderation".', 'theme-my-login' ); ?>
			<?php _e( 'Please be sure to include the variable %activateurl% or else the user will not be able to activate their account!', 'theme-my-login' ); ?>
			<?php _e( 'If any field is left empty, the default will be used instead.', 'theme-my-login' ); ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_activation_mail_from_name"><?php _e( 'From Name', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_activation][mail_from_name]" type="text" id="<?php echo $this->options_key; ?>_user_activation_mail_from_name" value="<?php echo $this->get_option( array( 'user_activation', 'mail_from_name' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_activation_mail_from"><?php _e( 'From E-mail', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_activation][mail_from]" type="text" id="<?php echo $this->options_key; ?>_user_activation_mail_from" value="<?php echo $this->get_option( array( 'user_activation', 'mail_from' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_activation_mail_content_type"><?php _e( 'E-mail Format', 'theme-my-login' ); ?></label></th>
				<td>
					<select name="<?php echo $this->options_key; ?>[user_activation][mail_content_type]" id="<?php echo $this->options_key; ?>_user_activation_mail_content_type">
						<option value="plain"<?php selected( $this->get_option( array( 'user_activation', 'mail_content_type' ) ), 'plain' ); ?>><?php _e( 'Plain Text', 'theme-my-login' ); ?></option>
						<option value="html"<?php  selected( $this->get_option( array( 'user_activation', 'mail_content_type' ) ), 'html' ); ?>><?php  _e( 'HTML', 'theme-my-login' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_activation_title"><?php _e( 'Subject', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_activation][title]" type="text" id="<?php echo $this->options_key; ?>_user_activation_title" value="<?php echo $this->get_option( array( 'user_activation', 'title' ) ); ?>" class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_activation_message"><?php _e( 'Message', 'theme-my-login' ); ?></label></th>
				<td>
					<p class="description"><?php _e( 'Available Variables', 'theme-my-login' ); ?>: %blogname%, %siteurl%, %activateurl%, %user_login%, %user_email%, %user_ip%</p>
					<textarea name="<?php echo $this->options_key; ?>[user_activation][message]" id="<?php echo $this->options_key; ?>_user_activation_message" class="large-text" rows="10"><?php echo $this->get_option( array( 'user_activation', 'message' ) ); ?></textarea>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Renders User Approval settings section
	 *
	 * This is the callback for add_settings_section()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_section_user_approval() {
		?>
		<p class="description">
			<?php _e( 'This e-mail will be sent to a new user upon admin approval when "Admin Approval" is checked for "User Moderation".', 'theme-my-login' ); ?>
			<?php _e( 'Please be sure to include the variable %user_pass% if using default passwords or else the user will not know their password!', 'theme-my-login' ); ?>
			<?php _e( 'If any field is left empty, the default will be used instead.', 'theme-my-login' ); ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_mail_from_name"><?php _e( 'From Name', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_approval][mail_from_name]" type="text" id="<?php echo $this->options_key; ?>_user_approval_mail_from_name" value="<?php echo $this->get_option( array( 'user_approval', 'mail_from_name' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_mail_from"><?php _e( 'From E-mail', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_approval][mail_from]" type="text" id="<?php echo $this->options_key; ?>_user_approval_mail_from" value="<?php echo $this->get_option( array( 'user_approval', 'mail_from' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_mail_content_type"><?php _e( 'E-mail Format', 'theme-my-login' ); ?></label></th>
				<td>
					<select name="<?php echo $this->options_key; ?>[user_approval][mail_content_type]" id="<?php echo $this->options_key; ?>_user_approval_mail_content_type">
						<option value="plain"<?php selected( $this->get_option( array( 'user_approval', 'mail_content_type' ) ), 'plain' ); ?>><?php _e( 'Plain Text', 'theme-my-login' ); ?></option>
						<option value="html"<?php  selected( $this->get_option( array( 'user_approval', 'mail_content_type' ) ), 'html' ); ?>><?php  _e( 'HTML', 'theme-my-login' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_title"><?php _e( 'Subject', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_approval][title]" type="text" id="<?php echo $this->options_key; ?>_user_approval_title" value="<?php echo $this->get_option( array( 'user_approval', 'title' ) ); ?>" class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_message"><?php _e( 'Message', 'theme-my-login' ); ?></label></th>
				<td>
					<p class="description"><?php _e( 'Available Variables', 'theme-my-login' ); ?>: %blogname%, %siteurl%, %loginurl%, %user_login%, %user_email%, %user_pass%</p>
					<textarea name="<?php echo $this->options_key; ?>[user_approval][message]" id="<?php echo $this->options_key; ?>_user_approval_message" class="large-text" rows="10"><?php echo $this->get_option( array( 'user_approval', 'message' ) ); ?></textarea></td>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Renders User Approval Admin settings section
	 *
	 * This is the callback for add_settings_section()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_section_user_approval_admin() {
		?>
		<p class="description">
			<?php _e( 'This e-mail will be sent to the e-mail address or addresses (multiple addresses may be separated by commas) specified below upon user registration when "Admin Approval" is checked for "User Moderation".', 'theme-my-login' ); ?>
			<?php _e( 'If any field is left empty, the default will be used instead.', 'theme-my-login' ); ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_admin_mail_to"><?php _e( 'To', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_approval][admin_mail_to]" type="text" id="<?php echo $this->options_key; ?>_user_approval_admin_mail_to" value="<?php echo $this->get_option( array( 'user_approval', 'admin_mail_to' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_admin_mail_from_name"><?php _e( 'From Name', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_approval][admin_mail_from_name]" type="text" id="<?php echo $this->options_key; ?>_user_approval_admin_mail_from_name" value="<?php echo $this->get_option( array( 'user_approval', 'admin_mail_from_name' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_admin_mail_from"><?php _e( 'From E-mail', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_approval][admin_mail_from]" type="text" id="<?php echo $this->options_key; ?>_user_approval_admin_mail_from" value="<?php echo $this->get_option( array( 'user_approval', 'admin_mail_from' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_admin_mail_content_type"><?php _e( 'E-mail Format', 'theme-my-login' ); ?></label></th>
				<td>
					<select name="<?php echo $this->options_key; ?>[user_approval][admin_mail_content_type]" id="<?php echo $this->options_key; ?>_user_approval_admin_mail_content_type">
						<option value="plain"<?php selected( $this->get_option( array( 'user_approval', 'mail_content_type' ) ), 'plain' ); ?>><?php _e( 'Plain Text', 'theme-my-login' ); ?></option>
						<option value="html"<?php  selected( $this->get_option( array( 'user_approval', 'mail_content_type' ) ), 'html' ); ?>><?php  _e( 'HTML', 'theme-my-login' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_admin_title"><?php _e( 'Subject', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_approval][admin_title]" type="text" id="<?php echo $this->options_key; ?>_user_approval_admin_title" value="<?php echo $this->get_option( array( 'user_approval', 'admin_title' ) ); ?>" class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_admin_message"><?php _e( 'Message', 'theme-my-login' ); ?></label></th>
				<td>
					<p class="description"><?php _e( 'Available Variables', 'theme-my-login' ); ?>: %blogname%, %siteurl%, %pendingurl%, %user_login%, %user_email%, %user_ip%</p>
					<textarea name="<?php echo $this->options_key; ?>[user_approval][admin_message]" id="<?php echo $this->options_key; ?>_user_approval_admin_message" class="large-text" rows="10"><?php echo $this->get_option( array( 'user_approval', 'admin_message' ) ); ?></textarea></td>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">&nbsp;</th>
				<td>
					<input name="<?php echo $this->options_key; ?>[user_approval][admin_disable]" type="checkbox" id="<?php echo $this->options_key; ?>_user_approval_admin_disable" value="1"<?php checked( 1, $this->get_option( array( 'user_approval', 'admin_disable' ) ) ); ?> />
					<label for="<?php echo $this->options_key; ?>_user_approval_admin_disable"><?php _e( 'Disable Admin Notification', 'theme-my-login' ); ?></label>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Renders User Denial settings section
	 *
	 * This is the callback for add_settings_section()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_section_user_denial() {
		?>
		<p class="description">
			<?php _e( 'This e-mail will be sent to a user who is deleted/denied when "Admin Approval" is checked for "User Moderation" and the user\'s role is "Pending".', 'theme-my-login' ); ?>
			<?php _e( 'If any field is left empty, the default will be used instead.', 'theme-my-login' ); ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_denial_mail_from_name"><?php _e( 'From Name', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_denial][mail_from_name]" type="text" id="<?php echo $this->options_key; ?>_user_denial_mail_from_name" value="<?php echo $this->get_option( array( 'user_denial', 'mail_from_name' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_denial_mail_from"><?php _e( 'From E-mail', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_denial][mail_from]" type="text" id="<?php echo $this->options_key; ?>_user_denial_mail_from" value="<?php echo $this->get_option( array( 'user_denial', 'mail_from' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_denial_mail_content_type"><?php _e( 'E-mail Format', 'theme-my-login' ); ?></label></th>
				<td>
					<select name="<?php echo $this->options_key; ?>[user_denial][mail_content_type]" id="<?php echo $this->options_key; ?>_user_denial_mail_content_type">
						<option value="plain"<?php selected( $this->get_option( array( 'user_denial', 'mail_content_type' ) ), 'plain' ); ?>><?php _e( 'Plain Text', 'theme-my-login' ); ?></option>
						<option value="html"<?php  selected( $this->get_option( array( 'user_denial', 'mail_content_type' ) ), 'html' ); ?>><?php  _e( 'HTML', 'theme-my-login' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_denial_title"><?php _e('Subject', 'theme-my-login'); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_denial][title]" type="text" id="<?php echo $this->options_key; ?>_user_denial_title" value="<?php echo $this->get_option( array( 'user_denial', 'title' ) ); ?>" class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_denial_message"><?php _e('Message', 'theme-my-login'); ?></label></th>
				<td>
					<p class="description"><?php _e( 'Available Variables', 'theme-my-login' ); ?>: %blogname%, %siteurl%, %user_login%, %user_email%</p>
					<textarea name="<?php echo $this->options_key; ?>[user_denial][message]" id="<?php echo $this->options_key; ?>_user_denial_message" class="large-text" rows="10"><?php echo $this->get_option( array( 'user_denial', 'message' ) ); ?></textarea>
				</td>
			</tr>
		</table>
		<?php
	}
}

/**
 * Holds the reference to Theme_My_Login_Custom_Email_Admin object
 * @global object $theme_my_login_custom_email_admin
 * @since 6.0
 */
$theme_my_login_custom_email_admin = new Theme_My_Login_Custom_Email_Admin;

endif; // Class exists

