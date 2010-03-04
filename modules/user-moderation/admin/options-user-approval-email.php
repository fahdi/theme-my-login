<table class="form-table">
    <tr>
		<td>
			<p><em><?php _e('Available Variables', 'theme-my-login'); ?>: %blogname%, %siteurl%, %user_login%, %user_email%, %user_pass%</em></p>
			<label for="theme_my_login_user_approval_title"><?php _e('Subject', 'theme-my-login'); ?></label><br />
			<input name="theme_my_login[email][user_approval][title]" type="text" id="theme_my_login_user_approval_title" value="<?php if ( isset($theme_my_login->options['email']['user_approval']['title']) ) echo $theme_my_login->options['email']['user_approval']['title']; ?>" class="full-text" /><br />
			<label for="theme_my_login_user_approval_message"><?php _e('Message', 'theme-my-login'); ?></label><br />
			<textarea name="theme_my_login[email][user_approval][message]" id="theme_my_login_user_approval_message" class="large-text" rows="10"><?php if ( isset($theme_my_login->options['email']['user_approval']['message']) ) echo $theme_my_login->options['email']['user_approval']['message']; ?></textarea><br />
		</td>
	</tr>
</table>