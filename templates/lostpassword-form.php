<div class="login" id="theme-my-login<?php $template->the_current_instance(); ?>">
	<?php $template->the_action_message( 'lostpassword' ); ?>
	<?php $template->the_errors(); ?>
	<form name="lostpasswordform" id="lostpasswordform<?php $template->the_current_instance(); ?>" action="<?php $template->the_action_url( 'lostpassword' ); ?>" method="post">
		<p>
			<label for="user_login<?php $template->the_current_instance(); ?>"><?php _e( 'Username or E-mail:', 'theme-my-login' ) ?></label>
			<input type="text" name="user_login" id="user_login<?php $template->the_current_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'user_login' ); ?>" size="20" />
		</p>
<?php do_action_ref_array( 'lostpassword_form', array( &$template ) ); ?>
		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit<?php $template->the_current_instance(); ?>" value="<?php _e( 'Get New Password', 'theme-my-login' ); ?>" />
		</p>
	</form>
	<?php $template->the_action_links( array( 'lostpassword' => false ) ); ?>
</div>