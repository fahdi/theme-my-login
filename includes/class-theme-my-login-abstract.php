<?php
/**
 * Holds the Theme My Login Abstract class
 *
 * @package Theme_My_Login
 */

if ( !class_exists( 'Theme_My_Login_Abstract' ) ) :
/*
 * Theme My Login Abstract class
 *
 * This class is the base class to be extended.
 *
 * @since 6.0
 */
abstract class Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key;

	/**
	 * Holds options array
	 *
	 * Extending classes should explicity define options here
	 * or create a method named default_options() which returns
	 * an array of options.
	 *
	 * @since 6.3
	 * @access protected
	 * @var object
	 */
	protected $options = array();

	/**
	 * Constructor
	 *
	 * @since 6.3
	 * @access public
	 */
	public function __construct() {
		$this->load_options();
		$this->load();
	}

	/**
	 * Called when object is constructed
	 *
	 * @since 6.3
	 * @access protected
	 */
	abstract protected function load();

	/**
	 * Loads options from DB
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param array|string
	 */
	public function load_options() {
		if ( method_exists( $this, 'default_options' ) )
			$this->options = (array) $this->default_options();

		if ( !$this->options_key )
			return;

		$options = get_option( $this->options_key, array() );
		$options = wp_parse_args( $options, $this->options );

		$this->options = $options;
	}

	/**
	 * Saves options to DB
	 *
	 * @since 6.3
	 * @access public
	 */
	public function save_options() {
		if ( $this->options_key )
			update_option( $this->options_key, $this->options );
	}

	/**
	 * Retrieves an option
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string|array $option Name of option to retrieve or an array of hierarchy for multidimensional options
	 * @param mixed $default Default value to return if $option is not set
	 * @return mixed Value of requested option or $default if option is not set
	 */
	public function get_option( $option, $default = false ) {
		$options = $this->options;
		$value = false;
		if ( is_array( $option ) ) {
			foreach ( $option as $_option ) {
				if ( !isset( $options[$_option] ) ) {
					$value = $default;
					break;
				}
				$options = $value = $options[$_option];
			}
		} else {
			$value = isset( $options[$option] ) ? $options[$option] : $default;
		}
		return $value;
	}

	/**
	 * Retrieves all options
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @return array Options
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Sets an option
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $option Name of option to set or an array of hierarchy for multidimensional options
	 * @param mixed $value Value of new option
	 */
	public function set_option( $option, $value = '' ) {
		if ( is_array( $option ) ) {
			$options = $this->options;
			$last = array_pop( $option );
			foreach ( $option as $_option ) {
				if ( !isset( $options[$_option] ) )
					$options[$_option] = array();
				$options = $options[$_option];
			}
			$options[$last] = $value;
			$this->options = array_merge( $this->options, $options );
		} else {
			$this->options[$option] = $value;
		}
	}

	/**
	 * Sets all options
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param array $options Options array
	 */
	public function set_options( $options ) {
		$this->options = (array) $options;
	}

	/**
	 * Deletes an option
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $option Name of option to delete
	 */
	public function delete_option( $option ) {
		if ( isset( $this->options[$option] ) )
			unset( $this->options[$option] );
	}
}
endif;

