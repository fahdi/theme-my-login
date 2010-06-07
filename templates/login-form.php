<div class="login" id="theme-my-login<?php $template->the_current_instance(); ?>">
	<?php $template->the_action_message( 'login' ); ?>
	<?php $template->the_errors(); ?>
	<form name="loginform" id="loginform<?php $template->the_current_instance(); ?>" action="<?php $template->the_action_url( 'login' ); ?>" method="post">
		<p>
			<label for="log<?php $template->the_current_instance(); ?>"><?php _e( 'Username', 'theme-my-login' ) ?></label>
			<input type="text" name="log" id="log<?php $template->the_current_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'log' ); ?>" size="20" />
		</p>
		<p>
			<label for="pwd<?php $template->the_current_instance(); ?>"><?php _e( 'Password', 'theme-my-login' ) ?></label>
			<input type="password" name="pwd" id="pwd<?php $template->the_current_instance(); ?>" class="input" value="" size="20" />
		</p>
<?php do_action_ref_array( 'login_form', array( &$template ) ); ?>
		<p class="forgetmenot">
			<input name="rememberme" type="checkbox" id="rememberme<?php $template->the_current_instance(); ?>" value="forever" />
			<label for="rememberme<?php $template->the_current_instance(); ?>"><?php _e( 'Remember Me', 'theme-my-login' ); ?></label>
		</p>
		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit<?php $template->the_current_instance(); ?>" value="<?php _e( 'Log In', 'theme-my-login' ); ?>" />
			<input type="hidden" name="redirect_to" value="<?php $template->the_redirect_url(); ?>" />
			<input type="hidden" name="testcookie" value="1" />
		</p>
	</form>
	<?php $template->the_action_links( array( 'login' => false ) ); ?>
</div>