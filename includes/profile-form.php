<?php
    
require (WP_PLUGIN_DIR . '/theme-my-login/includes/wp-login-functions.php');
require_once (ABSPATH . '/wp-admin/includes/misc.php');
require_once (ABSPATH . '/wp-admin/includes/user.php');

if ( !isset($user_id) ) {
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
}

wp_reset_vars(array('action', 'redirect', 'profile', 'user_id', 'wp_http_referer'));
if (isset($wp_http_referer))
    $wp_http_referer = remove_query_arg(array('update', 'delete_count'), stripslashes($wp_http_referer));
$user_id = (int) $user_id;

global $profileuser;
$profileuser = get_user_to_edit($user_id);

if ( !current_user_can('edit_user', $user_id) )
    wp_die(__('You do not have permission to edit this user.'));

login_header('', $this->errors);

if (isset($_GET['updated']) && $_GET['updated'] == true) {
    echo '<p class="message">Your profile has been updated.</p>';
}
?>

<form name="profile" id="your-profile" action="<?php echo ssl_or_not($this->QueryURL().'profile=1') ?>" method="post">
<?php wp_nonce_field('update-user_' . $user_id) ?>
<?php if ( isset($wp_http_referer) ) : ?>
<input type="hidden" name="wp_http_referer" value="<?php echo clean_url($wp_http_referer); ?>" />
<?php endif; ?>
<p>
<input type="hidden" name="from" value="profile" />
<input type="hidden" name="checkuser_id" value="<?php echo $user_ID ?>" />
</p>

<h3><?php _e('Name') ?></h3>

<table class="form-table">
    <tr>
        <th><label for="user_login"><?php _e('Username'); ?></label></th>
        <td><input type="text" name="user_login" id="user_login" value="<?php echo $profileuser->user_login; ?>" disabled="disabled" /> <?php _e('Your username cannot be changed'); ?></td>
    </tr>
    <tr>
        <th><label for="first_name"><?php _e('First name') ?></label></th>
        <td><input type="text" name="first_name" id="first_name" value="<?php echo $profileuser->first_name ?>" /></td>
    </tr>
    <tr>
        <th><label for="last_name"><?php _e('Last name') ?></label></th>
        <td><input type="text" name="last_name" id="last_name" value="<?php echo $profileuser->last_name ?>" /></td>
    </tr>
    <tr>
        <th><label for="nickname"><?php _e('Nickname') ?></label></th>
        <td><input type="text" name="nickname" id="nickname" value="<?php echo $profileuser->nickname ?>" /></td>
    </tr>
    <tr>
        <th><label for="display_name"><?php _e('Display name publicly&nbsp;as') ?></label></th>
        <td>
            <select name="display_name" id="display_name">
            <?php
                $public_display = array();
                $public_display['display_displayname'] = $profileuser->display_name;
                $public_display['display_nickname'] = $profileuser->nickname;
                $public_display['display_username'] = $profileuser->user_login;
                $public_display['display_firstname'] = $profileuser->first_name;
                $public_display['display_firstlast'] = $profileuser->first_name.' '.$profileuser->last_name;
                $public_display['display_lastfirst'] = $profileuser->last_name.' '.$profileuser->first_name;
                $public_display = array_unique(array_filter(array_map('trim', $public_display)));
                foreach($public_display as $id => $item) {
            ?>
                <option id="<?php echo $id; ?>" value="<?php echo $item; ?>"><?php echo $item; ?></option>
            <?php
                }
            ?>
            </select>
        </td>
    </tr>
</table>

<h3><?php _e('Contact Info') ?></h3>

<table class="form-table">
<tr>
    <th><label for="email"><?php _e('E-mail') ?></label></th>
    <td><input type="text" name="email" id="email" value="<?php echo $profileuser->user_email ?>" /> <?php _e('Required'); ?></td>
</tr>

<tr>
    <th><label for="url"><?php _e('Website') ?></label></th>
    <td><input type="text" name="url" id="url" value="<?php echo $profileuser->user_url ?>" /></td>
</tr>

<tr>
    <th><label for="aim"><?php _e('AIM') ?></label></th>
    <td><input type="text" name="aim" id="aim" value="<?php echo $profileuser->aim ?>" /></td>
</tr>

<tr>
    <th><label for="yim"><?php _e('Yahoo IM') ?></label></th>
    <td><input type="text" name="yim" id="yim" value="<?php echo $profileuser->yim ?>" /></td>
</tr>

<tr>
    <th><label for="jabber"><?php _e('Jabber / Google Talk') ?></label></th>
    <td><input type="text" name="jabber" id="jabber" value="<?php echo $profileuser->jabber ?>" /></td>
</tr>
</table>

<h3><?php _e('About Yourself'); ?></h3>

<table class="form-table">
<tr>
    <th><label for="description"><?php _e('Biographical Info'); ?></label></th>
    <td><textarea name="description" id="description" rows="5" cols="30"><?php echo $profileuser->description ?></textarea><br /><?php _e('Share a little biographical information to fill out your profile. This may be shown publicly.'); ?><br/><br/></td>
</tr>

<?php
$show_password_fields = apply_filters('show_password_fields', true);
if ( $show_password_fields ) :
?>
<tr>
    <th><label for="pass1"><?php _e('New Password'); ?></label></th>
    <td>
        <input type="password" name="pass1" id="pass1" size="16" value="" /><br/><?php _e("If you would like to change the password type a new one. Otherwise leave this blank."); ?><br />
        <input type="password" name="pass2" id="pass2" size="16" value="" /><br/><?php _e("Type your new password again."); ?><br />
    </td>
</tr>
<?php endif; ?>
</table>

<?php
    do_action('profile_personal_options');
    do_action('show_user_profile');
?>

<?php if (count($profileuser->caps) > count($profileuser->roles)): ?>
<br class="clear" />
    <table width="99%" style="border: none;" cellspacing="2" cellpadding="3" class="editform">
        <tr>
            <th scope="row"><?php _e('Additional Capabilities') ?></th>
            <td><?php
            $output = '';
            foreach($profileuser->caps as $cap => $value) {
                if(!$wp_roles->is_role($cap)) {
                    if($output != '') $output .= ', ';
                    $output .= $value ? $cap : "Denied: {$cap}";
                }
            }
            echo $output;
            ?></td>
        </tr>
    </table>
<?php endif; ?>

<p class="submit">
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="user_id" id="user_id" value="<?php echo $user_id; ?>" />
    <input type="submit" id="submit" value="<?php _e('Update Profile') ?>" name="submit" />
</p>
</form>
</div>
