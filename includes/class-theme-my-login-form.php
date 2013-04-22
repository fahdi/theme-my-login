<?php
/**
 * Holds the Theme My Login Form class
 *
 * @package Theme_My_Login
 * @since 6.4
 */

if ( ! class_exists( 'Theme_My_Login_Form' ) ) :
/*
 * Theme My Login form class
 *
 * This class contains properties and methods common to form creation.
 *
 * @since 6.4
 */
abstract class Theme_My_Login_Form extends Theme_My_Login_Abstract {
	/**
	 * Holds form action
	 *
	 * @since 6.4
	 * @var string
	 */
	public $action;

	/**
	 * Holds the form errors
	 *
	 * @since 6.4
	 * @var WP_Error
	 */
	public $errors;

	/**
	 * Holds form fields
	 *
	 * @since 6.4
	 * @var array
	 */
	private $fields = array();

	/**
	 * Holds form field groups
	 *
	 * @since 6.4
	 * @var array
	 */
	private $groups = array();

	/**
	 * Retrieves default options
	 *
	 * @since 6.4
	 *
	 * @return array Default options
	 */
	public static function default_options() {
		return array(
			'instance'       => 0,
			'show_title'     => true,
			'show_log_link'  => true,
			'show_reg_link'  => true,
			'show_pass_link' => true,
			'before_widget'  => '',
			'after_widget'   => '',
			'before_title'   => '',
			'after_title'    => '',
			'title'          => '',
			'message'        => ''
		);
	}

	/**
	 * Constructor
	 *
	 * @since 6.4
	 */
	public function __construct( $args = array() ) {
		// Parse options
		$args = shortcode_atts( $this->default_options(), wp_parse_args( $args ) );

		// Set options
		$this->set_options( $args );

		// Set title
		if ( ! $this->get_option( 'title' ) )
			$this->set_option( 'title', Theme_My_Login::get_page_title( $this->action ) );

		// Build the form
		$this->build();

		do_action_ref_array( 'tml_build_form', array( &$this ) );
	}

	/**
	 * Builds the form
	 *
	 * Must be overridden by subclasses
	 *
	 * @since 6.4
	 */
	abstract function build();

	/**
	 * Adds a field to the form
	 *
	 * @since 6.4
	 *
	 * @param string $name Name of field
	 * @param string $group Name of group
	 * @param array $args Field arguments
	 */
	public function add_field( $name, $group = 'general', $args = array() ) {
		$defaults = array(
			'type'     => 'text',
			'id'       => '',
			'label'    => '',
			'class'    => '',
			'size'     => '',
			'value'    => '',
			'selected' => '',
			'checked'  => '',
			'options'  => '',
			'hint'     => '',
			'html'     => '',
			'group'    => ''
		);
		$args = wp_parse_args( $args, $defaults );

		$args['name'] = $name;

		$name = sanitize_key( $name );

		$this->fields[$name] = (object) $args;

		if ( $group ) {
			if ( ! $this->get_group( $group ) )
				$this->add_group( $group );

			$this->add_field_to_group( $name, $group );
		}
	}

	/**
	 * Returns field object
	 *
	 * @since 6.4
	 *
	 * @param string $name Field name
	 * @return object Field object
	 */
	public function &get_field( $name ) {
		if ( isset( $this->fields[$name] ) )
			return $this->fields[$name];
		return null;
	}

	/**
	 * Removes field object
	 *
	 * @since 6.4
	 *
	 * @param string $name Field name
	 */
	public function remove_field( $name ) {
		if ( $field = $this->get_field( $name ) ) {
			// Remove field from groups
			foreach ( $this->groups as $group ) {
				$this->remove_field_from_group( $field->name, $group->name );
			}
			unset( $this->fields[$name] );
		}
	}

	/**
	 * Adds a field group to the form
	 *
	 * @since 6.4
	 *
	 * @param string $name Group name
	 * @param array $args Group arguments
	 */
	public function add_group( $name, $args = array() ) {
		$defaults = array(
			'title'       => '',
			'description' => '',
			'fields'      => array()
		);
		$args = wp_parse_args( $args, $defaults );

		$args['name'] = $name;

		$name = sanitize_key( $name );

		if ( ! isset( $this->groups[$name] ) )
			$this->groups[$name] = (object) $args;
	}

	/**
	 * Adds a field to an existing group
	 *
	 * @since 6.4
	 *
	 * @param string $field Field name
	 * @param string $group Group name
	 * @return bool False if group or field does not exist; true otherwise
	 */
	public function add_field_to_group( $field, $group ) {
		$field = $this->get_field( $field );
		$group = $this->get_group( $group );
		if ( ! $field || ! $group )
			return false;

		if ( ! in_array( $field->name, $group->fields ) )
			$group->fields[] = $field->name;

		return true;
	}

	/**
	 * Removes a field from a group
	 *
	 * @since 6.4
	 *
	 * @param string $field Field name
	 * @param string $group Group name
	 * @return bool False if group or field does not exist; true otherwise
	 */
	public function remove_field_from_group( $field, $group ) {
		$field = $this->get_field( $field );
		$group = $this->get_group( $group );
		if ( ! $field || ! $group )
			return false;

		if ( in_array( $field->name, $group->fields ) ) {
			$key = array_search( $field->name, $group->fields );
			unset( $group->fields[$key] );
		}

		return true;
	}

	/**
	 * Returns group object
	 *
	 * @since 6.4
	 *
	 * @param string $name Group name
	 * @return object Group object
	 */
	public function &get_group( $name ) {
		$null = null;
		if ( isset( $this->groups[$name] ) )
			return $this->groups[$name];
		return $null;
	}

	/**
	 * Removes group object
	 *
	 * @since 6.4
	 *
	 * @param string $name Group name
	 */
	public function remove_group( $name ) {
		if ( isset( $this->groups[$name] ) )
			unset( $this->groups[$name] );
	}

	/**
	 * Retreives field HTML
	 *
	 * @since 6.4
	 *
	 * @param string $name Field name
	 * @param array $args Output args
	 * @return string HTML output of form field
	 */
	public function get_field_html( $name, $args = array() ) {

		if ( ! $field = $this->get_field( $name ) )
			return;

		$defaults = array(
			'before_field'   => '',
			'after_field'    => '',
			'before_label'   => '',
			'after_label'    => '',
			'before_element' => '',
			'after_element'  => ''
		);
		$args = wp_parse_args( $args, $defaults );

		// Let fields override global form args
		$args = shortcode_atts( $args, get_object_vars( $field ) );
		extract( $args );

		$html = sprintf( $before_field, "form-field_{$field->name}" );

		if ( ! empty( $field->label ) )
			$html .= sprintf( $before_label, "form-label_{$field->name}" ) . '<label for="' . esc_attr( $field->id ) . '">' . $field->label . '</label>' . $after_label;

		$html .= sprintf( $before_element, "form-input_{$field->name}" );

		if ( ! empty( $field->html ) ) {
			$html .= $field->html;
		} elseif ( ! empty( $field->do_action ) ) {
			ob_start();
			do_action( $field->do_action );
			$html .= ob_get_contents();
			ob_end_clean();
		} else {
			switch ( $field->type ) {
				case 'select' :
					$html .= '<select' . $this->get_attr_html( $field ) . '>';
					foreach ( (array) $field->options as $value => $label ) {
						$html .= '<option value="' . $value . '"';
						if ( $field->selected || ( $field->value && $value == $field->value ) )
							$html .= ' selected="selected"';
						$html .= '>' . $label . '</option>';
					}
					$html .= '</select>';
					break;
				case 'radio' :
					$options = array();
					foreach ( (array) $field->options as $value => $label ) {
						$field_id = $field->id . '_' . $value;
						$options[$value] = '<input type="radio"' . $this->get_attr_html( $field );
						if ( $field->checked || ( $field->value && $value == $field->value ) )
							$options[$value] .= ' checked="checked"';
						$options[$value] .= ' /><label for="' . esc_attr( $field_id ) . '">' . $label . '</label>';
					}
					$html .= implode( $options, ! empty( $field->separator ) ? $field->separator : ' ' );
					break;
				case 'checkbox' :
					$html .= '<input type="checkbox"' . $this->get_attr_html( $field ) . ' />';
					$checkbox_label = empty( $field->checkbox_label ) ? $field->label : $field->checkbox_label;
					$html .= '<label for="' . esc_attr( $field->id ) . '">' . $checkbox_label . '</label>';
					break;
				case 'textarea' :
					$html .= '<textarea' . $this->get_attr_html( $field ) . '>' . $field->value . '</textarea>';
					break;
				case 'submit' :
				case 'reset' :
				case 'button' :
				case 'image' :
				case 'text' :
				case 'password' :
				case 'hidden' :
					switch ( $field->name ) {
						case 'redirect_to' :
							$field->value = apply_filters( 'tml_redirect_url', $field->value, $this->action );
							break;
					}
				case 'file' :
					$html .= '<input type="' . esc_attr( $field->type ) . '"' . $this->get_attr_html( $field ) . ' />';
					break;
				default :
					$html .= apply_filters_ref_array( 'tml_form_custom_field_html', array( '', $name, &$this ) );
			}

			if ( ! empty( $field->hint ) )
				$html .= '<span class="hint">' . $field->hint . '</span>';
		}

		$html .= $after_element . $after_field;

		return apply_filters_ref_array( 'tml_form_field_html', array( $html, $name, $args, &$this ) );
	}

	/**
	 * Retrieves HTML output for all fields within a group
	 *
	 * @since 6.4
	 *
	 * @param string $name Group name
	 * @param array $args Output args
	 * @return string HTML output of group fields
	 */
	public function get_group_html( $name, $args = array() ) {

		if ( ! $group = $this->get_group( $name ) )
			return;

		$defaults = array(
			'before_group' => '',
			'after_group'  => '',
			'before_title' => '<h4>',
			'after_title'  => '</h4>'
		);
		$args = wp_parse_args( $args, $defaults );

		// Let groups override global form args
		$args = shortcode_atts( $args, get_object_vars( $group ) );
		extract( $args );

		$html = sprintf( $before_group, "form-group_{$group->name}" );

		if ( ! empty( $group->title ) )
			$html .= $before_title . $group->title . $after_title;

		if ( ! empty( $group->description ) )
			$html .= '<p class="description">' . $group->description . '</p>';

		foreach ( (array) $group->fields as $field ) {
			$html .= $this->get_field_html( $field, $args );
		}
		$html .= $after_group;

		return $html;
	}

	/**
	 * Retrieves HTML output for all fields within the form
	 *
	 * @since 6.4
	 *
	 * @param array $args Output args
	 * @return string HTML output of form
	 */
	public function get_form_html( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'form_name'     => '',
			'form_id'       => '',
			'form_action'   => '',
			'form_method'   => '',
			'form_enctype'  => '',
			'before_form'   => '',
			'after_form'    => '',
			'before_fields' => '',
			'after_fields'  => '',
			'group_fields'  => true
		) );
		$args = apply_filters_ref_array( 'tml_form_args', array( $args, &$this ) );
		extract( $args );

		$html = sprintf( $before_form, "form-container_{$form_name}" );

		if ( $this->get_option( 'show_title' ) )
			$html .= $this->get_option( 'before_title' ) . $this->get_option( 'title' ) . $this->get_option( 'after_title' );

		if ( $message = $this->get_option( 'message' ) )
			$html .= '<p class="message">' . apply_filters( 'tml_action_template_message', apply_filters( 'login_message', $message ), $this->action ) . '</p>';

		$html .= $this->get_error_html();

		$html .= '<form action="' . $form_action . '"';
		if ( $form_name )
			$html .= ' name="' . $form_name . '"';
		if ( $form_id )
			$html .= ' id="' . $form_id . '"';
		if ( $form_method )
			$html .= ' method="' . $form_method . '"';
		if ( $form_enctype )
			$html .= ' enctype="' . $form_enctype . '"';
		$html .= '>' . $before_fields;

		if ( $group_fields && ! empty( $this->groups ) ) {
			foreach ( $this->groups as $group ) {
				$html .= $this->get_group_html( $group->name, $args );
			}
		} else {
			foreach ( $this->fields as $field ) {
				$html .= $this->get_field_html( $field->name, $args );
			}
		}

		$html .= $after_fields . '</form>' . $this->get_action_link_html() . $after_form;

		return $html;
	}

	/**
	 * Outputs HTML for all fields within the form
	 *
	 * @since 6.4
	 *
	 * @param array $args Output args
	 */
	public function display( $args = array() ) {
		echo $this->get_form_html( $args );
	}

	/**
	 * Retrieves HTML output for errors
	 *
	 * @since 6.4
	 *
	 * @return string Error HTML
	 */
	public function get_error_html() {
		$html = '';
		if ( is_wp_error( $this->errors ) ) {
			$errors = '';
			$messages = '';
			foreach ( $this->errors->get_error_codes() as $code ) {
				$severity = $this->errors->get_error_data( $code );
				foreach ( $this->errors->get_error_messages( $code ) as $error ) {
					if ( 'message' == $severity )
						$messages .= '    ' . $error . "<br />\n";
					else
						$errors .= '    ' . $error . "<br />\n";
				}
			}
			if ( ! empty( $errors ) )
				$html .= '<p class="error">' . apply_filters( 'login_errors', $errors ) . "</p>\n";
			if ( ! empty( $messages ) )
				$html .= '<p class="message">' . apply_filters( 'login_messages', $messages ) . "</p>\n";
		}
		return $html;
	}

	/**
	 * Retrieves HTML output for action links
	 *
	 * @since 6.4
	 *
	 * @return string Action link HTML
	 */
	public function get_action_link_html() {
		$action_links = array();

		if ( 'login' != $this->action && $this->get_option( 'show_log_link' ) ) {
			$action_links[] = array(
				'title' => Theme_My_Login::get_page_title( 'login' ),
				'url'   => Theme_My_Login::get_page_link( 'login'  )
			);
		}

		if ( 'register' != $this->action && $this->get_option( 'show_reg_link' ) && get_option( 'users_can_register' ) ) {
			$action_links[] = array(
				'title' => Theme_My_Login::get_page_title( 'register' ),
				'url'   => Theme_My_Login::get_page_link( 'register'  )
			);
		}

		if ( ! in_array( $this->action, array( 'lostpassword', 'resetpass' ) ) && $this->get_option( 'show_pass_link' ) ) {
			$action_links[] = array(
				'title' => Theme_My_Login::get_page_title( 'lostpassword' ),
				'url'   => Theme_My_Login::get_page_link( 'lostpassword'  )
			);
		}

		$action_links = apply_filters( 'tml_action_links', $action_links );

		$html = '';
		if ( $action_links ) {
			$html .= '<ul class="tml-action-links">' . "\n";
			foreach ( (array) $action_links as $link ) {
				$html .= '<li><a href="' . esc_url( $link['url'] ) . '" rel="nofollow">' . esc_html( $link['title'] ) . '</a></li>' . "\n";
			}
			$html .= '</ul>' . "\n";
		}
		return $html;
	}

	/**
	 * Returns a string of HTML attributes
	 *
	 * @since 6.4
	 *
	 * @param array $attr Array of attributes to parse
	 * @return string HTML output of attributes
	 */
	public function get_attr_html( $attr ) {

		$valid_attr = apply_filters( 'tml_form_field_valid_attr', array( 'name', 'id', 'value', 'class', 'style', 'rows', 'cols', 'size', 'selected', 'checked' ) );

		$html = '';
		foreach ( $attr as $name => $value ) {
			if ( empty( $value ) )
				continue;

			if ( ! in_array( $name, $valid_attr ) )
				continue;

			$html .= " $name=" . '"' . esc_attr( $value ) . '"';
		}
		return $html;
	}

	public function get_instance() {
		if ( $instance = $this->get_option( 'instance' ) )
			return $instance;
	}
}
endif; // Class exists
