<?php

global $ThemeMyLogin, $wp_roles, $wp_version;
$user_roles = $wp_roles->get_names();

if ( $_POST ) {

    check_admin_referer('theme-my-login-settings');

    $ThemeMyLogin->options['uninstall'] = isset($_POST['uninstall']) ? 1 : 0;
    $ThemeMyLogin->options['show_page'] = isset($_POST['show_page']) ? 1 : 0;
    $ThemeMyLogin->options['custom_pass'] = isset($_POST['custom_pass']) ? 1 : 0;
    if ( isset($_POST['moderate_users']) ) {
        $ThemeMyLogin->options['moderate_users'] = 1;
        add_role('pending', 'Pending', array());
        add_role('denied', 'Denied', array());
    } else {
        $ThemeMyLogin->options['moderate_users'] = 0;
        remove_role('pending');
        remove_role('denied');
    }
    $ThemeMyLogin->options['use_css'] = isset($_POST['use_css']) ? 1 : 0;
    $ThemeMyLogin->options['page_id'] = (int) $_POST['page_id'];
    $ThemeMyLogin->options['email_from_name'] = stripslashes($_POST['email_from_name']);
    $ThemeMyLogin->options['email_from'] = stripslashes($_POST['email_from']);
    $ThemeMyLogin->options['email_content_type'] = stripslashes($_POST['email_content_type']);
    
    $ThemeMyLogin->options['welcome_title'] = stripslashes($_POST['welcome_title']);
    $ThemeMyLogin->options['login_title'] = stripslashes($_POST['login_title']);
    $ThemeMyLogin->options['register_title'] = stripslashes($_POST['register_title']);
    $ThemeMyLogin->options['lost_pass_title'] = stripslashes($_POST['lost_pass_title']);
    $ThemeMyLogin->options['logout_title'] = stripslashes($_POST['logout_title']);
    
    $ThemeMyLogin->options['register_message'] = stripslashes($_POST['register_message']);
    $ThemeMyLogin->options['success_message'] = stripslashes($_POST['success_message']);
    $ThemeMyLogin->options['lost_pass_message'] = stripslashes($_POST['lost_pass_message']);

    foreach ( $_POST['links'] as $role => $tmp ) {
        foreach ( $tmp as $key => $data ) {
            $links[$role][] = array('title' => $data['title'], 'url' => $data['url']);
        }
    }
    $ThemeMyLogin->options['links'] = $links;
    
    foreach ( $_POST['redirects'] as $role => $data ) {
        $redirects[$role] = array('login_url' => $data['login_url']);
    }
    $ThemeMyLogin->options['redirects'] = $redirects;
    
    $ThemeMyLogin->options['retrieve_pass_email'] = array(
        'subject' => stripslashes($_POST['retrieve_pass_subject']),
        'message' => stripslashes($_POST['retrieve_pass_message'])
        );
    $ThemeMyLogin->options['reset_pass_email'] = array(
        'subject' => stripslashes($_POST['reset_pass_subject']),
        'message' => stripslashes($_POST['reset_pass_message']),
        'admin_disable' => isset($_POST['reset_pass_admin_disable']) ? 1 : 0
        );
    $ThemeMyLogin->options['registration_email'] = array(
        'subject' => stripslashes($_POST['registration_subject']),
        'message' => stripslashes($_POST['registration_message']),
        'admin_disable' => isset($_POST['registration_admin_disable']) ? 1 : 0,
        'user_disable' => isset($_POST['registration_user_disable']) ? 1 : 0
        );
    $ThemeMyLogin->options['user_approval_email'] = array(
        'subject' => stripslashes($_POST['user_approval_subject']),
        'message' => stripslashes($_POST['user_approval_message'])
        );
    $ThemeMyLogin->options['user_denial_email'] = array(
        'subject' => stripslashes($_POST['user_denial_subject']),
        'message' => stripslashes($_POST['user_denial_message'])
        );
        
    $ThemeMyLogin->SaveOptions();

    if ( isset($_POST['uninstall']) ) {
        $info_message = __('To complete uninstall, deactivate this plugin. If you do not wish to uninstall, please uncheck the "Complete Uninstall" checkbox.', 'theme-my-login');
    } elseif ( isset($_POST['defaults']) ) {
        $ThemeMyLogin->options = '';
        $ThemeMyLogin->InitOptions(true);
        $info_message = __('All settings restored to default state.', 'theme-my-login');
    } else $info_message =__('Settings saved.', 'theme-my-login');
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
            <li><a href="#fragment-2">Template</a></li>
            <li><a href="#fragment-3">Links</a></li>
            <li><a href="#fragment-4">Redirection</a></li>
            <li><a href="#fragment-5">E-mail</a></li>
        </ul>
        
        <div id="fragment-1" class="tabs-div">
            <?php include WP_PLUGIN_DIR . '/theme-my-login/admin/admin-general.php'; ?>
        </div>

        <div id="fragment-2" class="tabs-div">
            <?php include WP_PLUGIN_DIR . '/theme-my-login/admin/admin-template.php'; ?>
        </div>

        <div id="fragment-3" class="tabs-div">
            <?php include WP_PLUGIN_DIR . '/theme-my-login/admin/admin-links.php'; ?>
        </div>

        <div id="fragment-4" class="tabs-div">
            <?php include WP_PLUGIN_DIR . '/theme-my-login/admin/admin-redirection.php'; ?>
        </div>

        <div id="fragment-5" class="tabs-div">
            <?php include WP_PLUGIN_DIR . '/theme-my-login/admin/admin-email.php'; ?>
        </div>
        
    </div>
    
    <?php if ( version_compare($wp_version, '2.7', '>=') ) : ?>
    <p><input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes'); ?>" /></p>
    <?php else : ?>
    <p><input type="submit" name="Submit" class="button" value="<?php _e('Save Changes'); ?>" /></p>
    <?php endif; ?>
    </form>
    
</div>
