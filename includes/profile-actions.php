<?php

require (WP_PLUGIN_DIR . '/theme-my-login/includes/wp-login-functions.php');
require_once (ABSPATH . '/wp-admin/includes/misc.php');
require_once (ABSPATH . '/wp-admin/includes/user.php');
require_once (ABSPATH . WPINC . '/registration.php');

if ( !isset($user_id) ) {
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
case 'update':
    check_admin_referer('update-user_' . $user_id);

    if ( !current_user_can('edit_user', $user_id) )
        wp_die(__('You do not have permission to edit this user.'));

    do_action('personal_options_update');

    $this->errors = edit_user($user_id);

    if ( !is_wp_error( $this->errors ) ) {
        $redirect = (admin_url('profile.php') . '?updated=true');
        wp_redirect($redirect);
        exit;
    }
    break;
} // end action switch
?>
