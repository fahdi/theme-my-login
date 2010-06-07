<div class="login" id="theme-my-login<?php $template->the_current_instance(); ?>">
	<?php $template->the_action_message( 'register' ); ?>
	<?php $template->the_errors(); ?>
    <form name="registerform" id="registerform<?php $template->the_current_instance(); ?>" action="<?php $template->the_action_url( 'register' ); ?>" method="post">
        <p>
            <label for="user_login<?php $template->the_current_instance(); ?>"><?php _e( 'Username', 'theme-my-login' ) ?></label>
            <input type="text" name="user_login" id="user_login<?php $template->the_current_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'user_login' ); ?>" size="20" />
        </p>
        <p>
            <label for="user_email<?php $template->the_current_instance(); ?>"><?php _e( 'E-mail', 'theme-my-login' ) ?></label>
            <input type="text" name="user_email" id="user_email<?php $template->the_current_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'user_email' ); ?>" size="20" />
        </p>
<?php do_action_ref_array( 'register_form', array( &$template ) ); ?>
        <p class="submit">
            <input type="submit" name="wp-submit" id="wp-submit<?php $template->the_current_instance(); ?>" value="<?php _e( 'Register', 'theme-my-login' ); ?>" />
        </p>
    </form>
	<?php $template->the_action_links( array( 'register' => false ) ); ?>
</div>