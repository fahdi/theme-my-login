            <ul class="tabs-nav">
                <?php
                $i = 1;
                foreach ($user_roles as $role => $value) {
                    echo '<li><a href="#fragment-4-' . $i . '">' . ucwords($role) . '</a></li>' . "\n";
                    $i++;
                }
                ?>
            </ul>

            <?php
            $i1 = 1;
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
