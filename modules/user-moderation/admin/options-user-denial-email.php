<table class="form-table">
    <tr>
		<td>
			<p><em><?php _e('Available Variables', 'theme-my-login'); ?>: %blogname%, %siteurl%, %user_login%, %user_email%</em></p>
			<label for="theme_my_login_user_denial_title"><?php _e('Subject', 'theme-my-login'); ?></label><br />
			<input name="theme_my_login[email][user_denial][title]" type="text" id="theme_my_login_user_denial_title" value="<?php echo $theme_my_login->options['email']['user_denial']['title']; ?>" class="full-text" /><br />
			<label for="theme_my_login_user_denial_message"><?php _e('Message', 'theme-my-login'); ?></label><br />
			<textarea name="theme_my_login[email][user_denial][message]" id="theme_my_login_user_denial_message" class="large-text" rows="10"><?php echo $theme_my_login->options['email']['user_denial']['message']; ?></textarea><br />
		</td>
	</tr>
</table>