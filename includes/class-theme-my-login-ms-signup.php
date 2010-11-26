<?php
/**
 * Holds the Theme My Login multisite signup class
 *
 * @package Theme My Login
 */

if ( !class_exists( 'Theme_My_Login_MS_Signup' ) ) :
/*
 * Theme My Login multisite signup class
 *
 * This class contains properties and methods common to the multisite signup process.
 *
 * @since 6.1
 */
class Theme_My_Login_MS_Signup {
	/**
	 * Holds reference to global $theme_my_login object
	 *
	 * @since 6.0
	 * @access public
	 * @var object
	 */
	var $theme_my_login;

	var $theme_my_login_template;

	/**
	 * PHP4 style constructor
	 *
	 * @since 6.0
	 * @access public
	 */
	function Theme_My_Login_MS_Signup() {
		$this->__construct();
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 6.0
	 * @access public
	 */
	function __construct() {
		if ( isset( $_REQUEST['action'] ) && 'register' == $_REQUEST['action'] )
			add_action( 'wp_head', array( &$this, 'wp_head' ) );
		add_action( 'tml_request_register', array( &$this, 'request' ) );
		add_action( 'tml_display_register', array( &$this, 'display' ) );
	}

	function request( &$theme_my_login ) {
		global $current_site;

		$this->theme_my_login =& $theme_my_login;

		require_once( ABSPATH . WPINC . '/registration.php' );

		if ( !is_main_site() ) {
			switch_to_blog( $current_site->blog_id );
			$redirect_to = $theme_my_login->get_login_page_link( array( 'action' => 'register' ) );
			restore_current_blog();
			wp_redirect( $redirect_to );
			exit();
		}
	}

	function display( &$template ) {
		global $wpdb, $blogname, $blog_title, $errors, $domain, $path;

		$this->theme_my_login_template =& $template;

		$theme_my_login =& $template->theme_my_login;

		do_action( 'before_signup_form' );

		echo '<div class="login mu_register" id="theme-my-login' . esc_attr( $template->instance ) . '">';

		$active_signup = get_site_option( 'registration' );
		if ( !$active_signup )
			$active_signup = 'all';

		$active_signup = apply_filters( 'wpmu_active_signup', $active_signup ); // return "all", "none", "blog" or "user"

		// Make the signup type translatable.
		$i18n_signup['all'] = _x( 'all', 'Multisite active signup type' );
		$i18n_signup['none'] = _x( 'none', 'Multisite active signup type' );
		$i18n_signup['blog'] = _x( 'blog', 'Multisite active signup type' );
		$i18n_signup['user'] = _x( 'user', 'Multisite active signup type' );

		if ( is_super_admin() )
			echo '<p class="message">' . sprintf( __( 'Greetings Site Administrator! You are currently allowing &#8220;%s&#8221; registrations. To change or disable registration go to your <a href="%s">Options page</a>.' ), $i18n_signup[$active_signup], esc_url( network_admin_url( 'ms-options.php' ) ) ) . '</p>';

		$newblogname = isset( $_GET['new'] ) ? strtolower( preg_replace( '/^-|-$|[^-a-zA-Z0-9]/', '', $_GET['new'] ) ) : null;

		$current_user = wp_get_current_user();
		if ( $active_signup == "none" ) {
			_e( 'Registration has been disabled.', $theme_my_login->textdomain );
		} elseif ( $active_signup == 'blog' && !is_user_logged_in() ) {
			printf( __( 'You must first <a href="%s">log in</a>, and then you can create a new site.', $theme_my_login->textdomain ), wp_login_url( $theme_my_login->get_current_url() ) );
		} else {
			$stage = isset( $_POST['stage'] ) ?  $_POST['stage'] : 'default';
			switch ( $stage ) {
				case 'validate-user-signup' :
					if ( $active_signup == 'all' || $_POST[ 'signup_for' ] == 'blog' && $active_signup == 'blog' || $_POST[ 'signup_for' ] == 'user' && $active_signup == 'user' ) {
						$result = wpmu_validate_user_signup( $_POST['user_name'], $_POST['user_email'] );
						extract( $result );

						$theme_my_login->errors = $errors;

						if ( $errors->get_error_code() ) {
							$this->signup_user( $user_name, $user_email, $errors );
							return false;
						}

						if ( 'blog' == $_POST['signup_for'] ) {
							$this->signup_blog( $user_name, $user_email );
							return false;
						}

						wpmu_signup_user( $user_name, $user_email, apply_filters( 'add_signup_meta', array() ) );

						?>
						<h2><?php printf( __( '%s is your new username', $theme_my_login->textdomain ), $user_name) ?></h2>
						<p><?php _e( 'But, before you can start using your new username, <strong>you must activate it</strong>.', $theme_my_login->textdomain ) ?></p>
						<p><?php printf(__( 'Check your inbox at <strong>%1$s</strong> and click the link given.', $theme_my_login->textdomain ),  $user_email) ?></p>
						<p><?php _e( 'If you do not activate your username within two days, you will have to sign up again.', $theme_my_login->textdomain ); ?></p>
						<?php
						do_action( 'signup_finished' );
					} else {
						_e( 'User registration has been disabled.', $theme_my_login->textdomain );
					}
				break;
				case 'validate-blog-signup':
					if ( $active_signup == 'all' || $active_signup == 'blog' ) {
						// Re-validate user info.
						$result = wpmu_validate_user_signup( $_POST['user_name'], $_POST['user_email'] );
						extract( $result );

						if ( $errors->get_error_code() ) {
							$this->signup_user( $user_name, $user_email, $errors );
							return false;
						}

						$result = wpmu_validate_blog_signup( $_POST['blogname'], $_POST['blog_title'] );
						extract( $result );

						if ( $errors->get_error_code() ) {
							$this->signup_blog( $user_name, $user_email, $blogname, $blog_title, $errors );
							return false;
						}

						$public = (int) $_POST['blog_public'];
						$meta = array ('lang_id' => 1, 'public' => $public);
						$meta = apply_filters( 'add_signup_meta', $meta );

						wpmu_signup_blog( $domain, $path, $blog_title, $user_name, $user_email, $meta );
						?>
						<h2><?php printf( __( 'Congratulations! Your new site, %s, is almost ready.', $theme_my_login->textdomain ), "<a href='http://{$domain}{$path}'>{$blog_title}</a>" ) ?></h2>

						<p><?php _e( 'But, before you can start using your site, <strong>you must activate it</strong>.', $theme_my_login->textdomain ) ?></p>
						<p><?php printf( __( 'Check your inbox at <strong>%s</strong> and click the link given.', $theme_my_login->textdomain ),  $user_email) ?></p>
						<p><?php _e( 'If you do not activate your site within two days, you will have to sign up again.', $theme_my_login->textdomain ); ?></p>
						<h2><?php _e( 'Still waiting for your email?' ); ?></h2>
						<p>
							<?php _e( 'If you haven&#8217;t received your email yet, there are a number of things you can do:', $theme_my_login->textdomain ) ?>
							<ul id="noemail-tips">
								<li><p><strong><?php _e( 'Wait a little longer. Sometimes delivery of email can be delayed by processes outside of our control.', $theme_my_login->textdomain ) ?></strong></p></li>
								<li><p><?php _e( 'Check the junk or spam folder of your email client. Sometime emails wind up there by mistake.', $theme_my_login->textdomain ) ?></p></li>
								<li><?php printf( __( 'Have you entered your email correctly?  You have entered %s, if it&#8217;s incorrect, you will not receive your email.', $theme_my_login->textdomain ), $user_email ) ?></li>
							</ul>
						</p>
						<?php
						do_action( 'signup_finished' );
					} else {
						_e( 'Site registration has been disabled.', $theme_my_login->textdomain );
					}
					break;
				case 'gimmeanotherblog':
					$current_user = wp_get_current_user();
					if ( !is_user_logged_in() )
						die();

					$result = wpmu_validate_blog_signup( $_POST['blogname'], $_POST['blog_title'], $current_user );
					extract( $result );

					if ( $errors->get_error_code() ) {
						$this->signup_another_blog( $blogname, $blog_title, $errors );
						return false;
					}

					$public = (int) $_POST['blog_public'];
					$meta = apply_filters( 'signup_create_blog_meta', array( 'lang_id' => 1, 'public' => $public ) ); // deprecated
					$meta = apply_filters( 'add_signup_meta', $meta );

					wpmu_create_blog( $domain, $path, $blog_title, $current_user->id, $meta, $wpdb->siteid );
					?>
					<h2><?php printf( __( 'The site %s is yours.', $theme_my_login->textdomain ), "<a href='http://{$domain}{$path}'>{$blog_title}</a>" ) ?></h2>
					<p>
						<?php printf( __( '<a href="http://%1$s">http://%2$s</a> is your new site.  <a href="%3$s">Log in</a> as &#8220;%4$s&#8221; using your existing password.', $theme_my_login->textdomain ), $domain.$path, $domain.$path, "http://" . $domain.$path . "wp-login.php", $current_user->user_login ) ?>
					</p>
					<?php
					do_action( 'signup_finished' );
					break;
				case 'default':
				default :
					$user_email = isset( $_POST[ 'user_email' ] ) ? $_POST[ 'user_email' ] : '';

					do_action( 'preprocess_signup_form' ); // populate the form from invites, elsewhere?

					if ( is_user_logged_in() && ( $active_signup == 'all' || $active_signup == 'blog' ) )
						$this->signup_another_blog( $newblogname );
					elseif ( is_user_logged_in() == false && ( $active_signup == 'all' || $active_signup == 'user' ) )
						$this->signup_user( $newblogname, $user_email );
					elseif ( is_user_logged_in() == false && ( $active_signup == 'blog' ) )
						_e( 'Sorry, new registrations are not allowed at this time.', $theme_my_login->textdomain );
					else
						_e( 'You are logged in already. No need to register again!', $theme_my_login->textdomain );

					if ( $newblogname ) {
						$newblog = get_blogaddress_by_name( $newblogname );

						if ( $active_signup == 'blog' || $active_signup == 'all' )
							printf( __( '<p><em>The site you were looking for, <strong>%s</strong> does not exist, but you can create it now!</em></p>', $theme_my_login->textdomain ), $newblog );
						else
							printf( __( '<p><em>The site you were looking for, <strong>%s</strong>, does not exist.</em></p>', $theme_my_login->textdomain ), $newblog );
					}
					break;
			}
		}
		echo '</div>';
		do_action( 'after_signup_form' );
	}

	function wp_head() {
		do_action( 'signup_header' );
		echo '<meta name="robots" content="noindex,nofollow" />' . "\n";
	}

	function signup_user( $user_name = '', $user_email = '' ) {
		global $current_site;

		$template =& $this->theme_my_login_template;

		if ( isset( $_POST['signup_for'] ) )
			$signup[esc_html( $_POST['signup_for'] )] = 'checked="checked"';
		else
			$signup['blog'] = 'checked="checked"';

		// allow definition of default variables
		$filtered_results = apply_filters( 'signup_user_init', array( 'user_name' => $user_name, 'user_email' => $user_email, 'errors' => $this->theme_my_login->errors ) );

		if ( !empty( $this->theme_my_login_template->options['ms_signup_user_template'] ) )
			$templates[] = $this->theme_my_login_template->options['ms_signup_user_template'];
		$templates[] = 'ms-signup-user-form.php';

		$template->get_template( $templates, true, $filtered_results );
	}

	function signup_blog( $user_name = '', $user_email = '', $blogname = '', $blog_title = '' ) {
		global $current_site;

		$template =& $this->theme_my_login_template;

		// allow definition of default variables
		$filtered_results = apply_filters( 'signup_blog_init', array( 'user_name' => $user_name, 'user_email' => $user_email, 'blogname' => $blogname, 'blog_title' => $blog_title, 'errors' => $this->theme_my_login->errors ) );

		if ( empty( $filtered_results['blogname'] ) )
			$filtered_results['blogname'] = $filtered_results['user_name'];

		if ( !empty( $this->theme_my_login_template->options['ms_signup_blog_template'] ) )
			$templates[] = $this->theme_my_login_template->options['ms_signup_blog_template'];
		$templates[] = 'ms-signup-blog-form.php';

		$template->get_template( $templates, true, $filtered_results );
	}

	function signup_another_blog( $blogname = '', $blog_title = '' ) {
		global $current_site;

		$template =& $this->theme_my_login_template;

		// allow definition of default variables
		$filtered_results = apply_filters( 'signup_another_blog_init', array( 'blogname' => $blogname, 'blog_title' => $blog_title, 'errors' => $this->theme_my_login->errors ) );

		if ( !empty( $this->theme_my_login_template->options['ms_signup_another_blog_template'] ) )
			$templates[] = $this->theme_my_login_template->options['ms_signup_another_blog_template'];
		$templates[] = 'ms-signup-another-blog-form.php';

		$template->get_template( $templates, true, $filtered_results );
	}
}
endif;

?>