            <ul class="tabs-nav">
                <li><a href="#fragment-5-1">General</a></li>
                <li><a href="#fragment-5-2">New Registration</a></li>
                <li><a href="#fragment-5-3">Password Retrieval</a></li>
                <li><a href="#fragment-5-4">Password Reset</a></li>
            </ul>

            <div id="fragment-5-1" class="tabs-div">
                <table class="form-table">
                    <tr valign="top">
                        <td>
                            <label for="email_from_name"><?php _e('From Name', 'theme-my-login'); ?></label><br />
                            <input name="email_from_name" type="text" id="email_from_name" value="<?php echo htmlspecialchars($ThemeMyLogin->options['email_from_name']); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <td>
                            <label for="email_from"><?php _e('From E-mail', 'theme-my-login'); ?></label><br />
                            <input name="email_from" type="text" id="email_from" value="<?php echo htmlspecialchars($ThemeMyLogin->options['email_from']); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <td>
                        <label for"email_content_type"><?php _e('E-mail Format', 'theme-my-login'); ?></label><br />
                        <select name="email_content_type" id="email_content_type">
                        <option value="text/plain"<?php if ('text/plain' == $ThemeMyLogin->options['email_content_type']) echo ' selected="selected"'; ?>>Plain Text</option>
                        <option value="text/html"<?php if ('text/html' == $ThemeMyLogin->options['email_content_type']) echo ' selected="selected"'; ?>>HTML</option>
                        </select>
                        </td>
                    </tr>
                </table>
            </div>

            <div id="fragment-5-2" class="tabs-div">
                <table class="form-table">
                    <tr>
                        <td>
                            <p><em>Avilable Variables: %blogname%, %siteurl%, %user_login%, %user_email%, %user_pass%, %user_ip%</em></p>
                            Subject<br />
                            <input name="registration_subject" type="text" id="registration_subject" value="<?php echo htmlspecialchars($ThemeMyLogin->options['registration_email']['subject']); ?>" class="full-text" /><br />
                            Message<br />
                            <textarea name="registration_message" id="registration_message" class="large-text"><?php echo htmlspecialchars($ThemeMyLogin->options['registration_email']['message']); ?></textarea><br />
                            <p>
                            <label for "registration_admin_disable"><input name="registration_admin_disable" type="checkbox" id="registration_admin_disable" value="1" <?php if ( $ThemeMyLogin->options['registration_email']['admin_disable'] ) { echo 'checked="checked"'; } ?> /> Disable Admin Notification</label>&nbsp;
                            <label for "registration_user_disable"><input name="registration_user_disable" type="checkbox" id="registration_user_disable" value="1" <?php if ( $ThemeMyLogin->options['registration_email']['user_disable'] ) { echo 'checked="checked"'; } ?> /> Disable User Notification</label>&nbsp;
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <div id="fragment-5-3" class="tabs-div">
                <table class="form-table">
                    <tr>
                        <td>
                            <p><em>Avilable Variables: %blogname%, %siteurl%, %reseturl%, %user_login%, %user_email%, %user_ip%</em></p>
                            Subject<br />
                            <input name="retrieve_pass_subject" type="text" id="retrieve_pass_subject" value="<?php echo htmlspecialchars($ThemeMyLogin->options['retrieve_pass_email']['subject']); ?>" class="full-text" /><br />
                            Message<br />
                            <textarea name="retrieve_pass_message" id="retrieve_pass_message" class="large-text"><?php echo htmlspecialchars($ThemeMyLogin->options['retrieve_pass_email']['message']); ?></textarea><br />
                        </td>
                    </tr>
                </table>
            </div>

            <div id="fragment-5-4" class="tabs-div">
                <table class="form-table">
                    <tr>
                        <td>
                            <p><em>Avilable Variables: %blogname%, %siteurl%, %user_login%, %user_email%, %user_pass%, %user_ip%</em></p>
                            Subject<br />
                            <input name="reset_pass_subject" type="text" id="reset_pass_subject" value="<?php echo htmlspecialchars($ThemeMyLogin->options['reset_pass_email']['subject']); ?>" class="full-text" /><br />
                            Message<br />
                            <textarea name="reset_pass_message" id="reset_pass_message" class="large-text"><?php echo htmlspecialchars($ThemeMyLogin->options['reset_pass_email']['message']); ?></textarea><br />
                            <p>
                            <label for "reset_pass_admin_disable"><input name="reset_pass_admin_disable" type="checkbox" id="reset_pass_admin_disable" value="1" <?php if ( $ThemeMyLogin->options['reset_pass_email']['admin_disable'] ) { echo 'checked="checked"'; } ?> /> Disable Admin Notification</label>&nbsp;
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
