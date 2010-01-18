            <ul class="tabs-nav">
                <?php
                $i = 1;
                foreach ($user_roles as $role => $value) {
                    if ( 'pending' == $role )
                        continue;
                    echo '<li><a href="#fragment-2-' . $i . '">' . ucwords($role) . '</a></li>' . "\n";
                    $i++;
                }
                ?>
            </ul>

            <?php
            $i1 = 1;
            foreach ($user_roles as $role => $value) {
                if ( 'pending' == $role )
                    continue;
            ?>
            <div id="fragment-2-<?php echo $i1; ?>" class="tabs-div">
            
                <p class="description"><?php _e('These links will show up in the widget when a user is logged in, according to their role.', 'theme-my-login'); ?></p>

                <table id="links-<?php echo $role; ?>" class="form-table link-table">
                    <?php $i2 = 0; ?>
                    <?php $alt = 'alternate'; ?>
                    <?php if ( is_array($links[$role]) ) { ?>
                        <?php foreach ( $links[$role] as $key => $data ) {
                            $alt = ('alternate' == $alt) ? '' : 'alternate';
                            ?>
                    <tr id="link-row-<?php echo $i2; ?>" class="<?php echo $alt; ?>">
                        <td>
                            Title<br />
                            <input name="links[<?php echo $role; ?>][<?php echo $i2; ?>][title]" type="text" id="links[<?php echo $role; ?>][<?php echo $i2; ?>][title]" value="<?php echo htmlspecialchars($data['title']); ?>" class="regular-text link-title" /><br />
                            URL<br />
                            <input name="links[<?php echo $role; ?>][<?php echo $i2; ?>][url]" type="text" id="links[<?php echo $role; ?>][<?php echo $i2; ?>][url]" value="<?php echo $data['url']; ?>" class="extended-text link-url" /><br />
                            <p>
                            <a class="link remove <?php echo $role; ?>" href="" title="Remove This Link"><img src="<?php echo WP_PLUGIN_URL; ?>/theme-my-login/images/remove.gif" /></a>
                            <a class="link add <?php echo $role; ?>" href="" title="Add Another Link"><img src="<?php echo WP_PLUGIN_URL; ?>/theme-my-login/images/add.gif" /></a>
                            </p>
                        </td>
                    </tr>
                            <?php
                            $i2++;
                        }
                    } else { ?>
                    <tr id="link-row-0" class="">
                        <td>
                            Title<br />
                            <input name="links[<?php echo $role; ?>][0][title]" type="text" id="links[<?php echo $role; ?>][0][title]" value="" class="regular-text link-title" /><br />
                            URL<br />
                            <input name="links[<?php echo $role; ?>][0][url]" type="text" id="links[<?php echo $role; ?>][0][url]" value="" class="extended-text link-url" /><br />
                            <p>
                            <a class="link remove <?php echo $role; ?>" href="" title="Remove This Link"><img src="<?php echo WP_PLUGIN_URL; ?>/theme-my-login/images/remove.gif" /></a>
                            <a class="link add <?php echo $role; ?>" href="" title="Add Another Link"><img src="<?php echo WP_PLUGIN_URL; ?>/theme-my-login/images/add.gif" /></a>
                            </p>
                        </td>
                    </tr>
                    <?php } ?>
                </table>

            </div>

            <?php
                $i1++;
            }
            ?>
