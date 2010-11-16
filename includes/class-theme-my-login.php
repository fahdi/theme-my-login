<?php
/**
 * Holds the Theme My Login class
 *
 * @package Theme My Login
 */

if ( !class_exists( 'Theme_My_Login' ) ) :
/*
 * Theme My Login class
 *
 * This class contains properties and methods common to the front-end.
 *
 * @since 6.0
 */
class Theme_My_Login {
	/**
	 * Holds plugin textdomain
	 *
	 * @since 6.0
	 * @access public
	 * @var string
	 */
	var $textdomain = 'theme-my-login';

	/**
	 * Holds TML options key
	 *
	 * @since 6.0
	 * @access public
	 * @var string
	 */
	var $options_key = 'theme_my_login';

	/**
	 * Holds TML options
	 *
	 * @since 6.0
	 * @access public
	 * @var array
	 */
	var $options = array();

	/**
	 * Hold WP_Error object
	 *
	 * @since 6.0
	 * @access public
	 * @var object
	 */
	var $errors;

	/**
	 * Total instances of TML
	 *
	 * @since 6.0
	 * @access public
	 * @var int
	 */
	var $count = 0;

	/**
	 * Current instance being requested via HTTP GET or POST
	 *
	 * @since 6.0
	 * @access public
	 * @var int
	 */
	var $request_instance;

	/**
	 * Current action being requested via HTTP GET or POST
	 *
	 * @since 6.0
	 * @access public
	 * @var string
	 */
	var $request_action;

	/**
	 * PHP4 style constructor
	 *
	 * @since 6.0
	 * @access public
	 */
	function Theme_My_Login() {
		$this->__construct();
	}

	/**
	 * PHP5 constructor
	 *
	 * @since 6.0
	 * @access public
	 */
	function __construct() {
		$this->request_action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';
		$this->request_instance = isset( $_REQUEST['instance'] ) ? $_REQUEST['instance'] : '';

		$this->load_options();

		// Re-load options after modules loaded so that modules can hook into "tml_init_options"
		//add_action( 'tml_modules_loaded', array( &$this, 'load_options' ), 1 );

		add_action( 'parse_request', array( &$this, 'the_request' ) );

		add_action( 'init', array( &$this, 'init' ) );

		add_action( 'wp_print_footer_scripts', array( &$this, 'print_footer_scripts' ) );

		add_action( 'wp_head', array( &$this, 'login_head' ) );

		add_filter( 'the_title', array( &$this, 'the_title' ), 10, 2 );
		add_filter( 'single_post_title', array( &$this, 'single_post_title' ) );
		add_filter( 'wp_setup_nav_menu_item', array( &$this, 'wp_setup_nav_menu_item' ) );

		add_filter( 'site_url', array( &$this, 'site_url' ), 10, 3 );

		add_filter( 'wp_list_pages_excludes', array( &$this, 'wp_list_pages_excludes' ) );
		add_filter( 'wp_list_pages', array( &$this, 'wp_list_pages' ) );

		add_action( 'wp_authenticate', array( &$this, 'wp_authenticate' ) );

		add_action( 'tml_new_user_registered', 'wp_new_user_notification', 10, 2 );
		add_action( 'tml_user_password_changed', 'wp_password_change_notification' );

		add_shortcode( 'theme-my-login', array( &$this, 'shortcode' ) );
	}

	function init() {
		load_plugin_textdomain( $this->textdomain, '', TML_DIRNAME . '/language' );

		$this->errors = new WP_Error();

		if ( $this->get_option( 'enable_css' ) )
			wp_enqueue_style( 'theme-my-login', $this->get_stylesheet(), false, $this->get_option( 'version' ) );
	}

	/**
	 * Determine if specified page is the logn page
	 *
	 * since 6.0
	 * @access public
	 *
	 * @param int $page_id Optional. The page ID (Defaults to current page)
	 */
	function is_login_page( $page_id = '' ) {
		if ( empty( $page_id ) ) {
			global $wp_query;
			if ( $wp_query->is_page )
				$page_id = $wp_query->get_queried_object_id();
		}

		$is_login_page = ( $page_id == $this->get_option( 'page_id' ) );

		return apply_filters( 'tml_is_login_page', $is_login_page );
	}

	/**
	 * Proccesses the request
	 *
	 * Callback for "parse_request" hook in WP::parse_request()
	 *
	 * @see WP::parse_request()
	 * @since 6.0
	 * @access public
	 */
	function the_request() {
		global $action;

		$errors =& $this->errors;
		$action =& $this->request_action;
		$instance =& $this->request_instance;

		if ( is_admin() )
			return;

		do_action_ref_array( 'tml_request', array( &$this ) );

		// Set a cookie now to see if they are supported by the browser.
		setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN );
		if ( SITECOOKIEPATH != COOKIEPATH )
			setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN );

		// allow plugins to override the default actions, and to add extra actions if they want
		do_action( 'login_form_' . $action );

		if ( has_action( 'tml_request_' . $action ) ) {
			do_action_ref_array( 'tml_request_' . $action, array( &$this ) );
		} else {
			$http_post = ( 'POST' == $_SERVER['REQUEST_METHOD'] );
			switch ( $action ) {
				case 'logout' :
					check_admin_referer( 'log-out' );

					$user = wp_get_current_user();

					$redirect_to = apply_filters( 'logout_redirect', site_url( 'wp-login.php?loggedout=true' ), isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user );

					wp_logout();

					wp_safe_redirect( $redirect_to );
					exit();
					break;
				case 'lostpassword' :
				case 'retrievepassword' :
					if ( $http_post ) {
						$errors = $this->retrieve_password();
						if ( !is_wp_error( $errors ) ) {
							$redirect_to = !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : $this->get_current_url( 'checkemail=confirm' );
							if ( !empty( $instance ) )
								$redirect_to = add_query_arg( 'instance', $instance, $redirect_to );
							wp_safe_redirect( $redirect_to );
							exit();
						}
					}

					if ( isset( $_REQUEST['error'] ) && 'invalidkey' == $_REQUEST['error'] )
						$errors->add( 'invalidkey', __( 'Sorry, that key does not appear to be valid.', $this->textdomain ) );
					break;
				case 'resetpass' :
				case 'rp' :
					$errors = $this->reset_password( $_GET['key'], $_GET['login'] );

					if ( !is_wp_error( $errors ) ) {
						$redirect_to = apply_filters( 'resetpass_redirect', $this->get_current_url( 'checkemail=newpass' ) );
						if ( !empty( $instance ) )
							$redirect_to = add_query_arg( 'instance', $instance, $redirect_to );
						wp_safe_redirect( $redirect_to );
						exit();
					}

					$redirect_to = $this->get_current_url( 'action=lostpassword&error=invalidkey' );
					if ( !empty( $instance ) )
						$redirect_to = add_query_arg( 'instance', $instance, $redirect_to );
					wp_redirect( $redirect_to );
					exit();
					break;
				case 'register' :
					if ( function_exists( 'is_multisite' ) && is_multisite() ) {
						// Multisite uses wp-signup.php
						wp_redirect( apply_filters( 'wp_signup_location', get_bloginfo('wpurl') . '/wp-signup.php' ) );
						exit;
					}

					if ( !get_option( 'users_can_register' ) ) {
						wp_redirect( $this->get_current_url( 'registration=disabled' ) );
						exit();
					}

					$user_login = '';
					$user_email = '';
					if ( $http_post ) {
						require_once( ABSPATH . WPINC . '/registration.php' );

						$user_login = $_POST['user_login'];
						$user_email = $_POST['user_email'];

						$errors = $this->register_new_user( $user_login, $user_email );
						if ( !is_wp_error( $errors ) ) {
							$redirect_to = !empty( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : $this->get_current_url( 'checkemail=registered' );
							if ( !empty( $instance ) )
								$redirect_to = add_query_arg( 'instance', $instance, $redirect_to );
							$redirect_to = apply_filters( 'register_redirect', $redirect_to );
							wp_redirect( $redirect_to );
							exit();
						}
					}
					break;
				case 'login' :
				default:
					$secure_cookie = '';
					$interim_login = isset( $_REQUEST['interim-login'] );

					// If the user wants ssl but the session is not ssl, force a secure cookie.
					if ( !empty( $_POST['log'] ) && !force_ssl_admin() ) {
						$user_name = sanitize_user( $_POST['log'] );
						if ( $user = get_userdatabylogin( $user_name ) ) {
							if ( get_user_option( 'use_ssl', $user->ID ) ) {
								$secure_cookie = true;
								force_ssl_admin( true );
							}
						}
					}

					if ( isset( $_REQUEST['redirect_to'] ) && !empty( $_REQUEST['redirect_to'] ) ) {
						$redirect_to = $_REQUEST['redirect_to'];
						// Redirect to https if user wants ssl
						if ( $secure_cookie && false !== strpos( $redirect_to, 'wp-admin' ) )
							$redirect_to = preg_replace( '|^http://|', 'https://', $redirect_to );
					} else {
						$redirect_to = admin_url();
					}

					$reauth = empty( $_REQUEST['reauth'] ) ? false : true;

					// If the user was redirected to a secure login form from a non-secure admin page, and secure login is required but secure admin is not, then don't use a secure
					// cookie and redirect back to the referring non-secure admin page.  This allows logins to always be POSTed over SSL while allowing the user to choose visiting
					// the admin via http or https.
					if ( !$secure_cookie && is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos( $redirect_to, 'https' ) ) && ( 0 === strpos( $redirect_to, 'http' ) ) )
						$secure_cookie = false;

					if ( $http_post ) {
						$user = wp_signon( '', $secure_cookie );

						$redirect_to = apply_filters( 'login_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user );

						if ( $http_post && !is_wp_error( $user ) && !$reauth ) {
							// If the user can't edit posts, send them to their profile.
							if ( !$user->has_cap( 'edit_posts' ) && ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ) )
								$redirect_to = admin_url( 'profile.php' );
							wp_safe_redirect( $redirect_to );
							exit();
						}

						$errors = $user;
					}

					$this->redirect_to = $redirect_to;

					// Clear errors if loggedout is set.
					if ( !empty( $_GET['loggedout'] ) || $reauth )
						$errors = new WP_Error();

					// If cookies are disabled we can't log in even with a valid user+pass
					if ( isset( $_POST['testcookie'] ) && empty( $_COOKIE[TEST_COOKIE] ) )
						$errors->add( 'test_cookie', __( '<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href="http://www.google.com/cookies.html">enable cookies</a> to use WordPress.', $this->textdomain ) );

					// Some parts of this script use the main login form to display a message
					if		( isset( $_GET['loggedout'] ) && TRUE == $_GET['loggedout'] )
						$errors->add( 'loggedout', __( 'You are now logged out.', $this->textdomain ), 'message' );
					elseif	( isset( $_GET['registration'] ) && 'disabled' == $_GET['registration'] )
						$errors->add( 'registerdisabled', __( 'User registration is currently not allowed.', $this->textdomain ) );
					elseif	( isset( $_GET['checkemail'] ) && 'confirm' == $_GET['checkemail'] )
						$errors->add( 'confirm', __( 'Check your e-mail for the confirmation link.', $this->textdomain ), 'message' );
					elseif	( isset( $_GET['checkemail'] ) && 'newpass' == $_GET['checkemail'] )
						$errors->add( 'newpass', __( 'Check your e-mail for your new password.', $this->textdomain ), 'message' );
					elseif	( isset( $_GET['checkemail'] ) && 'registered' == $_GET['checkemail'] )
						$errors->add( 'registered', __( 'Registration complete. Please check your e-mail.', $this->textdomain ), 'message' );
					elseif	( $interim_login )
						$errors->add( 'expired', __( 'Your session has expired. Please log-in again.', $this->textdomain ), 'message' );

					// Clear any stale cookies.
					if ( $reauth )
						wp_clear_auth_cookie();
					break;
			} // end switch
		} // endif has_filter()
	}

	/**
	 * Returns link for login page
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $query Optional. Query arguments to add to link
	 * @param bool $remove_filter True to remove "page_link" filter
	 * @return string Login page link with optional $query arguments appended
	 */
	function get_login_page_link( $query = '' ) {
		$link = get_page_link( $this->get_option( 'page_id' ) );
		if ( !empty( $query ) ) {
			$q = wp_parse_args( $query );
			$link = add_query_arg( $q, $link );
		}
		return apply_filters( 'tml_page_link', $link, $query );
	}

	/**
	 * Changes the_title() to reflect the current action
	 *
	 * Callback for "the_title" hook in the_title()
	 *
	 * @see the_title()
	 * @since 6.0
	 * @acess public
	 *
	 * @param string $title The current post title
	 * @param int $post_id The current post ID
	 * @return string The modified post title
	 */
	function the_title( $title, $post_id = '' ) {
		if ( is_admin() && !defined( 'IS_PROFILE_PAGE' ) )
			return $title;

		// No post ID until WP 3.0!
		if ( empty( $post_id ) ) {
			global $wpdb;
			$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s", $title ) );
		}

		if ( $this->get_option( 'page_id' ) == $post_id ) {
			if ( !in_the_loop() ) {
				$title = is_user_logged_in() ? __( 'Log Out', $this->textdomain ) : __( 'Log In', $this->textdomain );
			} else {
				$action = empty( $this->request_instance ) ? $this->request_action : 'login';
				$title = Theme_My_Login_Template::get_title( $action );
			}
		}
		return $title;
	}

	/**
	 * Changes single_post_title() to reflect the current action
	 *
	 * Callback for "single_post_title" hook in single_post_title()
	 *
	 * @see single_post_title()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $title The current post title
	 * @return string The modified post title
	 */
	function single_post_title( $title ) {
		if ( $this->is_login_page() ) {
			$action = empty( $this->request_instance ) ? $this->request_action : 'login';
			$title = Theme_My_Login_Template::get_title( $action );
		}
		return $title;
	}

	/**
	 * Excludes TML page if set in the admin
	 *
	 * Callback for "wp_list_pages_excludes" hook in wp_list_pages()
	 *
	 * @see wp_list_pages()
	 * @since 6.0
	 * @access public
	 *
	 * @param array $exclude_array Array of excluded pages
	 * @return array Modified array of excluded pages
	 */
	function wp_list_pages_excludes( $exclude_array ) {
		$exclude_array = (array) $exclude_array;
		if ( !$this->get_option( 'show_page' ) )
			$exclude_array[] = $this->get_option( 'page_id' );
		return $exclude_array;
	}

	/**
	 * Changes login link to logout if user is logged in
	 *
	 * Callback for "wp_list_pages" hook in wp_list_pages()
	 *
	 * @see wp_list_pages()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $output The output
	 * @return string The filtered output
	 */
	function wp_list_pages( $output ) {
		if ( is_user_logged_in() )
			$output = str_replace( '"' . $this->get_login_page_link() . '"', '"' . wp_logout_url() . '"', $output );
		return $output;
	}

	/**
	 * Alters menu item title & link according to whether user is logged in or not
	 *
	 * Callback for "wp_setup_nav_menu_item" hook in wp_setup_nav_menu_item()
	 *
	 * @see wp_setup_nav_menu_item()
	 * @since 6.0
	 * @access public
	 *
	 * @param object $menu_item The menu item
	 * @return object The (possibly) modified menu item
	 */
	function wp_setup_nav_menu_item( $menu_item ) {
		if ( 'page' == $menu_item->object && $this->is_login_page( $menu_item->object_id ) ) {
			$menu_item->title = $this->the_title( $menu_item->title, $menu_item->object_id );
			$menu_item->url = is_user_logged_in() ? wp_logout_url() : $this->get_login_page_link();
		}
		return $menu_item;
	}

	/**
	 * Handler for "theme-my-login" shortcode
	 *
	 * Optional $atts contents:
	 *
	 * - instance - A unqiue instance ID for this instance.
	 * - default_action - The action to display. Defaults to "login".
	 * - login_template - The template used for the login form. Defaults to "login-form.php".
	 * - register_template - The template used for the register form. Defaults to "register-form.php".
	 * - lostpassword_template - The template used for the lost password form. Defaults to "lostpassword-form.php".
	 * - user_template - The templated used for when a user is logged in. Defalts to "user-panel.php".
	 * - show_title - True to display the current title, false to hide. Defaults to true.
	 * - show_log_link - True to display the login link, false to hide. Defaults to true.
	 * - show_reg_link - True to display the register link, false to hide. Defaults to true.
	 * - show_pass_link - True to display the lost password link, false to hide. Defaults to true.
	 * - register_widget - True to allow registration in widget, false to send to register page. Defaults to false.
	 * - lostpassword_widget - True to allow password recovery in widget, false to send to lost password page. Defaults to false.
	 * - logged_in_widget - True to display the widget when logged in, false to hide. Defaults to true.
	 * - show_gravatar - True to display the user's gravatar, false to hide. Defaults to true.
	 * - gravatar_size - The size of the user's gravatar. Defaults to "50".
	 * - before_widget - Content to display before widget. Defaults to "<li>".
	 * - after_widget - Content to display after widget. Defaults to "</li>".
	 * - before_title - Content to display before the title (if displayed). Defaults to "<h2>".
	 * - after_title - Content to display after the title (if displayed). Defaults to "</h2>".
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $atts Attributes passed from the shortcode
	 * @return string HTML output from Theme_My_Login_Template->display()
	 */
	function shortcode( $atts = '' ) {

		if ( $this->is_login_page() && in_the_loop() ) {
			$atts['instance'] = '';
			$atts['show_title'] = false;
			$atts['before_widget'] = '';
			$atts['after_widget'] = '';
		} else {
			$atts['instance'] = $this->get_new_instance();
		}

		$template =& new Theme_My_Login_Template( $atts );

		return $template->display();
	}

	/**
	 * Incremenets $this->count and returns it
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @return int New value of $this->count
	 */
	function get_new_instance() {
		$this->count++;
		return $this->count;
	}

	/**
	 * Returns current URL
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $query Optionally append query to the current URL
	 * @return string URL with optional path appended
	 */
	function get_current_url( $query = '' ) {
		$url = remove_query_arg( array( 'instance', 'action', 'checkemail', 'error', 'loggedout', 'registered', 'redirect_to', 'updated', 'key', '_wpnonce', 'reauth', 'login' ) );
		if ( !empty( $query ) ) {
			$r = wp_parse_args( $query );
			foreach ( $r as $k => $v ) {
				if ( strpos( $v, ' ' ) !== false )
					$r[$k] = rawurlencode( $v );
			}
			$url = add_query_arg( $r, $url );
		}
		return $url;
	}

	/**
	 * Rewrites URL's containing wp-login.php created by site_url()
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $url The URL
	 * @param string $path The path specified
	 * @param string $orig_scheme The current connection scheme (HTTP/HTTPS)
	 * @return string The modified URL
	 */
	function site_url( $url, $path, $orig_scheme ) {
		global $pagenow;
		if ( 'wp-login.php' != $pagenow && strpos( $url, 'wp-login.php' ) !== false && !isset( $_REQUEST['interim-login'] ) ) {
			$parsed_url = parse_url( $url );
			$url = $this->get_login_page_link();
			if ( 'https' == strtolower( $orig_scheme ) )
				$url = preg_replace( '|^http://|', 'https://', $url );
			if ( isset( $parsed_url['query'] ) ) {
				wp_parse_str( $parsed_url['query'], $r );
				foreach ( $r as $k => $v ) {
					if ( strpos($v, ' ') !== false )
						$r[$k] = rawurlencode( $v );
				}
				$url = add_query_arg( $r, $url );
			}
		}
		return $url;
	}

	/**
	 * Enqueues the specified sylesheet
	 *
	 * First looks in theme/template directories for the stylesheet, falling back to plugin directory
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $file Filename of stylesheet to load
	 * @return string Path to stylesheet
	 */
	function get_stylesheet( $file = 'theme-my-login.css' ) {
		if ( file_exists( get_stylesheet_directory() . '/' . $file ) )
			$stylesheet = get_stylesheet_directory_uri() . '/' . $file;
		elseif ( file_exists( get_template_directory() . '/' . $file ) )
			$stylesheet = get_template_directory_uri() . '/' . $file;
		else
			$stylesheet = plugins_url( '/theme-my-login/' . $file );
		return $stylesheet;
	}

	/**
	 * Prints javascript in the footer
	 *
	 * @since 6.0
	 * @access public
	 */
	function print_footer_scripts() {
		if ( is_admin() )
			return;

		$action = empty( $this->request_action ) ? 'login' : $this->request_action;
		switch ( $action ) {
			case 'lostpassword' :
			case 'retrievepassword' :
			case 'register' :
			?>
<script type="text/javascript">
try{document.getElementById('user_login<?php echo $this->request_instance; ?>').focus();}catch(e){}
if(typeof wpOnload=='function')wpOnload()
</script>
<?php
				break;
			case 'login' :
				$user_login = '';
				if ( isset($_POST['log']) )
					$user_login = ( 'incorrect_password' == $this->errors->get_error_code() || 'empty_password' == $this->errors->get_error_code() ) ? esc_attr( stripslashes( $_POST['log'] ) ) : '';
			?>
<script type="text/javascript">
function wp_attempt_focus() {
setTimeout( function() {
try {
<?php if ( $user_login ) { ?>
d = document.getElementById('user_pass<?php echo $this->request_instance; ?>');
<?php } else { ?>
d = document.getElementById('user_login<?php echo $this->request_instance; ?>');
<?php } ?>
d.value = '';
d.focus();
} catch(e){}
}, 200 );
}
wp_attempt_focus();
if(typeof wpOnload=='function')wpOnload()
</script>
<?php
				break;
		}
	}

	/**
	 * Calls "login_head" hook on login page
	 *
	 * Callback for "wp_head" hook
	 *
	 * @since 6.0
	 * @access public
	 */
	function login_head() {
		if ( $this->is_login_page() )
			do_action( 'login_head' );
	}

	/**
	 * Initializes TML options
	 *
	 * @since 6.0
	 * @access public
	 */
	function init_options() {
		$this->options = apply_filters( 'tml_init_options', array(
			'page_id' => 0,
			'show_page' => 1,
			'enable_css' => 1,
			'active_modules' => array(),
			'initial_nag' => 1
		) );
	}

	/**
	 * Loads TML options
	 *
	 * @since 6.0
	 * @access public
	 */
	function load_options( $return = false ) {

		$this->init_options();

		$options = get_option( $this->options_key );

		if ( is_array( $options ) ) {
			$this->options = array_merge( $this->options, $options );
		} else {
			update_option( $this->options_key, $this->options );
		}

		if ( $return )
			return $this->options;
	}

	/**
	 * Saves TML options
	 *
	 * @since 6.0
	 * @access public
	 */
	function save_options() {
		$options = get_option( $this->options_key );
		if ( $options != $this->options )
			update_option( $this->options_key, $this->options );
	}

	/**
	 * Retrieve a TML option
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $option Name of option to retrieve or an array of hierarchy for multidimensional options
	 * @param mixed $default optional. Default value to return if $option is not set
	 * @return mixed Value of requested option or $default if option is not set
	 */
	function get_option( $option, $default = false ) {
		$options = $this->options;
		$value = false;
		if ( is_array( $option ) ) {
			foreach ( $option as $key ) {
				if ( !isset( $options[$key] ) ) {
					$value = $default;
					break;
				}
				$options = $value = $options[$key];
			}
		} else {
			$value = isset( $options[$option] ) ? $options[$option] : $default;
		}
		return apply_filters( 'tml_get_option', $value, $option, $default );
	}

	/**
	 * Set a TML option
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $option Name of option to set
	 * @param mixed $value Value of new option
	 * @param bool $save True will save to DB
	 */
	function set_option( $option, $value = '', $save = false ) {
		$this->options[$option] = apply_filters( 'tml_set_option', $value, $option );
		if ( $save )
			$this->save_options();
	}

	/**
	 * Deletes (unsets) a TML option
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $option Name of option to delete
	 */
	function delete_option( $option ) {
		if ( isset( $this->options[$option] ) )
			unset( $this->options[$option] );
	}

	/**
	 * Merges arrays recursively, replacing duplicate string keys
	 *
	 * @since 6.0
	 * @access public
	 */
	function array_merge_recursive() {
		$args = func_get_args();

		$result = array_shift( $args );

		foreach ( $args as $arg ) {
			foreach ( $arg as $key => $value ) {
				// Renumber numeric keys as array_merge() does.
				if ( is_numeric( $key ) ) {
					if ( !in_array( $value, $result ) )
						$result[] = $value;
				}
				// Recurse only when both values are arrays.
				elseif ( array_key_exists( $key, $result ) && is_array( $result[$key] ) && is_array( $value ) ) {
					$result[$key] = $this->array_merge_recursive( $result[$key], $value );
				}
				// Otherwise, use the latter value.
				else {
					$result[$key] = $value;
				}
			}
		}
		return $result;
	}

	/**
	 * Returns active and valid TML modules
	 *
	 * Returns all valid modules specified via $this->options['active_modules']
	 *
	 * @since 6.0
	 * @access public
	 */
	function get_active_and_valid_modules() {
		$modules = array();
		$active_modules = apply_filters( 'tml_active_modules', $this->get_option( 'active_modules' ) );
		foreach ( (array) $active_modules as $module ) {
			// check the $plugin filename
			// Validate plugin filename	
			if ( !validate_file( $module ) // $module must validate as file
				|| '.php' == substr( $module, -4 ) // $module must end with '.php'
				|| file_exists( TML_ABSPATH . '/modules/' . $module )	// $module must exist
				)
			$modules[] = TML_ABSPATH . '/modules/' . $module;
		}
		return $modules;
	}

	/**
	 * Determine if $module is an active TML module
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $module Filename of module to check
	 * @return bool True if $module is active, false if not
	 */
	function is_module_active( $module ) {
		$active_modules = apply_filters( 'tml_active_modules', $this->get_option( 'active_modules' ) );
		return in_array( $module, (array) $active_modules );
	}

	/**
	 * Handles e-mail address login
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $username Username or email
	 * @param string $password User's password
	 */
	function wp_authenticate( &$user_login ) {
		global $wpdb;
		if ( is_email( $user_login ) ) {
			if ( $found = $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM $wpdb->users WHERE user_email = %s", $user_login ) ) )
				$user_login = $found;
		}
		return;
	}

	/**
	 * Handles sending password retrieval email to user.
	 *
	 * @since 6.0
	 * @access public
	 * @uses $wpdb WordPress Database object
	 *
	 * @return bool|WP_Error True: when finish. WP_Error on error
	 */
	function retrieve_password() {
		global $wpdb;

		$errors = new WP_Error();

		if ( empty( $_POST['user_login'] ) && empty( $_POST['user_email'] ) )
			$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Enter a username or e-mail address.', $this->textdomain ) );

		if ( strpos( $_POST['user_login'], '@' ) ) {
			$user_data = get_user_by_email( trim( $_POST['user_login'] ) );
			if ( empty( $user_data ) )
				$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: There is no user registered with that email address.', $this->textdomain ) );
		} else {
			$login = trim( $_POST['user_login'] );
			$user_data = get_userdatabylogin( $login );
		}

		do_action( 'lostpassword_post' );

		if ( $errors->get_error_code() )
			return $errors;

		if ( !$user_data ) {
			$errors->add( 'invalidcombo', __( '<strong>ERROR</strong>: Invalid username or e-mail.', $this->textdomain ) );
			return $errors;
		}

		// redefining user_login ensures we return the right case in the email
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		do_action( 'retreive_password', $user_login );  // Misspelled and deprecated
		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

		if ( !$allow )
			return new WP_Error( 'no_password_reset', __( 'Password reset is not allowed for this user', $this->textdomain ) );
		else if ( is_wp_error( $allow ) )
			return $allow;

		$key = $wpdb->get_var( $wpdb->prepare( "SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login ) );
		if ( empty( $key ) ) {
			// Generate something random for a key...
			$key = wp_generate_password( 20, false );
			do_action( 'retrieve_password_key', $user_login, $key );
			// Now insert the new md5 key into the db
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => $key ), array( 'user_login' => $user_login ) );
		}
		$site_url = ( function_exists( 'network_site_url' ) ) ? 'network_site_url' : 'site_url'; // Pre 3.0 compatibility
		$message = __( 'Someone has asked to reset the password for the following site and username.', $this->textdomain ) . "\r\n\r\n";
		$message .= $site_url() . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s', $this->textdomain ), $user_login ) . "\r\n\r\n";
		$message .= __( 'To reset your password visit the following address, otherwise just ignore this email and nothing will happen.', $this->textdomain ) . "\r\n\r\n";
		$message .= $site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . "\r\n";

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$blogname = $GLOBALS['current_site']->site_name;
		} else {
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$title = sprintf( __( '[%s] Password Reset', $this->textdomain ), $blogname );

		$title = apply_filters( 'retrieve_password_title', $title, $user_data->ID );
		$message = apply_filters( 'retrieve_password_message', $message, $key, $user_data->ID );

		if ( $message && !wp_mail( $user_email, $title, $message ) )
			wp_die( __( 'The e-mail could not be sent.', $this->textdomain ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...', $this->textdomain ) );

		return true;
	}

	/**
	 * Handles resetting the user's password.
	 *
	 * @since 6.0
	 * @access public
	 * @uses $wpdb WordPress Database object
	 *
	 * @param string $key Hash to validate sending user's password
	 * @return bool|WP_Error
	 */
	function reset_password( $key, $login ) {
		global $wpdb;

		$key = preg_replace( '/[^a-z0-9]/i', '', $key );

		if ( empty( $key ) || !is_string( $key ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key', $this->textdomain ) );

		if ( empty( $login ) || !is_string( $login ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key', $this->textdomain ) );

		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login ) );
		if ( empty( $user ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key', $this->textdomain ) );

		// Generate something random for a password...
		$new_pass = wp_generate_password();

		do_action( 'password_reset', $user, $new_pass );

		$site_url = ( function_exists( 'network_site_url' ) ) ? 'network_site_url' : 'site_url'; // Pre 3.0 compatibility

		wp_set_password( $new_pass, $user->ID );
		update_user_option( $user->ID, 'default_password_nag', true, true ); //Set up the Password change nag.
		$message  = sprintf( __( 'Username: %s', $this->textdomain ), $user->user_login ) . "\r\n";
		$message .= sprintf( __( 'Password: %s', $this->textdomain ), $new_pass ) . "\r\n";
		$message .= $site_url( 'wp-login.php', 'login' ) . "\r\n";

		if ( function_exists( 'is_multisite') && is_multisite() ) {
			$blogname = $GLOBALS['current_site']->site_name;
		} else {
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$title = sprintf( __( '[%s] Your new password', $this->textdomain ), $blogname );

		$title = apply_filters( 'password_reset_title', $title, $user->ID );
		$message = apply_filters( 'password_reset_message', $message, $new_pass, $user->ID );

		if ( $message && !wp_mail( $user->user_email, $title, $message ) )
			wp_die( __( 'The e-mail could not be sent.', $this->textdomain ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...', $this->textdomain ) );

		do_action( 'tml_user_password_changed', $user );

		return true;
	}

	/**
	 * Handles registering a new user.
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $user_login User's username for logging in
	 * @param string $user_email User's email address to send password and add
	 * @return int|WP_Error Either user's ID or error on failure.
	 */
	function register_new_user( $user_login, $user_email ) {
		$errors = new WP_Error();

		$sanitized_user_login = sanitize_user( $user_login );
		$user_email = apply_filters( 'user_registration_email', $user_email );

		// Check the username
		if ( $sanitized_user_login == '' ) {
			$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Please enter a username.', $this->textdomain ) );
		} elseif ( !validate_username( $user_login ) ) {
			$errors->add( 'invalid_username', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', $this->textdomain ) );
			$sanitized_user_login = '';
		} elseif ( username_exists( $sanitized_user_login ) ) {
			$errors->add( 'username_exists', __( '<strong>ERROR</strong>: This username is already registered, please choose another one.', $this->textdomain ) );
		}

		// Check the e-mail address
		if ( '' == $user_email ) {
			$errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please type your e-mail address.', $this->textdomain ) );
		} elseif ( !is_email( $user_email ) ) {
			$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address isn&#8217;t correct.', $this->textdomain ) );
			$user_email = '';
		} elseif ( email_exists( $user_email ) ) {
			$errors->add( 'email_exists', __( '<strong>ERROR</strong>: This email is already registered, please choose another one.', $this->textdomain ) );
		}

		do_action( 'register_post', $sanitized_user_login, $user_email, $errors );

		$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

		if ( $errors->get_error_code() )
			return $errors;

		$user_pass = apply_filters( 'tml_user_registration_pass', wp_generate_password() );
		$user_id = wp_create_user( $sanitized_user_login, $user_pass, $user_email );
		if ( !$user_id ) {
			$errors->add( 'registerfail', sprintf( __( '<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !', $this->textdomain ), get_option( 'admin_email' ) ) );
			return $errors;
		}

		update_user_option( $user_id, 'default_password_nag', true, true ); //Set up the Password change nag.

		do_action( 'tml_new_user_registered', $user_id, $user_pass );

		return $user_id;
	}
}

endif; // Class exists

?>