<?php

if ( !class_exists( 'Theme_My_Login_User_Moderation_Admin' ) ) :
/**
 * Theme My Login User Moderation module admin class
 *
 * @since 6.0
 */
class Theme_My_Login_User_Moderation_Admin extends Theme_My_Login_Module {
	/**
	 * Attaches actions/filters explicitly to users.php
	 *
	 * Callback for "load-users.php" hook
	 *
	 * @since 6.0
	 * @access public
	 */
	function load_users_page() {
		// Shorthand reference
		$theme_my_login =& $this->theme_my_login;
		$user_moderation =& $GLOBALS['theme_my_login_user_moderation'];

		add_filter( 'user_row_actions', array( &$this, 'user_row_actions' ), 10, 2 );
		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
		add_action( 'delete_user', array( &$this, 'deny_user' ) );

		// Is there an action?
		if ( isset( $_GET['action'] ) ) {

			// Is it a sanctioned action?
			if ( in_array( $_GET['action'], array( 'approve', 'resendactivation' ) ) ) {

				// Is there a user ID?
				$user = isset( $_GET['user'] ) ? $_GET['user'] : '';

				// No user ID?
				if ( !$user || !current_user_can( 'edit_user', $user ) )
					wp_die( __( 'You can&#8217;t edit that user.', $theme_my_login->textdomain ) );

				// Where did we come from?
				$redirect_to = isset( $_REQUEST['wp_http_referer'] ) ? remove_query_arg( array( 'wp_http_referer', 'updated', 'delete_count' ), stripslashes( $_REQUEST['wp_http_referer'] ) ) : 'users.php';

				// Are we approving?
				if ( 'approve' == $_GET['action'] ) {
					check_admin_referer( 'approve-user' );

					$newpass = $theme_my_login->is_module_active( 'custom-passwords/custom-passwords.php' ) ? 0 : 1;

					if ( !$this->approve_user( $user, $newpass ) )
						wp_die( __( 'You can&#8217;t edit that user.', $theme_my_login->textdomain ) );

					$redirect_to = add_query_arg( 'update', 'approve', $redirect_to );
				}
				// Are we resending an activation e-mail?
				elseif ( 'resendactivation' == $_GET['action'] ) {
					check_admin_referer( 'resend-activation' );

					// Apply activation e-mail filters
					$user_moderation->apply_user_activation_notification_filters();
					if ( !$user_moderation->new_user_activation_notification( $user ) )
						wp_die( __( 'The e-mail could not be sent.', $theme_my_login->textdomain ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...', $theme_my_login->textdomain ) );

					$redirect_to = add_query_arg( 'update', 'sendactivation', $redirect_to );
				}
				wp_redirect( $redirect_to );
				exit;
			}
		}
	}

	/**
	 * Adds update messages to the admin screen
	 *
	 * Callback for "admin_notices" hook in file admin-header.php
	 *
	 * @since 6.0
	 * @access public
	 */
	function admin_notices() {
		if ( isset( $_GET['update'] ) && in_array( $_GET['update'], array( 'approve', 'sendactivation' ) ) ) {
			echo '<div id="message" class="updated fade"><p>';
			if ( 'approve' == $_GET['update'] )
				_e( 'User approved.', $this->theme_my_login->textdomain );
			elseif ( 'sendactivation' == $_GET['update'] )
				_e( 'Activation sent.', $this->theme_my_login->textdomain );
			echo '</p></div>';
		}
	}

	/**
	 * Adds "Approve" link for each pending user on users.php
	 *
	 * Callback for "user_row_actions" hook in {@internal unknown}
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param array $actions The user actions
	 * @param WP_User $user_object The current user object
	 * @return array The filtered user actions
	 */
	function user_row_actions( $actions, $user_object ) {
		$current_user = wp_get_current_user();
		if ( $current_user->ID != $user_object->ID ) {
			if ( in_array( 'pending', (array) $user_object->roles ) ) {
				$_actions = array();
				// If moderation type is e-mail activation, add "Resend Activation" link
				if ( 'email' == $this->theme_my_login->options['moderation']['type'] ) {
					$_actions['resend-activation'] = '<a href="' . add_query_arg( 'wp_http_referer',
						urlencode( esc_url( stripslashes( $_SERVER['REQUEST_URI'] ) ) ),
						wp_nonce_url( "users.php?action=resendactivation&amp;user=$user_object->ID", 'resend-activation' ) ) . '">' . __( 'Resend Activation', $this->theme_my_login->textdomain ) . '</a>';
				} elseif ( 'admin' == $this->theme_my_login->options['moderation']['type'] ) {
					// Add "Approve" link
					$_actions['approve-user'] = '<a href="' . add_query_arg( 'wp_http_referer',
						urlencode( esc_url( stripslashes( $_SERVER['REQUEST_URI'] ) ) ),
						wp_nonce_url( "users.php?action=approve&amp;user=$user_object->ID", 'approve-user' ) ) . '">' . __( 'Approve', $this->theme_my_login->textdomain ) . '</a>';
				}
				$actions = array_merge( $_actions, $actions );
			}
		}
		return $actions;
	}

	/**
	 * Handles activating a new user by admin approval
	 *
	 * @param string $user_id User's ID
	 * @param bool $newpass Whether or not to assign a new password
	 * @return bool Returns false if not a valid user
	 */
	function approve_user( $user_id, $newpass = false ) {
		global $wpdb;

		$user_id = (int) $user_id;

		// Get user by ID
		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE ID = %d", $user_id ) );
		if ( empty( $user ) )
			return false;

		do_action( 'approve_user', $user->ID );

		// Clear the activation key if there is one
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => '' ), array( 'ID' => $user->ID ) );

		$user_object = new WP_User( $user->ID );
		$user_object->set_role( get_option( 'default_role' ) );
		unset( $user_object );

		$user_pass = __( 'Same as when you signed up.', $this->theme_my_login->textdomain );
		if ( $newpass ) {
			$user_pass = wp_generate_password();
			wp_set_password( $user_pass, $user->ID );
		}

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$blogname = $GLOBALS['current_site']->site_name;
		} else {
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$message  = sprintf( __( 'You have been approved access to %s', $this->theme_my_login->textdomain ), $blogname ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s', $this->theme_my_login->textdomain ), $user->user_login ) . "\r\n";
		$message .= sprintf( __( 'Password: %s', $this->theme_my_login->textdomain ), $user_pass ) . "\r\n\r\n";
		$message .= site_url( 'wp-login.php', 'login' ) . "\r\n";	

		$title = sprintf( __( '[%s] Registration Approved', $this->theme_my_login->textdomain ), $blogname );

		$title = apply_filters( 'user_approval_notification_title', $title, $user->ID );
		$message = apply_filters( 'user_approval_notification_message', $message, $user_pass, $user->ID );

		if ( $message && !wp_mail( $user->user_email, $title, $message ) )
			  die( '<p>' . __( 'The e-mail could not be sent.', $this->theme_my_login->textdomain ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...', $this->theme_my_login->textdomain ) . '</p>' );

		return true;
	}

	/**
	 * Called upon deletion of a user in the "Pending" role
	 *
	 * @param string $user_id User's ID
	 */
	function deny_user( $user_id ) {

		$user_id = (int) $user_id;

		$user = new WP_User( $user_id );
		if ( in_array( 'pending', (array) $user->roles ) )
			return;

		do_action( 'deny_user', $user->ID );

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$blogname = $GLOBALS['current_site']->site_name;
		} else {
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$message = sprintf( __( 'You have been denied access to %s', $this->theme_my_login->textdomain ), $blogname );
		$title = sprintf( __( '[%s] Registration Denied', $this->theme_my_login->textdomain ), $blogname );

		$title = apply_filters( 'user_denial_notification_title', $title, $user_id );
		$message = apply_filters( 'user_denial_notification_message', $message, $user_id );

		if ( $message && !wp_mail( $user->user_email, $title, $message ) )
			  die( '<p>' . __( 'The e-mail could not be sent.', $this->theme_my_login->textdomain ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...', $this->theme_my_login->textdomain ) . '</p>' );
	}

	/**
	 * Adds "Moderation" tab to Theme My Login menu
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
		$admin->add_menu_page( __( 'Moderation', $this->theme_my_login->textdomain ), 'tml-options-moderation', array( &$this, 'display_settings' ) );
		if ( $this->theme_my_login->is_module_active( 'custom-email/custom-email.php' ) ) {
			$admin->add_submenu_page( 'tml-options-email', __( 'User Activation', $this->theme_my_login->textdomain ), 'tml-options-email-user-activation', array( &$this, 'display_user_activation_email_settings' ) );
			$admin->add_submenu_page( 'tml-options-email', __( 'User Approval', $this->theme_my_login->textdomain ), 'tml-options-email-user-approval', array( &$this, 'display_user_approval_email_settings' ) );
			$admin->add_submenu_page( 'tml-options-email', __( 'User Denial', $this->theme_my_login->textdomain ), 'tml-options-email-user-denial', array( &$this, 'display_user_denial_email_settings' ) );
		}	
	}

	/**
	 * Outputs user moderation settings
	 *
	 * Callback for "$hookname" hook in method Theme_My_Login_Admin::add_submenu_page()
	 *
	 * @see Theme_My_Login_Admin::add_submenu_page()
	 * @since 6.0
	 * @access public
	 */
	function display_settings() {
		// Shorthand reference
		$theme_my_login =& $this->theme_my_login;
		?>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e( 'User Moderation', $this->theme_my_login->textdomain ); ?></th>
		<td>
			<input name="theme_my_login[moderation][type]" type="radio" id="theme_my_login_moderation_type_none" value="none" <?php if ( 'none' == $theme_my_login->options['moderation']['type'] ) echo 'checked="checked"'; ?> />
			<label for="theme_my_login_moderation_type_none"><?php _e( 'None', $theme_my_login->textdomain ); ?></label>
			<p class="description"><?php _e( 'Check this option to require no moderation.', $theme_my_login->textdomain ); ?></p>
			<input name="theme_my_login[moderation][type]" type="radio" id="theme_my_login_moderation_type_email" value="email" <?php if ( 'email' == $theme_my_login->options['moderation']['type'] ) echo 'checked="checked"'; ?> />
			<label for="theme_my_login_moderation_type_email"><?php _e( 'E-mail Confirmation', $theme_my_login->textdomain ); ?></label>
			<p class="description"><?php _e( 'Check this option to require new users to confirm their e-mail address before they may log in.', $this->theme_my_login->textdomain ); ?></p>
			<input name="theme_my_login[moderation][type]" type="radio" id="theme_my_login_moderation_type_admin" value="admin" <?php if ( 'admin' == $theme_my_login->options['moderation']['type'] ) echo 'checked="checked"'; ?> />
			<label for="theme_my_login_moderation_type_admin"><?php _e( 'Admin Approval', $theme_my_login->textdomain ); ?></label>
			<p class="description"><?php _e( 'Check this option to require new users to be approved by an administrator before they may log in.', $this->theme_my_login->textdomain ); ?></p>
		</td>
	</tr>
</table>
<?php
	}

	/**
	 * Outputs user activation e-mail settings
	 *
	 * Callback for "$hookname" hook in method Theme_My_Login_Admin::add_submenu_page()
	 *
	 * @see Theme_My_Login_Admin::add_submenu_page()
	 * @since 6.0
	 * @access public
	 */
	function display_user_activation_email_settings() {
		// Shorthand reference to $theme_my_login object
		$theme_my_login =& $this->theme_my_login;
		// User activation email options
		$user_activation = $theme_my_login->get_option( array( 'email', 'user_activation' ), array() );
		?>
<table class="form-table">
    <tr>
		<td>
			<p class="description">
				<?php _e( 'This e-mail will be sent to a new user upon registration when "E-mail Confirmation" is checked for "User Moderation".', $theme_my_login->textdomain ); ?>
				<?php _e( 'Please be sure to include the variable %activateurl% or else the user will not be able to activate their account!', $theme_my_login->textdomain ); ?>
				<?php _e( 'If any field is left empty, the default will be used instead.', $theme_my_login->textdomain ); ?>
			</p>

			<p><label for="theme_my_login_user_activation_mail_from_name"><?php _e( 'From Name', $theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][user_activation][mail_from_name]" type="text" id="theme_my_login_user_activation_mail_from_name" value="<?php if ( isset( $user_activation['mail_from_name'] ) ) echo $user_activation['mail_from_name']; ?>" class="extended-text" /></p>

			<p><label for="theme_my_login_user_activation_mail_from"><?php _e( 'From E-mail', $theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][user_activation][mail_from]" type="text" id="theme_my_login_user_activation_mail_from" value="<?php if ( isset( $user_activation['mail_from'] ) ) echo $user_activation['mail_from']; ?>" class="extended-text" /></p>

            <p><label for="theme_my_login_user_activation_mail_content_type"><?php _e( 'E-mail Format', $theme_my_login->textdomain ); ?></label><br />
            <select name="theme_my_login[email][user_activation][mail_content_type]" id="theme_my_login_user_activation_mail_content_type">
            <option value="plain"<?php if ( isset( $user_activation['mail_content_type'] ) && 'plain' == $user_activation['mail_content_type'] ) echo ' selected="selected"'; ?>>Plain Text</option>
            <option value="html"<?php if ( isset( $user_activation['mail_content_type'] ) && 'html' == $user_activation['mail_content_type'] ) echo ' selected="selected"'; ?>>HTML</option>
            </select></p>

			<p><label for="theme_my_login_user_activation_title"><?php _e( 'Subject', $theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][user_activation][title]" type="text" id="theme_my_login_user_activation_title" value="<?php if ( isset( $user_activation['title'] ) ) echo $user_activation['title']; ?>" class="full-text" /></p>

			<p><label for="theme_my_login_user_activation_message"><?php _e( 'Message', $theme_my_login->textdomain ); ?></label><br />
			<textarea name="theme_my_login[email][user_activation][message]" id="theme_my_login_user_activation_message" class="large-text" rows="10"><?php if ( isset( $user_activation['message'] ) ) echo $user_activation['message']; ?></textarea></p>

			<p class="description"><?php _e( 'Available Variables', $theme_my_login->textdomain ); ?>: %blogname%, %siteurl%, %activateurl%, %user_login%, %user_email%, %user_ip%</p>
		</td>
	</tr>
</table>
<?php
	}

	/**
	 * Outputs user approval e-mail settings
	 *
	 * Callback for "$hookname" hook in method Theme_My_Login_Admin::add_submenu_page()
	 *
	 * @see Theme_My_Login_Admin::add_submenu_page()
	 * @since 6.0
	 * @access public
	 */
	function display_user_approval_email_settings() {
		// Shorthand reference to $theme_my_login object
		$theme_my_login =& $this->theme_my_login;
		// User approval email options
		$user_approval = $theme_my_login->get_option( array( 'email', 'user_approval' ), array() );
		?>
<table class="form-table">
    <tr>
		<td>
			<h3><?php _e( 'User Notification', $theme_my_login->textdomain ); ?></h3>

			<p class="description">
				<?php _e( 'This e-mail will be sent to a new user upon admin approval when "Admin Approval" is checked for "User Moderation".', $theme_my_login->textdomain ); ?>
				<?php _e( 'Please be sure to include the variable %user_pass% if using default passwords or else the user will not know their password!', $theme_my_login->textdomain ); ?>
				<?php _e( 'If any field is left empty, the default will be used instead.', $theme_my_login->textdomain ); ?>
			</p>

			<p><label for="theme_my_login_user_approval_mail_from_name"><?php _e( 'From Name', $theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][user_approval][mail_from_name]" type="text" id="theme_my_login_user_approval_mail_from_name" value="<?php if ( isset( $user_approval['mail_from_name'] ) ) echo $user_approval['mail_from_name']; ?>" class="extended-text" /></p>

			<p><label for="theme_my_login_user_approval_mail_from"><?php _e( 'From E-mail', $theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][user_approval][mail_from]" type="text" id="theme_my_login_user_approval_mail_from" value="<?php if ( isset( $user_approval['mail_from'] ) ) echo $user_approval['mail_from']; ?>" class="extended-text" /></p>

            <p><label for="theme_my_login_user_approval_mail_content_type"><?php _e( 'E-mail Format', $theme_my_login->textdomain ); ?></label><br />
            <select name="theme_my_login[email][user_approval][mail_content_type]" id="theme_my_login_user_approval_mail_content_type">
            <option value="plain"<?php if ( isset( $user_approval['mail_content_type'] ) && 'plain' == $user_approval['mail_content_type'] ) echo ' selected="selected"'; ?>>Plain Text</option>
            <option value="html"<?php if ( isset( $user_approval['mail_content_type'] ) && 'html' == $user_approval['mail_content_type'] ) echo ' selected="selected"'; ?>>HTML</option>
            </select></p>

			<p><label for="theme_my_login_user_approval_title"><?php _e( 'Subject', $theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][user_approval][title]" type="text" id="theme_my_login_user_approval_title" value="<?php if ( isset( $user_approval['title'] ) ) echo $user_approval['title']; ?>" class="full-text" /></p>

			<p><label for="theme_my_login_user_approval_message"><?php _e( 'Message', $theme_my_login->textdomain ); ?></label><br />
			<textarea name="theme_my_login[email][user_approval][message]" id="theme_my_login_user_approval_message" class="large-text" rows="10"><?php if ( isset( $user_approval['message'] ) ) echo $user_approval['message']; ?></textarea></p>

			<p class="description"><?php _e( 'Available Variables', $theme_my_login->textdomain ); ?>: %blogname%, %siteurl%, %loginurl%, %user_login%, %user_email%, %user_pass%</p>
		</td>
	</tr>
	<tr>
		<td>
			<h3><?php _e( 'Admin Notification', $theme_my_login->textdomain ); ?></h3>

			<p class="description">
				<?php _e( 'This e-mail will be sent to the e-mail address or addresses (multiple addresses may be separated by commas) specified below upon user registration when "Admin Approval" is checked for "User Moderation".', $theme_my_login->textdomain ); ?>
				<?php _e( 'If any field is left empty, the default will be used instead.', $theme_my_login->textdomain ); ?>
			</p>

			<p><label for="theme_my_login_user_approval_admin_mail_to"><?php _e( 'To', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][user_approval][admin_mail_to]" type="text" id="theme_my_login_user_approval_admin_mail_to" value="<?php if ( isset( $user_approval['admin_mail_to'] ) ) echo $user_approval['admin_mail_to']; ?>" class="extended-text" /></p>

			<p><label for="theme_my_login_user_approval_admin_mail_from_name"><?php _e( 'From Name', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][user_approval][admin_mail_from_name]" type="text" id="theme_my_login_user_approval_admin_mail_from_name" value="<?php if ( isset( $user_approval['admin_mail_from_name'] ) ) echo $user_approval['admin_mail_from_name']; ?>" class="extended-text" /></p>

			<p><label for="theme_my_login_user_approval_admin_mail_from"><?php _e( 'From E-mail', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][user_approval][admin_mail_from]" type="text" id="theme_my_login_user_approval_admin_mail_from" value="<?php if ( isset( $user_approval['admin_mail_from'] ) ) echo $user_approval['admin_mail_from']; ?>" class="extended-text" /></p>

            <p><label for="theme_my_login_user_approval_admin_mail_content_type"><?php _e( 'E-mail Format', $this->theme_my_login->textdomain ); ?></label><br />
            <select name="theme_my_login[email][user_approval][admin_mail_content_type]" id="theme_my_login_user_approval_admin_mail_content_type">
            <option value="plain"<?php if ( isset( $user_approval['admin_mail_content_type'] ) && 'plain' == $user_approval['admin_mail_content_type'] ) echo ' selected="selected"'; ?>>Plain Text</option>
            <option value="html"<?php if ( isset( $user_approval['admin_mail_content_type'] ) && 'html' == $user_approval['admin_mail_content_type'] ) echo ' selected="selected"'; ?>>HTML</option>
            </select></p>

			<p><label for="theme_my_login_user_approval_admin_title"><?php _e( 'Subject', $this->theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][user_approval][admin_title]" type="text" id="theme_my_login_user_approval_admin_title" value="<?php if ( isset( $user_approval['admin_title'] ) ) echo $user_approval['admin_title']; ?>" class="full-text" /></p>

			<p><label for="theme_my_login_user_approval_admin_message"><?php _e( 'Message', $this->theme_my_login->textdomain ); ?></label><br />
			<textarea name="theme_my_login[email][user_approval][admin_message]" id="theme_my_login_user_approval_admin_message" class="large-text" rows="10"><?php if ( isset( $user_approval['admin_message'] ) ) echo $user_approval['admin_message']; ?></textarea></p>

			<p class="description"><?php _e( 'Available Variables', $this->theme_my_login->textdomain ); ?>: %blogname%, %siteurl%, %pendingurl%, %user_login%, %user_email%, %user_ip%</p>

			<p><label for="theme_my_login_user_approval_admin_disable"><input name="theme_my_login[email][user_approval][admin_disable]" type="checkbox" id="theme_my_login_user_approval_admin_disable" value="1"<?php checked( 1, isset( $user_approval['admin_disable'] ) && $user_approval['admin_disable'] ); ?> /> <?php _e( 'Disable Admin Notification', $this->theme_my_login->textdomain ); ?></label></p>
		</td>
	</tr>
</table>
<?php
	}

	/**
	 * Outputs user denial e-mail settings
	 *
	 * Callback for "$hookname" hook in method Theme_My_Login_Admin::add_submenu_page()
	 *
	 * @see Theme_My_Login_Admin::add_submenu_page()
	 * @since 6.0
	 * @access public
	 */
	function display_user_denial_email_settings() {
		// Shorthand reference to $theme_my_login object
		$theme_my_login =& $this->theme_my_login;
		// User denial email options
		$user_denial = $theme_my_login->get_option( array( 'email', 'user_denial' ), array() );
		?>
<table class="form-table">
    <tr>
		<td>
			<p class="description">
				<?php _e( 'This e-mail will be sent to a user who is deleted/denied when "Admin Approval" is checked for "User Moderation" and the user\'s role is "Pending".', $theme_my_login->textdomain ); ?>
				<?php _e( 'If any field is left empty, the default will be used instead.', $theme_my_login->textdomain ); ?>
			</p>

			<p><label for="theme_my_login_user_denial_mail_from_name"><?php _e( 'From Name', $theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][user_denial][mail_from_name]" type="text" id="theme_my_login_user_denial_mail_from_name" value="<?php if ( isset( $user_denial['mail_from_name'] ) ) echo $user_denial['mail_from_name']; ?>" class="extended-text" /></p>

			<p><label for="theme_my_login_user_denial_mail_from"><?php _e( 'From E-mail', $theme_my_login->textdomain ); ?></label><br />
			<input name="theme_my_login[email][user_denial][mail_from]" type="text" id="theme_my_login_user_denial_mail_from" value="<?php if ( isset( $user_denial['mail_from'] ) ) echo $user_denial['mail_from']; ?>" class="extended-text" /></p>

            <p><label for="theme_my_login_user_denial_mail_content_type"><?php _e( 'E-mail Format', $theme_my_login->textdomain ); ?></label><br />
            <select name="theme_my_login[email][user_denial][mail_content_type]" id="theme_my_login_user_denial_mail_content_type">
            <option value="plain"<?php if ( isset( $user_denial['mail_content_type'] ) && 'plain' == $user_denial['mail_content_type'] ) echo ' selected="selected"'; ?>>Plain Text</option>
            <option value="html"<?php if ( isset( $user_denial['mail_content_type'] ) && 'html' == $user_denial['mail_content_type'] ) echo ' selected="selected"'; ?>>HTML</option>
            </select></p>

			<p><label for="theme_my_login_user_denial_title"><?php _e('Subject', $theme_my_login->textdomain); ?></label><br />
			<input name="theme_my_login[email][user_denial][title]" type="text" id="theme_my_login_user_denial_title" value="<?php if ( isset( $user_denial['title'] ) ) echo $user_denial['title']; ?>" class="full-text" /></p>

			<p><label for="theme_my_login_user_denial_message"><?php _e('Message', $theme_my_login->textdomain); ?></label><br />
			<textarea name="theme_my_login[email][user_denial][message]" id="theme_my_login_user_denial_message" class="large-text" rows="10"><?php if ( isset( $user_denial['message'] ) ) echo $user_denial['message']; ?></textarea></p>

			<p class="description"><?php _e( 'Available Variables', $theme_my_login->textdomain ); ?>: %blogname%, %siteurl%, %user_login%, %user_email%</p>
		</td>
	</tr>
</table>
<?php
	}

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
		if ( $this->theme_my_login->is_module_active( 'custom-email/custom-email.php' ) )
			$settings['email']['user_approval']['admin_disable'] = empty( $settings['email']['user_approval']['admin_disable'] ) ? 0 : 1;
		return $settings;
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
		add_action( 'load-users.php', array( &$this, 'load_users_page' ) );
	}

}

/**
 * Holds the reference to Theme_My_Login_User_Moderation_Admin object
 * @global object $theme_my_login_user_moderation_admin
 * @since 6.0
 */
$theme_my_login_user_moderation_admin = new Theme_My_Login_User_Moderation_Admin();

endif; // Class exists

?>