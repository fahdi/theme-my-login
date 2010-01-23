            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Passwords', 'theme-my-login'); ?></th>
                    <td>
                        <input name="custom_pass" type="radio" id="custom_pass_off" value="0" <?php if ( 0 == $ThemeMyLogin->options['custom_pass'] ) { echo 'checked="checked"'; } ?> />
                        <label for="custom_pass_off"><?php _e('Auto-Generated', 'theme-my-login'); ?></label><br />
                        <input name="custom_pass" type="radio" id="custom_pass_on" value="1" <?php if ( 1 == $ThemeMyLogin->options['custom_pass'] ) { echo 'checked="checked"'; } ?> />
                        <label for="custom_pass_on"><?php _e('Custom', 'theme-my-login'); ?></label><br />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('User Moderation', 'theme-my-login'); ?></th>
                    <td>
                        <input name="moderation" type="radio" id="moderation_none" value="none" <?php if ( 'none' == $ThemeMyLogin->options['moderation'] ) { echo 'checked="checked"'; } ?> />
                        <label for="moderation_none"><?php _e('None', 'theme-my-login'); ?></label><br />
                        <input name="moderation" type="radio" id="moderation_email" value="email" <?php if ( 'email' == $ThemeMyLogin->options['moderation'] ) { echo 'checked="checked"'; } ?> />
                        <label for="moderation_email"><?php _e('E-mail Confirmation', 'theme-my-login'); ?></label><br />
                        <input name="moderation" type="radio" id="moderation_admin" value="admin" <?php if ( 'admin' == $ThemeMyLogin->options['moderation'] ) { echo 'checked="checked"'; } ?> />
                        <label for="moderation_admin"><?php _e('Admin Approval', 'theme-my-login'); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Stylesheet', 'theme-my-login'); ?></th>
                    <td>
                        <input name="use_css" type="checkbox" id="use_css" value="1" <?php if ( $ThemeMyLogin->options['use_css'] ) { echo 'checked="checked"'; } ?> />
                        <label for="use_css"><?php _e('Use theme-my-login.css', 'theme-my-login'); ?></label>
                        <p class="description"><?php _e('In order to keep changes between upgrades, you can store your customized "theme-my-login.css" in your current theme directory.', 'theme-my-login'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Template Tag', 'theme-my-login'); ?></th>
                    <td>
                        <input name="template_tag" type="checkbox" id="template_tag" value="1" <?php if ( $ThemeMyLogin->options['template_tag'] ) { echo 'checked="checked"'; } ?> />
                        <label for="template_tag"><?php _e('Enable Template Tag', 'theme-my-login'); ?></label>
                        <p class="description"><?php _e('Enable this setting to activate the template tag. If you do not intend to use the template tag, leave this disabled for optimization.', 'theme-my-login'); ?></p>
                    </td>
                </tr>
            </table>
