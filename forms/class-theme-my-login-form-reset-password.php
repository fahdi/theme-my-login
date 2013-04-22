<?php
/**
 * Holds the Reset Password form
 *
 * @package Theme_My_Login
 * @since 6.4
 */

if ( ! class_exists( 'Theme_My_Login_Form_Reset_Password' ) ) :
/**
 * The Reset Password form class
 *
 * @since 6.4
 */
final class Theme_My_Login_Form_Reset_Password extends Theme_My_Login_Form {
	/**
	 * Holds form action
	 *
	 * @since 6.4
	 * @var string
	 */
	public $action = 'resetpass';

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
			'message'        => __( 'Enter your new password below.' )
		) );
	}

	/**
	 * Builds the form
	 *
	 * @since 6.4
	 */
	public function build() {
		// Password field
		$this->add_field( 'pass1', 'general', array(
			'type'         => 'password',
			'id'           => 'pass1',
			'label'        => __( 'New password' ),
			'class'        => 'input',
			'size'         => 20,
			'autocomplete' => 'off',
			'before_field' => '<p>',
			'after_field'  => '</p>'
		) );

		// Confirm password field
		$this->add_field( 'pass2', 'general', array(
			'type'         => 'password',
			'id'           => 'pass2',
			'label'        => __( 'Confirm new password' ),
			'class'        => 'input',
			'size'         => 20,
			'autocomplete' => 'off',
			'before_field' => '<p>',
			'after_field'  => '</p>'
		) );

		// Pass strength field
		$this->add_field( 'pass-strength-result', 'general', array(
			'type' => 'custom',
			'html' =>
			'<div id="pass-strength-result" class="hide-if-no-js">' . __( 'Strength indicator' ) . '</div>' .
			'<p class="description indicator-hint">' .
				__( 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).' ) .
			'</p>'
		) );

		// Do "resetpassword_form" action
		$this->add_field( 'resetpassword_form_action', 'general', array(
			'type' => 'hook',
			'hook' => 'do_action',
			'args' => array( 'resetpassword_form' )
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

		// Key (hidden) field
		$this->add_field( 'key', 'submit', array(
			'type'  => 'hidden',
			'value' => isset( $_REQUEST['key'] ) ? $_REQUEST['key'] : ''
		) );

		// Login (hidden) field
		$this->add_field( 'login', 'submit', array(
			'type'  => 'hidden',
			'id'    => 'user_login',
			'value' => isset( $_REQUEST['login'] ) ? $_REQUEST['login'] : ''
		) );

		// Instance (hidden) field
		$this->add_field( 'instance', 'submit', array(
			'type'  => 'hidden',
			'value' => $this->get_option( 'instance' )
		) );

		// Action (hidden) field
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
			'form_name'    => 'resetpasswordform',
			'form_id'      => 'resetpasswordform',
			'form_method'  => 'post'
		) );
		return parent::get_form_html( $args );
	}
}
endif; // Class exists
