<?php

if ( !class_exists('WPLogin') ) {
    class WPLogin {
    
        var $textdomain;
    
        var $action;
        var $errors;
        var $redirect_to;
        var $secure_cookie;

        var $instance;
        
        var $options;

        function WPLogin($textdomain = '', $options = '') {
            $this->__construct($textdomain, $options);
        }
        
        function __construct($textdomain = '', $options = '') {
        
            $this->textdomain = $textdomain;
            
            $this->LoadOptions($options);

            $this->ForceSSL();
            
            $this->action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
            $this->instance = isset($_REQUEST['instance']) ? $_REQUEST['instance'] : '';
            $this->errors = new WP_Error();
            
            if ( isset($_GET['key']) )
                $this->action = 'resetpass';

            nocache_headers();

            header('Content-Type: '.get_bloginfo('html_type').'; charset='.get_bloginfo('charset'));
            
            $this->HandleRelocate();
            
            $this->SetTestCookie();
            
            if ( !empty( $_REQUEST['redirect_to'] ) ) {
                $this->redirect_to = $_REQUEST['redirect_to'];
                // Redirect to https if user wants ssl
                if ( $this->secure_cookie && false !== strpos($this->redirect_to, 'wp-admin') )
                    $this->redirect_to = preg_replace('|^http://|', 'https://', $this->redirect_to);
            } else {
                $this->redirect_to = admin_url();
            }

            $this->redirect_to = apply_filters('login_redirect', $this->redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', '');

            // allow plugins to override the default actions, and to add extra actions if they want
            do_action('login_form_' . $this->action);

            switch ($this->action) {
            
                case 'logout' :
                    $this->LogoutAction();
                    break;
                    
                case 'lostpassword' :
                case 'retrievepassword' :
                    $this->RetrievePasswordAction();
                    break;
                    
                case 'resetpass' :
                case 'rp' :
                    $this->ResetPassAction();
                    break;
                    
                case 'register' :
                    $this->RegisterAction();
                    break;
                    
                case 'login' :
                default:
                    $this->LoginAction();
                    break;
            }
        
        }
        
        function Display($instance, $args = '') {
            global $user_ID, $current_user, $wp_version;
            
            $this->LoadOptions($args);

            $action = (isset($this->options['default_action'])) ? $this->options['default_action'] : 'login';
            if ( $instance == $this->instance || (empty($this->instance) && 'tml-main' == $instance) )
                $action = $this->action;
                
            ob_start();
            echo $this->options['before_widget'];
            if ( is_user_logged_in() ) {
                if ( $this->options['logged_in_widget'] ) {
                    $user = new WP_User($user_ID);
                    $user_role = array_shift($user->roles);
                    $replace_this = array('/%user_login%/', '/%display_name%/');
                    $replace_with = array($user->user_login, $user->display_name);
                    $welcome = preg_replace($replace_this, $replace_with, $this->options['welcome_title']);
                    if ( $this->options['show_title'] )
                        echo $this->options['before_title'] . $welcome . $this->options['after_title'] . "\n";
                    if ($this->options['show_gravatar'])
                        echo '<div class="login-avatar">' . get_avatar( $user_ID, $size = $this->options['gravatar_size'] ) . '</div>' . "\n";
                    do_action('login_avatar', $current_user);
                    echo '<ul class="login-links">' . "\n";
                    foreach ($this->options['links'][$user_role] as $key => $data) {
                        echo '<li><a href="' . $data['url'] . '">' . $data['title'] . '</a></li>' . "\n";
                    }
                    do_action('login_links', $current_user);
                    $redirect = $this->GuessURL();
                    if ( version_compare($wp_version, '2.7', '>=') )
                        echo '<li><a href="' . wp_logout_url($redirect) . '">' . __('Log Out') . '</a></li>' . "\n";
                    else
                        echo '<li><a href="' . site_url('wp-login.php?action=logout&redirect_to='.$redirect, 'login') . '">' . __('Log Out') . '</a></li>' . "\n";
                    echo '</ul>' . "\n";
                }
            } else {
                if ( $this->options['show_title'] )
                    echo $this->options['before_title'] . $this->GetTitle($instance) . $this->options['after_title'] . "\n";
                if ( $instance == $this->instance || !empty($action) ) {
                    switch ($action) {
                        case 'lostpassword' :
                        case 'retrievepassword' :
                            echo $this->RetrievePasswordForm($instance);
                            break;
                        case 'register' :
                            echo $this->RegisterForm($instance);
                            break;
                        case 'login' :
                        default :
                            echo $this->LoginForm($instance);
                            break;
                    }
                } else {
                    echo $this->LoginForm($instance);
                }
            }
            echo $this->options['after_widget'] . "\n";
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        }
        
        function LoadOptions($options = array()) {
            $this->options = wp_parse_args($options);
        }
        
        function GetTitle($instance) {
            $action = (isset($this->options['default_action'])) ? $this->options['default_action'] : 'login';
            if ( $instance == $this->instance || (empty($this->instance) && 'tml-main' == $instance) )
                $action = $this->action;

            switch ($action) {
                case 'register':
                    return $this->options['register_title'];
                    break;
                case 'lostpassword':
                case 'retrievepassword':
                    return $this->options['lost_pass_title'];
                    break;
                case 'login':
                default:
                    return $this->options['login_title'];
                    break;
            }
        }

        function PageHeader($instance, $message = '') {
            global $error;

            echo '<div class="login" id="' . $instance . '">';
            
            if ( !empty( $message ) ) echo '<p class="message">' . apply_filters('login_message', $message) . "</p>\n";

            // Incase a plugin uses $error rather than the $errors object
            if ( !empty( $error ) ) {
                $this->errors->add('error', $error);
                unset($error);
            }

            if ( $instance == $this->instance || (empty($this->instance) && 'tml-main' == $instance) ) {
                if ( $this->errors->get_error_code() ) {
                    $errors = '';
                    $messages = '';
                    foreach ( $this->errors->get_error_codes() as $code ) {
                        $severity = $this->errors->get_error_data($code);
                        foreach ( $this->errors->get_error_messages($code) as $error ) {
                            if ( 'message' == $severity )
                                $messages .= '    ' . $error . "<br />\n";
                            else
                                $errors .= '    ' . $error . "<br />\n";
                        }
                    }
                    if ( !empty($errors) )
                        echo '<p class="error">' . apply_filters('login_errors', $errors) . "</p>\n";
                    if ( !empty($messages) )
                        echo '<p class="message">' . apply_filters('login_messages', $messages) . "</p>\n";
                }
            }
        }
        
        function PageFooter($instance) {
            $action = (isset($this->options['default_action'])) ? $this->options['default_action'] : 'login';
            if ( $instance == $this->instance || (empty($this->instance) && 'tml-main' == $instance) )
                $action = $this->action;
                
            echo '<ul class="links">' . "\n";
            if ( $this->options['show_log_link'] && in_array($action, array('register', 'lostpassword')) || $action == 'login' && isset($_GET['checkemail']) && 'registered' != $_GET['checkemail'] ) {
                $url = $this->GuessURL(array('instance' => $instance, 'action' => 'login'));
                echo '<li><a href="' . $url . '">' . $this->options['login_title'] . '</a></li>' . "\n";
            }
            if ( $this->options['show_reg_link'] && get_option('users_can_register') ) {
                if ( 'register' != $action ) {
                    $url = ($this->options['register_widget']) ? $this->GuessURL(array('instance' => $instance, 'action' => 'register')) : site_url('wp-login.php?action=register', 'login');
                    $url = apply_filters('login_footer_registration_link', $url);
                    echo '<li><a href="' . $url . '">' . $this->options['register_title'] . '</a></li>' . "\n";
                }
            }
            if ( $this->options['show_pass_link'] ) {
                if ( 'lostpassword' != $action ) {
                    $url = ($this->options['lost_pass_widget']) ? $this->GuessURL(array('instance' => $instance, 'action' => 'lostpassword')) : site_url('wp-login.php?action=lostpassword', 'login');
                    $url = apply_filters('login_footer_forgotpassword_link', $url);
                    echo '<li><a href="' . $url . '">' . $this->options['lost_pass_title'] . '</a></li>' . "\n";
                }
            }
            echo '</ul>' . "\n";
            echo '</div>' . "\n";
        }
        
        function LoginForm($instance) {
        
            // Clear errors if loggedout is set.
            if ( !empty($_GET['loggedout']) )
                $this->errors = new WP_Error();

            // If cookies are disabled we can't log in even with a valid user+pass
            if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
                $this->errors->add('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress."));

            // Some parts of this script use the main login form to display a message
            if        ( isset($_GET['loggedout']) && TRUE == $_GET['loggedout'] )            $this->errors->add('loggedout', __('You are now logged out.'), 'message');
            elseif    ( isset($_GET['registration']) && 'disabled' == $_GET['registration'] )    $this->errors->add('registerdisabled', __('User registration is currently not allowed.'));
            elseif    ( isset($_GET['checkemail']) && 'confirm' == $_GET['checkemail'] )    $this->errors->add('confirm', __('Check your e-mail for the confirmation link.'), 'message');
            elseif    ( isset($_GET['checkemail']) && 'newpass' == $_GET['checkemail'] )    $this->errors->add('newpass', __('Check your e-mail for your new password.'), 'message');
            elseif    ( isset($_GET['checkemail']) && 'registered' == $_GET['checkemail'] )    $this->errors->add('registered', $this->options['success_message'], 'message');
            
            $this->PageHeader($instance);

            if ( isset($_POST['log']) )
                $user_login = ( 'incorrect_password' == $this->errors->get_error_code() || 'empty_password' == $this->errors->get_error_code() ) ? attribute_escape(stripslashes($_POST['log'])) : '';

            $user_login = ( $this->instance == $instance && isset($user_login) ) ? $user_login : '';

            if ( !isset($_GET['checkemail']) || (isset($_GET['checkemail']) && $instance != $this->instance) || (!in_array( $_GET['checkemail'], array('confirm', 'newpass') ) && $instance == $this->instance) || (in_array( $_GET['checkemail'], array('confirm', 'newpass') ) && $instance != $this->instance) ) {
                ?>
                <form name="loginform" id="loginform-<?php echo $instance; ?>" action="<?php echo $this->GuessURL(array('instance' => $instance, 'action' => 'login')); ?>" method="post">
                    <p>
                        <label for="log"><?php _e('Username') ?></label>
                        <input type="text" name="log" id="user_login-<?php echo $instance; ?>" class="input" value="<?php echo isset($user_login) ? $user_login : ''; ?>" size="20" />
                    </p>
                    <p>
                        <label for="pwd"><?php _e('Password') ?></label>
                        <input type="password" name="pwd" id="user_pass-<?php echo $instance; ?>" class="input" value="" size="20" />
                    </p>
                <?php do_action('login_form', $instance); ?>
                    <p class="forgetmenot"><input name="rememberme" type="checkbox" id="rememberme-<?php echo $instance; ?>" value="forever" /> <label for="rememberme"><?php _e('Remember Me'); ?></label></p>
                    <p class="submit">
                        <input type="submit" name="login-submit" id="login-submit-<?php echo $instance; ?>" value="<?php _e('Log In'); ?>" />
                        <input type="hidden" name="redirect_to" value="<?php echo attribute_escape($this->redirect_to); ?>" />
                        <input type="hidden" name="testcookie" value="1" />
                    </p>
                </form>
                <?php
            }
            if ($instance == $this->instance) { ?>
                <script type="text/javascript">
                <?php if ( $user_login ) { ?>
                    setTimeout( function(){ try{
                    d = document.getElementById('user_pass-<?php echo $this->instance; ?>');
                    d.value = '';
                    d.focus();
                    } catch(e){}
                    }, 200);
                <?php } else { ?>
                    try{document.getElementById('user_login-<?php echo $this->instance; ?>').focus();}catch(e){}
                <?php } ?>
                </script>
            <?php }
            $this->PageFooter($instance);
        }
        
        function RegisterForm($instance) {
            $user_login = isset($_POST['user_login']) ? $_POST['user_login'] : '';
            $user_email = isset($_POST['user_email']) ? $_POST['user_email'] : '';
            $this->PageHeader($instance);
            ?>
            <form name="registerform" id="registerform-<?php echo $instance; ?>" action="<?php echo $this->GuessURL(array('instance' => $instance, 'action' => 'register')); ?>" method="post">
                <p>
                    <label for="user_login"><?php _e('Username') ?></label>
                    <input type="text" name="user_login" id="user_login-<?php echo $instance; ?>" class="input" value="<?php echo attribute_escape(stripslashes($user_login)); ?>" size="20" />
                </p>
                <p>
                    <label for="user_email"><?php _e('E-mail') ?></label>
                    <input type="text" name="user_email" id="user_email-<?php echo $instance; ?>" class="input" value="<?php echo attribute_escape(stripslashes($user_email)); ?>" size="20" />
                </p>
                <?php do_action('register_form', $instance); ?>
                <p id="reg_passmail-<?php echo $instance; ?>"><?php echo $this->options['register_message']; ?></p>
                <p class="submit">
                    <input type="submit" name="register-submit" id="register-submit-<?php echo $instance; ?>" value="<?php _e('Register'); ?>" />
                </p>
            </form>
            <?php
            if ($instance == $this->instance) { ?>
                <script type="text/javascript">
                try{document.getElementById('user_login-<?php echo $this->instance; ?>').focus();}catch(e){}
                </script>
            <?php }
            $this->PageFooter($instance);
        }
        
        function RetrievePasswordForm($instance) {
            do_action('lost_password', $instance);
            $this->PageHeader($instance, $this->options['lost_pass_message']);
            $user_login = isset($_POST['user_login']) ? stripslashes($_POST['user_login']) : '';
            ?>
            <form name="lostpasswordform" id="lostpasswordform-<?php echo $instance; ?>" action="<?php echo $this->GuessURL(array('instance' => $instance, 'action' => 'lostpassword')); ?>" method="post">
                <p>
                    <label for="user_login"><?php _e('Username or E-mail:') ?></label>
                    <input type="text" name="user_login" id="user_login-<?php echo $instance; ?>" class="input" value="<?php echo attribute_escape($user_login); ?>" size="20" />
                </p>
                <?php do_action('lostpassword_form', $instance); ?>
                <p class="submit">
                    <input type="submit" name="lostpassword-submit" id="lostpassword-submit-<?php echo $instance; ?>" value="<?php _e('Get New Password'); ?>" />
                </p>
            </form>
            <?php
            if ($instance == $this->instance) { ?>
                <script type="text/javascript">
                try{document.getElementById('user_login-<?php echo $this->instance; ?>').focus();}catch(e){}
                </script>
            <?php }
            $this->PageFooter($instance);
        }
        
        function LogoutAction() {
            global $wp_version;
            
            if ( version_compare($wp_version, '2.7', '>=') )
                check_admin_referer('log-out');
            wp_logout();

            $this->redirect_to = site_url('wp-login.php?loggedout=true', 'login');
            if ( isset( $_REQUEST['redirect_to'] ) )
                $this->redirect_to = $_REQUEST['redirect_to'];

            wp_safe_redirect($this->redirect_to);
            exit();
        }
        
        function RetrievePasswordAction() {
            if ( isset($_POST['lostpassword-submit']) ) {
                $this->errors = $this->RetrievePassword();
                if ( !is_wp_error($this->errors) ) {
                    $this->redirect_to = ( isset($this->instance) ) ? $this->GuessURL(array('instance' => $this->instance, 'checkemail' => 'confirm')) : site_url('wp-login.php?instance='.$this->instance.'&checkemail=confirm', 'login');
                    wp_redirect($this->redirect_to);
                    exit();
                }
            }

            if ( isset($_GET['error']) && 'invalidkey' == $_GET['error'] )
                $this->errors->add('invalidkey', __('Sorry, that key does not appear to be valid.'));
        }
        
        function ResetPassAction() {
            $this->errors = $this->ResetPassword($_GET['key'], $_GET['login']);

            if ( ! is_wp_error($this->errors) ) {
                $this->redirect_to = ( isset($this->instance) ) ? $this->GuessURL(array('checkemail' => 'newpass')) : site_url('wp-login.php?checkemail=newpass', 'login');
                wp_redirect($this->redirect_to);
                exit();
            }

            $this->redirect_to = ( isset($this->instance) ) ? $this->GuessURL(array('action' => 'lostpassword', 'error' => 'invalidkey')) : site_url('wp-login.php?action=lostpassword&error=invalidkey', 'login');
            wp_redirect($this->redirect_to);
            exit();
        }
        
        function RegisterAction() {
            if ( !get_option('users_can_register') ) {
                $this->redirect_to = ( isset($this->instance) ) ? $this->GuessURL(array('registration' => 'disabled')) : site_url('wp-login.php?registration=disabled', 'login');
                wp_redirect($this->redirect_to);
                exit();
            }

            if ( isset($_POST['register-submit']) ) {
                require_once (ABSPATH . WPINC . '/registration.php');

                $user_login = $_POST['user_login'];
                $user_email = $_POST['user_email'];
                $this->errors = $this->RegisterNewUser($user_login, $user_email);

                if ( !is_wp_error($this->errors) ) {
                    $this->redirect_to = ( isset($this->instance) ) ? $this->GuessURL(array('checkemail' => 'registered')) : site_url('wp-login.php?checkemail=registered', 'login');
                    wp_redirect($this->redirect_to);
                    exit();
                }
            }
        }
        
        function LoginAction() {
            $this->secure_cookie = '';

            // If the user wants ssl but the session is not ssl, force a secure cookie.
            if ( !empty($_POST['log']) && !force_ssl_admin() ) {
                $user_name = sanitize_user($_POST['log']);
                if ( $user = get_userdatabylogin($user_name) ) {
                    if ( get_user_option('use_ssl', $user->ID) ) {
                        $this->secure_cookie = true;
                        force_ssl_admin(true);
                    }
                }
            }

            if ( !$this->secure_cookie && is_ssl() && force_ssl_login() && !force_ssl_admin() )
                $this->secure_cookie = false;

            if ( isset($_POST['login-submit']) ) {
                $user = wp_signon('', $this->secure_cookie);

                $this->redirect_to = apply_filters('login_redirect', $this->redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user);

                if ( !is_wp_error($user) ) {
                    // If the user can't edit posts, send them to their profile.
                    if ( !$user->has_cap('edit_posts') && ( empty( $this->redirect_to ) || $this->redirect_to == 'wp-admin/' || $this->redirect_to == admin_url() ) )
                        $this->redirect_to = admin_url('profile.php');
                    wp_safe_redirect($this->redirect_to);
                    exit();
                }
                
                $this->errors = $user;
                
            }

        }
        
        function ForceSSL() {
            if ( force_ssl_admin() && !is_ssl() ) {
                if ( 0 === strpos($_SERVER['REQUEST_URI'], 'http') ) {
                    wp_redirect(preg_replace('|^http://|', 'https://', $_SERVER['REQUEST_URI']));
                    exit();
                } else {
                    wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                    exit();
                }
            }
        }
        
        function SetTestCookie() {
            //Set a cookie now to see if they are supported by the browser.
            setcookie(TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN);
            if ( SITECOOKIEPATH != COOKIEPATH )
                setcookie(TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN);
        }
        
        function HandleRelocate() {
            if ( defined('RELOCATE') ) { // Move flag is set
                if ( isset( $_SERVER['PATH_INFO'] ) && ($_SERVER['PATH_INFO'] != $_SERVER['PHP_SELF']) )
                    $_SERVER['PHP_SELF'] = str_replace( $_SERVER['PATH_INFO'], '', $_SERVER['PHP_SELF'] );

                $schema = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
                if ( dirname($schema . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']) != get_option('siteurl') )
                    update_option('siteurl', dirname($schema . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']) );
            }
        }
        
        function GuessURL($args = array()) {
            $keys = array('action', 'checkemail', 'error', 'loggedout', 'registered', 'redirect_to', 'updated', 'key');
            $url = remove_query_arg($keys);

            if (!empty($args))
                $url = add_query_arg($args, $url);

            return $url;
        }
        
        function RetrievePassword() {
            global $wpdb;

            $errors = new WP_Error();

            if ( empty( $_POST['user_login'] ) && empty( $_POST['user_email'] ) )
                $errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or e-mail address.'));

            if ( strpos($_POST['user_login'], '@') ) {
                $user_data = get_user_by_email(trim($_POST['user_login']));
                if ( empty($user_data) )
                    $errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.'));
            } else {
                $login = trim($_POST['user_login']);
                $user_data = get_userdatabylogin($login);
            }

            do_action('lostpassword_post');

            if ( $errors->get_error_code() )
                return $errors;

            if ( !$user_data ) {
                $errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or e-mail.'));
                return $errors;
            }

            // redefining user_login ensures we return the right case in the email
            $user_login = $user_data->user_login;
            $user_email = $user_data->user_email;

            do_action('retreive_password', $user_login);  // Misspelled and deprecated
            do_action('retrieve_password', $user_login);

            $allow = apply_filters('allow_password_reset', true, $user_data->ID);

            if ( ! $allow )
                return new WP_Error('no_password_reset', __('Password reset is not allowed for this user'));
            else if ( is_wp_error($allow) )
                return $allow;

            $key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
            if ( empty($key) ) {
                // Generate something random for a key...
                $key = wp_generate_password(20, false);
                do_action('retrieve_password_key', $user_login, $key);
                // Now insert the new md5 key into the db
                $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
            }
            $message = __('Someone has asked to reset the password for the following site and username.') . "\r\n\r\n";
            $message .= get_option('siteurl') . "\r\n\r\n";
            $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
            $message .= __('To reset your password visit the following address, otherwise just ignore this email and nothing will happen.') . "\r\n\r\n";
            $message .= site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . "\r\n";

            $title = sprintf(__('[%s] Password Reset'), get_option('blogname'));

            $title = apply_filters('retrieve_password_title', $title, $user_data);
            $message = apply_filters('retrieve_password_message', $message, $key, $user_data);

            tml_apply_mail_filters();
            if ( $message && !wp_mail($user_email, $title, $message) )
                die('<p>' . __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') . '</p>');
            tml_remove_mail_filters();
            
            return true;
        }
        
        function ResetPassword($key, $login) {
            global $wpdb;

            $key = preg_replace('/[^a-z0-9]/i', '', $key);

            if ( empty( $key ) || !is_string( $key ) )
                return new WP_Error('invalid_key', __('Invalid key'));

            if ( empty($login) || !is_string($login) )
                return new WP_Error('invalid_key', __('Invalid key'));

            $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login));
            if ( empty( $user ) )
                return new WP_Error('invalid_key', __('Invalid key'));

            // Generate something random for a password...
            $new_pass = wp_generate_password();

            do_action('password_reset', $user, $new_pass);

            wp_set_password($new_pass, $user->ID);
            update_usermeta($user->ID, 'default_password_nag', true); //Set up the Password change nag.
            $message  = sprintf(__('Username: %s'), $user->user_login) . "\r\n";
            $message .= sprintf(__('Password: %s'), $new_pass) . "\r\n";
            $message .= site_url('wp-login.php', 'login') . "\r\n";

            $title = sprintf(__('[%s] Your new password'), get_option('blogname'));

            $title = apply_filters('password_reset_title', $title, $user);
            $message = apply_filters('password_reset_message', $message, $new_pass, $user);

            tml_apply_mail_filters();
            if ( $message && !wp_mail($user->user_email, $title, $message) )
                die('<p>' . __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') . '</p>');
            tml_remove_mail_filters();
            
            if ( !$this->options['reset_pass_email']['admin_disable'] )
                wp_password_change_notification($user);

            return true;
        }
        
        function RegisterNewUser($user_login, $user_email) {
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

    }
}

?>
