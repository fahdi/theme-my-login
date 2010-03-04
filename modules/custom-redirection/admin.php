<?php

function wdbj_tml_custom_redirect_admin_menu() {
	global $wp_roles;
	$parent = plugin_basename(TML_MODULE_DIR . '/custom-redirection/admin/options.php');
	wdbj_tml_add_menu_page(__('Redirection', 'theme-my-login'), $parent);
	$user_roles = $wp_roles->get_names();
	foreach ( $user_roles as $role => $label ) {
		if ( 'pending' == $role )
			continue;
		wdbj_tml_add_submenu_page($parent, translate_user_role($label), '', 'wdbj_tml_custom_redirect_user_role_admin_page', array('role' => $role));
	}
}

function wdbj_tml_custom_redirect_user_role_admin_page($role) {
	$redirection = wdbj_tml_get_option('redirection', $role);
	?>
<table class="form-table">
    <tr valign="top">
		<th scope="row"><?php _e('Log in'); ?></th>
        <td>
			<input name="theme_my_login[redirection][<?php echo $role; ?>][login_type]" type="radio" id="theme_my_login_redirection_<?php echo $role; ?>_login_type_default" value="default"<?php checked('default', $redirection['login_type']); ?> /> <label for="theme_my_login_redirection_<?php echo $role; ?>_login_type_default"><?php _e('Default', 'theme-my-login'); ?></label><br />
            <input name="theme_my_login[redirection][<?php echo $role; ?>][login_type]" type="radio" id="theme_my_login_redirection_<?php echo $role; ?>_login_type_referer" value="referer"<?php checked('referer', $redirection['login_type']); ?> /> <label for="theme_my_login_redirection_<?php echo $role; ?>_login_type_referer"><?php _e('Referer', 'theme-my-login'); ?></label><br />
			<input name="theme_my_login[redirection][<?php echo $role; ?>][login_type]" type="radio" id="theme_my_login_redirection_<?php echo $role; ?>_login_type_custom" value="custom"<?php checked('custom', $redirection['login_type']); ?> />
			<input name="theme_my_login[redirection][<?php echo $role; ?>][login_url]" type="text" id="theme_my_login_redirection_<?php echo $role; ?>_login_url" value="<?php echo $redirection['login_url']; ?>" class="regular-text" />
        </td>
    </tr>
    <tr valign="top">
		<th scope="row"><?php _e('Log out'); ?></th>
        <td>
			<input name="theme_my_login[redirection][<?php echo $role; ?>][logout_type]" type="radio" id="theme_my_login_redirection_<?php echo $role; ?>_logout_type_default" value="default"<?php checked('default', $redirection['logout_type']); ?> /> <label for="theme_my_login_redirection_<?php echo $role; ?>_logout_type_default"><?php _e('Default', 'theme-my-login'); ?></label><br />
            <input name="theme_my_login[redirection][<?php echo $role; ?>][logout_type]" type="radio" id="theme_my_login_redirection_<?php echo $role; ?>_logout_type_referer" value="referer"<?php checked('referer', $redirection['logout_type']); ?> /> <label for="theme_my_login_redirection_<?php echo $role; ?>_logout_type_referer"><?php _e('Referer', 'theme-my-login'); ?></label><br />
			<input name="theme_my_login[redirection][<?php echo $role; ?>][logout_type]" type="radio" id="theme_my_login_redirection_<?php echo $role; ?>_logout_type_custom" value="custom"<?php checked('custom', $redirection['logout_type']); ?> />
			<input name="theme_my_login[redirection][<?php echo $role; ?>][logout_url]" type="text" id="theme_my_login_redirection_<?php echo $role; ?>_logout_url" value="<?php echo $redirection['logout_url']; ?>" class="regular-text" />
        </td>
    </tr>
</table>
<?php
}

?>
