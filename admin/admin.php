<?php

global $ThemeMyLogin, $wp_roles;
$user_roles = $wp_roles->get_names();

if ( $_POST ) {

    check_admin_referer('theme-my-login-settings');

    $ThemeMyLogin->options['custom_pass'] = ( $_POST['custom_pass'] ) ? 1 : 0;
    $ThemeMyLogin->options['moderation'] = ( in_array($_POST['moderation'], array('none', 'email', 'admin')) ) ? $_POST['moderation'] : 'none';
    if ( in_array($_POST['moderation'], array('email', 'admin')) ) {
        if ( !$wp_roles->is_role('pending') )
            add_role('pending', 'Pending', array());
    } else {
        if ( $wp_roles->is_role('pending') )
            remove_role('pending');
    }
    $ThemeMyLogin->options['use_css'] = isset($_POST['use_css']) ? 1 : 0;
    $ThemeMyLogin->options['email_from_name'] = stripslashes($_POST['email_from_name']);
    $ThemeMyLogin->options['email_from'] = stripslashes($_POST['email_from']);
    $ThemeMyLogin->options['email_content_type'] = stripslashes($_POST['email_content_type']);

    foreach ( $_POST['links'] as $role => $tmp ) {
        foreach ( $tmp as $key => $data ) {
            $links[$role][] = array('title' => $data['title'], 'url' => $data['url']);
        }
    }
    $ThemeMyLogin->options['links'] = $links;
    
    $ThemeMyLogin->options['override_redirect'] = ( $_POST['override_redirect'] ) ? 1 : 0;
    foreach ( $_POST['redirects'] as $role => $data ) {
        $redirects[$role] = array('login_url' => $data['login_url'], 'logout_url' => $data['logout_url']);
    }
    $ThemeMyLogin->options['redirects'] = $redirects;
    
    $ThemeMyLogin->options['registration_email'] = array(
        'subject' => stripslashes($_POST['registration_subject']),
        'message' => stripslashes($_POST['registration_message']),
        'admin_disable' => isset($_POST['registration_admin_disable']) ? 1 : 0
        );
    $ThemeMyLogin->options['retrieve_pass_email'] = array(
        'subject' => stripslashes($_POST['retrieve_pass_subject']),
        'message' => stripslashes($_POST['retrieve_pass_message'])
        );
    $ThemeMyLogin->options['reset_pass_email'] = array(
        'subject' => stripslashes($_POST['reset_pass_subject']),
        'message' => stripslashes($_POST['reset_pass_message']),
        'admin_disable' => isset($_POST['reset_pass_admin_disable']) ? 1 : 0
        );
    $ThemeMyLogin->options['confirmation_email'] = array(
        'subject' => stripslashes($_POST['confirmation_subject']),
        'message' => stripslashes($_POST['confirmation_message'])
        );
    $ThemeMyLogin->options['user_approval_email'] = array(
        'subject' => stripslashes($_POST['user_approval_subject']),
        'message' => stripslashes($_POST['user_approval_message'])
        );
    $ThemeMyLogin->options['user_denial_email'] = array(
        'subject' => stripslashes($_POST['user_denial_subject']),
        'message' => stripslashes($_POST['user_denial_message'])
        );
        
    $ThemeMyLogin->saveOptions();

    $info_message =__('Settings saved.', 'theme-my-login');
}

$links = $ThemeMyLogin->options['links'];
$redirects = $ThemeMyLogin->options['redirects'];

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
            <li><a href="#fragment-2">Links</a></li>
            <li><a href="#fragment-3">Redirection</a></li>
            <li><a href="#fragment-4">E-mail</a></li>
        </ul>
        
        <div id="fragment-1" class="tabs-div">
            <?php include WP_PLUGIN_DIR . '/theme-my-login/admin/admin-general.php'; ?>
        </div>

        <div id="fragment-2" class="tabs-div">
            <?php include WP_PLUGIN_DIR . '/theme-my-login/admin/admin-links.php'; ?>
        </div>

        <div id="fragment-3" class="tabs-div">
            <?php include WP_PLUGIN_DIR . '/theme-my-login/admin/admin-redirection.php'; ?>
        </div>

        <div id="fragment-4" class="tabs-div">
            <?php include WP_PLUGIN_DIR . '/theme-my-login/admin/admin-email.php'; ?>
        </div>
        
    </div>
    
    <p><input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes'); ?>" /></p>
    </form>
    
</div>
