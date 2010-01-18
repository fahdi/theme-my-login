            <ul class="tabs-nav">
                <li><a href="#fragment-3-1">General</a></li>
                <?php
                $i = 2;
                foreach ($user_roles as $role => $value) {
                    if ( 'pending' == $role )
                        continue;
                    echo '<li><a href="#fragment-3-' . $i . '">' . ucwords($role) . '</a></li>' . "\n";
                    $i++;
                }
                ?>
            </ul>

            <div id="fragment-3-1" class="tabs-div">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Redirection', 'theme-my-login'); ?></th>
                        <td>
                            <input name="override_redirect" type="radio" id="override_redirect_on" value="1" <?php if ( 1 == $ThemeMyLogin->options['override_redirect'] ) { echo 'checked="checked"'; } ?> />
                            <label for="override_redirect_on"><?php _e('Allow Override', 'theme-my-login'); ?></label><br />
                            <input name="override_redirect" type="radio" id="override_redirect_off" value="0" <?php if ( 0 == $ThemeMyLogin->options['override_redirect'] ) { echo 'checked="checked"'; } ?> />
                            <label for="override_redirect_off"><?php _e('Always Redirect', 'theme-my-login'); ?></label><br />
                        </td>
                    </tr>
                </table>
            </div>
            
            <?php
            $i1 = 2;
            foreach ($user_roles as $role => $value) {
                if ( 'pending' == $role )
                    continue;
            ?>
            <div id="fragment-3-<?php echo $i1; ?>" class="tabs-div">

                <table id="redirection-<?php echo $role; ?>" class="form-table redirection-table">
                    <tr id="redirect-row-<?php echo $i2; ?>">
                        <td>
                            Log In URL<br />
                            <input name="redirects[<?php echo $role; ?>][login_url]" type="text" id="redirects[<?php echo $role; ?>][login_url]" value="<?php echo $redirects[$role]['login_url']; ?>" class="extended-text redirect-url" /><br />
                            Log Out URL<br />
                            <input name="redirects[<?php echo $role; ?>][logout_url]" type="text" id="redirects[<?php echo $role; ?>][logout_url]" value="<?php echo $redirects[$role]['logout_url']; ?>" class="extended-text redirect-url" /><br />
                        </td>
                    </tr>
                </table>

            </div>

            <?php
                $i1++;
            }
            ?>
