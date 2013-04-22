<?php
/**
 * Holds the Login form
 *
 * @package Theme_My_Login
 * @since 6.4
 */

if ( ! class_exists( 'Theme_My_Login_Form_Login' ) ) :
/**
 * The Login form class
 *
 * @since 6.4
 */
final class Theme_My_Login_Form_Login extends Theme_My_Login_Form {
	/**
	 * Holds form action
	 *
	 * @since 6.4
	 * @var string
	 */
	public $action = 'login';

	/**
	 * Retrieves default options
	 *
	 * @since 6.4
	 *
	 * @return array Default options
	 */
	public static function default_options() {
		return array_merge( parent::default_options(), array(
			'show_log_link'  => false,
			'show_gravatar'  => true,
			'gravatar_size'  => 50,
			'redirect_to'    => isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : ''
		) );
	}

	/**
	 * Builds the form or user panel depending on login status
	 *
	 * @since 6.4
	 */
	public function build() {
		if ( is_user_logged_in() )
			$this->build_user_panel();
		else
			$this->build_form();
	}

	/**
	 * Builds the form
	 *
	 * @since 6.4
	 */
	public function build_form() {
		// Username field
		$this->add_field( 'log', 'general', array(
			'type'  => 'text',
			'id'    => 'user_login',
			'value' => isset( $_REQUEST['log'] ) ? $_REQUEST['log'] : '',
			'label' => __( 'Username' ),
			'class' => 'input',
			'size'  => 20,
			'before_field' => '<p>',
			'after_field'  => '</p>'
		) );

		// Password field
		$this->add_field( 'pwd', 'general', array(
			'type'  => 'password',
			'id'    => 'user_pass',
			'label' => __( 'Password' ),
			'class' => 'input',
			'size'  => 20,
			'before_field' => '<p>',
			'after_field'  => '</p>'
		) );

		// Do "login_form" action
		$this->add_field( 'login_form_action', 'general', array(
			'type' => 'hook',
			'hook' => 'do_action',
			'args' => array( 'login_form' )
		) );

		// Submit group
		$this->add_group( 'submit', array(
			'before_group' => '<p class="submit">',
			'after_group'  => '</p>'
		) );

		// Submit field
		$this->add_field( 'wp-submit', 'submit', array(
			'type'  => 'submit',
			'id'    => 'wp-submit',
			'value' => __( 'Log In' )
		) );

		// Redirect field
		$this->add_field( 'redirect_to', 'submit', array(
			'type'  => 'hidden',
			'value' => apply_filters( 'login_redirect', $this->get_option( 'redirect_to', admin_url() ), $this->get_option( 'redirect_to' ), wp_get_current_user() )
		) );

		// Instance field
		$this->add_field( 'instance', 'submit', array(
			'type'  => 'hidden',
			'value' => $this->get_option( 'instance' )
		) );

		// Action field
		$this->add_field( 'action', 'submit', array(
			'type'  => 'hidden',
			'value' => $this->action
		) );
	}

	/**
	 * Builds the user panel
	 *
	 * @since 6.4
	 */
	public function build_user_panel() {
		// Gravatar
		if ( $this->get_option( 'show_gravatar' ) ) {
			$this->add_field( 'user_avatar', 'general', array(
				'type' => 'custom',
				'html' => '<div class="tml-user-avatar">' . get_avatar( wp_get_current_user()->ID, $this->get_option( 'gravatar_size' ) ) . '</div>'
			) );
		}

		// User links
		$this->add_field( 'user_links', 'general', array(
			'type' => 'custom',
			'html' => self::get_user_link_html()
		) );

		// Submit group
		$this->add_group( 'submit', array(
			'before_group' => '<p class="submit">',
			'after_group'  => '</p>'
		) );

		// Submit field
		$this->add_field( 'wp-submit', 'submit', array(
			'type'  => 'submit',
			'id'    => 'wp-submit',
			'value' => __( 'Log Out' )
		) );

		// Instance field
		$this->add_field( 'instance', 'submit', array(
			'type'  => 'hidden',
			'value' => $this->get_option( 'instance' )
		) );

		// Action field
		$this->add_field( 'action', 'submit', array(
			'type'  => 'hidden',
			'value' => 'logout'
		) );

		// Nonce field
		$this->add_field( '_wpnonce', 'submit', array(
			'type'  => 'hidden',
			'value' => wp_create_nonce( 'log-out' )
		) );

		// Remove action links
		$this->set_option( 'show_reg_link',  false );
		$this->set_option( 'show_pass_link', false );

		// Change title
		$this->set_option( 'title', sprintf( __( 'Howdy, %1$s' ), wp_get_current_user()->display_name ) );
	}

	/**
	 * Retrieves form HTML
	 *
	 * @since 6.4
	 *
	 * @param array $args Output args
	 * @return string Form HTML
	 */
	public function get_form_html( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'before_form'  => '<div class="login" id="theme-my-login">',
			'after_form'   => '</div>',
			'form_name'    => 'loginform',
			'form_id'      => 'loginform',
			'form_method'  => 'post'
		) );
		return parent::get_form_html( $args );
	}

	/**
	 * Returns logged-in user links
	 *
	 * @since 6.0
	 *
	 * @return array Logged-in user links
	 */
	public static function get_user_link_html() {
		$user_links = apply_filters( 'tml_user_links', array(
			array(
				'title' => __( 'Dashboard' ),
				'url'   => admin_url() ),
			array(
				'title' => __( 'Profile' ),
				'url'   => admin_url( 'profile.php' )
			)
		) );

		$html = '<ul class="tml-user-links">';
		foreach ( (array) $user_links as $link ) {
			$html .= '<li><a href="' . esc_url( $link['url'] ) . '">' . esc_html( $link['title'] ) . '</a></li>' . "\n";
		}
		$html .= '</ul>';
		return $html;
	}
}
endif; // Class exists
