<?php
/*
Plugin Name: Custom Passwords
Description: Enabling this module will initialize and enable custom passwords. There are no other settings for this module.
*/

if ( !class_exists( 'Theme_My_Login_Custom_Passwords' ) ) :
/**
 * Theme My Login Custom Passwords module class
 *
 * Adds the ability for users to set their own passwords upon registration and password reset.
 *
 * @since 6.0
 */
class Theme_My_Login_Custom_Passwords extends Theme_My_Login_Module {
	/**
	 * Outputs password fields to registration form
	 *
	 * Callback for "tml_register_form" hook in file "register-form.php", included by Theme_My_Login_Template::display()
	 *
	 * @see Theme_My_Login::display()
	 * @since 6.0
	 * @access public
	 *
	 * @param object $template Reference to Theme_My_Login_Template object
	 */
	function password_fields( &$template ) {
	?>
	<p><label for="pass1<?php $template->the_instance(); ?>"><?php _e( 'Password:', $this->theme_my_login->textdomain );?></label>
	<input autocomplete="off" name="pass1" id="pass1<?php $template->the_instance(); ?>" class="input" size="20" value="" type="password" /></p>
	<p><label for="pass2<?php $template->the_instance(); ?>"><?php _e( 'Confirm Password:', $this->theme_my_login->textdomain );?></label>
	<input autocomplete="off" name="pass2" id="pass2<?php $template->the_instance(); ?>" class="input" size="20" value="" type="password" /></p>
<?php
	}

	/**
	 * Handles password errors for registration form
	 *
	 * Callback for "registration_errors" hook in Theme_My_Login::register_new_user()
	 *
	 * @see Theme_My_Login::register_new_user()
	 * @since 6.0
	 * @access public
	 *
	 * @param WP_Error $errors WP_Error object
	 * @return WP_Error WP_Error object
	 */
	function password_errors( $errors = '' ) {
		// Make sure $errors is a WP_Error object
		if ( empty( $errors ) )
			$errors = new WP_Error();
		// Make sure passwords aren't empty
		if ( empty( $_POST['pass1'] ) || $_POST['pass1'] == '' || empty( $_POST['pass2'] ) || $_POST['pass2'] == '' ) {
			$errors->add( 'empty_password', __( '<strong>ERROR</strong>: Please enter a password.', $this->theme_my_login->textdomain ) );
		// Make sure passwords match
		} elseif ( $_POST['pass1'] !== $_POST['pass2'] ) {
			$errors->add( 'password_mismatch', __( '<strong>ERROR</strong>: Your passwords do not match.', $this->theme_my_login->textdomain ) );
		// Make sure password is long enough
		} elseif ( strlen( $_POST['pass1'] ) < 6 ) {
			$errors->add( 'password_length', __( '<strong>ERROR</strong>: Your password must be at least 6 characters in length.', $this->theme_my_login->textdomain ) );
		// All is good, assign password to a friendlier key
		} else {
			$_POST['user_pass'] = $_POST['pass1'];
		}
		return $errors;
	}

	/**
	 * Sets the user password
	 *
	 * Callback for "tml_user_registration_pass" hook in Theme_My_Login::register_new_user()
	 *
	 * @see Theme_My_Login::register_new_user()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $user_pass Auto-generated password passed in from filter
	 * @return string Password POSTed by user
	 */
	function set_password( $user_pass ) {
		// Make sure password isn't empty
		if ( isset( $_POST['user_pass'] ) && !empty( $_POST['user_pass'] ) )
			$user_pass = $_POST['user_pass'];
		return $user_pass;
	}

	/**
	 * Removes the default password nag
	 *
	 * Callback for "tml_new_user_registered" hook in Theme_My_Login::register_new_user()
	 *
	 * @see Theme_My_Login::register_new_user()
	 * @since 6.0
	 * @access public
	 *
	 * @param int $user_id The user's ID
	 */
	function remove_default_password_nag( $user_id ) {
		update_usermeta( $user_id, 'default_password_nag', false );
	}

	/**
	 * Resets the user's password
	 *
	 * Callback for "tml_request_resetpass" and "tml_request_rp" hooks in Theme_My_Login::the_request()
	 *
	 * @see Theme_My_Login::the_request()
	 * @since 6.0
	 * @access public
	 *
	 * @param object $theme_my_login
	 */
	function resetpass_action( &$theme_my_login ) {
		// Set local reference to $theme_my_login->errors
		$errors =& $theme_my_login->errors;

		// Validate the reset key
		$user = $this->validate_reset_key( $_REQUEST['key'], $_REQUEST['login'] );
		// Handle errors
		if ( is_wp_error( $user ) ) {
			// Redirect to current page with "action=lostpassword&error=invalidkey" added to the query
			$redirect_to = $theme_my_login->get_current_url( 'action=lostpassword&error=invalidkey' );
			// Add instance to query if specified
			if ( !empty( $theme_my_login->request_instance ) )
				$redirect_to = add_query_arg( 'instance', $theme_my_login->request_instance, $redirect_to );
			// Redirect
			wp_redirect( $redirect_to );
			exit();
		}

		// Check if form has been posted
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			// Reset the password
			$errors = $this->reset_password();
			// Make sure there aren't any errors
			if ( !is_wp_error( $errors ) ) {
				// Redirect to current page with "resetpass=complete" added to the query
				$redirect_to = $theme_my_login->get_current_url( 'resetpass=complete' );
				// Add instance to query if specified
				if ( !empty( $theme_my_login->request_instance ) )
					$redirect_to = add_query_arg( 'instance', $theme_my_login->request_instance, $redirect_to );
				// Redirect
				wp_redirect( $redirect_to );
				exit();
			}
		}
	}

	/**
	 * Outputs reset password form HTML
	 *
	 * This function will first look in the current theme's directory for "resetpass-form.php" and include it if found.
	 * Otherwise, the HTML below will be included instead.
	 *
	 * @see Theme_My_Login_Template::display()
	 * @since 6.0
	 * @access public
	 *
	 * @param object $template Reference to $theme_my_login_template object
	 */
	function get_resetpass_form( &$template ) {
		$templates = array();
		// Allow template override via shortcode or template tag args
		if ( !empty( $template->options['resetpass_template'] ) )
			$templates[] = $template->options['resetpass_template'];
		// Default template
		$templates[] = 'resetpass-form.php';
		// Load the template
		$template->get_template( $templates );
	}

	/**
	 * Changes template message according to a specific action
	 *
	 * Callback for "tml_action_template_message" hook in Theme_My_Login_Template::get_action_template_message()
	 *
	 * @see Theme_My_Login_Template::get_action_template_message()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $message The original message
	 * @param string $action The requested action
	 * @return string The new messgage
	 */
	function action_template_message( $message, $action ) {
		switch ( $action ) {
			case 'lostpassword' :
				$message = __( 'Please enter your username or e-mail address. You will receive an e-mail with a link to reset your password.', $this->theme_my_login->textdomain );
				break;
			case 'resetpass' :
				$message = __( 'Please enter a new password.', $this->theme_my_login->textdomain );
				break;
			case 'register' :
				$message = '';
				break;
		}
		return $message;
	}

	/**
	 * Changes the register template message
	 *
	 * Callback for "tml_register_passmail_template_message" hook
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @return string The new register message
	 */
	function register_passmail_template_message() {
		return;
	}
	/**
	 * Handles display of various action/status messages
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param object $theme_my_login Reference to global $theme_my_login object
	 */
	function action_messages( &$theme_my_login ) {
		// Change "Registration complete. Please check your e-mail." to reflect the fact that they already set a password
		if ( isset( $_GET['registration'] ) && 'complete' == $_GET['registration'] )
			$theme_my_login->errors->add( 'registration_complete', __( 'Registration complete. You may now log in.', $this->theme_my_login->textdomain ), 'message' );
		// Display the following message instead of "Check your e-mail for your new password."
		elseif ( isset( $_GET['resetpass'] ) && 'complete' == $_GET['resetpass'] )
			$theme_my_login->errors->add( 'password_saved', __( 'Your password has been saved. You may now log in.', $this->theme_my_login->textdomain ), 'message' );
	}

	/**
	 * Changes where the user is redirected upon successful registration
	 *
	 * Callback for "register_redirect" hook in Theme_My_Login::the_request()
	 *
	 * @see Theme_My_Login::the_request()
	 * @since 6.0
	 * @access public
	 *
	 * @return string $redirect_to Default redirect
	 * @return string URL to redirect to
	 */
	function register_redirect( $redirect_to ) {
		// Redirect to login page with "registration=complete" added to the query
		$redirect_to = site_url( 'wp-login.php?registration=complete' );
		// Add instance to the query if specified
		if ( isset( $_REQUEST['instance'] ) & !empty( $_REQUEST['instance'] ) )
			$redirect_to = add_query_arg( 'instance', $_REQUEST['instance'], $redirect_to );
		return $redirect_to;
	}

	/**
	 * Changes where the user is redirected upon successful password reset
	 *
	 * Callback for "resetpass_redirect" hook in Theme_My_Login::the_request()
	 *
	 * @see Theme_My_Login::the_request()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $redirect_to Default redirect
	 * @return string URL to redirect to
	 */
	function resetpass_redirect( $redirect_to ) {
		// Redirect to the login page with "resetpass=complete" added to the query
		$redirect_to = site_url( 'wp-login.php?resetpass=complete' );
		// Add instance to the query if specified
		if ( isset( $_REQUEST['instance'] ) & !empty( $_REQUEST['instance'] ) )
			$redirect_to = add_query_arg( 'instance', $_REQUEST['instance'], $redirect_to );	
		return $redirect_to;
	}

	/**
	 * Validates the reset key
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $key The validation key
	 * @param string $login The user login
	 * @return object|WP_Error Row from $wpdb->users table on success, WP_Error on failure
	 */
	function validate_reset_key( $key, $login ) {
		global $wpdb;

		// Strip non-alphanumeric characters
		$key = preg_replace( '/[^a-z0-9]/i', '', $key );

		// Make sure $key isn't empty
		if ( empty( $key ) || !is_string( $key ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key', $this->theme_my_login->textdomain ) );

		// Make sure $login isn't empty
		if ( empty( $login ) || !is_string( $login ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key', $this->theme_my_login->textdomain ) );

		// Make sure the $key and $login pair match
		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login ) );
		if ( empty( $user ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key', $this->theme_my_login->textdomain ) );

		return $user;
	}

	/**
	 * Resets the user password
	 *
	 * @since 6.0
	 * @access public
	 *
	 * return bool|WP_Error True on success, WP_Error on failure
	 */
	function reset_password() {
		// Validate the reset key
		$user = $this->validate_reset_key( $_REQUEST['key'], $_REQUEST['login'] );
		if ( is_wp_error( $user ) )
			return $user;

		// Validate the password
		$errors = $this->password_errors( new WP_Error() );
		if ( $errors->get_error_code() )
			return $errors;

		// Assign the password to a local variable
		$new_pass = $_POST['user_pass'];

		// Call "password_reset" hook
		do_action( 'password_reset', $user->user_login, $new_pass );

		// Set the password
		wp_set_password( $new_pass, $user->ID );

		// Remove the password nag
		update_usermeta( $user->ID, 'default_password_nag', false );

		// Notification e-mail message
		$message  = sprintf( __( 'Username: %s', $this->theme_my_login->textdomain ), $user->user_login ) . "\r\n";
		$message .= sprintf( __( 'Password: %s', $this->theme_my_login->textdomain ), $new_pass ) . "\r\n";
		$message .= site_url( 'wp-login.php', 'login' ) . "\r\n";

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		// Notification e-mail subject
		$title = sprintf( __( '[%s] Your new password', $this->theme_my_login->textdomain ), $blogname );

		// Apply filters to notification e-mail subject
		$title = apply_filters( 'password_reset_title', $title, $user->ID );
		// Apply filters to notification e-mail message
		$message = apply_filters( 'password_reset_message', $message, $new_pass, $user->ID );

		// Make sure the message sends
		if ( $message && !wp_mail( $user->user_email, $title, $message ) )
			die( '<p>' . __( 'The e-mail could not be sent.', $this->theme_my_login->textdomain ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...', $this->theme_my_login->textdomain ) . '</p>' );

		// Notify the admin of the change
		wp_password_change_notification( $user );

		return true;
	}

	/**
	 * Loads the module
	 *
	 * @since 6.0
	 * @access public
	 */
	function load() {
		// Register password
		add_action( 'tml_register_form', array( &$this, 'password_fields' ) );
		add_filter( 'registration_errors', array( &$this, 'password_errors' ) );
		add_filter( 'tml_user_registration_pass', array( &$this, 'set_password' ) );
		add_action( 'tml_new_user_registered', array( &$this, 'remove_default_password_nag' ) );
		// Reset password
		add_action( 'tml_display_resetpass', array( &$this, 'get_resetpass_form' ) );
		add_action( 'tml_display_rp', array( &$this, 'get_resetpass_form' ) );
		add_action( 'tml_request_resetpass', array( &$this, 'resetpass_action' ) );
		add_action( 'tml_request_rp', array( &$this, 'resetpass_action' ) );
		// Template messages
		add_filter( 'tml_register_passmail_template_message', array( &$this, 'register_passmail_template_message' ) );
		add_filter( 'tml_action_template_message', array( &$this, 'action_template_message' ), 10, 2 );
		add_action( 'tml_request', array( &$this, 'action_messages' ) );
		// Redirection
		add_filter( 'register_redirect', array( &$this, 'register_redirect' ) );
		add_filter( 'resetpass_redirect', array( &$this, 'resetpass_redirect' ) );
	}
}

/**
 * Holds the reference to Theme_My_Login_Custom_Passwords object
 * @global object $theme_my_login_custom_passwords
 * @since 6.0
 */
$theme_my_login_custom_passwords = new Theme_My_Login_Custom_Passwords();

endif; // Class exists

?>