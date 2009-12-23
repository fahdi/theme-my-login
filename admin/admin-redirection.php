            <ul class="tabs-nav">
                <li><a href="#fragment-4-1">General</a></li>
                <?php
                $i = 2;
                foreach ($user_roles as $role => $value) {
                    echo '<li><a href="#fragment-4-' . $i . '">' . ucwords($role) . '</a></li>' . "\n";
                    $i++;
                }
                ?>
            </ul>

            <div id="fragment-4-1" class="tabs-div">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Redirection', 'theme-my-login'); ?></th>
                        <td>
                            <input name="override_redirect" type="checkbox" id="override_redirect" value="1" <?php if ( $ThemeMyLogin->options['override_redirect'] ) { echo 'checked="checked"'; } ?> />
                            <label for="override_redirect"><?php _e('Override All Redirection', 'theme-my-login'); ?></label>
                            <p class="description">If checked, the redirection settings specified here will be used regardless of query variables. If unchecked and the "redirect_to" value is set, it will be used instead.</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <?php
            $i1 = 2;
            foreach ($user_roles as $role => $value) {
            ?>
            <div id="fragment-4-<?php echo $i1; ?>" class="tabs-div">

                <table id="redirection-<?php echo $role; ?>" class="form-table redirection-table">
                    <tr id="redirect-row-<?php echo $i2; ?>">
                        <td>
                            Log In URL<br />
                            <input name="redirects[<?php echo $role; ?>][login_url]" type="text" id="redirects[<?php echo $role; ?>][login_url]" value="<?php echo $redirects[$role]['login_url']; ?>" class="extended-text redirect-url" />
                        </td>
                    </tr>
                </table>

            </div>

            <?php
                $i1++;
            }
            ?>
