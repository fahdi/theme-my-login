<?php
/**
 * Holds the Theme My Login widget class
 *
 * @package Theme_My_Login
 */

if ( ! class_exists( 'Theme_My_Login_Widget' ) ) :
/*
 * Theme My Login widget class
 *
 * @since 6.0
 */
class Theme_My_Login_Widget extends WP_Widget {
	/**
	 * Constructor
	 *
	 * @since 6.0
	 * @access public
	 */
	public function __construct() {
		$widget_options = array(
			'classname'   => 'widget_theme_my_login',
			'description' => __( 'A login form for your blog.', 'theme-my-login' )
		);
		$this->WP_Widget( 'theme-my-login', __( 'Theme My Login', 'theme-my-login' ), $widget_options );
	}

	/**
	 * Displays the widget
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	public function widget( $args, $instance ) {

		$instance = wp_parse_args( $instance, array(
			'logged_in_widget'  => true,
			'logged_out_widget' => true,
			'show_title'        => true,
			'show_reg_link'     => true,
			'show_pass_link'    => true,
			'show_gravatar'     => true,
			'gravatar_size'     => 50
		) );

		// Show if logged in?
		if ( is_user_logged_in() && ! $instance['logged_in_widget'] )
			return;

		// Show if logged out?
		if ( ! is_user_logged_in() && ! $instance['logged_out_widget'] )
			return;

		$args = array_merge( $args, $instance );

		theme_my_login( $args );
	}

	/**
	* Updates the widget
	*
	* @since 6.0
	* @access public
	*/
	public function update( $new_instance, $old_instance ) {
		$instance['logged_in_widget']  = ! empty( $new_instance['logged_in_widget'] );
		$instance['logged_out_widget'] = ! empty( $new_instance['logged_out_widget'] );
		$instance['show_title']        = ! empty( $new_instance['show_title'] );
		$instance['show_reg_link']     = ! empty( $new_instance['show_reg_link'] );
		$instance['show_pass_link']    = ! empty( $new_instance['show_pass_link'] );
		$instance['show_gravatar']     = ! empty( $new_instance['show_gravatar'] );
		$instance['gravatar_size']     = absint( $new_instance['gravatar_size'] );
		return $instance;
	}

	/**
	* Displays the widget admin form
	*
	* @since 6.0
	* @access public
	*/
	public function form( $instance ) {
		$instance = wp_parse_args( $instance, array(
			'logged_in_widget'  => true,
			'logged_out_widget' => true,
			'show_title'        => true,
			'show_reg_link'     => true,
			'show_pass_link'    => true,
			'show_gravatar'     => true,
			'gravatar_size'     => 50,
		) );
		?>
		<p>
			<input name="<?php echo $this->get_field_name( 'logged_in_widget' ); ?>" type="checkbox" id="<?php echo $this->get_field_id( 'logged_in_widget' ); ?>" value="1"<?php checked( $instance['logged_in_widget'] ); ?> />
			<label for="<?php echo $this->get_field_id( 'logged_in_widget' ); ?>"><?php _e( 'Show When Logged In', 'theme-my-login' ); ?></label>
		</p>

		<p>
			<input name="<?php echo $this->get_field_name( 'logged_out_widget' ); ?>" type="checkbox" id="<?php echo $this->get_field_id( 'logged_out_widget' ); ?>" value="1"<?php checked( $instance['logged_out_widget'] ); ?> />
			<label for="<?php echo $this->get_field_id( 'logged_out_widget' ); ?>"><?php _e( 'Show When Logged Out', 'theme-my-login' ); ?></label>
		</p>

		<p>
			<input name="<?php echo $this->get_field_name( 'show_title' ); ?>" type="checkbox" id="<?php echo $this->get_field_id( 'show_title' ); ?>" value="1"<?php checked( $instance['show_title'] ); ?> />
			<label for="<?php echo $this->get_field_id( 'show_title' ); ?>"><?php _e( 'Show Title', 'theme-my-login' ); ?></label>
		</p>

		<p>
			<input name="<?php echo $this->get_field_name( 'show_reg_link' ); ?>" type="checkbox" id="<?php echo $this->get_field_id( 'show_reg_link' ); ?>" value="1"<?php checked( $instance['show_reg_link'] ); ?> />
			<label for="<?php echo $this->get_field_id( 'show_reg_link' ); ?>"><?php _e( 'Show Register Link', 'theme-my-login' ); ?></label>
		</p>

		<p>
			<input name="<?php echo $this->get_field_name( 'show_pass_link' ); ?>" type="checkbox" id="<?php echo $this->get_field_id( 'show_pass_link' ); ?>" value="1"<?php checked( $instance['show_pass_link'] ); ?> />
			<label for="<?php echo $this->get_field_id( 'show_pass_link' ); ?>"><?php _e( 'Show Lost Password Link', 'theme-my-login' ); ?></label>
		</p>

		<p>
			<input name="<?php echo $this->get_field_name( 'show_gravatar' ); ?>" type="checkbox" id="<?php echo $this->get_field_id( 'show_gravatar' ); ?>" value="1"<?php checked( $instance['show_gravatar'] ); ?> />
			<label for="<?php echo $this->get_field_id( 'show_gravatar' ); ?>"><?php _e( 'Show Gravatar', 'theme-my-login' ); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'gravatar_size' ); ?>"><?php _e( 'Gravatar Size', 'theme-my-login' ); ?>:</label>
			<input name="<?php echo $this->get_field_name( 'gravatar_size' ); ?>" type="text" id="<?php echo $this->get_field_id( 'gravatar_size' ); ?>" value="<?php echo $instance['gravatar_size']; ?>" size="3" />
		</p>
		<?php
	}
}
endif; // Class exists

