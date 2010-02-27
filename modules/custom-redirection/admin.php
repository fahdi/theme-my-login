<?php

function jkf_tml_custom_redirect_admin_menu() {
	global $wp_roles;
	$parent = plugin_basename(TML_MODULE_DIR . '/custom-redirection/admin/options.php');
	jkf_tml_add_menu_page(__('Redirection', 'theme-my-login'), $parent);
	$user_roles = $wp_roles->get_names();
	foreach ( $user_roles as $role => $label ) {
		if ( 'pending' == $role )
			continue;
		jkf_tml_add_submenu_page($parent, translate_user_role($label), '', 'jkf_tml_custom_redirect_user_role_admin_page', array('role' => $role));
	}
}

function jkf_tml_custom_redirect_save_settings($settings) {
	return $settings;
}

function jkf_tml_custom_redirect_user_role_admin_page($args = '') {
	global $theme_my_login;
	extract($args);
	?>
<table class="form-table">
    <tr valign="top">
		<th scope="row"><?php _e('Log in'); ?></th>
        <td>
			<input name="theme_my_login[redirection][<?php echo $role; ?>][login_type]" type="radio" id="theme_my_login_redirection_<?php echo $role; ?>_login_type_default" value="default"<?php checked('default', $theme_my_login->options['redirection'][$role]['login_type']); ?> /> <label for="theme_my_login_redirection_<?php echo $role; ?>_login_type_default"><?php _e('Default', 'theme-my-login'); ?></label><br />
            <input name="theme_my_login[redirection][<?php echo $role; ?>][login_type]" type="radio" id="theme_my_login_redirection_<?php echo $role; ?>_login_type_referer" value="referer"<?php checked('referer', $theme_my_login->options['redirection'][$role]['login_type']); ?> /> <label for="theme_my_login_redirection_<?php echo $role; ?>_login_type_referer"><?php _e('Referer', 'theme-my-login'); ?></label><br />
			<input name="theme_my_login[redirection][<?php echo $role; ?>][login_type]" type="radio" id="theme_my_login_redirection_<?php echo $role; ?>_login_type_custom" value="custom"<?php checked('custom', $theme_my_login->options['redirection'][$role]['login_type']); ?> />
			<input name="theme_my_login[redirection][<?php echo $role; ?>][login_url]" type="text" id="theme_my_login_redirection_<?php echo $role; ?>_login_url" value="<?php echo $theme_my_login->options['redirection'][$role]['login_url']; ?>" class="regular-text" />
        </td>
    </tr>
    <tr valign="top">
		<th scope="row"><?php _e('Log out'); ?></th>
        <td>
			<input name="theme_my_login[redirection][<?php echo $role; ?>][logout_type]" type="radio" id="theme_my_login_redirection_<?php echo $role; ?>_logout_type_default" value="default"<?php checked('default', $theme_my_login->options['redirection'][$role]['logout_type']); ?> /> <label for="theme_my_login_redirection_<?php echo $role; ?>_logout_type_default"><?php _e('Default', 'theme-my-login'); ?></label><br />
            <input name="theme_my_login[redirection][<?php echo $role; ?>][logout_type]" type="radio" id="theme_my_login_redirection_<?php echo $role; ?>_logout_type_referer" value="referer"<?php checked('referer', $theme_my_login->options['redirection'][$role]['logout_type']); ?> /> <label for="theme_my_login_redirection_<?php echo $role; ?>_logout_type_referer"><?php _e('Referer', 'theme-my-login'); ?></label><br />
			<input name="theme_my_login[redirection][<?php echo $role; ?>][logout_type]" type="radio" id="theme_my_login_redirection_<?php echo $role; ?>_logout_type_custom" value="custom"<?php checked('custom', $theme_my_login->options['redirection'][$role]['logout_type']); ?> />
			<input name="theme_my_login[redirection][<?php echo $role; ?>][logout_url]" type="text" id="theme_my_login_redirection_<?php echo $role; ?>_logout_url" value="<?php echo $theme_my_login->options['redirection'][$role]['logout_url']; ?>" class="regular-text" />
        </td>
    </tr>
</table>
<?php
}

?>