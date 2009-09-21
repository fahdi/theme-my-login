<?php

global $ThemeMyLogin, $wp_roles, $wp_version;
$user_roles = $wp_roles->get_names();

if ( $_POST ) {

    check_admin_referer('theme-my-login-settings');

    $ThemeMyLogin->options['general']['uninstall'] = ( isset($_POST['general']['uninstall']) ) ? 1 : 0;
    $ThemeMyLogin->options['general']['defaults'] = ( isset($_POST['general']['defaults']) ) ? 1 : 0;
    $ThemeMyLogin->options['general']['show_page'] = ( isset($_POST['general']['show_page']) ) ? 1 : 0;
    $ThemeMyLogin->options['general']['custom_pass'] = ( isset($_POST['general']['custom_pass']) ) ? 1 : 0;
    $ThemeMyLogin->options['general']['page_id'] = (int) $_POST['general']['page_id'];
    $ThemeMyLogin->options['general']['from_name'] = stripslashes($_POST['general']['from_name']);
    $ThemeMyLogin->options['general']['from_email'] = stripslashes($_POST['general']['from_email']);
    $ThemeMyLogin->options['general']['email_format'] = stripslashes($_POST['general']['email_format']);
    
    $ThemeMyLogin->SetOption('titles', stripslashes_deep($_POST['titles']));
    $ThemeMyLogin->SetOption('messages', stripslashes_deep($_POST['messages']));

    foreach ( $_POST['links'] as $role => $tmp ) {
        foreach ( $tmp as $key => $data ) {
            $links[$role][] = array('title' => $data['title'], 'url' => $data['url']);
        }
    }
    $ThemeMyLogin->SetOption('links', $links);
    foreach ( $_POST['redirects'] as $role => $data ) {
        $redirects[$role] = array('login_url' => $data['login_url']);
    }
    $ThemeMyLogin->SetOption('redirects', $redirects);
    foreach ( $_POST['emails'] as $email => $data ) {
        $emails[$email] = array('subject' => stripslashes($data['subject']), 'message' => stripslashes($data['message']));
    }
    $emails['newregistration']['admin-disable'] = ( isset($_POST['emails']['newregistration']['admin-disable']) ) ? 1 : 0;
    $emails['newregistration']['user-disable'] = ( isset($_POST['emails']['newregistration']['user-disable']) ) ? 1 : 0;
    $emails['resetpassword']['admin-disable'] = ( isset($_POST['emails']['resetpassword']['admin-disable']) ) ? 1 : 0;
    $ThemeMyLogin->SetOption('emails', $emails);
    $ThemeMyLogin->SaveOptions();

    if ( isset($_POST['general']['uninstall']) ) {
        $info_message = __('To complete uninstall, deactivate this plugin. If you do not wish to uninstall, please uncheck the "Complete Uninstall" checkbox.', 'theme-my-login');
    } elseif ( isset($_POST['general']['defaults']) ) {
        $ThemeMyLogin->options = '';
        $ThemeMyLogin->InitOptions(true);
        $info_message = __('All settings restored to default state.', 'theme-my-login');
    } else $info_message =__('Settings saved.', 'theme-my-login');
}

$titles = $ThemeMyLogin->GetOption('titles');
$messages = $ThemeMyLogin->GetOption('messages');
$links = $ThemeMyLogin->GetOption('links');
$redirects = $ThemeMyLogin->GetOption('redirects');
$emails = $ThemeMyLogin->GetOption('emails');

?>

<div class="updated" style="background:#f0f8ff; border:1px solid #addae6">
    <p><?php _e('If you like this plugin, please help keep it up to date by <a href="http://www.jfarthing.com/donate">donating through PayPal</a>!', 'theme-my-login'); ?></p>
</div>

<div class="wrap">
<?php if ( function_exists('screen_icon') ) screen_icon('options-general'); ?>

    <h2><?php _e('Theme My Login Settings'); ?></h2>

    <?php if ( isset($info_message) && !empty($info_message) ) : ?>
    <div id="message" class="updated fade">
        <p><strong><?php echo $info_message ?></strong></p>
    </div>
    <?php endif; ?>

    <?php if(  isset($error_message) && !empty($error_message) ) : ?>
    <div id="message" class="error">
        <p><strong><?php echo $error_message ?></strong></p>
    </div>
    <?php endif; ?>

    <form id="theme-my-login-settings" action="" method="post">
    <?php wp_nonce_field('theme-my-login-settings'); ?>
    
    <div id="container" class="tabs">

        <ul class="tabs-nav">
            <li><a href="#fragment-1">General</a></li>
            <li><a href="#fragment-2">Template</a></li>
            <li><a href="#fragment-3">Links</a></li>
            <li><a href="#fragment-4">Redirection</a></li>
            <li><a href="#fragment-5">E-mail</a></li>
        </ul>
        
        <div id="fragment-1" class="tabs-div">
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Plugin', 'theme-my-login'); ?></th>
                    <td>
                        <input name="general[uninstall]" type="checkbox" id="general[uninstall]" value="1" <?php if ( isset($ThemeMyLogin->options['general']['uninstall']) && true == $ThemeMyLogin->options['general']['uninstall'] ) { echo 'checked="checked"'; } ?> />
                        <label for="general[uninstall]"><?php _e('Uninstall', 'theme-my-login'); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Defaults', 'theme-my-login'); ?></th>
                    <td>
                        <input name="general[defaults]" type="checkbox" id="general[defaults]" value="1" <?php if ( isset($ThemeMyLogin->options['general']['defaults']) && true == $ThemeMyLogin->options['general']['defaults'] ) { echo 'checked="checked"'; } ?> />
                        <label for="general[defaults]"><?php _e('Reset Defaults', 'theme-my-login'); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Page List', 'theme-my-login'); ?></th>
                    <td>
                        <input name="general[show_page]" type="checkbox" id="general[show_page]" value="1" <?php if ( isset($ThemeMyLogin->options['general']['show_page']) && $ThemeMyLogin->options['general']['show_page'] ) { echo 'checked="checked"'; } ?> />
                        <label for="general[show_page]"><?php _e('Show Login Page', 'theme-my-login'); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Registration', 'theme-my-login'); ?></th>
                    <td>
                        <input name="general[custom_pass]" type="checkbox" id="general[custom_pass]" value="1" <?php if ( isset($ThemeMyLogin->options['general']['custom_pass']) && true == $ThemeMyLogin->options['general']['custom_pass'] ) { echo 'checked="checked"'; } ?> />
                        <label for="general[custom_pass]"><?php _e('Allow Users To Set Their Own Password', 'theme-my-login'); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="general[page_id]"><?php _e('Page ID', 'theme-my-login'); ?></label></th>
                    <td>
                        <input name="general[page_id]" type="text" id="general[page_id]" value="<?php echo $ThemeMyLogin->options['general']['page_id']; ?>" size="1" />
                        <span class="description"><strong>DO NOT</strong> change this unless you are <strong>ABSOLUTELY POSITIVE</strong> you know what you are doing!</span>
                    </td>
                </tr>
            </table>
            
        </div>

        <div id="fragment-2" class="tabs-div">
        
            <ul class="tabs-nav">
                <li><a href="#fragment-2-1">Titles</a></li>
                <li><a href="#fragment-2-2">Messages</a></li>
            </ul>
            
            <div id="fragment-2-1" class="tabs-div">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="titles[welcome]"><?php _e('Welcome', 'theme-my-login'); ?></label></th>
                        <td>
                            <input name="titles[welcome]" type="text" id="titles[welcome]" value="<?php echo htmlspecialchars($titles['welcome']); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="titles[login]"><?php _e('Log In', 'theme-my-login'); ?></label></th>
                        <td>
                            <input name="titles[login]" type="text" id="titles[login]" value="<?php echo htmlspecialchars($titles['login']); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="titles[register]"><?php _e('Register', 'theme-my-login'); ?></label></th>
                        <td>
                            <input name="titles[register]" type="text" id="titles[register]" value="<?php echo htmlspecialchars($titles['register']); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="titles[lostpassword]"><?php _e('Lost Password', 'theme-my-login'); ?></label></th>
                        <td>
                            <input name="titles[lostpassword]" type="text" id="titles[lostpassword]" value="<?php echo htmlspecialchars($titles['lostpassword']); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="titles[logout]"><?php _e('Log Out', 'theme-my-login'); ?></label></th>
                        <td>
                            <input name="titles[logout]" type="text" id="titles[logout]" value="<?php echo htmlspecialchars($titles['logout']); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
            </div>
            
            <div id="fragment-2-2" class="tabs-div">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="register_msg"><?php _e('Register', 'theme-my-login'); ?></label></th>
                        <td>
                            <input name="messages[register]" type="text" id="messages[register]" value="<?php echo htmlspecialchars($messages['register']); ?>" class="extended-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="register_complete"><?php _e('Registration Complete', 'theme-my-login'); ?></label></th>
                        <td>
                            <input name="messages[success]" type="text" id="messages[success]" value="<?php echo htmlspecialchars($messages['success']); ?>" class="extended-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="password_msg"><?php _e('Lost Password', 'theme-my-login'); ?></label></th>
                        <td>
                            <input name="messages[lostpassword]" type="text" id="messages[lostpassword]" value="<?php echo htmlspecialchars($messages['lostpassword']); ?>" class="extended-text" />
                        </td>
                    </tr>
                </table>
            </div>
            
        </div>

        <div id="fragment-3" class="tabs-div">
        
            <ul class="tabs-nav">
                <?php
                $i = 1;
                foreach ($user_roles as $role => $value) {
                    echo '<li><a href="#fragment-3-' . $i . '">' . ucwords($role) . '</a></li>' . "\n";
                    $i++;
                }
                ?>
            </ul>

            <?php
            $i1 = 1;
            foreach ($user_roles as $role => $value) {
            ?>
            <div id="fragment-3-<?php echo $i1; ?>" class="tabs-div">
            
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

        </div>

        <div id="fragment-4" class="tabs-div">

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

        </div>

        <div id="fragment-5" class="tabs-div">

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
                            <label for="general[from_name]"><?php _e('From Name', 'theme-my-login'); ?></label><br />
                            <input name="general[from_name]" type="text" id="general[from_name]" value="<?php echo htmlspecialchars($ThemeMyLogin->options['general']['from_name']); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <td>
                            <label for="general[from_email]"><?php _e('From E-mail', 'theme-my-login'); ?></label><br />
                            <input name="general[from_email]" type="text" id="general[from_email]" value="<?php echo htmlspecialchars($ThemeMyLogin->options['general']['from_email']); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <td>
                        <label for"general[email_format]"><?php _e('E-mail Format', 'theme-my-login'); ?></label><br />
                        <select name="general[email_format]" id="general[email_format]">
                        <option value="text/plain"<?php if ('text/plain' == $ThemeMyLogin->options['general']['email_format']) echo ' selected="selected"'; ?>>Plain Text</option>
                        <option value="text/html"<?php if ('text/html' == $ThemeMyLogin->options['general']['email_format']) echo ' selected="selected"'; ?>>HTML</option>
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
                            <input name="emails[newregistration][subject]" type="text" id="emails[newregistration][subject]" value="<?php echo htmlspecialchars($emails['newregistration']['subject']); ?>" class="full-text" /><br />
                            Message<br />
                            <textarea name="emails[newregistration][message]" id="emails[newregistration][message]" class="large-text"><?php echo htmlspecialchars($emails['newregistration']['message']); ?></textarea><br />
                            <p>
                            <label for "emails[newregistration][admin-disable]"><input name="emails[newregistration][admin-disable]" type="checkbox" id="emails[newregistration][admin-disable]" value="1" <?php if ( isset($emails['newregistration']['admin-disable']) && true == $emails['newregistration']['admin-disable'] ) { echo 'checked="checked"'; } ?> /> Disable Admin Notification</label>&nbsp;
                            <label for "emails[newregistration][user-disable]"><input name="emails[newregistration][user-disable]" type="checkbox" id="emails[newregistration][useradmin-disable]" value="1" <?php if ( isset($emails['newregistration']['user-disable']) && true == $emails['newregistration']['user-disable'] ) { echo 'checked="checked"'; } ?> /> Disable User Notification</label>&nbsp;
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
                            <input name="emails[retrievepassword][subject]" type="text" id="emails[retrievepassword][subject]" value="<?php echo htmlspecialchars($emails['retrievepassword']['subject']); ?>" class="full-text" /><br />
                            Message<br />
                            <textarea name="emails[retrievepassword][message]" id="emails[retrievepassword][message]" class="large-text"><?php echo htmlspecialchars($emails['retrievepassword']['message']); ?></textarea><br />
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
                            <input name="emails[resetpassword][subject]" type="text" id="emails[resetpassword][subject]" value="<?php echo htmlspecialchars($emails['resetpassword']['subject']); ?>" class="full-text" /><br />
                            Message<br />
                            <textarea name="emails[resetpassword][message]" id="emails[resetpassword][message]" class="large-text"><?php echo htmlspecialchars($emails['resetpassword']['message']); ?></textarea><br />
                            <p>
                            <label for "emails[resetpassword][admin-disable]"><input name="emails[resetpassword][admin-disable]" type="checkbox" id="emails[resetpassword][admin-disable]" value="1" <?php if ( isset($ThemeMyLogin->options['emails']['resetpassword']['admin-disable']) && true == $emails['resetpassword']['admin-disable'] ) { echo 'checked="checked"'; } ?> /> Disable Admin Notification</label>&nbsp;
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
        
    </div>
    
    <?php if ( version_compare($wp_version, '2.7', '>=') ) : ?>
    <p><input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes'); ?>" /></p>
    <?php else : ?>
    <p><input type="submit" name="Submit" class="button" value="<?php _e('Save Changes'); ?>" /></p>
    <?php endif; ?>
    </form>
    
</div>
