<?php
/**
 * Holds the Theme My Login class
 *
 * @package Theme_My_Login
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login' ) ) :
/*
 * Theme My Login class
 *
 * This class contains properties and methods common to the front-end.
 *
 * @since 6.0
 */
class Theme_My_Login extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login';

	/**
	 * Holds errors object
	 *
	 * @since 6.0
	 * @access public
	 * @var object
	 */
	public $errors;

	/**
	 * Holds current page being requested
	 *
	 * @since 6.3
	 * @access public
	 * @var string
	 */
	public $request_page;

	/**
	 * Holds current action being requested
	 *
	 * @since 6.0
	 * @access public
	 * @var string
	 */
	public $request_action;

	/**
	 * Holds current instance being requested
	 *
	 * @since 6.0
	 * @access public
	 * @var int
	 */
	public $request_instance;

	/**
	 * Holds loaded instances
	 *
	 * @since 6.3
	 * @access protected
	 * @var array
	 */
	protected $loaded_instances = array();

	/**
	 * Holds loaded modules
	 *
	 * @since 6.3
	 * @access protected
	 * @var array
	 */
	protected $loaded_modules = array();

	/**
	 * Returns default options
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @return array Default options
	 */
	public function default_options() {
		return array(
			'show_page' => true,
			'enable_css' => true,
			'email_login' => true,
			'active_modules' => array()
		);
	}

	/**
	 * Returns default actions
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @return array Default actions
	 */
	public function default_actions() {
		$actions = array(
			'login',
			'logout',
			'lostpassword',
			'postpass',
			'register',
			'resetpass'
		);

		if ( is_multisite() )
			$actions[] = 'activate';

		return apply_filters( 'tml_default_actions', $actions );
	}

	/**
	 * Loads the plugin
	 *
	 * @since 6.0
	 * @access public
	 */
	protected function load() {

		$this->load_instance();

		add_action( 'plugins_loaded',             array( &$this, 'plugins_loaded'             )        );
		add_action( 'init',                       array( &$this, 'init'                       )        );
		add_action( 'widgets_init',               array( &$this, 'widgets_init'               )        );
		add_action( 'parse_request',              array( &$this, 'parse_request'              )        );
		add_action( 'wp',                         array( &$this, 'wp'                         )        );
		add_action( 'wp_head',                    array( &$this, 'login_head'                 )        );
		add_action( 'wp_print_footer_scripts',    array( &$this, 'print_footer_scripts'       )        );
		add_action( 'wp_authenticate',            array( &$this, 'wp_authenticate'            )        );
		add_action( 'wp_before_admin_bar_render', array( &$this, 'wp_before_admin_bar_render' )        );

		add_filter( 'rewrite_rules_array',        array( &$this, 'rewrite_rules_array'        )        );
		add_filter( 'the_posts',                  array( &$this, 'the_posts'                  ), 10, 2 );
		add_filter( 'wp_setup_nav_menu_item',     array( &$this, 'wp_setup_nav_menu_item'     )        );
		add_filter( 'site_url',                   array( &$this, 'site_url'                   ), 10, 4 );
		add_filter( 'logout_url',                 array( &$this, 'logout_url'                 ), 10, 2 );
		add_filter( 'wp_list_pages',              array( &$this, 'wp_list_pages'              )        );
		add_filter( 'redirect_canonical',         array( &$this, 'redirect_canonical'         ), 10, 2 );

		add_action( 'tml_new_user_registered',   'wp_new_user_notification', 10, 2 );
		add_action( 'tml_user_password_changed', 'wp_password_change_notification' );

		add_shortcode( 'theme-my-login', array( &$this, 'shortcode' ) );
	}

	/**
	 * Loads active modules
	 *
	 * @since 6.3
	 * @access public
	 */
	public function plugins_loaded() {
		foreach ( $this->get_option( 'active_modules', array() ) as $module ) {
			$this->load_module( $module );
		}
		do_action_ref_array( 'tml_modules_loaded', array( &$this ) );
	}

	/**
	 * Initializes the plugin
	 *
	 * @since 6.0
	 * @access public
	 */
	public function init() {
		global $wp;

		load_plugin_textdomain( 'theme-my-login', '', 'theme-my-login/language' );

		$this->errors = new WP_Error();

		if ( ! is_admin() && $this->get_option( 'enable_css' ) )
			wp_enqueue_style( 'theme-my-login', Theme_My_Login::get_stylesheet(), false, $this->get_option( 'version' ) );
	}

	/**
	 * Registers the widget
	 *
	 * @since 6.0
	 * @access public
	 */
	public function widgets_init() {
		if ( class_exists( 'Theme_My_Login_Widget' ) )
			register_widget( 'Theme_My_Login_Widget' );
	}

	/**
	 * Determine if specified page is a TML page
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $action Action to check
	 * @return bool True if viewing a TML page, false otherwise
	 */
	public function is_login_page( $action = '' ) {
		if ( empty( $action ) )
			$action = $this->request_page;
		return apply_filters( 'tml_is_login_page', in_array( $action, $this->default_actions() ) );
	}

	/**
	 * Handles permalink rewrite rules
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param array $rules Rewrite rules
	 * @return array Rewrite rules
	 */
	function rewrite_rules_array( $rules ) {
		if ( defined( 'WP_INSTALLING' ) )
			return $rules;

		$tml_rules = array();
		foreach ( $this->default_actions() as $action ) {
			$slug = apply_filters( 'tml_page_link_slug', $action );
			$slug = trim( $slug, '/' );
			if ( empty( $slug ) )
				$slug = $action;
			$tml_rules["{$slug}/?$"] = "index.php?pagename={$action}";
		}
		return array_merge( $tml_rules, $rules );
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
	public function parse_request( &$wp ) {
		if ( is_admin() )
			return;

		$this->request_page     = isset( $wp->query_vars['pagename'] ) ? $wp->query_vars['pagename']           : '';
		$this->request_action   = isset( $_REQUEST['action']         ) ? sanitize_key( $_REQUEST['action']   ) : '';
		$this->request_instance = isset( $_REQUEST['instance']       ) ? sanitize_key( $_REQUEST['instance'] ) : 0;

		if ( $this->request_page && ! $this->request_action )
			$this->request_action = $this->request_page;

		do_action_ref_array( 'tml_request', array( &$this ) );

		// allow plugins to override the default actions, and to add extra actions if they want
		do_action( 'login_form_' . $this->request_action );

		if ( has_action( 'tml_request_' . $this->request_action ) ) {
			do_action_ref_array( 'tml_request_' . $this->request_action, array( &$this ) );
		} else {
			$http_post = ( 'POST' == $_SERVER['REQUEST_METHOD'] );
			switch ( $this->request_action ) {
				case 'postpass' :
					global $wp_hasher;

					if ( empty( $wp_hasher ) ) {
						require_once( ABSPATH . 'wp-includes/class-phpass.php' );
						// By default, use the portable hash from phpass
						$wp_hasher = new PasswordHash( 8, true );
					}

					// 10 days
					setcookie( 'wp-postpass_' . COOKIEHASH, $wp_hasher->HashPassword( stripslashes( $_POST['post_password'] ) ), time() + 864000, COOKIEPATH );

					wp_safe_redirect( wp_get_referer() );
					exit;

					break;
				case 'logout' :
					check_admin_referer( 'log-out' );

					$user = wp_get_current_user();

					wp_logout();

					$redirect_to = apply_filters( 'logout_redirect', site_url( 'wp-login.php?loggedout=true' ), isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user );
					wp_safe_redirect( $redirect_to );
					exit;
					break;
				case 'lostpassword' :
				case 'retrievepassword' :
					if ( $http_post ) {
						$this->errors = $this->retrieve_password();
						if ( ! is_wp_error( $this->errors ) ) {
							$redirect_to = ! empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : Theme_My_Login_Common::get_current_url( 'checkemail=confirm' );
							wp_safe_redirect( $redirect_to );
							exit;
						}
					}

					if ( isset( $_REQUEST['error'] ) && 'invalidkey' == $_REQUEST['error'] )
						$this->errors->add( 'invalidkey', __( 'Sorry, that key does not appear to be valid.' ) );

					do_action( 'lost_password' );
					break;
				case 'resetpass' :
				case 'rp' :
					$user = $this->check_password_reset_key( $_REQUEST['key'], $_REQUEST['login'] );

					if ( is_wp_error( $user ) ) {
						wp_redirect( Theme_My_Login_Common::get_current_url( 'action=lostpassword&error=invalidkey' ) );
						exit;
					}

					if ( isset( $_POST['pass1'] ) && $_POST['pass1'] != $_POST['pass2'] ) {
						$this->errors->add( 'password_reset_mismatch', __( 'The passwords do not match.' ) );
					} elseif ( isset( $_POST['pass1'] ) && ! empty( $_POST['pass1'] ) ) {
						$this->reset_password( $user, $_POST['pass1'] );

						$redirect_to = Theme_My_Login_Common::get_current_url( 'resetpass=complete' );
						wp_safe_redirect( $redirect_to );
						exit;
					}

					wp_enqueue_script( 'utils' );
					wp_enqueue_script( 'user-profile' );
					break;
				case 'register' :
					if ( ! get_option( 'users_can_register' ) ) {
						wp_redirect( Theme_My_Login_Common::get_current_url( 'registration=disabled' ) );
						exit;
					}

					$user_login = '';
					$user_email = '';
					if ( $http_post ) {
						$user_login = $_POST['user_login'];
						$user_email = $_POST['user_email'];

						$this->errors = Theme_My_Login::register_new_user( $user_login, $user_email );
						if ( ! is_wp_error( $this->errors ) ) {
							$redirect_to = ! empty( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : Theme_My_Login_Common::get_current_url( 'checkemail=registered' );
							$redirect_to = apply_filters( 'register_redirect', $redirect_to );
							wp_safe_redirect( $redirect_to );
							exit;
						}
					}
					break;
				case 'login' :
				default:
					$secure_cookie = '';
					$interim_login = isset( $_REQUEST['interim-login'] );

					// If the user wants ssl but the session is not ssl, force a secure cookie.
					if ( ! empty( $_POST['log'] ) && ! force_ssl_admin() ) {
						$user_name = sanitize_user( $_POST['log'] );
						if ( $user = get_user_by( 'login', $user_name ) ) {
							if ( get_user_option( 'use_ssl', $user->ID ) ) {
								$secure_cookie = true;
								force_ssl_admin( true );
							}
						}
					}

					if ( ! empty( $_REQUEST['redirect_to'] ) ) {
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
					if ( ! $secure_cookie && is_ssl() && force_ssl_login() && ! force_ssl_admin() && ( 0 !== strpos( $redirect_to, 'https' ) ) && ( 0 === strpos( $redirect_to, 'http' ) ) )
						$secure_cookie = false;

					if ( $http_post && isset( $_POST['log'] ) ) {

						$user = wp_signon( '', $secure_cookie );

						$redirect_to = apply_filters( 'login_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user );

						if ( ! is_wp_error( $user ) && ! $reauth ) {
							if ( ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ) ) {
								// If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
								if ( is_multisite() && ! get_active_blog_for_user( $user->ID ) && ! is_super_admin( $user->ID ) )
									$redirect_to = user_admin_url();
								elseif ( is_multisite() && ! $user->has_cap( 'read' ) )
									$redirect_to = get_dashboard_url( $user->ID );
								elseif ( ! $user->has_cap( 'edit_posts' ) )
									$redirect_to = admin_url( 'profile.php' );
							}
							wp_safe_redirect( $redirect_to );
							exit;
						}

						$this->errors = $user;
					}

					// Clear errors if loggedout is set.
					if ( ! empty( $_GET['loggedout'] ) || $reauth )
						$this->errors = new WP_Error();

					// Some parts of this script use the main login form to display a message
					if		( isset( $_GET['loggedout'] ) && true == $_GET['loggedout'] )
						$this->errors->add( 'loggedout', __( 'You are now logged out.' ), 'message' );
					elseif	( isset( $_GET['registration'] ) && 'disabled' == $_GET['registration'] )
						$this->errors->add( 'registerdisabled', __( 'User registration is currently not allowed.' ) );
					elseif	( isset( $_GET['checkemail'] ) && 'confirm' == $_GET['checkemail'] )
						$this->errors->add( 'confirm', __( 'Check your e-mail for the confirmation link.' ), 'message' );
					elseif ( isset( $_GET['resetpass'] ) && 'complete' == $_GET['resetpass'] )
						$this->errors->add( 'password_reset', __( 'Your password has been reset.', 'theme-my-login' ), 'message' );
					elseif	( isset( $_GET['checkemail'] ) && 'registered' == $_GET['checkemail'] )
						$this->errors->add( 'registered', __( 'Registration complete. Please check your e-mail.' ), 'message' );
					elseif	( $interim_login )
						$this->errors->add( 'expired', __( 'Your session has expired. Please log-in again.' ), 'message' );
					elseif ( strpos( $redirect_to, 'about.php?updated' ) )
						$this->errors->add('updated', __( '<strong>You have successfully updated WordPress!</strong> Please log back in to experience the awesomeness.' ), 'message' );
					elseif	( $reauth )
						$this->errors->add( 'reauth', __( 'Please log in to continue.', 'theme-my-login' ), 'message' );

					// Clear any stale cookies.
					if ( $reauth )
						wp_clear_auth_cookie();
					break;
			} // end switch
		} // endif has_filter()
	}

	/**
	 * Fill posts array for virtual pages
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param array $posts Array of posts
	 * @param object $wp_query Reference to WP_Query object
	 * @return array Array of posts
	 */
	public function the_posts( $posts, &$wp_query ) {
		if ( $this->is_login_page() && $wp_query->is_main_query() )
			return array( $this->get_page_object() );
		return $posts;
	}

	/**
	 * Used to add/remove filters from login page
	 *
	 * @since 6.1.1
	 * @access public
	 */
	public function wp() {
		global $withcomments;

		if ( $this->is_login_page() ) {
			$withcomments = false;

			do_action( 'login_init' );

			remove_action( 'wp_head', 'feed_links',                       2 );
			remove_action( 'wp_head', 'feed_links_extra',                 3 );
			remove_action( 'wp_head', 'rsd_link'                            );
			remove_action( 'wp_head', 'wlwmanifest_link'                    );
			remove_action( 'wp_head', 'parent_post_rel_link',            10 );
			remove_action( 'wp_head', 'start_post_rel_link',             10 );
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
			remove_action( 'wp_head', 'rel_canonical'                       );

			// Don't index any of these forms
			add_action( 'login_head', 'wp_no_robots' );

			if ( force_ssl_admin() && ! is_ssl() ) {
				if ( 0 === strpos( $_SERVER['REQUEST_URI'], 'http' ) ) {
					wp_redirect( preg_replace( '|^http://|', 'https://', $_SERVER['REQUEST_URI'] ) );
					exit;
				} else {
					wp_redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
					exit;
				}
			}
		}
	}

	/**
	 * Removes "Edit" menu from admin bar on virtual page
	 *
	 * @since 6.3
	 * @access public
	 */
	public function wp_before_admin_bar_render() {
		global $wp_admin_bar;

		if ( $this->is_login_page() )
			$wp_admin_bar->remove_menu( 'edit' );
	}

	/**
	 * Returns object for login page
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @return object Login page object
	 */
	public function get_page_object( $args = '' ) {
		$defaults = array(
			'ID'                    => -999999,
			'post_author'           => 1,
			'post_date'             => 0,
			'post_date_gmt'         => 0,
			'post_content'          => '[theme-my-login]',
			'post_title'            => Theme_My_Login_Template::get_title( $this->request_page ),
			'post_excerpt'          => '',
			'post_status'           => 'publish',
			'comment_status'        => 'closed',
			'ping_status'           => 'closed',
			'post_password'         => '',
			'post_name'             => $this->request_page,
			'to_ping'               => '',
			'pinged'                => '',
			'post_modified'         => 0,
			'post_modified_gmt'     => 0,
			'post_content_filtered' => '',
			'post_parent'           => 0,
			'guid'                  => $this->get_page_link(),
			'menu_order'            => 0,
			'post_type'             => 'page',
			'post_mime_type'        => '',
			'comment_count'         => 0
		);
		$page = wp_parse_args( $args, $defaults );
		$page = apply_filters( 'tml_page_object', $page, $args );
		return (object) $page;
	}

	/**
	 * Returns link for login page
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $action The action of which URL to retrieve
	 * @param string|array $query Optional. Query arguments to add to link
	 * @param int $blog_id Blog ID
	 * @return string Login page link with optional $query arguments appended
	 */
	function get_page_link( $action = 'login', $query = '', $blog_id = null ) {
		global $wp_rewrite;

		if ( $wp_rewrite->using_permalinks() ) {
			switch ( $action ) {
				case 'retrievepassword' :
					$action = 'lostpassword';
					break;
				case 'rp' :
					$action = 'resetpass';
					break;
			}
			$slug = apply_filters( 'tml_page_link_slug', $action );

			$link = $wp_rewrite->get_page_permastruct();
			$link = str_replace( '%pagename%', $slug, $link );
			$link = get_home_url( $blog_id, $link );
			$link = user_trailingslashit( $link, 'page' );
		} else {
			$link = get_home_url( $blog_id, "?pagename=$action" );
		}

		if ( ! empty( $query ) )
			$link = add_query_arg( array_map( 'rawurlencode', wp_parse_args( $query ) ), $link );

		return apply_filters( 'tml_page_link', $link, $action, $query, $blog_id );
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
	public function wp_list_pages( $output ) {
		if ( $this->get_option( 'show_page' ) ) {

			$classes = array( 'page_item' );
			if ( $this->is_login_page() )
				$classes[] = 'current_page_item';

			if ( is_user_logged_in() ) {
				$title = apply_filters( 'tml_title', __( 'Log Out' ), 'logout' );
				$link = wp_logout_url();
				$classes[] = 'tml_logout_link';
			} else {
				$title = apply_filters( 'tml_title', __( 'Log In' ), 'login' );
				$link = wp_login_url();
				$classes[] = 'tml_login_link';
			}
			$classes = apply_filters( 'tml_menu_item_classes', $classes );

			$output .= '<li class="' . implode( ' ', $classes ) . '"><a href="' . $link . '">' . $title . '</a></li>';
		}
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
	public function wp_setup_nav_menu_item( $menu_item ) {
		if ( 'page' == $menu_item->object && $this->is_login_page( $menu_item->post_name ) ) {
			$menu_item->url = $this->get_page_link( $menu_item->post_name );
			$menu_item->type = 'custom';
		}

		if ( is_admin() )
			return $menu_item;

		if ( 'custom' == $menu_item->object ) {
			if ( $menu_item->url == wp_login_url() ) {
				if ( is_user_logged_in() ) {
					$menu_item->title = apply_filters( 'tml_title', __( 'Log Out' ), 'logout' );
					$menu_item->url = wp_logout_url();
					$menu_item->classes[] = 'tml_logout_link';
				} else {
					$menu_item->classes[] = 'tml_login_link';
				}
				$menu_item->classes = apply_filters( 'tml_menu_item_classes', $menu_item->classes );
			}
		}
		return $menu_item;
	}

	/**
	 * Cancels canonical guess redirect for Login pages
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $redirect_url The canonical redirect URL
	 * @param string $requested_url The originally requested URL
	 * @return string|bool The canonical redirect URL or false to cancel
	 */
	public function redirect_canonical( $redirect_url, $requested_url ) {
		if ( is_404() && $this->is_login_page() )
			return false;
		return $redirect_url;
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
	 * - resetpass_template - The template used for the reset password form. Defaults to "resetpass-form.php".
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
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $atts Attributes passed from the shortcode
	 * @return string HTML output from Theme_My_Login_Template->display()
	 */
	public function shortcode( $atts = '' ) {

		$atts = wp_parse_args( $atts );

		if ( $this->is_login_page() && in_the_loop() && is_main_query() ) {
			$instance =& $this->get_instance();

			if ( ! empty( $this->request_page ) )
				$atts['default_action'] = $this->request_page;

			if ( ! isset( $atts['show_title'] ) )
				$atts['show_title'] = false;

			foreach ( $atts as $option => $value ) {
				$instance->set_option( $option, $value );
			}
		} else {
			$instance =& $this->get_instance( $this->load_instance( $atts ) );
		}
		return $instance->display();
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
	 * @param int $blog_id Blog ID
	 * @return string The modified URL
	 */
	public function site_url( $url, $path, $orig_scheme, $blog_id ) {
		global $pagenow;

		if ( 'wp-login.php' != $pagenow && false !== strpos( $url, 'wp-login.php' ) && ! isset( $_REQUEST['interim-login'] ) ) {
			$parsed_url = parse_url( $url );

			$q = array();
			if ( isset( $parsed_url['query'] ) )
				$q = wp_parse_args( $parsed_url['query'] );

			$action = 'login';
			if ( isset( $q['action'] ) ) {
				$action = $q['action'];
				unset( $q['action'] );
			}

			$url = $this->get_page_link( $action, $q, $blog_id );

			if ( 'https' == strtolower( $orig_scheme ) )
				$url = preg_replace( '|^http://|', 'https://', $url );
		}
		return $url;
	}

	/**
	 * Filters logout URL to allow for logout permalink
	 *
	 * This is needed because WP doesn't pass the action parameter to site_url
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $logout_url Logout URL
	 * @param string $redirect Redirect URL
	 * @return string Logout URL
	 */
	public function logout_url( $logout_url, $redirect ) {
		$logout_url = $this->get_page_link( 'logout' );
		if ( $redirect )
			$logout = add_query_arg( 'redirect_to', urlencode( $redirect ), $logout_url );
		$logout_url = wp_nonce_url( $logout_url, 'log-out' );
		return $logout_url;
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
	public function get_stylesheet( $file = 'theme-my-login.css' ) {
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
	public function print_footer_scripts() {
		if ( ! $this->is_login_page() )
			return;

		switch ( $this->request_action ) {
			case 'lostpassword' :
			case 'retrievepassword' :
			case 'register' :
			?>
<script type="text/javascript">
try{document.getElementById('user_login').focus();}catch(e){}
if(typeof wpOnload=='function')wpOnload()
</script>
<?php
				break;
			case 'login' :
			default :
				$user_login = '';
				if ( isset($_POST['log']) )
					$user_login = ( 'incorrect_password' == $this->errors->get_error_code() || 'empty_password' == $this->errors->get_error_code() ) ? esc_attr( stripslashes( $_POST['log'] ) ) : '';
			?>
<script type="text/javascript">
function wp_attempt_focus() {
setTimeout( function() {
try {
<?php if ( $user_login ) { ?>
d = document.getElementById('user_pass');
<?php } else { ?>
d = document.getElementById('user_login');
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
	public function login_head() {
		if ( $this->is_login_page() ) {
			do_action( 'login_enqueue_scripts' );
			do_action( 'login_head' );
		}
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
	public function wp_authenticate( &$user_login ) {
		global $wpdb;
		if ( is_email( $user_login ) && $this->get_option( 'email_login' ) ) {
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
	public function retrieve_password() {
		global $wpdb, $current_site;

		$errors = new WP_Error();

		if ( empty( $_POST['user_login'] ) ) {
			$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Enter a username or e-mail address.' ) );
		} else if ( strpos( $_POST['user_login'], '@' ) ) {
			$user_data = get_user_by_email( trim( $_POST['user_login'] ) );
			if ( empty( $user_data ) )
				$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: There is no user registered with that email address.' ) );
		} else {
			$login = trim( $_POST['user_login'] );
			$user_data = get_user_by( 'login', $login );
		}

		do_action( 'lostpassword_post' );

		if ( $errors->get_error_code() )
			return $errors;

		if ( ! $user_data ) {
			$errors->add( 'invalidcombo', __( '<strong>ERROR</strong>: Invalid username or e-mail.' ) );
			return $errors;
		}

		// redefining user_login ensures we return the right case in the email
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		do_action( 'retreive_password', $user_login );  // Misspelled and deprecated
		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

		if ( ! $allow )
			return new WP_Error( 'no_password_reset', __( 'Password reset is not allowed for this user' ) );
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
		$message = __( 'Someone requested that the password be reset for the following account:' ) . "\r\n\r\n";
		$message .= network_home_url( '/' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s' ), $user_login ) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
		$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . ">\r\n";

		if ( is_multisite() ) {
			$blogname = $current_site->site_name;
		} else {
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$title = sprintf( __( '[%s] Password Reset' ), $blogname );

		$title = apply_filters( 'retrieve_password_title', $title, $user_data->ID );
		$message = apply_filters( 'retrieve_password_message', $message, $key, $user_data->ID );

		if ( $message && ! wp_mail( $user_email, $title, $message ) )
			wp_die( __( 'The e-mail could not be sent.' ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...' ) );

		return true;
	}

	/**
	 * Retrieves a user row based on password reset key and login
	 *
	 * @since 6.1.1
	 * @access public
	 * @uses $wpdb WordPress Database object
	 *
	 * @param string $key Hash to validate sending user's password
	 * @param string $login The user login
	 *
	 * @return object|WP_Error
	 */
	public function check_password_reset_key( $key, $login ) {
		global $wpdb;

		$key = preg_replace( '/[^a-z0-9]/i', '', $key );

		if ( empty( $key ) || ! is_string( $key ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key' ) );

		if ( empty( $login ) || ! is_string( $login ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key' ) );

		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login ) );

		if ( empty( $user ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key' ) );

		return $user;
	}

	/**
	 * Handles resetting the user's password.
	 *
	 * @since 6.0
	 * @access public
	 * @uses $wpdb WordPress Database object
	 *
	 * @param string $key Hash to validate sending user's password
	 */
	public function reset_password( $user, $new_pass ) {
		do_action( 'password_reset', $user, $new_pass );

		wp_set_password( $new_pass, $user->ID );

		do_action_ref_array( 'tml_user_password_changed', array( &$user ) );
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
	public function register_new_user( $user_login, $user_email ) {
		$errors = new WP_Error();

		$sanitized_user_login = sanitize_user( $user_login );
		$user_email = apply_filters( 'user_registration_email', $user_email );

		// Check the username
		if ( $sanitized_user_login == '' ) {
			$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Please enter a username.' ) );
		} elseif ( ! validate_username( $user_login ) ) {
			$errors->add( 'invalid_username', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.' ) );
			$sanitized_user_login = '';
		} elseif ( username_exists( $sanitized_user_login ) ) {
			$errors->add( 'username_exists', __( '<strong>ERROR</strong>: This username is already registered, please choose another one.' ) );
		}

		// Check the e-mail address
		if ( '' == $user_email ) {
			$errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please type your e-mail address.' ) );
		} elseif ( ! is_email( $user_email ) ) {
			$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address isn&#8217;t correct.' ) );
			$user_email = '';
		} elseif ( email_exists( $user_email ) ) {
			$errors->add( 'email_exists', __( '<strong>ERROR</strong>: This email is already registered, please choose another one.' ) );
		}

		do_action( 'register_post', $sanitized_user_login, $user_email, $errors );

		$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

		if ( $errors->get_error_code() )
			return $errors;

		$user_pass = apply_filters( 'tml_user_registration_pass', wp_generate_password( 12, false ) );
		$user_id = wp_create_user( $sanitized_user_login, $user_pass, $user_email );
		if ( ! $user_id ) {
			$errors->add( 'registerfail', sprintf( __( '<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !' ), get_option( 'admin_email' ) ) );
			return $errors;
		}

		update_user_option( $user_id, 'default_password_nag', true, true ); //Set up the Password change nag.

		do_action( 'tml_new_user_registered', $user_id, $user_pass );

		return $user_id;
	}

	/**
	 * Retrieves active instance object
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @return object Instance object
	 */
	public function &get_active_instance() {
		return $this->get_instance( (int) $this->request_instance );
	}

	/**
	 * Retrieves a loaded instance object
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param int $id Instance ID
	 * @return object Instance object
	 */
	public function &get_instance( $id = 0 ) {
		if ( isset( $this->loaded_instances[$id] ) )
			return $this->loaded_instances[$id];

		$null = null;
		return $null;
	}

	/**
	 * Sets an instance object
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param object $object Instance object
	 */
	public function set_instance( $object ) {
		$this->loaded_instances[] =& $object;
	}

	/**
	 * Instantiates an instance
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param array|string $args Query string or array of arguments
	 * @return int Instance ID
	 */
	public function load_instance( $args = '' ) {
		$args['instance'] = count( $this->loaded_instances );

		if ( $args['instance'] == $this->request_instance ) {
			$args['active']         = true;
			$args['default_action'] = $this->request_action;
		}

		$this->loaded_instances[] = new Theme_My_Login_Template( $args );

		return $args['instance'];
	}

	/**
	 * Checks if a module is loaded
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $name Module name
	 * @return bool True if module is loaded, false otherwise
	 */
	public function is_module_loaded( $name ) {
		$name = sanitize_key( basename( $name, '.php' ) );
		return isset( $this->loaded_modules[$name] );
	}

	/**
	 * Retrieves a loaded module object
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $name Module name
	 * @return object Module object
	 */
	public function &get_module( $name ) {
		$name = sanitize_key( basename( $name, '.php' ) );
		if ( isset( $this->loaded_modules[$name] ) )
			return $this->loaded_modules[$name];

		$null = null;
		return $null;
	}

	/**
	 * Sets a module object
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $name Module name
	 * @param object $object Module object
	 */
	public function set_module( $name, $object ) {
		$name = sanitize_key( basename( $name, '.php' ) );
		$this->loaded_modules[$name] =& $object;
	}

	/**
	 * Instantiates module object
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $file Module file path
	 * @param bool $load_admin True to load module admin class
	 */
	public function load_module( $file ) {
		if ( false === strpos( $file, WP_PLUGIN_DIR ) )
			$file = WP_PLUGIN_DIR . '/theme-my-login/modules/' . rtrim( $file, '/' );

		$data = get_file_data( $file, array(
			'name'        => 'Plugin Name',
			'description' => 'Description',
			'class'       => 'Class',
			'admin_class' => 'Admin Class'
		) );

		$name = sanitize_key( basename( $file, '.php' ) );

		if ( class_exists( $data['class'] ) )
			$this->loaded_modules[$name] = new $data['class'];

		if ( ! is_admin() )
			return;

		if ( class_exists( $data['admin_class'] ) )
			$this->loaded_modules["{$name}-admin"] = new $data['admin_class'];
	}
}
endif; // Class exists

