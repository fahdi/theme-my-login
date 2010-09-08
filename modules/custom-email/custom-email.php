<?php
/*
Plugin Name: Custom E-mail
Description: Enabling this module will initialize custom e-mails. You will then have to configure the settings via the "E-mail" tab.
*/

if ( !class_exists( 'Theme_My_Login_Custom_Email' ) ) :
/**
 * Theme My Login Custom E-mail module class
 *
 * Customize e-mails sent from the login/registration system.
 *
 * @since 6.0
 */
class Theme_My_Login_Custom_Email extends Theme_My_Login_Module {
	/**
	 * Holds reference to module specific options in $theme_my_login object options
	 *
	 * @since 6.0
	 * @access public
	 * @var array
	 */
	var $options;

	/**
	 * Mail from
	 *
	 * @since 6.0
	 * @access public
	 * @var string
	 */
	var $mail_from;

	/**
	 * Mail from name
	 *
	 * @since 6.0
	 * @access public
	 * @var string
	 */
	var $mail_from_name;

	/**
	 * Mail content type
	 *
	 * @since 6.0
	 * @access public
	 * @var string
	 */
	var $mail_content_type;

	/**
	 * Sets variables to be used with mail header filters
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $mail_from E-mail address to send the mail from
	 * @param string $mail_from_name Name to send the mail from
	 * @param string $mail_content_type Content type for the message
	 */
	function set_mail_headers( $mail_from = '', $mail_from_name = '', $mail_content_type = 'text' ) {
		$this->mail_from = $mail_from;
		$this->mail_from_name = $mail_from_name;
		$this->mail_content_type = $mail_content_type;
	}

	/**
	 * Applies all password retrieval mail filters
	 *
	 * Callback for "retrieve_password" hook in method Theme_My_Login::retrieve_password()
	 *
	 * @see Theme_My_Login::retrieve_password()
	 * @since 6.0
	 * @access public
	 */
	function apply_retrieve_pass_filters() {
		$options =& $this->options['retrieve_pass'];
		$this->set_mail_headers( $options['mail_from'], $options['mail_from_name'], $options['mail_content_type'] );
		add_filter( 'retrieve_password_title', array( &$this, 'retrieve_pass_title_filter' ), 10, 2 );
		add_filter( 'retrieve_password_message', array( &$this, 'retrieve_pass_message_filter' ), 10, 3 );
	}

	/**
	 * Applies all password reset mail filters
	 *
	 * Callback for "password_reset" hook in method Theme_My_Login::reset_password()
	 *
	 * @see Theme_My_Login::reset_password()
	 * @since 6.0
	 * @access public
	 */
	function apply_reset_pass_filters() {
		$options =& $this->options['reset_pass'];
		$this->set_mail_headers( $options['mail_from'], $options['mail_from_name'], $options['mail_content_type'] );
		add_filter( 'password_reset_title', array( &$this, 'reset_pass_title_filter' ), 10, 2 );
		add_filter( 'password_reset_message', array( &$this, 'reset_pass_message_filter' ), 10, 3 );
		add_filter( 'password_change_notification_mail_to', array( &$this, 'password_change_notification_mail_to_filter' ) );
		add_filter( 'password_change_notification_title', array( &$this, 'password_change_notification_title_filter' ), 10, 2 );
		add_filter( 'password_change_notification_message', array( &$this, 'password_change_notification_message_filter' ), 10, 2 );
		add_filter( 'send_password_change_notification', array( &$this, 'send_password_change_notification_filter' ) );
	}

	/**
	 * Applies all new user mail filters
	 *
	 * Callback for "register_post" hook in method Theme_My_Login::register_new_user()
	 *
	 * @see Theme_My_Login::register_new_user()
	 * @since 6.0
	 * @access public
	 */
	function apply_new_user_filters() {
		add_filter( 'new_user_notification_title', array( &$this, 'new_user_notification_title_filter' ), 10, 2 );
		add_filter( 'new_user_notification_message', array( &$this, 'new_user_notification_message_filter' ), 10, 3 );
		add_filter( 'send_new_user_notification', array( &$this, 'send_new_user_notification_filter' ) );
		add_filter( 'new_user_admin_notification_mail_to', array( &$this, 'new_user_admin_notification_mail_to_filter' ) );
		add_filter( 'new_user_admin_notification_title', array( &$this, 'new_user_admin_notification_title_filter' ), 10, 2 );
		add_filter( 'new_user_admin_notification_message', array( &$this, 'new_user_admin_notification_message_filter' ), 10, 2 );
		add_filter( 'send_new_user_admin_notification', array( &$this, 'send_new_user_admin_notification_filter' ) );
	}

	/**
	 * Changes the mail from address
	 *
	 * Callback for "wp_mail_from" hook in wp_mail()
	 *
	 * @see wp_mail()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $from_email Default from email
	 * @return string New from email
	 */
	function mail_from_filter( $from_email ) {
		return empty( $this->mail_from ) ? $from_email : $this->mail_from;
	}

	/**
	 * Changes the mail from name
	 *
	 * Callback for "wp_mail_from_name" hook in wp_mail()
	 *
	 * @see wp_mail()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $from_name Default from name
	 * @return string New from name
	 */
	function mail_from_name_filter( $from_name ) {
		return empty( $this->mail_from_name ) ? $from_name : $this->mail_from_name;
	}

	/**
	 * Changes the mail content type
	 *
	 * Callback for "wp_mail_content_type" hook in wp_mail()
	 *
	 * @see wp_mail()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $content_type Default content type
	 * @return string New content type
	 */
	function mail_content_type_filter( $content_type ) {
		return empty( $this->mail_content_type ) ? $content_type : 'text/' . $this->mail_content_type;
	}

	/**
	 * Changes the retrieve password e-mail subject
	 *
	 * Callback for "retrieve_pass_title" hook in Theme_My_Login::retrieve_password()
	 *
	 * @see Theme_My_Login::retrieve_password()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $title Default subject
	 * @param int $user_id User ID
	 * @return string New subject
	 */
	function retrieve_pass_title_filter( $title, $user_id ) {
		return empty( $this->options['retrieve_pass']['title'] ) ? $title : $this->replace_vars( $this->options['retrieve_pass']['title'], $user_id );
	}

	/**
	 * Changes the retrieve password e-mail message
	 *
	 * Callback for "retrieve_password_message" hook in Theme_My_Login::retrieve_password()
	 *
	 * @see Theme_My_Login::retrieve_password()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $message Default message
	 * @param string $key The user's reset key
	 * @param int $user_id User ID
	 * @return string New message
	 */
	function retrieve_pass_message_filter( $message, $key, $user_id ) {
		$user = get_userdata($user_id);
		$replacements = array(
			'%loginurl%' => site_url( 'wp-login.php', 'login' ),
			'%reseturl%' => site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' )
			);
		return empty( $this->options['retrieve_pass']['message'] ) ? $message : $this->replace_vars( $this->options['retrieve_pass']['message'], $user_id, $replacements );
	}

	/**
	 * Changes the password reset e-mail subject
	 *
	 * Callback for "password_reset_title" hook in Theme_My_Login::reset_password()
	 *
	 * @see Theme_My_Login::reset_password()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $title Default title
	 * @param int $user_id User ID
	 * @return string New title
	 */
	function reset_pass_title_filter( $title, $user_id ) {
		return empty( $this->options['reset_pass']['title'] ) ? $title : $this->replace_vars( $this->options['reset_pass']['title'], $user_id );
	}

	/**
	 * Changes the password reset e-mail message
	 *
	 * Callback for "password_reset_message" hook in Theme_My_Login::reset_password()
	 *
	 * @see Theme_My_Login::reset_password()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $message Default message
	 * @param string $new_pass The user's new password
	 * @param int $user_id User ID
	 * @return string New message
	 */
	function reset_pass_message_filter( $message, $new_pass, $user_id ) {
		$replacements = array(
			'%loginurl%' => site_url( 'wp-login.php', 'login' ),
			'%user_pass%' => $new_pass
			);	
		return empty( $this->options['reset_pass']['message'] ) ? $message : $this->replace_vars( $this->options['reset_pass']['message'], $user_id, $replacements );
	}

	/**
	 * Changes who the password change notification e-mail is sent to
	 *
	 * Callback for "password_change_notification_mail_to" hook in Theme_My_Login_Custom_Email::password_change_notification()
	 *
	 * @see Theme_My_Login_Custom_Email::password_change_notification()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $to Default admin e-mail address
	 * @return string New e-mail address(es)
	 */
	function  password_change_notification_mail_to_filter( $to ) {
		return empty( $this->options['reset_pass']['admin_mail_to'] ) ? $to : $this->options['reset_pass']['admin_mail_to'];
	}

	/**
	 * Changes the password change notification e-mail subject
	 *
	 * Callback for "password_change_notification_title" hook in Theme_My_Login_Custom_Email::password_change_notification()
	 *
	 * @see Theme_My_Login_Custom_Email::password_change_notification()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $title Default subject
	 * @param int $user_id User ID
	 * @return string New subject
	 */
	function password_change_notification_title_filter( $title, $user_id ) {
		return empty( $this->options['reset_pass']['admin_title'] ) ? $title : $this->replace_vars( $this->options['reset_pass']['admin_title'], $user_id );
	}

	/**
	 * Changes the password change notification e-mail message
	 *
	 * Callback for "password_change_notification_message" hook in Theme_My_Login_Custom_Email::password_change_notification()
	 *
	 * @see Theme_My_Login_Custom_Email::password_change_notification()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $title Default message
	 * @param int $user_id User ID
	 * @return string New message
	 */
	function password_change_notification_message_filter( $message, $user_id ) {	
		return empty( $this->options['reset_pass']['admin_message'] ) ? $message : $this->replace_vars( $this->options['reset_pass']['admin_message'], $user_id );
	}

	/**
	 * Determines whether or not to send the password change notification e-mail
	 *
	 * Callback for "send_password_change_notification" hook in Theme_My_Login_Custom_Email::password_change_notification()
	 *
	 * @see Theme_My_Login_Custom_Email::password_change_notification()
	 * @since 6.0
	 * @access public
	 *
	 * @param bool $enable Default setting
	 * @return bool New setting
	 */
	function send_password_change_notification_filter( $enable ) {
		$options =& $this->options['reset_pass'];
		$this->set_mail_headers( $options['admin_mail_from'], $options['admin_mail_from_name'], $options['admin_mail_content_type'] );
		if ( $this->options['reset_pass']['admin_disable'] )
			return false;
		return $enable;
	}

	/**
	 * Changes the new user e-mail subject
	 *
	 * Callback for "new_user_notification_title" hook in Theme_My_Login_Custom_Email::new_user_notification()
	 *
	 * @see Theme_My_Login_Custom_Email::new_user_notification()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $title Default title
	 * @param int $user_id User ID
	 * @return string New title
	 */
	function new_user_notification_title_filter( $title, $user_id ) {
		return empty( $this->options['new_user']['title'] ) ? $title : $this->replace_vars( $this->options['new_user']['title'], $user_id );
	}

	/**
	 * Changes the new user e-mail message
	 *
	 * Callback for "new_user_notification_message" hook in Theme_My_Login_Custom_Email::new_user_notification()
	 *
	 * @see Theme_My_Login_Custom_Email::new_user_notification()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $title Default message
	 * @param string $new_pass The user's password
	 * @param int $user_id User ID
	 * @return string New message
	 */
	function new_user_notification_message_filter( $message, $new_pass, $user_id ) {
		$replacements = array(
			'%loginurl%' => site_url( 'wp-login.php', 'login' ),
			'%user_pass%' => $new_pass
			);	
		return empty( $this->options['new_user']['message'] ) ? $message : $this->replace_vars( $this->options['new_user']['message'], $user_id, $replacements );
	}

	/**
	 * Determines whether or not to send the new user e-mail
	 *
	 * Callback for "send_new_user_notification" hook in Theme_My_Login_Custom_Email::new_user_notification()
	 *
	 * @see Theme_My_Login_Custom_Email::new_user_notification()
	 * @since 6.0
	 * @access public
	 *
	 * @param bool $enable Default setting
	 * @return bool New setting
	 */
	function send_new_user_notification_filter( $enable ) {
		$options =& $this->options['new_user'];
		$this->set_mail_headers( $options['mail_from'], $options['mail_from_name'], $options['mail_content_type'] );
		return $enable;
	}

	/**
	 * Changes who the new user admin notification e-mail is sent to
	 *
	 * Callback for "new_user_admin_notification_mail_to" hook in Theme_My_Login_Custom_Email::new_user_notification()
	 *
	 * @see Theme_My_Login_Custom_Email::new_user_notification()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $to Default admin e-mail address
	 * @return string New e-mail address(es)
	 */
	function new_user_admin_notification_mail_to_filter( $to ) {
		return empty( $this->options['new_user']['admin_mail_to'] ) ? $to : $this->options['new_user']['admin_mail_to'];
	}

	/**
	 * Changes the new user admin notification e-mail subject
	 *
	 * Callback for "new_user_admin_notification_title" hook in Theme_My_Login_Custom_Email::new_user_notification()
	 *
	 * @see Theme_My_Login_Custom_Email::new_user_notification()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $title Default subject
	 * @param int $user_id User ID
	 * @return string New subject
	 */
	function new_user_admin_notification_title_filter( $title, $user_id ) {
		return empty( $this->options['new_user']['admin_title'] ) ? $title : $this->replace_vars( $this->options['new_user']['admin_title'], $user_id );
	}

	/**
	 * Changes the new user admin notification e-mail message
	 *
	 * Callback for "new_user_admin_notification_message" hook in Theme_My_Login_Custom_Email::new_user_notification()
	 *
	 * @see Theme_My_Login_Custom_Email::new_user_notification()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $title Default message
	 * @param int $user_id User ID
	 * @return string New message
	 */
	function new_user_admin_notification_message_filter( $message, $user_id ) {
		return empty( $this->options['new_user']['admin_message'] ) ? $message : $this->replace_vars( $this->options['new_user']['admin_message'], $user_id );
	}

	/**
	 * Determines whether or not to send the new user admin notification e-mail
	 *
	 * Callback for "send_new_user_admin_notification" hook in Theme_My_Login_Custom_Email::new_user_notification()
	 *
	 * @see Theme_My_Login_Custom_Email::new_user_notification()
	 * @since 6.0
	 * @access public
	 *
	 * @param bool $enable Default setting
	 * @return bool New setting
	 */
	function send_new_user_admin_notification_filter( $enable ) {
		$options =& $this->options['new_user'];
		$this->set_mail_headers( $options['admin_mail_from'], $options['admin_mail_from_name'], $options['admin_mail_content_type'] );
		if ( $options['admin_disable'] )
			return false;
		return $enable;
	}

	/**
	 * Notify the blog admin of a new user
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param int $user_id User ID
	 * @param string $plaintext_pass Optional. The user's plaintext password
	 */
	function new_user_notification( $user_id, $plaintext_pass = '' ) {
		$user = new WP_User( $user_id );

		do_action( 'tml_new_user_notification', $user_id, $plaintext_pass );

		$user_login = stripslashes( $user->user_login );
		$user_email = stripslashes( $user->user_email );

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$blogname = $GLOBALS['current_site']->site_name;
		} else {
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		if ( apply_filters( 'send_new_user_admin_notification', true ) ) {
			$message  = sprintf( __( 'New user registration on your site %s:', $this->theme_my_login->textdomain ), $blogname ) . "\r\n\r\n";
			$message .= sprintf( __( 'Username: %s', $this->theme_my_login->textdomain ), $user_login ) . "\r\n\r\n";
			$message .= sprintf( __( 'E-mail: %s', $this->theme_my_login->textdomain ), $user_email ) . "\r\n";

			$title = sprintf( __( '[%s] New User Registration', $this->theme_my_login->textdomain ), $blogname );

			$title = apply_filters( 'new_user_admin_notification_title', $title, $user_id );
			$message = apply_filters( 'new_user_admin_notification_message', $message, $user_id );

			$to = apply_filters( 'new_user_admin_notification_mail_to', get_option( 'admin_email' ) );

			@wp_mail( $to, $title, $message );		
		}

		if ( empty( $plaintext_pass ) )
			return;

		if ( apply_filters( 'send_new_user_notification', true ) ) {
			$message  = sprintf( __( 'Username: %s', $this->theme_my_login->textdomain ), $user_login ) . "\r\n";
			$message .= sprintf( __( 'Password: %s', $this->theme_my_login->textdomain ), $plaintext_pass ) . "\r\n";
			$message .= wp_login_url() . "\r\n";

			$title = sprintf( __( '[%s] Your username and password', $this->theme_my_login->textdomain ), $blogname);

			$title = apply_filters( 'new_user_notification_title', $title, $user_id );
			$message = apply_filters( 'new_user_notification_message', $message, $plaintext_pass, $user_id );

			wp_mail( $user_email, $title, $message );
		}
	}

	/**
	 * Notify the blog admin of a user changing password
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param object $user User object
	 */
	function password_change_notification( &$user ) {
		$to = apply_filters( 'password_change_notification_mail_to', get_option( 'admin_email' ) );
		// send a copy of password change notification to the admin
		// but check to see if it's the admin whose password we're changing, and skip this
		if ( $user->user_email != $to && apply_filters( 'send_password_change_notification', true ) ) {
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				$blogname = $GLOBALS['current_site']->site_name;
			} else {
				// The blogname option is escaped with esc_html on the way into the database in sanitize_option
				// we want to reverse this for the plain text arena of emails.
				$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			}

			$title = sprintf( __( '[%s] Password Lost/Changed' ), $blogname );
			$message = sprintf( __( 'Password Lost and Changed for user: %s' ), $user->user_login ) . "\r\n";

			$title = apply_filters( 'password_change_notification_title', $title, $user->ID );
			$message = apply_filters( 'password_change_notification_message', $message, $user->ID );

			wp_mail( $to, $title, $message );
		}
	}

	/**
	 * Replaces certain user and blog variables in $input string
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $input The input string
	 * @param int $user_id User ID to replace user specific variables
	 * @param array $replacements Misc variables => values replacements
	 * @return string The $input string with variables replaced
	 */
	function replace_vars( $input, $user_id = '', $replacements = array() ) {
		// Get user data
		if ( $user_id )
			$user = get_userdata( $user_id );

		// Get all matches ($matches[0] will be '%value%'; $matches[1] will be 'value')
		preg_match_all( '/%([^%]*)%/', $input, $matches );

		// Allow %user_ip% variable
		$replacements['%user_ip%'] = $_SERVER['REMOTE_ADDR'];

		// Iterate through matches
		foreach ( $matches[0] as $key => $match ) {
			if ( !isset( $replacements[$match] ) ) {	
				if ( isset( $user ) && isset( $user->{$matches[1][$key]} ) ) // Replacement from WP_User object
					$replacements[$match] = ( '%user_pass%' == $match ) ? '' : $user->{$matches[1][$key]};
				else
					$replacements[$match] = get_bloginfo( $matches[1][$key] ); // Replacement from get_bloginfo()
			}
		}
		return str_replace( array_keys( $replacements ), array_values( $replacements ), $input );
	}

	/**
	 * Activates this module
	 *
	 * Callback for "tml_activate_custom-email/custom-email.php" hook in method Theme_My_Login_Admin::activate_module()
	 *
	 * @see Theme_My_Login_Admin::activate_module()
	 * @since 6.0
	 * @access public
	 *
	 * @param object $theme_my_login Reference to global $theme_my_login object
	 */
	function activate( &$theme_my_login ) {
		$options = $this->init_options();
		if ( !isset( $theme_my_login->options['email'] ) ) {
			$theme_my_login->options['email'] = $options['email'];
		} else {
			$theme_my_login->options['email'] = $theme_my_login->array_merge_recursive( $options['email'], $theme_my_login->options['email'] );
		}
	}

	/**
	 * Initializes options for this module
	 *
	 * Callback for "tml_init_options" hook in method Theme_My_Login_Base::init_options()
	 *
	 * @see Theme_My_Login_Base::init_options()
	 * @since 6.0
	 * @access public
	 *
	 * @param array $options Options passed in from filter
	 * @return array Original $options array with module options appended
	 */
	function init_options( $options = array() ) {
		// Make sure it's an array
		$options = (array) $options;
		// Assign our options
		$options['email'] = array(
			'new_user' => array(
				'mail_from' => '',
				'mail_from_name' => '',
				'mail_content_type' => '',
				'title' => '',
				'message' => '',
				'admin_mail_to' => '',
				'admin_mail_from' => '',
				'admin_mail_from_name' => '',
				'admin_mail_content_type' => '',
				'admin_title' => '',
				'admin_message' => '',
				'admin_disable' => 0
				),
			'retrieve_pass' => array(
				'mail_from' => '',
				'mail_from_name' => '',
				'mail_content_type' => '',
				'title' => '',
				'message' => ''
				),
			'reset_pass' => array(
				'mail_from' => '',
				'mail_from_name' => '',
				'mail_content_type' => '',
				'title' => '',
				'message' => '',
				'admin_mail_to' => '',
				'admin_mail_from' => '',
				'admin_mail_from_name' => '',
				'admin_mail_content_type' => '',
				'admin_title' => '',
				'admin_message' => '',
				'admin_disable' => 0
				)
			);
		return $options;
	}

	/**
	 * Loads the module
	 *
	 * @since 6.0
	 * @access public
	 */
	function load() {
		// Create a reference to custom e-mail options
		$this->options =& $this->theme_my_login->options['email'];
		// Activate
		add_action( 'tml_activate_custom-email/custom-email.php', array( &$this, 'activate' ) );
		// Initialize
		add_filter( 'tml_init_options', array( &$this, 'init_options' ) );
		// E-mail filters
		add_filter( 'wp_mail_from', array( &$this, 'mail_from_filter' ) );
		add_filter( 'wp_mail_from_name', array( &$this, 'mail_from_name_filter') );
		add_filter( 'wp_mail_content_type', array( &$this, 'mail_content_type_filter') );

		add_action( 'retrieve_password', array( &$this, 'apply_retrieve_pass_filters' ) );
		add_action( 'password_reset', array( &$this, 'apply_reset_pass_filters' ) );
		add_action( 'tml_new_user_notification', array( &$this, 'apply_new_user_filters' ) );

		remove_action( 'tml_new_user_registered', 'wp_new_user_notification', 10, 2 );
		add_action( 'tml_new_user_registered', array( &$this, 'new_user_notification' ), 10, 2 );

		remove_action( 'tml_user_password_changed', 'wp_password_change_notification' );
		add_action( 'tml_user_password_changed', array( &$this, 'password_change_notification' ) );
	}
}

/**
 * Holds the reference to Theme_My_Login_Custom_Email object
 * @global object $theme_my_login_custom_email
 * @since 6.0
 */
$theme_my_login_custom_email = new Theme_My_Login_Custom_Email();

if ( is_admin() )
	include_once( TML_ABSPATH . '/modules/custom-email/admin/custom-email-admin.php' );

endif; // Class exists

?>