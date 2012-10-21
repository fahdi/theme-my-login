<?php
/**
 * Plugin Name: reCAPTCHA
 * Description: Enabling this module will initialize reCAPTCHA. You will then have to configure the settings via the "reCAPTCHA" tab.
 *
 * Holds Theme My Login Recaptcha class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Recaptcha
 * @since 6.3
 */

if ( ! class_exists( 'Theme_My_Login_Recaptcha' ) ) :
/**
 * Theme My Login Custom Permalinks class
 *
 * Adds the ability to set permalinks for default actions.
 *
 * @since 6.3
 */
class Theme_My_Login_Recaptcha extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_recaptcha';

	/**
	 * Returns singleton instance
	 *
	 * @since 6.3
	 * @access public
	 * @return object
	 */
	public static function get_object() {
		return parent::get_object( __CLASS__ );
	}

	/**
	 * Returns default options
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @return array Default options
	 */
	public static function default_options() {
		return array(
			'public_key'  => '',
			'private_key' => '',
			'theme'       => 'red'
		);
	}

	/**
	 * Loads the module
	 *
	 * @since 6.3
	 * @access protected
	 */
	protected function load() {
		if ( ! ( $this->get_option( 'public_key' ) || $this->get_option( 'private_key' ) ) )
			return;

		add_action( 'register_form',       array( &$this, 'recaptcha_display'  ) );
		add_filter( 'registration_errors', array( &$this, 'recaptcha_validate' ) );

		if ( is_multisite() ) {
			add_action( 'signup_extra_fields',       array( &$this, 'recaptcha_display'  ) );
			add_filter( 'wpmu_validate_user_signup', array( &$this, 'recaptcha_validate' ) );
			add_filter( 'wpmu_validate_blog_signup', array( &$this, 'recaptcha_validate' ) );
		}
	}

	/**
	 * Displays reCAPTCHA
	 *
	 * @since 6.3
	 * @access public
	 */
	public function recaptcha_display( $errors = null ) {
		require_once( WP_PLUGIN_DIR . '/theme-my-login/modules/recaptcha/includes/recaptchalib.php' );
		?>
		<?php if ( $errors && ( $errmsg = $errors->get_error_message( 'recaptcha' ) ) ) : ?>
			<p class="error"><?php echo $errmsg ?></p>
		<?php endif; ?>
		<script type="text/javascript">
			var RecaptchaOptions = {
				theme: '<?php echo $this->get_option( 'theme' ); ?>'
			};
		</script>
		<?php echo recaptcha_get_html( $this->get_option( 'public_key' ) );
	}

	/**
	 * Validates reCAPTCHA
	 *
	 * @since 6.3
	 * @access public
	 */
	public function recaptcha_validate( $result ) {
		require_once( WP_PLUGIN_DIR . '/theme-my-login/modules/recaptcha/includes/recaptchalib.php' );

		$response = recaptcha_check_answer( $this->get_option( 'private_key' ), $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field'] );
		if ( ! $response->is_valid ) {
			if ( is_multisite() )
				$result['errors']->add( 'recaptcha', __( 'The CAPTCHA entered was incorrect', 'theme-my-login' ) );
			else
				$result->add( 'recaptcha', __( '<strong>ERROR</strong>: The CAPTCHA entered was incorrect', 'theme-my-login' ) );
		}

		return $result;
	}
}

Theme_My_Login_Recaptcha::get_object();

endif;

if ( is_admin() )
	include_once( dirname( __FILE__ ) . '/admin/recaptcha-admin.php' );

