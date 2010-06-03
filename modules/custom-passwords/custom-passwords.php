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
class Theme_My_Login_Custom_Passwords {

	var $theme_my_login;

	function password_fields( &$template ) {
	?>
	<p><label for="pass1-<?php $template->the_current_instance(); ?>"><?php _e( 'Password:', 'theme-my-login' );?></label>
	<input autocomplete="off" name="pass1" id="pass1-<?php $template->the_current_instance(); ?>" class="input" size="20" value="" type="password" /></p>
	<p><label for="pass2-<?php $template->the_current_instance(); ?>"><?php _e( 'Confirm Password:', 'theme-my-login' );?></label>
	<input autocomplete="off" name="pass2" id="pass2-<?php $template->the_current_instance(); ?>" class="input" size="20" value="" type="password" /></p>
<?php
	}

	function password_errors( $errors = '' ) {
		if ( empty( $errors ) )
			$errors = new WP_Error();
		if ( empty( $_POST['pass1'] ) || $_POST['pass1'] == '' || empty( $_POST['pass2'] ) || $_POST['pass2'] == '' ) {
			$errors->add( 'empty_password', __( '<strong>ERROR</strong>: Please enter a password.', 'theme-my-login' ) );
		} elseif ( $_POST['pass1'] !== $_POST['pass2'] ) {
			$errors->add( 'password_mismatch', __( '<strong>ERROR</strong>: Your passwords do not match.', 'theme-my-login' ) );
		} elseif ( strlen( $_POST['pass1'] ) < 6 ) {
			$errors->add( 'password_length', __( '<strong>ERROR</strong>: Your password must be at least 6 characters in length.', 'theme-my-login' ) );
		} else {
			$_POST['user_pw'] = $_POST['pass1'];
		}	
		return $errors;
	}

	function set_password( $user_pass ) {
		if ( isset( $_POST['user_pw'] ) && !empty( $_POST['user_pw'] ) )
			$user_pass = $_POST['user_pw'];
		return $user_pass;
	}

	function resetpass_action( &$theme_my_login ) {
		$errors =& $theme_my_login->errors;
		
		$user = $this->validate_reset_key( $_REQUEST['key'], $_REQUEST['login'] );
		if ( is_wp_error( $user ) ) {
			$redirect_to = Theme_My_Login::get_current_url( 'action=lostpassword&error=invalidkey&instance=' . $theme_my_login->request_instance );
			wp_redirect( $redirect_to );
			exit();
		}
		
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			$errors = $this->reset_password();
			if ( !is_wp_error( $errors ) ) {
				$redirect_to = Theme_My_Login::get_current_url( 'resetpass=complete&instance=' . $theme_my_login->request_instance );
				wp_redirect( $redirect_to );
				exit();
			}
		}
	}

	function get_resetpass_form( &$template ) {
		if ( !$template->get_template( 'resetpass-form.php' ) ) {
		?>
<div class="login" id="theme-my-login-<?php $template->the_current_instance(); ?>">
	<?php $template->the_action_message( 'resetpass' ); ?>
	<?php $template->the_errors(); ?>
	<form name="resetpasswordform" id="resetpasswordform-<?php $template->the_current_instance(); ?>" action="<?php $template->the_action_url( 'resetpass' ); ?>" method="post">
		<p>
			<label for="pass1-<?php $template->the_current_instance(); ?>"><?php _e( 'New Password:', 'theme-my-login' );?></label>
			<input autocomplete="off" name="pass1" id="pass1-<?php $template->the_current_instance(); ?>" class="input" size="20" value="" type="password" />
		</p>
		<p>
			<label for="pass2-<?php $template->the_current_instance(); ?>"><?php _e( 'Confirm Password:', 'theme-my-login' );?></label>
			<input autocomplete="off" name="pass2" id="pass2-<?php $template->the_current_instance(); ?>" class="input" size="20" value="" type="password" />
		</p>
<?php do_action( 'resetpassword_form', $template->get_current_instance() ); ?>
		<p class="submit">
			<input type="hidden" name="key" value="<?php $template->the_posted_value( 'key' ); ?>" />
			<input type="hidden" name="login" value="<?php $template->the_posted_value( 'login' ); ?>" />
			<input type="submit" name="wp-submit" id="wp-submit-<?php $template->the_current_instance(); ?>" value="<?php _e( 'Change Password', 'theme-my-login' ); ?>" />
		</p>
	</form>
	<?php $template->the_action_links( array( 'lost_password' => false ) ); ?>
</div>
<?php
		}
	}
	
	function resetpass_message() {
		return __( 'Please enter a new password.', 'theme-my-login' );
	}

	function register_redirect( $redirect_to ) {
		$request_instance = wdbj_tml_get_var( 'request_instance' );
		$redirect_to = site_url( 'wp-login.php?registration=complete' );
		if ( 'tml-page' != $request_instance )
			$redirect_to = wdbj_tml_get_current_url( 'registration=complete&instance=' . $request_instance );	
		return $redirect_to;
	}

	function resetpass_redirect( $redirect_to ) {
		$request_instance = wdbj_tml_get_var( 'request_instance' );
		$redirect_to = site_url( 'wp-login.php?resetpass=complete' );
		if ( 'tml-page' != $request_instance )
			$redirect_to = wdbj_tml_get_current_url( 'resetpass=complete&instance=' . $request_instance );	
		return $redirect_to;
	}

	function register_message( $message ) {
		if ( isset( $_GET['action'] ) && 'register' == $_GET['action'] )
			$message = '';
		return $message;
	}

	function lostpassword_message( $message ) {
		$message = __( 'Please enter your username or e-mail address. You will receive an e-mail with a link to reset your password.', 'theme-my-login' );
		return $message;
	}

	function action_messages( &$theme_my_login ) {
		if ( isset( $_GET['registration'] ) && 'complete' == $_GET['registration'] )
			$theme_my_login->errors->add( 'registration_complete', __( 'Registration complete. You may now log in.', 'theme-my-login' ), 'message' );
		elseif ( isset( $_GET['resetpass'] ) && 'complete' == $_GET['resetpass'] )
			$theme_my_login->errors->add( 'password_saved', __( 'Your password has been saved. You may now log in.', 'theme-my-login' ), 'message' );
	}

	function validate_reset_key( $key, $login ) {
		global $wpdb;

		$key = preg_replace( '/[^a-z0-9]/i', '', $key );

		if ( empty( $key ) || !is_string( $key ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key', 'theme-my-login' ) );

		if ( empty( $login ) || !is_string( $login ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key', 'theme-my-login' ) );

		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login ) );
		if ( empty( $user ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key', 'theme-my-login' ) );
			
		return $user;
	}

	function reset_password() {
		
		$user = $this->validate_reset_key( $_REQUEST['key'], $_REQUEST['login'] );
		if ( is_wp_error( $user ) )
			return $user;
		
		$errors = $this->password_errors( new WP_Error() );
		if ( $errors->get_error_code() )
			return $errors;
		
		$new_pass = $_POST['pass1'];

		do_action( 'password_reset', $user->user_login, $new_pass );

		wp_set_password( $new_pass, $user->ID );
		update_usermeta( $user->ID, 'default_password_nag', false );
		$message  = sprintf( __( 'Username: %s', 'theme-my-login' ), $user->user_login ) . "\r\n";
		$message .= sprintf( __( 'Password: %s', 'theme-my-login' ), $new_pass ) . "\r\n";
		$message .= site_url( 'wp-login.php', 'login' ) . "\r\n";

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		$title = sprintf( __( '[%s] Your new password', 'theme-my-login' ), $blogname );

		$title = apply_filters( 'password_reset_title', $title, $user->ID );
		$message = apply_filters( 'password_reset_message', $message, $new_pass, $user->ID );

		if ( $message && !wp_mail( $user->user_email, $title, $message ) )
			die( '<p>' . __( 'The e-mail could not be sent.', 'theme-my-login' ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...', 'theme-my-login' ) . '</p>' );

		wp_password_change_notification( $user );

		return true;
	}

	function Theme_My_Login_Custom_Passwords( &$theme_my_login ) {
		$this->theme_my_login =& $theme_my_login;
		
		// Password registration
		add_action( 'register_form', array( &$this, 'password_fields' ) );
		add_filter( 'registration_errors', array( &$this, 'password_errors' ) );
		add_filter( 'user_registration_pass', array( &$this, 'set_password' ) );
		// Password reset
		add_action( 'login_form_resetpass', array( &$this, 'get_resetpass_form' ) );
		add_action( 'login_form_rp', array( &$this, 'get_resetpass_form' ) );
		add_action( 'login_action_resetpass', array( &$this, 'resetpass_action' ) );
		add_action( 'login_action_rp', array( &$this, 'resetpass_action' ) );
		// Template messages
		add_filter( 'register_message', array( &$this, 'register_message' ) );
		add_filter( 'lostpassword_message', array( &$this, 'lostpassword_message' ) );
		add_filter( 'resetpass_message', array( &$this, 'resetpass_message' ) );
		add_action( 'tml_init', array( &$this, 'action_messages' ) );
		// Redirection
		add_filter( 'register_redirect', array( &$this, 'register_redirect' ) );
		add_filter( 'resetpass_redirect', array( &$this, 'resetpass_redirect' ) );
	}
}
endif;

/* Instaniate the class */
$Theme_My_login_Custom_Passwords = new Theme_My_Login_Custom_Passwords( $theme_my_login );

?>