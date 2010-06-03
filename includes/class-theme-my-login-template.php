<?php

if ( !class_exists( 'Theme_My_Login_Template' ) ) :
/*
 * Theme My Login template class
 *
 * This class contains properties and methods common to displaying output.
 *
 * @since 6.0
 */
class Theme_My_Login_Template {
	/**
	 * Holds instance specific template options
	 *
	 * @since 6.0
	 * @access public
	 * @var array
	 */
	var $options = array();

	/**
	 * Holds instance specific template errors
	 *
	 * @since 6.0
	 * @access public
	 * @var object
	 */
	var $errors;
	
	/**
	 * Displays output according to current action
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @return string HTML output
	 */
	function display() {
		$action = isset( $this->options['default_action'] ) ? $this->options['default_action'] : 'login';
		$instance = isset( $_REQUEST['instance'] ) ? $_REQUEST['instance'] : 'page';
		if ( $instance == $this->options['instance'] && isset( $_REQUEST['action'] ) )
			$action = $_REQUEST['action'];
			
		ob_start();
		echo $this->options['before_widget'];
		if ( $this->options['show_title'] )
			echo $this->options['before_title'] . $this->get_title( $action ) . $this->options['after_title'] . "\n";
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$user_role = $user->roles[0];
			echo '<div class="login" id="' . $this->options['instance'] . '">' . "\n";
			if ( $this->options['show_gravatar'] )
				$output .= '<div class="tml-user-avatar">' . get_avatar( $user->ID, $this->options['gravatar_size'] ) . '</div>' . "\n";
			$this->the_user_links();
			echo '</div>' . "\n";
		} else {
			if ( has_filter( 'login_form_' . $action ) ) {
				do_action_ref_array( 'login_form_' . $action, array( &$this ) );
			} else {
				switch ( $action ) {
					case 'lostpassword':
					case 'retrievepassword':
						$this->get_template( 'lostpassword-form.php' );
						break;
					case 'register':
						$this->get_template( 'register-form.php' );
						break;
					case 'login':
					default :
						$this->get_template( 'login-form.php' );
					break;
				}
			}
		}
		echo $this->options['after_widget'] . "\n";
		$output = ob_get_contents();
		ob_end_clean();
		return apply_filters( 'tml_display', $output, $this->options );
	}
	
	/**
	 * Returns action title
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $action The action to retrieve. Defaults to current action.
	 * @return string Title of $action
	 */
	function get_title( $action = '' ) {
		if ( empty( $action ) )
			$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$title = sprintf( __( 'Welcome, %s', 'theme-my-login' ), $user->display_name );
		} else {
			switch ( $action ) {
				case 'register':
					$title = __( 'Register', 'theme-my-login' );
					break;
				case 'lostpassword':
				case 'retrievepassword':
				case 'resetpass':
				case 'rp':
					$title = __( 'Lost Password', 'theme-my-login' );
					break;
				case 'login':
				default:
					$title = __( 'Log In', 'theme-my-login' );
			}
		}
		return apply_filters( 'tml_title', $title, $action );
	}

	/**
	 * Outputs action title
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $action The action to retieve. Defaults to current action.
	 */
	function the_title( $action = '' ) {
		echo $this->get_title( $action );
	}

	/**
	 * {@internal Missing short description}
	 *
	 * @since 6.0
	 * @access public
	 */
	function get_errors() {
		global $error;
		
		$wp_error =& $this->errors;
		
		if ( empty( $wp_error ) )
			$wp_error = new WP_Error();

		// Incase a plugin uses $error rather than the $errors object
		if ( !empty( $error ) ) {
			$wp_error->add('error', $error);
			unset($error);
		}

		$output = '';
		if ( $this->options['is_active'] ) {
			if ( $wp_error->get_error_code() ) {
				$errors = '';
				$messages = '';
				foreach ( $wp_error->get_error_codes() as $code ) {
					$severity = $wp_error->get_error_data( $code );
					foreach ( $wp_error->get_error_messages( $code ) as $error ) {
						if ( 'message' == $severity )
							$messages .= '    ' . $error . "<br />\n";
						else
							$errors .= '    ' . $error . "<br />\n";
					}
				}
				if ( !empty( $errors ) )
					$output .= '<p class="error">' . apply_filters( 'login_errors', $errors ) . "</p>\n";
				if ( !empty( $messages ) )
					$output .= '<p class="message">' . apply_filters( 'login_messages', $messages ) . "</p>\n";
			}
		}
		return $output;
	}
	
	/**
	 * {@internal Missing short description}
	 *
	 * @since 6.0
	 * @access public
	 */
	function the_errors() {
		echo $this->get_errors();
	}
	
	/**
	 * Returns the action links
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param array $args Optionally specify which actions to include/exclude. By default, all are included.
	 */
	function get_action_links( $args = '' ) {
		$args = wp_parse_args( $args, array( 'login' => true, 'register' => true, 'lostpassword' => true ) );
		$action_links = array();
		if ( $args['login'] && $this->options['show_log_link'] )
			$action_links[] = array( 'title' => $this->get_title( 'login' ), 'url' => $this->get_action_url( 'login' ) );
		if ( $args['register'] && $this->options['show_reg_link'] && get_option( 'users_can_register' ) )
			$action_links[] = array( 'title' => $this->get_title( 'register' ), 'url' => $this->get_action_url( 'register' ) );
		if ( $args['lostpassword'] && $this->options['show_pass_link'] )
			$action_links[] = array( 'title' => $this->get_title( 'lostpassword' ), 'url' => $this->get_action_url( 'lostpassword' ) );
		return apply_filters( 'tml_action_links', $action_links );
	}
	
	/**
	 * Outputs the action links
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param array $args Optionally specify which actions to include/exclude. By default, all are included.
	 */
	function the_action_links( $args = '' ) {
		if ( $action_links = $this->get_action_links( $args ) ) {
			echo '<ul class="tml-action-links">' . "\n";
			foreach ( (array) $action_links as $link ) {
				echo '<li><a href="' . esc_url( $link['url'] ) . '">' . esc_html( $link['title'] ) . '</a></li>' . "\n";
			}
			echo '</ul>' . "\n";
		}
	}
	
	/**
	 * Returns logged-in user links
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @return array Logged-in user links
	 */
	function get_user_links() {
		$user_links = array(
			array( 'title' => __( 'Dashboard', 'theme-my-login' ), 'url' => admin_url() ),
			array( 'title' => __( 'Profile', 'theme-my-login' ), 'url' => admin_url( 'profile.php' ) ),
			array( 'title' => __( 'Log out', 'theme-my-login' ), 'url' => wp_logout_url() )
			);
		return apply_filters( 'tml_user_links', $user_links );
	}
	
	/**
	 * Outputs logged-in user links
	 *
	 * @since 6.0
	 * @access public
	 */
	function the_user_links() {
		if ( $user_links = $this->get_user_links() ) {
			echo '<ul class="tml-user-links">';
			foreach ( (array) $user_links as $link ) {
				echo '<li><a href="' . esc_url( $link['url'] ) . '">' . esc_html( $link['title'] ) . '</a></li>' . "\n";
			}
			echo '</ul>';
		}
	}
	
	/**
	 * Returns requested action message
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $action Action to retrieve
	 * @return string The requested action message
	 */
	function get_action_message( $action = '' ) {
		if ( empty( $action ) || ( !in_array( $action, array( 'login', 'register', 'lostpassword' ) ) && !has_filter( $action . '_message' ) ) )
			return;
		if ( 'register' == $action )
			$message = __( 'A password will be e-mailed to you.', 'theme-my-login' );
		elseif ( 'lostpassword' == $action )
			$message = __( 'Please enter your username or e-mail address. You will receive a new password via e-mail.', 'theme-my-login' );
		else
			$message = '';
		return apply_filters( $action . '_message', $message );
	}
	
	/**
	 * Outputs requested action message
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $action Action to retrieve
	 * @param string $before_message Text/HTML to add before the message
	 * @param string $after_message Text/HTML to add after the message
	 */
	function the_action_message( $action = 'login', $before_message = '<p class="message">', $after_message = '</p>' ) {
		if ( $message = $this->get_action_message( $action ) )
			echo $before_message . $message . $after_message;
	}
	
	/**
	 * Locates specified template
	 *
	 * You can specify a hierarchy by using an array in order of hierarchy. For instance,
	 * specifying array( 'login.php', 'login-form.php' ) would first return 'login.php'
	 * if it is found, or 'login-form.php' if the previous wasn't found or false if none are found.
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $template_name The template(s) to locate
	 * @param bool $include If true, the template will be included if found
	 * @return string|bool Template path if found, false if not
	 */
	function get_template( $template_name, $include = true ) {
		// Create reference so we can use $template->{*} within the templates, instead of $this->{*}
		$template =& $this;

		if ( !$template_path = locate_template( array( $template_name ) ) )
			$template_path = TML_DIR . '/templates/' . $template_name;
		if ( file_exists( $template_path ) && is_readable( $template_path ) ) {
			if ( $include )
				include( $template_path );
		} else {
			$template_path = false;
		}
		return apply_filters( 'tml_template', $template_path );
	}
	
	/**
	 * Returns requested action URL
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $action Action to retrieve
	 * @param int|string $instance Optionally add an instance to the URL
	 * @return string The requested action URL
	 */
	function get_action_url( $action = 'login', $instance = '' ) {
		if ( empty( $instance ) )
			$instance = $this->options['instance'];
			
		if ( isset( $this->options[$action . '_widget'] ) && !$this->options[$action . '_widget'] )
			$url = site_url( 'wp-login.php?action=' . $action, 'login' );
		else
			$url = Theme_My_Login::get_current_url( array( 'action' => $action, 'instance' => $instance ), false );
			
		return apply_filters( 'tml_action_url', $url );
	}
	
	/**
	 * Outputs requested action URL
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $action Action to retrieve
	 * @param int|string $instance Optionally add an instance to the URL
	 */
	function the_action_url( $action = 'login', $instance = '' ) {
		echo esc_url( $this->get_action_url( $action, $instance ) );
	}
	
	/**
	 * Outputs redirect URL
	 *
	 * @since 6.0
	 * @access public
	 */
	function the_redirect_url() {
		echo esc_url( $this->options['redirect_to'] );
	}
	
	/**
	 * Returns current template instance ID
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @return int|string Current template instance ID
	 */
	function get_current_instance() {
		if ( isset( $this->options['instance'] ) )
			return $this->options['instance'];
	}
	
	/**
	 * Outputs current template instance ID
	 *
	 * @since 6.0
	 * @access public
	 */
	function the_current_instance() {
		echo esc_attr( $this->get_current_instance() );
	}
	
	/**
	 * Returns requested $value
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $value The value to retrieve
	 * @return string|bool The value if it exists, false if not
	 */
	function get_posted_value( $value ) {
		if ( $this->options['is_active'] && isset( $_REQUEST[$value] ) )
			return stripslashes( $_REQUEST[$value] );
		return false;
	}
	
	/**
	 * Outputs requested value
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $value The value to retrieve
	 */
	function the_posted_value( $value ) {
		echo esc_attr( $this->get_posted_value( $value ) );
	}
	
	/**
	 * Merges default template options with instance template options
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param array $options Instance options
	 */
	function load_options( $options = '' ) {
	
		$options = (array) $options;
		
		$defaults = array(
			'instance' => 0,
			'is_active' => 0,
			'default_action' => 'login',
			'show_title' => 1,
			'show_log_link' => 1,
			'show_reg_link' => 1,
			'show_pass_link' => 1,
			'register_widget' => 0,
			'lost_pass_widget' => 0,
			'logged_in_widget' => 1,
			'show_gravatar' => 1,
			'gravatar_size' => 50,
			'before_widget' => '<li>',
			'after_widget' => '</li>',
			'before_title' => '<h2>',
			'after_title' => '</h2>',
			'redirect_to' => ''
		);
		$this->options = array_merge( $defaults, $options );
	}
	
	/**
	 * PHP4 style constructor
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param array $options Instance options
	 * @param WP_Error Instance errrors
	 */
	function Theme_My_Login_Template( $options = '', $errors = '' ) {
		$this->__construct( $options, $errors );
	}
	
	/**
	 * PHP5 style constructor
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param array $options Instance options
	 * @param WP_Error Instance errrors
	 */
	function __construct( $options = '', $errors = '' ) {
		$this->load_options( $options );
		if ( $errors )
			$this->errors = $errors;
	}
}
endif;