<?php

if ( !class_exists( 'Theme_My_Login_Custom_Email_Admin' ) ) :
/**
 * Theme My Login Custom Email module admin class
 *
 * @since 6.0
 */
class Theme_My_Login_Custom_Email_Admin extends Theme_My_Login_Module {
	/**
	 * Sanitizes settings
	 *
	 * Callback for "tml_save_settings" hook in method Theme_My_Login_Admin::save_settings()
	 *
	 * @see Theme_My_Login_Admin::save_settings()
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $settings Settings passed in from filter
	 * @return string|array Sanitized settings
	 */
	function save_settings( $settings ) {
		// Checkboxes
		$settings['email']['new_user']['admin_disable'] = isset( $_POST['theme_my_login']['email']['new_user']['admin_disable'] );
		$settings['email']['reset_pass']['admin_disable'] = isset( $_POST['theme_my_login']['email']['reset_pass']['admin_disable'] );
		return $settings;
	}

	/**
	 * Outputs new user notification e-mail settings
	 *
	 * Callback for "$hookname" hook in method Theme_My_Login_Admin::add_submenu_page()
	 *
	 * @see Theme_My_Login_Admin::add_submenu_page()
	 * @since 6.0
	 * @access public
	 */
	function display_new_user_settings() {
		?>
<table class="form-table">
    <tr>
		<td>
			<h3><?php _e( 'User Notification', $this->theme_my_login->textdomain ); ?></h3>

			<p class="description">
				<?php _e( 'This e-mail will be sent to a new user upon registration.', $this->theme_my_login->textdomain ); ?>
				<?php _e( 'Please be sure to include the variable %user_pass% if using default passwords or else the user will not know their password!', $this->theme_my_login->textdomain ); ?>
				<?php _e( 'If any field is left empty, the default will be used instead.', $this->theme_my_login->textdomain ); ?>
			</p>

			<p><label for="theme_my_login_new_user_mail_from_name"><?php _e( 'From Name', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][new_user][mail_from_name]" type="text" id="theme_my_login_new_user_mail_from_name" value="<?php echo $this->theme_my_login->options['email']['new_user']['mail_from_name']; ?>" class="extended-text" /></p>

			<p><label for="theme_my_login_new_user_mail_from"><?php _e( 'From E-mail', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][new_user][mail_from]" type="text" id="theme_my_login_new_user_mail_from" value="<?php echo $this->theme_my_login->options['email']['new_user']['mail_from']; ?>" class="extended-text" /></p>

            <p><label for="theme_my_login_new_user_mail_content_type"><?php _e( 'E-mail Format', $this->theme_my_login->textdomain ); ?></label><br />
            <select name="theme_my_login[email][new_user][mail_content_type]" id="theme_my_login_new_user_mail_content_type">
            <option value="plain"<?php if ( 'plain' == $this->theme_my_login->options['email']['new_user']['mail_content_type'] ) echo ' selected="selected"'; ?>>Plain Text</option>
            <option value="html"<?php if ( 'html' == $this->theme_my_login->options['email']['new_user']['mail_content_type'] ) echo ' selected="selected"'; ?>>HTML</option>
            </select></p>

			<p><label for="theme_my_login_new_user_title"><?php _e( 'Subject', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][new_user][title]" type="text" id="theme_my_login_new_user_title" value="<?php echo $this->theme_my_login->options['email']['new_user']['title']; ?>" class="full-text" /></p>

			<p><label for="theme_my_login_new_user_message"><?php _e( 'Message', $this->theme_my_login->textdomain ); ?></label><br />
			<textarea name="theme_my_login[email][new_user][message]" id="theme_my_login_new_user_message" class="large-text" rows="10"><?php echo $this->theme_my_login->options['email']['new_user']['message']; ?></textarea></p>

			<p class="description"><?php _e( 'Available Variables', $this->theme_my_login->textdomain ); ?>: %blogname%, %siteurl%, %user_login%, %user_email%, %user_pass%, %user_ip%</p>
		</td>
	</tr>
	<tr>
		<td>
			<h3><?php _e( 'Admin Notification', $this->theme_my_login->textdomain ); ?></h3>

			<p class="description">
				<?php _e( 'This e-mail will be sent to the e-mail address or addresses (multiple addresses may be separated by commas) specified below, upon new user registration.', $this->theme_my_login->textdomain ); ?>
				<?php _e( 'If any field is left empty, the default will be used instead.', $this->theme_my_login->textdomain ); ?>
			</p>

			<p><label for="theme_my_login_new_user_admin_mail_to"><?php _e( 'To', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][new_user][admin_mail_to]" type="text" id="theme_my_login_new_user_admin_mail_to" value="<?php echo $this->theme_my_login->options['email']['new_user']['admin_mail_to']; ?>" class="extended-text" /></p>

			<p><label for="theme_my_login_new_user_admin_mail_from_name"><?php _e( 'From Name', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][new_user][admin_mail_from_name]" type="text" id="theme_my_login_new_user_admin_mail_from_name" value="<?php echo $this->theme_my_login->options['email']['new_user']['admin_mail_from_name']; ?>" class="extended-text" /></p>

			<p><label for="theme_my_login_new_user_admin_mail_from"><?php _e( 'From E-mail', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][new_user][admin_mail_from]" type="text" id="theme_my_login_new_user_admin_mail_from" value="<?php echo $this->theme_my_login->options['email']['new_user']['admin_mail_from']; ?>" class="extended-text" /></p>

            <p><label for="theme_my_login_new_user_admin_mail_content_type"><?php _e( 'E-mail Format', $this->theme_my_login->textdomain ); ?></label><br />
            <select name="theme_my_login[email][new_user][admin_mail_content_type]" id="theme_my_login_new_user_admin_mail_content_type">
            <option value="plain"<?php if ( 'plain' == $this->theme_my_login->options['email']['new_user']['admin_mail_content_type'] ) echo ' selected="selected"'; ?>>Plain Text</option>
            <option value="html"<?php if ( 'html' == $this->theme_my_login->options['email']['new_user']['admin_mail_content_type'] ) echo ' selected="selected"'; ?>>HTML</option>
            </select></p>

			<p><label for="theme_my_login_new_user_admin_title"><?php _e( 'Subject', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][new_user][admin_title]" type="text" id="theme_my_login_new_user_admin_title" value="<?php echo $this->theme_my_login->options['email']['new_user']['admin_title']; ?>" class="full-text" /></p>

			<p><label for="theme_my_login_new_user_admin_message"><?php _e( 'Message', $this->theme_my_login->textdomain ); ?></label><br />
			<textarea name="theme_my_login[email][new_user][admin_message]" id="theme_my_login_new_user_admin_message" class="large-text" rows="10"><?php echo $this->theme_my_login->options['email']['new_user']['admin_message']; ?></textarea></p>

			<p class="description"><?php _e( 'Available Variables', $this->theme_my_login->textdomain ); ?>: %blogname%, %siteurl%, %user_login%, %user_email%, %user_ip%</p>

			<p><label for="theme_my_login_new_user_admin_disable"><input name="theme_my_login[email][new_user][admin_disable]" type="checkbox" id="theme_my_login_new_user_admin_disable" value="1"<?php checked( 1, $this->theme_my_login->options['email']['new_user']['admin_disable'] ); ?> /> Disable Admin Notification</label></p>
		</td>
	</tr>
</table>
<?php
	}

	/**
	 * Outputs password retrieval e-mail settings
	 *
	 * Callback for "$hookname" hook in method Theme_My_Login_Admin::add_submenu_page()
	 *
	 * @see Theme_My_Login_Admin::add_submenu_page()
	 * @since 6.0
	 * @access public
	 */
	function display_retrieve_pass_settings() {
		?>
<table class="form-table">
	<tr>
		<td>
			<p class="description">
				<?php _e( 'This e-mail will be sent to a user when they attempt to recover their password.', $this->theme_my_login->textdomain ); ?>
				<?php _e( 'Please be sure to include the variable %reseturl% or else the user will not be able to recover their password!', $this->theme_my_login->textdomain ); ?>
				<?php _e( 'If any field is left empty, the default will be used instead.', $this->theme_my_login->textdomain ); ?>
			</p>

			<p><label for="theme_my_login_retrieve_pass_mail_from_name"><?php _e( 'From Name', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][retrieve_pass][mail_from_name]" type="text" id="theme_my_login_retrieve_pass_mail_from_name" value="<?php echo $this->theme_my_login->options['email']['retrieve_pass']['mail_from_name']; ?>" class="extended-text" /></p>

			<p><label for="theme_my_login_retrieve_pass_mail_from"><?php _e( 'From E-mail', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][retrieve_pass][mail_from]" type="text" id="theme_my_login_retrieve_pass_mail_from" value="<?php echo $this->theme_my_login->options['email']['retrieve_pass']['mail_from']; ?>" class="extended-text" /></p>

            <p><label for="theme_my_login_retrieve_pass_mail_content_type"><?php _e( 'E-mail Format', $this->theme_my_login->textdomain ); ?></label><br />
            <select name="theme_my_login[email][retrieve_pass][mail_content_type]" id="theme_my_login_retrieve_pass_mail_content_type">
            <option value="plain"<?php if ( 'plain' == $this->theme_my_login->options['email']['retrieve_pass']['mail_content_type'] ) echo ' selected="selected"'; ?>>Plain Text</option>
            <option value="html"<?php if ( 'html' == $this->theme_my_login->options['email']['retrieve_pass']['mail_content_type'] ) echo ' selected="selected"'; ?>>HTML</option>
            </select></p>

			<p><label for="theme_my_login_retrieve_pass_title"><?php _e( 'Subject', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][retrieve_pass][title]" type="text" id="theme_my_login_retrieve_pass_title" value="<?php echo $this->theme_my_login->options['email']['retrieve_pass']['title']; ?>" class="full-text" /></p>

			<p><label for="theme_my_login_retrieve_pass_message"><?php _e( 'Message', $this->theme_my_login->textdomain ); ?></label><br />
			<textarea name="theme_my_login[email][retrieve_pass][message]" id="theme_my_login_retrieve_pass_message" class="large-text" rows="10"><?php echo $this->theme_my_login->options['email']['retrieve_pass']['message']; ?></textarea></p>

			<p class="description"><?php _e( 'Available Variables', $this->theme_my_login->textdomain ); ?>: %blogname%, %siteurl%, %reseturl%, %user_login%, %user_email%, %user_ip%</p>
		</td>
	</tr>
</table>
<?php
	}

	/**
	 * Outputs password reset e-mail settings
	 *
	 * Callback for "$hookname" hook in method Theme_My_Login_Admin::add_submenu_page()
	 *
	 * @see Theme_My_Login_Admin::add_submenu_page()
	 * @since 6.0
	 * @access public
	 */
	function display_reset_pass_settings() {
		?>
<table class="form-table">
	<tr>
		<td>
			<h3><?php _e( 'User Notification', $this->theme_my_login->textdomain ); ?></h3>

			<p class="description">
				<?php _e( 'This e-mail will be sent to a user upon successful password recovery.', $this->theme_my_login->textdomain ); ?>
				<?php _e( 'Please be sure to include the variable %user_pass% if using default passwords or else the user will not know their password!', $this->theme_my_login->textdomain ); ?>
				<?php _e( 'If any field is left empty, the default will be used instead.', $this->theme_my_login->textdomain ); ?>
			</p>

			<p><label for="theme_my_login_reset_pass_mail_from_name"><?php _e( 'From Name', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][reset_pass][mail_from_name]" type="text" id="theme_my_login_reset_pass_mail_from_name" value="<?php echo $this->theme_my_login->options['email']['reset_pass']['mail_from_name']; ?>" class="extended-text" /></p>

			<p><label for="theme_my_login_retrieve_pass_mail_from"><?php _e( 'From E-mail', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][reset_pass][mail_from]" type="text" id="theme_my_login_reset_pass_mail_from" value="<?php echo $this->theme_my_login->options['email']['reset_pass']['mail_from']; ?>" class="extended-text" /></p>

            <p><label for="theme_my_login_retrieve_pass_mail_content_type"><?php _e( 'E-mail Format', $this->theme_my_login->textdomain ); ?></label><br />
            <select name="theme_my_login[email][reset_pass][mail_content_type]" id="theme_my_login_reset_pass_mail_content_type">
            <option value="plain"<?php if ( 'plain' == $this->theme_my_login->options['email']['reset_pass']['mail_content_type'] ) echo ' selected="selected"'; ?>>Plain Text</option>
            <option value="html"<?php if ( 'html' == $this->theme_my_login->options['email']['reset_pass']['mail_content_type'] ) echo ' selected="selected"'; ?>>HTML</option>
            </select></p>

			<p><label for="theme_my_login_reset_pass_title"><?php _e( 'Subject', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][reset_pass][title]" type="text" id="theme_my_login_reset_pass_title" value="<?php echo $this->theme_my_login->options['email']['reset_pass']['title']; ?>" class="full-text" /></p>

			<p><label for="theme_my_login_reset_pass_message"><?php _e( 'Message', $this->theme_my_login->textdomain ); ?></label><br />
			<textarea name="theme_my_login[email][reset_pass][message]" id="theme_my_login_reset_pass_message" class="large-text" rows="10"><?php echo $this->theme_my_login->options['email']['reset_pass']['message']; ?></textarea></p>

			<p class="description"><?php _e( 'Available Variables', $this->theme_my_login->textdomain ); ?>: %blogname%, %siteurl%, %user_login%, %user_email%, %user_pass%, %user_ip%</p>
		</td>
	</tr>
	<tr>
		<td>
			<h3><?php _e( 'Admin Notification', $this->theme_my_login->textdomain ); ?></h3>

			<p class="description">
				<?php _e( 'This e-mail will be sent to the e-mail address or addresses (multiple addresses may be separated by commas) specified below, upon user password change.', $this->theme_my_login->textdomain ); ?>
				<?php _e( 'If any field is left empty, the default will be used instead.', $this->theme_my_login->textdomain ); ?>
			</p>

			<p><label for="theme_my_login_reset_pass_admin_mail_to"><?php _e( 'To', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][reset_pass][admin_mail_to]" type="text" id="theme_my_login_reset_pass_admin_mail_to" value="<?php echo $this->theme_my_login->options['email']['reset_pass']['admin_mail_to']; ?>" class="extended-text" /></p>

			<p><label for="theme_my_login_reset_pass_admin_mail_from_name"><?php _e( 'From Name', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][reset_pass][admin_mail_from_name]" type="text" id="theme_my_login_reset_pass_admin_mail_from_name" value="<?php echo $this->theme_my_login->options['email']['reset_pass']['admin_mail_from_name']; ?>" class="extended-text" /></p>

			<p><label for="theme_my_login_reset_pass_admin_mail_from"><?php _e( 'From E-mail', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][reset_pass][admin_mail_from]" type="text" id="theme_my_login_reset_pass_admin_mail_from" value="<?php echo $this->theme_my_login->options['email']['reset_pass']['admin_mail_from']; ?>" class="extended-text" /></p>

            <p><label for="theme_my_login_reset_pass_admin_mail_content_type"><?php _e( 'E-mail Format', $this->theme_my_login->textdomain ); ?></label><br />
            <select name="theme_my_login[email][reset_pass][admin_mail_content_type]" id="theme_my_login_reset_pass_admin_mail_content_type">
            <option value="plain"<?php if ( 'plain' == $this->theme_my_login->options['email']['reset_pass']['admin_mail_content_type'] ) echo ' selected="selected"'; ?>>Plain Text</option>
            <option value="html"<?php if ( 'html' == $this->theme_my_login->options['email']['reset_pass']['admin_mail_content_type'] ) echo ' selected="selected"'; ?>>HTML</option>
            </select></p>

			<p><label for="theme_my_login_reset_pass_admin_title"><?php _e( 'Subject', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][reset_pass][admin_title]" type="text" id="theme_my_login_reset_pass_admin_title" value="<?php echo $this->theme_my_login->options['email']['reset_pass']['admin_title']; ?>" class="full-text" /></p>

			<p><label for="theme_my_login_reset_pass_admin_message"><?php _e( 'Message', $this->theme_my_login->textdomain ); ?></label><br />
			<textarea name="theme_my_login[email][reset_pass][admin_message]" id="theme_my_login_reset_pass_admin_message" class="large-text" rows="10"><?php echo $this->theme_my_login->options['email']['reset_pass']['admin_message']; ?></textarea></p>

			<p class="description"><?php _e( 'Available Variables', $this->theme_my_login->textdomain ); ?>: %blogname%, %siteurl%, %user_login%, %user_email%, %user_ip%</p>

			<p><label for="theme_my_login_reset_pass_admin_disable"><input name="theme_my_login[email][reset_pass][admin_disable]" type="checkbox" id="theme_my_login_reset_pass_admin_disable" value="1"<?php checked( 1, $this->theme_my_login->options['email']['reset_pass']['admin_disable'] ); ?> /> <?php _e( 'Disable Admin Notification', $this->theme_my_login->textdomain ); ?></label></p>
		</td>
	</tr>
</table>
<?php
	}

	/**
	 * Adds "E-mails" tab to Theme My Login menu
	 *
	 * Callback for "tml_admin_menu" hook in method Theme_My_Login_Admin::display_settings_page()
	 *
	 * @see Theme_My_Login_Admin::display_settings_page(), Theme_My_Login_Admin::add_menu_page, Theme_My_Login_Admin::add_submenu_page()
	 * @uses Theme_My_Login_Admin::add_menu_page, Theme_My_Login_Admin::add_submenu_page()
	 * @since 6.0
	 * @access public
	 *
	 * @param object $admin Reference to global $theme_my_login_admin object
	 */
	function admin_menu( &$admin ) {
		$admin->add_menu_page( __( 'E-mail', $this->theme_my_login->textdomain ), 'tml-options-email' );
		$admin->add_submenu_page( 'tml-options-email', __( 'New User', $this->theme_my_login->textdomain ), 'tml-options-email-new-user', array( &$this, 'display_new_user_settings' ) );
		$admin->add_submenu_page( 'tml-options-email', __( 'Retrieve Password', $this->theme_my_login->textdomain ), 'tml-options-email-retrieve-pass', array( &$this, 'display_retrieve_pass_settings' ) );
		$admin->add_submenu_page( 'tml-options-email', __( 'Reset Password', $this->theme_my_login->textdomain ), 'tml-options-email-reset-pass', array( &$this, 'display_reset_pass_settings' ) );
	}

	/**
	 * Loads the module
	 *
	 * @since 6.0
	 * @access public
	 */
	function load() {
		add_action( 'tml_admin_menu', array( &$this, 'admin_menu' ) );
		add_filter( 'tml_save_settings', array( &$this, 'save_settings' ) );
	}
}

/**
 * Holds the reference to Theme_My_Login_Custom_Email_Admin object
 * @global object $theme_my_login_custom_email_admin
 * @since 6.0
 */
$theme_my_login_custom_email_admin = new Theme_My_Login_Custom_Email_Admin();

endif; // Class exists

?>