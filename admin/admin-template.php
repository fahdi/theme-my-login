            <ul class="tabs-nav">
                <li><a href="#fragment-2-1">General</a></li>
                <li><a href="#fragment-2-2">Titles</a></li>
                <li><a href="#fragment-2-3">Messages</a></li>
            </ul>
            
            <div id="fragment-2-1" class="tabs-div">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Stylesheet', 'theme-my-login'); ?></th>
                        <td>
                            <input name="use_css" type="checkbox" id="use_css" value="1" <?php if ( $ThemeMyLogin->options['use_css'] ) { echo 'checked="checked"'; } ?> />
                            <label for="use_css"><?php _e('Use theme-my-login.css', 'theme-my-login'); ?></label>
                        </td>
                    </tr>
                </table>
            </div>

            <div id="fragment-2-2" class="tabs-div">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="welcome_title"><?php _e('Welcome', 'theme-my-login'); ?></label></th>
                        <td>
                            <input name="welcome_title" type="text" id="welcome_title" value="<?php echo htmlspecialchars($ThemeMyLogin->options['welcome_title']); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="login_title"><?php _e('Log In', 'theme-my-login'); ?></label></th>
                        <td>
                            <input name="login_title" type="text" id="login_title" value="<?php echo htmlspecialchars($ThemeMyLogin->options['login_title']); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="register_title"><?php _e('Register', 'theme-my-login'); ?></label></th>
                        <td>
                            <input name="register_title" type="text" id="register_title" value="<?php echo htmlspecialchars($ThemeMyLogin->options['register_title']); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="lost_pass_title"><?php _e('Lost Password', 'theme-my-login'); ?></label></th>
                        <td>
                            <input name="lost_pass_title" type="text" id="lost_pass_title" value="<?php echo htmlspecialchars($ThemeMyLogin->options['lost_pass_title']); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="logout_title"><?php _e('Log Out', 'theme-my-login'); ?></label></th>
                        <td>
                            <input name="logout_title" type="text" id="logout_title" value="<?php echo htmlspecialchars($ThemeMyLogin->options['logout_title']); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
            </div>

            <div id="fragment-2-3" class="tabs-div">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="register_message"><?php _e('Register', 'theme-my-login'); ?></label></th>
                        <td>
                            <input name="register_message" type="text" id="register_message" value="<?php echo htmlspecialchars($ThemeMyLogin->options['register_message']); ?>" class="extended-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="success_message"><?php _e('Registration Complete', 'theme-my-login'); ?></label></th>
                        <td>
                            <input name="success_message" type="text" id="success_message" value="<?php echo htmlspecialchars($ThemeMyLogin->options['success_message']); ?>" class="extended-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="lost_pass_message"><?php _e('Lost Password', 'theme-my-login'); ?></label></th>
                        <td>
                            <input name="lost_pass_message" type="text" id="lost_pass_message" value="<?php echo htmlspecialchars($ThemeMyLogin->options['lost_pass_message']); ?>" class="extended-text" />
                        </td>
                    </tr>
                </table>
            </div>
