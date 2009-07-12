<?php

global $wp_roles;
$user_roles = $wp_roles->get_names();

if ( $_POST['tml_submit'] ) {

    foreach ($user_roles as $role => $value) {
        $dashboard_link[$role] = isset($_POST['tml_dashboard_link'][$role]) ? true : false;
        $profile_link[$role] = isset($_POST['tml_profile_link'][$role]) ? true : false;
    }
    $this->SetOption('widget_show_logged_in', $_POST['tml_show_widget']);
    $this->SetOption('widget_show_gravatar', $_POST['tml_show_gravatar']);
    $this->SetOption('widget_gravatar_size', absint($_POST['tml_gravatar_size']));
    $this->SetOption('widget_dashboard_link', $dashboard_link);
    $this->SetOption('widget_profile_link', $profile_link);
    $this->SaveOptions();
    
}

$show_widget = $this->GetOption('widget_show_logged_in');
$show_gravatar = $this->GetOption('widget_show_gravatar');
$dashboard_link = $this->GetOption('widget_dashboard_link');
$profile_link = $this->GetOption('widget_profile_link');

?>

<p>
    <label for="tml_show_widget">Show Widget When Logged In:</label>
    <select name="tml_show_widget" id="tml_show_widget"><option value="1" <?php if ($show_widget == true) echo 'selected="selected"'; ?>>Yes</option><option value="0" <?php if ($show_widget == false) echo 'selected="selected"'; ?>>No</option></select>
</p>

<p>
    <label for="tml_show_gravatar">Show Gravatar:</label>
    <select name="tml_show_gravatar" id="tml_show_gravatar"><option value="1" <?php if ($show_gravatar == true) echo 'selected="selected"'; ?>>Yes</option><option value="0" <?php if ($show_gravatar == false) echo 'selected="selected"'; ?>>No</option></select>
</p>

<p>
    <label for="tml_gravatar_size">Gravatar Size:</label>
    <input name="tml_gravatar_size" type="text" id="tml_gravatar_size" value="<?php echo absint($this->GetOption('widget_gravatar_size')); ?>" size="2" />
</p>

<p>
    <label for="tml_dashboard_link">Show Dashboard Link For:</label><br />
    <?php foreach ($user_roles as $role => $value) : ?>
    <input name="tml_dashboard_link[<?php echo $role; ?>]" type="checkbox" id="tml_dashboard_link[<?php echo $role; ?>]" value="1"<?php if ($dashboard_link[$role] == true) { echo 'checked="checked"'; } ?> /> <?php echo ucwords($role); ?><br />
    <?php endforeach; ?>
</p>

<p>
    <label for="tml_profile_link">Show Profile Link For:</label><br />
    <?php foreach ($user_roles as $role => $value) : ?>
    <input name="tml_profile_link[<?php echo $role; ?>]" type="checkbox" id="tml_profile_link[<?php echo $role; ?>]" value="1"<?php if ($profile_link[$role] == true) { echo 'checked="checked"'; } ?> /> <?php echo ucwords($role); ?><br />
    <?php endforeach; ?>
</p>

<p>
    <input type="hidden" id="tml_submit" name="tml_submit" value="1" />
</p>
