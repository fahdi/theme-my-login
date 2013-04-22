<?php
/**
 * Holds the Lost Password form
 *
 * @package Theme_My_Login
 * @since 6.4
 */

if ( ! class_exists( 'Theme_My_Login_Form_Lost_Password' ) ) :
/**
 * The Lost Password form class
 *
 * @since 6.4
 */
final class Theme_My_Login_Form_Lost_Password extends Theme_My_Login_Form {
	/**
	 * Holds form action
	 *
	 * @since 6.4
	 * @var string
	 */
	public $action = 'lostpassword';

	/**
	 * Retrieves default options
	 *
	 * @since 6.4
	 *
	 * @return array Default options
	 */
	public static function default_options() {
		return array_merge( parent::default_options(), array(
			'show_pass_link' => false,
			'message'        => __( 'Please enter your username or email address. You will receive a link to create a new password via email.' )
		) );
	}

	/**
	 * Builds the form
	 *
	 * @since 6.4
	 */
	public function build() {
		// Username field
		$this->add_field( 'user_login', 'general', array(
			'type'  => 'text',
			'id'    => 'user_login',
			'value' => isset( $_REQUEST['user_login'] ) ? $_REQUEST['user_login'] : '',
			'label' => __( 'Username or E-mail:' ),
			'class' => 'input',
			'size'  => 20,
			'before_field' => '<p>',
			'after_field'  => '</p>'
		) );

		// Pseudo field for "login_form" action
		$this->add_field( 'lostpassword_form_action', 'general', array(
			'type' => 'custom'
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
			'value' => __( 'Get New Password' )
		) );

		// Redirect field
		$this->add_field( 'redirect_to', 'submit', array(
			'type'  => 'hidden',
			'value' => apply_filters( 'lostpassword_redirect', $this->get_option( 'redirect_to', Theme_My_Login::get_page_link( 'login', 'checkemail=confirm' ) ) )
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
			'form_name'    => 'lostpasswordform',
			'form_id'      => 'lostpasswordform',
			'form_method'  => 'post'
		) );
		return parent::get_form_html( $args );
	}
}
endif; // Class exists
