<?php

require('wp271-functions.php');

if (!function_exists('register_new_user')) :
function register_new_user($user_login, $user_email) {
    $errors = new WP_Error();

    $user_login = sanitize_user( $user_login );
    $user_email = apply_filters( 'user_registration_email', $user_email );

    // Check the username
    if ( $user_login == '' )
        $errors->add('empty_username', __('<strong>ERROR</strong>: Please enter a username.'));
    elseif ( !validate_username( $user_login ) ) {
        $errors->add('invalid_username', __('<strong>ERROR</strong>: This username is invalid.  Please enter a valid username.'));
        $user_login = '';
    } elseif ( username_exists( $user_login ) )
        $errors->add('username_exists', __('<strong>ERROR</strong>: This username is already registered, please choose another one.'));

    // Check the e-mail address
    if ($user_email == '') {
        $errors->add('empty_email', __('<strong>ERROR</strong>: Please type your e-mail address.'));
    } elseif ( !is_email( $user_email ) ) {
        $errors->add('invalid_email', __('<strong>ERROR</strong>: The email address isn&#8217;t correct.'));
        $user_email = '';
    } elseif ( email_exists( $user_email ) )
        $errors->add('email_exists', __('<strong>ERROR</strong>: This email is already registered, please choose another one.'));

    do_action('register_post', $user_login, $user_email, $errors);

    $errors = apply_filters( 'registration_errors', $errors );

    if ( $errors->get_error_code() )
        return $errors;

    $user_pass = wp_generate_password();
    $user_id = wp_create_user( $user_login, $user_pass, $user_email );
    if ( !$user_id ) {
        $errors->add('registerfail', sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !'), get_option('admin_email')));
        return $errors;
    }

    wp_new_user_notification($user_id, $user_pass);

    return $user_id;
}
endif;

$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);

if ( !get_option('users_can_register') ) {
    wp_redirect('wp-login.php?registration=disabled');
    exit();
}

$user_login = '';
$user_email = '';
if ( $http_post ) {
    require_once( ABSPATH . WPINC . '/registration.php');

    $user_login = $_POST['user_login'];
    $user_email = $_POST['user_email'];
    $errors = register_new_user($user_login, $user_email);
    if ( !is_wp_error($errors) ) {
        wp_redirect('wp-login.php?checkemail=registered');
        exit();
    }
}

$this->DoHeader('', $errors);
?>

    <form name="registerform" id="registerform" action="<?php echo get_permalink($this->GetOption('page_id')); ?>?action=register" method="post">
        <p>
            <label><?php _e('Username') ?><br />
            <input type="text" name="user_login" id="user_login" class="input" value="<?php echo attribute_escape(stripslashes($user_login)); ?>" size="20" tabindex="10" /></label>
        </p>
        <p>
            <label><?php _e('E-mail') ?><br />
            <input type="text" name="user_email" id="user_email" class="input" value="<?php echo attribute_escape(stripslashes($user_email)); ?>" size="25" tabindex="20" /></label>
        </p>
        <?php do_action('register_form'); ?>
        <p id="reg_passmail"><?php _e($this->GetOption('register_msg')) ?></p>
        <p class="submit"><input type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Register'); ?>" tabindex="100" /></p>
    </form>

    <ul class="nav">
        <li><a href="<?php echo site_url('wp-login.php', 'login') ?>"><?php _e('Log in') ?></a></li>
        <li><a href="<?php echo site_url('wp-login.php?action=lostpassword', 'login') ?>" title="<?php _e('Password Lost and Found') ?>"><?php _e('Lost your password?') ?></a></li>
    </ul>

</div>
