            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Plugin', 'theme-my-login'); ?></th>
                    <td>
                        <input name="uninstall" type="checkbox" id="uninstall" value="1" <?php if ( $ThemeMyLogin->options['uninstall'] ) { echo 'checked="checked"'; } ?> />
                        <label for="uninstall"><?php _e('Uninstall', 'theme-my-login'); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Defaults', 'theme-my-login'); ?></th>
                    <td>
                        <input name="defaults" type="checkbox" id="defaults" value="1" />
                        <label for="defaults"><?php _e('Reset Defaults', 'theme-my-login'); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Page List', 'theme-my-login'); ?></th>
                    <td>
                        <input name="show_page" type="checkbox" id="show_page" value="1" <?php if ( $ThemeMyLogin->options['show_page'] ) { echo 'checked="checked"'; } ?> />
                        <label for="show_page"><?php _e('Show Login Page', 'theme-my-login'); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Registration', 'theme-my-login'); ?></th>
                    <td>
                        <input name="custom_pass" type="checkbox" id="custom_pass" value="1" <?php if ( $ThemeMyLogin->options['custom_pass'] ) { echo 'checked="checked"'; } ?> />
                        <label for="custom_pass"><?php _e('Allow Users To Set Their Own Password', 'theme-my-login'); ?></label>
                        <br />
                        <input name="moderate_users" type="checkbox" id="moderate_users" value="1" <?php if ( $ThemeMyLogin->options['moderate_users'] ) { echo 'checked="checked"'; } ?> />
                        <label for="moderate_users"><?php _e('New Users Must Approved', 'theme-my-login'); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="page_id"><?php _e('Page ID', 'theme-my-login'); ?></label></th>
                    <td>
                        <input name="page_id" type="text" id="page_id" value="<?php echo $ThemeMyLogin->options['page_id']; ?>" size="1" />
                        <span class="description"><strong>DO NOT</strong> change this unless you are <strong>ABSOLUTELY POSITIVE</strong> you know what you are doing!</span>
                    </td>
                </tr>
            </table>
