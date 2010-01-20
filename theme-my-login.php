<?php
/*
Plugin Name: Theme My Login
Plugin URI: http://www.jfarthing.com/wordpress-plugins/theme-my-login-plugin
Description: Themes the WordPress login, registration and forgot password pages according to your theme.
Version: 4.4
Author: Jeff Farthing
Author URI: http://www.jfarthing.com
Text Domain: theme-my-login
*/


if ( !class_exists('ThemeMyLogin') ) {
    class ThemeMyLogin {

        var $options;
        var $instance_options;

        var $request_instance;
        var $current_instance;
        
        var $action;
        var $errors;
        var $redirect_to;

        function ThemeMyLogin() {
            $this->__construct();
        }
        
        function __construct() {

            $this->loadOptions();
            
            load_plugin_textdomain('theme-my-login', '', plugin_basename(dirname(__FILE__) . '/language'));

            register_activation_hook(__FILE__, array( &$this, 'install' ));
            register_uninstall_hook(__FILE__, array(&$this, 'uninstall'));
            
            add_action('init', array(&$this, 'init'));
            add_action('template_redirect', array(&$this, 'templateRedirect'), 1);
            
            if ( $this->options['custom_pass'] ) {
                add_action('register_form', array(&$this, 'customPassForm'));
                add_action('registration_errors', array(&$this, 'customPassErrors'));
                add_action('user_register', array(&$this, 'setUserPassword'));
            }
            
            if ( in_array($this->options['moderation'], array('email', 'admin')) ) {
                add_action('user_register', array(&$this, 'userModeration'), 100);
                add_action('login_form_activate', array(&$this, 'userActivation'));
                add_action('authenticate', array(&$this, 'authenticate'), 100, 3);
                add_action('delete_user', array(&$this, 'denyUser'));
                add_filter('allow_password_reset', array(&$this, 'allowPasswordReset'), 10, 2);
                add_filter('user_row_actions', array(&$this, 'userRowActions'), 10, 2);
            }
            
            add_action('admin_init', array(&$this, 'adminInit'));
            add_action('admin_menu', array(&$this, 'adminMenu'));
            
            add_filter('the_posts', array(&$this, 'createPage'));
            add_filter('page_rewrite_rules', array(&$this, 'pageRewriteRules'));
            add_filter('site_url', array(&$this, 'siteURL'), 10, 3);
            add_filter('login_redirect', array(&$this, 'loginRedirect'), 10, 3);
            add_filter('logout_redirect', array(&$this, 'logoutRedirect'), 10, 3);
            
            add_shortcode('theme-my-login', array(&$this, 'shortcode'));

        }
        
        function init() {
            // This happens before $wp and $wp_query are populated, but after all plugins, pluggable functions and widgets are loaded.
            $this->request_instance = ( isset($_REQUEST['instance']) ) ? $_REQUEST['instance'] : 'tml-page';
            $this->action = ( isset($_REQUEST['action']) ) ? $_REQUEST['action'] : '';
            $this->errors = new WP_Error();

            // validate action so as to default to the login screen
            if ( !in_array($this->action, array('logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'register', 'login'), true) && false === has_filter('login_form_' . $this->action) )
                $this->action = 'login';
        }

        // Create a dummy page to handle our actions
        function createPage($posts) {

            if ( defined('IS_TML') && IS_TML )
                return $posts;
                
            if ( $page = get_page_by_title('login') )
                return $posts;

            $pagename = get_query_var('pagename');
            
            $action = ( isset($_REQUEST['action']) && 'tml-page' == $this->request_instance ) ? $_REQUEST['action'] : 'login';

            if ( 'login' == $pagename ) {
                $posts[] = (object) array(
                    'post_content' => '[theme-my-login show_title="0" before_widget="" after_widget="" instance="tml-page"]',
                    'post_title' => $this->getTitle($action),
                    'post_excerpt' => '',
                    'post_status' => 'publish',
                    'comment_status' => 'closed',
                    'ping_status' => 'closed',
                    'post_name' => 'login',
                    'post_type' => 'page'
                    );
            }
            return $posts;
        }
        
        // Make our dummy page work with permalinks
        function pageRewriteRules($rules) {
            global $wp_rewrite;
            $wp_rewrite->add_rewrite_tag('%pagename%', '(login)', 'pagename=');
            $rules = array_merge($rules, $wp_rewrite->generate_rewrite_rules($wp_rewrite->get_page_permastruct(), EP_PAGES));
            return $rules;
        }
        
        function adminInit() {
            global $user_ID, $wp_version, $pagenow;

            if ( 'options-general.php' == $pagenow ) {
                $page = isset($_GET['page']) ? $_GET['page'] : '';
                if ( 'theme-my-login/admin/admin.php'== $page ) {
                    wp_enqueue_script('theme-my-login-admin', plugins_url('/theme-my-login/js/theme-my-login-admin.js'));
                    wp_enqueue_script('jquery-ui-tabs');
                    wp_enqueue_style('theme-my-login-admin', plugins_url('/theme-my-login/css/theme-my-login-admin.css'));

                    $admin_color = get_usermeta($user_ID, 'admin_color');
                    if ( 'classic' == $admin_color )
                        wp_enqueue_style('jquery-colors-classic', plugins_url('/theme-my-login/css/wp-colors-classic/wp-colors-classic.css'));
                    else
                        wp_enqueue_style('jquery-colors-fresh', plugins_url('/theme-my-login/css/wp-colors-fresh/wp-colors-fresh.css'));
                }
            } elseif ( 'users.php' == $pagenow && 'admin' == $this->options['moderation'] ) {
                if ( isset($_GET['action']) && 'approve' == $_GET['action'] ) {
                    check_admin_referer('approve-user');
                    
                    $user = isset($_GET['user']) ? $_GET['user'] : '';
                    if ( !$user )
                        wp_die(__('You can&#8217;t edit that user.', 'theme-my-login'));

                    if ( !current_user_can('edit_user', $user) )
                        wp_die(__('You can&#8217;t edit that user.', 'theme-my-login'));

                    require_once(WP_PLUGIN_DIR . '/theme-my-login/includes/functions.php');
                    if ( !approve_new_user($user) )
                        wp_die(__('You can&#8217;t edit that user.', 'theme-my-login'));

                    add_action('admin_notices', create_function('', "echo '<div id=\"message\" class=\"updated fade\"><p>' . __('User approved.', 'theme-my-login') . '</p></div>';"));
                }
            }
        }
        
        function userRowActions($actions, $user_object) {
            $current_user = wp_get_current_user();
            $user_role = reset($user_object->roles);
            if ( $current_user->ID != $user_object->ID ) {
                if ( 'pending' == $user_role ) {
                    $approve['approve-user'] = '<a href="' . add_query_arg( 'wp_http_referer', urlencode( esc_url( stripslashes( $_SERVER['REQUEST_URI'] ) ) ), wp_nonce_url("users.php?action=approve&amp;user=$user_object->ID", 'approve-user') ) . '">Approve</a>';
                    $actions = array_merge($approve, $actions);
                }
            }
            return $actions;
        }
        
        function adminMenu() {
            add_options_page(__('Theme My Login', 'theme-my-login'), __('Theme My Login', 'theme-my-login'), 8, 'theme-my-login/admin/admin.php');
        }
        
        // If our virtual page is reached, redirect to the right template
        function templateRedirect() {
            if ( is_page('login') )
                define('IS_TML', true);

            if ( is_page('login') || is_active_widget(false, null, 'theme-my-login') ) {

                if ( $this->options['use_css'] ) {
                    if ( file_exists(get_stylesheet_directory() . '/theme-my-login.css') )
                        $css_file = get_stylesheet_directory_uri() . '/theme-my-login.css';
                    elseif ( file_exists(get_template_directory() . '/theme-my-login.css') )
                        $css_file = get_template_directory_uri() . '/theme-my-login.css';
                    else
                        $css_file = plugins_url('/theme-my-login/css/theme-my-login.css');

                    wp_enqueue_style('theme-my-login', $css_file);
                }
                
                //Set a cookie now to see if they are supported by the browser.
                setcookie(TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN);
                if ( SITECOOKIEPATH != COOKIEPATH )
                    setcookie(TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN);

                // allow plugins to override the default actions, and to add extra actions if they want
                do_action('login_form_' . $this->action);

                $http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
                switch ( $this->action ) {
                    case 'logout' :
                        check_admin_referer('log-out');
                    
                        $user = wp_get_current_user();

                        $redirect_to = site_url('wp-login.php?loggedout=true');
                        if ( isset( $_REQUEST['redirect_to'] ) )
                            $redirect_to = $_REQUEST['redirect_to'];
                        
                        $redirect_to = apply_filters('logout_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user);
                    
                        wp_logout();

                        wp_safe_redirect($redirect_to);
                        exit();
                        break;
                    case 'lostpassword' :
                    case 'retrievepassword' :
                        if ( $http_post ) {
                            require_once(WP_PLUGIN_DIR . '/theme-my-login/includes/functions.php');
                            $this->errors = retrieve_password();
                            if ( !is_wp_error($this->errors) ) {
                                $redirect_to = site_url('wp-login.php?checkemail=confirm');
                                if ( 'tml-page' != $this->request_instance )
                                    $redirect_to = $this->getCurrentURL('checkemail=confirm&instance=' . $this->request_instance);
                                wp_redirect($redirect_to);
                                exit();
                            }
                        }

                        if ( isset($_REQUEST['error']) && 'invalidkey' == $_REQUEST['error'] ) $this->errors->add('invalidkey', __('Sorry, that key does not appear to be valid.', 'theme-my-login'));
                        break;
                    case 'resetpass' :
                    case 'rp' :
                        require_once(WP_PLUGIN_DIR . '/theme-my-login/includes/functions.php');
                        $this->errors = reset_password($_GET['key'], $_GET['login']);

                        if ( ! is_wp_error($this->errors) ) {
                            $redirect_to = site_url('wp-login.php?checkemail=newpass');
                            if ( 'tml-page' != $this->request_instance )
                                $redirect_to = $this->getCurrentURL('checkemail=newpass&instance=' . $this->request_instance);
                            wp_redirect($redirect_to);
                            exit();
                        }

                        $redirect_to = site_url('wp-login.php?action=lostpassword&error=invalidkey');
                        if ( 'tml-page' != $this->request_instance )
                            $redirect_to = $this->getCurrentURL('action=lostpassword&error=invalidkey&instance=' . $this->request_instance);
                        wp_redirect($redirect_to);
                        exit();
                        break;
                    case 'register' :
                        if ( !get_option('users_can_register') ) {
                            wp_redirect($this->getCurrentURL('registration=disabled'));
                            exit();
                        }

                        $user_login = '';
                        $user_email = '';
                        if ( $http_post ) {
                            require_once(ABSPATH . WPINC . '/registration.php');
                            require_once(WP_PLUGIN_DIR . '/theme-my-login/includes/functions.php');

                            $user_login = $_POST['user_login'];
                            $user_email = $_POST['user_email'];
                            $this->errors = register_new_user($user_login, $user_email);
                            if ( !is_wp_error($this->errors) ) {
                                if ( 'email' == $this->options['moderation'] )
                                    $redirect_to = $this->getCurrentURL('pending=activation&instance=' . $this->request_instance);
                                elseif ( 'admin' == $this->options['moderation'] )
                                    $redirect_to = $this->getCurrentURL('pending=approval&instance=' . $this->request_instance);
                                else
                                    $redirect_to = $this->getCurrentURL('checkemail=registered&instance=' . $this->request_instance);
                                wp_redirect($redirect_to);
                                exit();
                            }
                        }
                        break;
                    case 'login' :
                    default:
                        $secure_cookie = '';

                        // If the user wants ssl but the session is not ssl, force a secure cookie.
                        if ( !empty($_POST['log']) && !force_ssl_admin() ) {
                            $user_name = sanitize_user($_POST['log']);
                            if ( $user = get_userdatabylogin($user_name) ) {
                                if ( get_user_option('use_ssl', $user->ID) ) {
                                    $secure_cookie = true;
                                    force_ssl_admin(true);
                                }
                            }
                        }

                        if ( isset( $_REQUEST['redirect_to'] ) ) {
                            $this->redirect_to = $_REQUEST['redirect_to'];
                            // Redirect to https if user wants ssl
                            if ( $secure_cookie && false !== strpos($this->redirect_to, 'wp-admin') )
                                $this->redirect_to = preg_replace('|^http://|', 'https://', $this->redirect_to);
                        } else {
                            $this->redirect_to = admin_url();
                        }

                        if ( !$secure_cookie && is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos($this->redirect_to, 'https') ) && ( 0 === strpos($this->redirect_to, 'http') ) )
                            $secure_cookie = false;

                        $user = wp_signon('', $secure_cookie);

                        $this->redirect_to = apply_filters('login_redirect', $this->redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user);

                        if ( !is_wp_error($user) ) {
                            // If the user can't edit posts, send them to their profile.
                            if ( !$user->has_cap('edit_posts') && ( empty( $this->redirect_to ) || $this->redirect_to == 'wp-admin/' || $this->redirect_to == admin_url() ) )
                                $this->redirect_to = admin_url('profile.php');
                            wp_safe_redirect($this->redirect_to);
                            exit();
                        }

                        $this->errors = $user;
                        break;
                }
            }
        }
        
        function display($args = '') {

            $this->instance_options = wp_parse_args($args, $this->options);
            
            $this->action = ( isset($this->instance_options['default_action']) ) ? $this->instance_options['default_action'] : 'login';
            if ( $this->request_instance == $this->current_instance )
                $this->action = $_REQUEST['action'];

            ob_start();
            echo $this->instance_options['before_widget'];
            if ( $this->instance_options['show_title'] )
                echo $this->instance_options['before_title'] . $this->getTitle($this->action) . $this->instance_options['after_title'] . "\n";
            if ( is_user_logged_in() ) {
                $user = wp_get_current_user();
                $user_role = reset($user->roles);
                if ( $this->instance_options['show_gravatar'] )
                    echo '<div class="tml-user-avatar">' . get_avatar( $user->ID, $this->instance_options['gravatar_size'] ) . '</div>' . "\n";
                echo '<ul class="tml-user-links">' . "\n";
                if ( $this->instance_options['links'][$user_role] ) {
                    foreach ( $this->instance_options['links'][$user_role] as $key => $data ) {
                        echo '<li><a href="' . $data['url'] . '">' . $data['title'] . '</a></li>' . "\n";
                    }
                }
                echo '<li><a href="' . wp_logout_url() . '">' . __('Log Out', 'theme-my-login') . '</a></li>' . "\n" . '</ul>' . "\n";
            } else {
                switch ( $this->action ) {
                    case 'lostpassword' :
                    case 'retrievepassword' :
                        $this->getLostPasswordForm();
                        break;
                    case 'register' :
                        $this->getRegisterForm();
                        break;
                    case 'login' :
                    default :
                        $this->getLoginForm();
                        break;
                }
            }
            echo $this->instance_options['after_widget'] . "\n";
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        }
        
        function getHeader($message = '') {
            global $error;

            echo '<div class="login" id="' . $this->current_instance . '">';

            $message = apply_filters('login_message', $message);
            if ( !empty($message) ) echo '<p class="message">' . $message . "</p>\n";

            // Incase a plugin uses $error rather than the $errors object
            if ( !empty( $error ) ) {
                $this->errors->add('error', $error);
                unset($error);
            }

            if ( $this->request_instance == $this->current_instance ) {
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

        function getFooter($login_link = true, $register_link = true, $password_link = true) {
            echo '<ul class="tml-links">' . "\n";
            if ( $login_link && $this->instance_options['show_log_link'] ) {
                $url = $this->getCurrentURL('instance=' . $this->current_instance);
                echo '<li><a href="' . $url . '">' . $this->getTitle('login') . '</a></li>' . "\n";
            }
            if ( $register_link && $this->instance_options['show_reg_link'] && get_option('users_can_register') ) {
                $url = ($this->instance_options['register_widget']) ? $this->getCurrentURL('action=register&instance=' . $this->current_instance) : site_url('wp-login.php?action=register', 'login');
                echo '<li><a href="' . $url . '">' . $this->getTitle('register') . '</a></li>' . "\n";
            }
            if ( $password_link && $this->instance_options['show_pass_link'] ) {
                $url = ($this->instance_options['lost_pass_widget']) ? $this->getCurrentURL('action=lostpassword&instance=' . $this->current_instance) : site_url('wp-login.php?action=lostpassword', 'login');
                echo '<li><a href="' . $url . '">' . $this->getTitle('lostpassword') . '</a></li>' . "\n";
            }
            echo '</ul>' . "\n";
            echo '</div>' . "\n";
        }
        
        function getLoginForm() {
        
            $this->redirect_to = apply_filters('login_redirect', $this->redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user);

            // Clear errors if loggedout is set.
            if ( !empty($_GET['loggedout']) )
                $this->errors = new WP_Error();

            // If cookies are disabled we can't log in even with a valid user+pass
            if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
                $this->errors->add('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress.", 'theme-my-login'));

            // Some parts of this script use the main login form to display a message
            if ( $this->request_instance == $this->current_instance ) {
                if        ( isset($_GET['loggedout']) && TRUE == $_GET['loggedout'] )
                    $this->errors->add('loggedout', __('You are now logged out.', 'theme-my-login'), 'message');
                elseif    ( isset($_GET['registration']) && 'disabled' == $_GET['registration'] )
                    $this->errors->add('registerdisabled', __('User registration is currently not allowed.', 'theme-my-login'));
                elseif    ( isset($_GET['checkemail']) && 'confirm' == $_GET['checkemail'] )
                    $this->errors->add('confirm', __('Check your e-mail for the confirmation link.', 'theme-my-login'), 'message');
                elseif    ( isset($_GET['checkemail']) && 'newpass' == $_GET['checkemail'] )
                    $this->errors->add('newpass', __('Check your e-mail for your new password.', 'theme-my-login'), 'message');
                elseif    ( isset($_GET['checkemail']) && 'registered' == $_GET['checkemail'] ) {
                    if ( $this->options['custom_pass'] )
                        $this->errors->add('registered', __('Registration complete. You may now log in.', 'theme-my-login'), 'message');
                    else
                        $this->errors->add('registered', __('Registration complete. Please check your e-mail.', 'theme-my-login'), 'message');
                } elseif    ( isset($_GET['pending']) && 'activation' == $_GET['pending'] )
                    $this->errors->add('pending', __('Registration successful. You must now confirm your email address before you can log in.', 'theme-my-login'), 'message');
                elseif    ( isset($_GET['pending']) && 'approval' == $_GET['pending'] )
                    $this->errors->add('registered', __('Registration successful. You must now be approved by an administrator before you can log in. You will be notified by e-mail once your account has been reviewed.', 'theme-my-login'), 'message');
                elseif    ( isset($_GET['activation']) && 'complete' == $_GET['activation'] ) {
                    if ( $this->options['custom_pass'] )
                        $this->errors->add('activated', __('Your account was activated successfully! You can now log in with the username and password you provided when you signed up.', 'theme-my-login'), 'message');
                    else
                        $this->errors->add('activated', __('Your account was activated successfully! Please check your e-mail for your password.', 'theme-my-login'), 'message');
                } elseif    ( isset($_GET['activation']) && 'invalidkey' == $_GET['activation'] )
                    $this->errors->add('invalidkey', __('Sorry, that key does not appear to be valid.', 'theme-my-login'));
            }

            $this->getHeader();

            if ( isset($_POST['log']) )
                $user_login = ( 'incorrect_password' == $this->errors->get_error_code() || 'empty_password' == $this->errors->get_error_code() ) ? attribute_escape(stripslashes($_POST['log'])) : '';

            $user_login = ( $this->request_instance == $this->current_instance && isset($user_login) ) ? $user_login : '';

            if ( !isset($_GET['checkemail']) ||
                ( isset($_GET['checkemail']) && $this->request_instance != $this->current_instance ) ||
                ( !in_array( $_GET['checkemail'], array('confirm', 'newpass') ) && $this->request_instance == $this->current_instance ) ||
                ( in_array( $_GET['checkemail'], array('confirm', 'newpass') ) && $this->request_instance != $this->current_instance ) ) {
                ?>
                <form name="loginform" id="loginform-<?php echo $this->current_instance; ?>" action="<?php echo $this->getCurrentURL('action=login&instance=' . $this->current_instance); ?>" method="post">
                    <p>
                        <label for="log-<?php echo $this->current_instance; ?>"><?php _e('Username', 'theme-my-login') ?></label>
                        <input type="text" name="log" id="log-<?php echo $this->current_instance; ?>" class="input" value="<?php echo isset($user_login) ? $user_login : ''; ?>" size="20" />
                    </p>
                    <p>
                        <label for="pwd-<?php echo $this->current_instance; ?>"><?php _e('Password', 'theme-my-login') ?></label>
                        <input type="password" name="pwd" id="pwd-<?php echo $this->current_instance; ?>" class="input" value="" size="20" />
                    </p>
                <?php do_action('login_form', $this->current_instance); ?>
                    <p class="forgetmenot"><input name="rememberme" type="checkbox" id="rememberme-<?php echo $this->current_instance; ?>" value="forever" /> <label for="rememberme-<?php echo $this->current_instance; ?>"><?php _e('Remember Me', 'theme-my-login'); ?></label></p>
                    <p class="submit">
                        <input type="submit" name="wp-submit" id="wp-submit-<?php echo $this->current_instance; ?>" value="<?php _e('Log In', 'theme-my-login'); ?>" />
                        <input type="hidden" name="redirect_to" value="<?php echo esc_attr($this->redirect_to); ?>" />
                        <input type="hidden" name="testcookie" value="1" />
                    </p>
                </form>
                <?php
            }
            if ( $this->request_instance == $this->current_instance && isset($_GET['checkemail']) && in_array( $_GET['checkemail'], array('confirm', 'newpass') ) )
                $login_link = true;
            else
                $login_link = false;
            $this->getFooter($login_link, true, true);
        }
        
        function getRegisterForm() {
            $user_login = isset($_POST['user_login']) ? $_POST['user_login'] : '';
            $user_email = isset($_POST['user_email']) ? $_POST['user_email'] : '';
            
            $message = ( $this->options['custom_pass'] ) ? '' : __('A password will be e-mailed to you.', 'theme-my-login');
            if ( 'email' == $this->options['moderation'] )
                $message = __('E-mail moderation is currently enabled. You must confirm your e-mail before you can log in.', 'theme-my-login');
            elseif ( 'admin' == $this->options['moderation'] )
                $message = __('User moderation is currently enabled. Your registration must be approved before you can log in.', 'theme-my-login');
            $message = apply_filters('tml_register_foreword', $message);
            
            $this->getHeader($message);
            ?>
            <form name="registerform" id="registerform-<?php echo $this->current_instance; ?>" action="<?php echo $this->getCurrentURL('action=register&instance=' . $this->current_instance); ?>" method="post">
                <p>
                    <label for="user_login-<?php echo $this->current_instance; ?>"><?php _e('Username', 'theme-my-login') ?></label>
                    <input type="text" name="user_login" id="user_login-<?php echo $this->current_instance; ?>" class="input" value="<?php echo attribute_escape(stripslashes($user_login)); ?>" size="20" />
                </p>
                <p>
                    <label for="user_email-<?php echo $this->current_instance; ?>"><?php _e('E-mail', 'theme-my-login') ?></label>
                    <input type="text" name="user_email" id="user_email-<?php echo $this->current_instance; ?>" class="input" value="<?php echo attribute_escape(stripslashes($user_email)); ?>" size="20" />
                </p>
                <?php do_action('register_form', $this->current_instance); ?>
                <p class="submit">
                    <input type="submit" name="wp-submit" id="wp-submit-<?php echo $this->current_instance; ?>" value="<?php _e('Register', 'theme-my-login'); ?>" />
                </p>
            </form>
            <?php
            $this->getFooter(true, false, true);
        }

        function getLostPasswordForm() {
            do_action('lost_password', $this->current_instance);
            $this->getHeader(__('Please enter your username or e-mail address. You will receive a new password via e-mail.', 'theme-my-login'));
            $user_login = isset($_POST['user_login']) ? stripslashes($_POST['user_login']) : '';
            ?>
            <form name="lostpasswordform" id="lostpasswordform-<?php echo $this->current_instance; ?>" action="<?php echo $this->getCurrentURL('action=lostpassword&instance=' . $this->current_instance); ?>" method="post">
                <p>
                    <label for="user_login-<?php echo $this->current_instance; ?>"><?php _e('Username or E-mail:', 'theme-my-login') ?></label>
                    <input type="text" name="user_login" id="user_login-<?php echo $this->current_instance; ?>" class="input" value="<?php echo attribute_escape($user_login); ?>" size="20" />
                </p>
                <?php do_action('lostpassword_form', $this->current_instance); ?>
                <p class="submit">
                    <input type="submit" name="wp-submit" id="wp-submit-<?php echo $this->current_instance; ?>" value="<?php _e('Get New Password', 'theme-my-login'); ?>" />
                </p>
            </form>
            <?php
            $this->getFooter(true, true, false);
        }

        // Get title depending on current action
        function getTitle($action = '') {
            if ( empty($action) )
                $action = $this->action;
                
            if ( is_user_logged_in() ) {
                $user = wp_get_current_user();
                $title = sprintf(__('Welcome, %s', 'theme-my-login'), $user->display_name);
            } else {
                switch ( $action ) {
                    case 'register':
                        $title = __('Register', 'theme-my-login');
                        break;
                    case 'lostpassword':
                    case 'retrievepassword':
                    case 'resetpass':
                    case 'rp':
                        $title = __('Lost Password', 'theme-my-login');
                        break;
                    case 'login':
                    default:
                        $title = __('Log In', 'theme-my-login');
                }
            }
            return apply_filters('tml_title', $title);
        }

        function getNewInstance() {
            static $instance = 0;
            ++$instance;
            $this->current_instance = 'tml-' . $instance;
        }
        
        function shortcode($atts = '') {
            if ( isset($atts['instance']) )
                $this->current_instance = $atts['instance'];
            else
                $this->getNewInstance();
            $atts = shortcode_atts($this->options, $atts);
            return $this->display($atts);
        }
        
        function siteURL($url, $path, $orig_scheme) {
            global $wp_rewrite;

            $schema = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
            $self =  $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            if ( preg_match('/wp-login.php/', $url) && !isset($_REQUEST['interim-login']) ) {
                $orig_url = $url;
                $url = $this->getPermalink();
                if ( strpos($orig_url, '?') ) {
                    $query = substr($orig_url, strpos($orig_url, '?') + 1);
                    parse_str($query, $r);
                    $url = add_query_arg($r, $url);
                }
            }
            return $url;
        }
        
        function getCurrentURL($query = '') {
            $schema = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
            $self =  $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            
            $keys = array('instance', 'action', 'checkemail', 'error', 'loggedout', 'registered', 'redirect_to', 'updated', 'key', '_wpnonce');
            $url = remove_query_arg($keys, $self);

            if ( !empty($query) ) {
                $query = wp_parse_args($query);
                $url = add_query_arg($query, $url);
            }

            return $url;
        }
        
        function getPermalink() {
            global $wp_rewrite;
            
            $pagestruct = $wp_rewrite->get_page_permastruct();
            
            if ( '' != $pagestruct ) {
                $link = str_replace('%pagename%', 'login', $pagestruct);
                $link = trailingslashit(get_option('home')) . "$link";
                $link = user_trailingslashit($link, 'page');
            } else {
                $link = trailingslashit(get_option('home')) . '?pagename=login';
            }
            return $link;
        }
        
        function loginRedirect($redirect_to, $request, $user) {
            global $pagenow;
            
            if ( 'wp-login.php' == $pagenow )
                return $redirect_to;
            
            $orig_redirect = $redirect_to;
            
            $redirect_to = ( 'tml-page' == $this->current_instance ) ? $_SERVER['HTTP_REFERER'] : $this->getCurrentURL();

            if ( is_object($user) && !is_wp_error($user) ) {
                $user_role = reset($user->roles);
                if ( '' != $this->options['redirects'][$user_role]['login_url'] )
                    $role_redirect = $this->options['redirects'][$user_role]['login_url'];
            }

            if ( !empty($request) && $this->options['override_redirect'] )
                $redirect_to = $request;
            elseif ( isset($role_redirect) && !$this->options['override_redirect'] )
                $redirect_to = $role_redirect;

            return $redirect_to;
        }
        
        function logoutRedirect($redirect_to, $request, $user) {
            $orig_redirect = $redirect_to;

            $redirect_to = remove_query_arg(array('instance', 'action', 'checkemail', 'error', 'loggedout', 'registered', 'redirect_to', 'updated', 'key', '_wpnonce'), $_SERVER['HTTP_REFERER']);

            if ( is_object($user) && !is_wp_error($user) ) {
                $user_role = reset($user->roles);
                if ( '' != $this->options['redirects'][$user_role]['logout_url'] )
                    $role_redirect = $this->options['redirects'][$user_role]['logout_url'];
            }

            if ( !empty($request) && $this->options['override_redirect'] )
                $redirect_to = $request;
            elseif ( isset($role_redirect) && !$this->options['override_redirect'] )
                $redirect_to = $role_redirect;

            return $redirect_to;
        }
        
        function customPassForm($instance) {
            ?>
            <p><label for="pass1-<?php echo $instance; ?>"><?php _e('Password:', 'theme-my-login');?></label>
            <input autocomplete="off" name="pass1" id="pass1-<?php echo $instance; ?>" class="input" size="20" value="" type="password" /></p>
            <p><label for="pass2-<?php echo $instance; ?>"><?php _e('Confirm Password:', 'theme-my-login');?></label>
            <input autocomplete="off" name="pass2" id="pass2-<?php echo $instance; ?>" class="input" size="20" value="" type="password" /></p>
            <?php
        }

        function customPassErrors($errors){
            if (empty($_POST['pass1']) || $_POST['pass1'] == '' || empty($_POST['pass2']) || $_POST['pass2'] == ''){
                $errors->add('empty_password', __('<strong>ERROR</strong>: Please enter a password.', 'theme-my-login'));
            } elseif ($_POST['pass1'] !== $_POST['pass2']){
                $errors->add('password_mismatch', __('<strong>ERROR</strong>: Your passwords do not match.', 'theme-my-login'));
            } elseif (strlen($_POST['pass1'])<6){
                $errors->add('password_length', __('<strong>ERROR</strong>: Your password must be at least 6 characters in length.', 'theme-my-login'));
            } else {
                $_POST['user_pw'] = $_POST['pass1'];
            }
            return $errors;
        }

        function setUserPassword($user_id) {
            if ( $this->options['custom_pass'] && isset($_POST['user_pw']) && '' != $_POST['user_pw'] )
                wp_set_password(stripslashes($_POST['user_pw']), $user_id);
        }
        
        function userModeration($user_id) {
            $user = new WP_User($user_id);
            $user->set_role('pending');
            if ( 'email' == $this->options['moderation'] ) {
                global $wpdb;
                $key = wp_generate_password(20, false);
                $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user->user_login));
            }
        }
        
        function userActivation() {
            require_once(WP_PLUGIN_DIR . '/theme-my-login/includes/functions.php');
            $newpass = ( $this->options['custom_pass'] ) ? 0 : 1;
            $this->errors = activate_new_user($_GET['key'], $_GET['login'], $newpass);

            if ( ! is_wp_error($this->errors) ) {
                $redirect_to = site_url('wp-login.php?activation=complete');
                if ( 'tml-page' != $this->request_instance )
                    $redirect_to = $this->getCurrentURL('activation=complete&instance=' . $this->request_instance);
                wp_redirect($redirect_to);
                exit();
            }

            $redirect_to = site_url('wp-login.php?activation=invalidkey');
            if ( 'tml-page' != $this->request_instance )
                $redirect_to = $this->getCurrentURL('activation=invalidkey&instance=' . $this->request_instance);
            wp_redirect($redirect_to);
            exit();
        }
        
        function authenticate($user, $username, $password) {
            global $wpdb;

            if ( is_a($user, 'WP_User') ) {
                $user_role = reset($user->roles);
                if ( 'pending' == $user_role ) {
                    if ( $this->options['redirects'][$user_role]['login_url'] ) {
                        wp_safe_redirect($this->options['redirects'][$user_role]['login_url']);
                        exit();
                    } else {
                        if ( 'email' == $this->options['moderation'] )
                            return new WP_Error('pending', __('<strong>ERROR</strong>: You have not yet confirmed your e-mail address.', 'theme-my-login'));
                        else
                            return new WP_Error('pending', __('<strong>ERROR</strong>: Your registration has not yet been approved.', 'theme-my-login'));
                    }
                }
            }
            return $user;
        }
        
        function denyUser($id) {
            $user = new WP_User($id);
            $user_role = reset($user->roles);
            if ( 'pending' != $user_role )
                return;
                
            // The blogname option is escaped with esc_html on the way into the database in sanitize_option
            // we want to reverse this for the plain text arena of emails.
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

            $replace_this = array('/%blogname%/', '/%siteurl%/', '/%user_login%/', '/%user_email%/');
            $replace_with = array($blogname, get_option('siteurl'), $user->user_login, $user->user_email);
            $subject = $this->options['user_denial_email']['subject'];
            $message = $this->options['user_denial_email']['message'];

            if ( !empty($subject) )
                $subject = preg_replace($replace_this, $replace_with, $subject);
            else
                $subject = sprintf(__('[%s] Registration Denied', 'theme-my-login'), $blogname);
            if ( !empty($message) )
                $message = preg_replace($replace_this, $replace_with, $message);
            else
                $message = sprintf(__('You have been denied access to %s', 'theme-my-login'), $blogname);

            $this->sendEmail($user->user_email, $subject, $message);
        }

        function allowPasswordReset($allow, $user_id) {
            $user = new WP_User($user_id);
            $user_role = reset($user->roles);
            if ( 'pending' == $user_role )
                $allow = false;

            return $allow;
        }
        
        function sendEmail($to, $subject, $message) {
            $headers = '';
            if ( $this->options['email_from'] && $this->options['email_from_name'] )
                $headers .= 'From: ' . $this->options['email_from_name'] . ' <' . $this->options['email_from'] . '>' . "\r\n";
            elseif ( $this->options['email_from'] )
                $headers .= 'From: ' . $this->options['email_from'] . "\r\n";
                
            if ( 'text/html' == $this->options['email_content_type'] )
                $headers .= 'Content-type: text/html; charset=' . get_option('blog_charset') . "\r\n";
            else
                $headers .= 'Content-type: text/plain; charset=' . get_option('blog_charset') . "\r\n";
                
            return wp_mail($to, $subject, $message, $headers);
        }
        
        function defaultOptions($save = false) {

            // General
            $this->options['custom_pass']           = 0;
            $this->options['moderation']            = 'none';
            $this->options['email_from']            = '';
            $this->options['email_from_name']       = '';
            $this->options['email_content_type']    = 'text/plain';
            $this->options['use_css']               = 1;
            $this->options['override_redirect']     = 1;

            // Widget
            $this->options['default_action']        = 'login';
            $this->options['show_title']            = 1;
            $this->options['show_log_link']         = 1;
            $this->options['show_reg_link']         = 1;
            $this->options['show_pass_link']        = 1;
            $this->options['register_widget']       = 1;
            $this->options['lost_pass_widget']      = 1;
            $this->options['logged_in_widget']      = 1;
            $this->options['show_gravatar']         = 1;
            $this->options['gravatar_size']         = 50;
            $this->options['before_widget']         = '<li>';
            $this->options['after_widget']          = '</li>';
            $this->options['before_title']          = '<h2>';
            $this->options['after_title']           = '</h2>';

            // E-mails
            $this->options['retrieve_pass_email']   = array('subject' => '', 'message' => '');
            $this->options['reset_pass_email']      = array('subject' => '', 'message' => '', 'admin_disable' => 0);
            $this->options['registration_email']    = array('subject' => '', 'message' => '', 'admin_disable' => 0);
            $this->options['confirmation_email']    = array('subject' => '', 'message' => '');
            $this->options['user_approval_email']   = array('subject' => '', 'message' => '');
            $this->options['user_denial_email']     = array('subject' => '', 'message' => '');

            // Links & Redirects
            global $wp_roles;
            if ( empty($wp_roles) )
                $wp_roles = new WP_Roles();
            $user_roles = $wp_roles->get_names();
            foreach ( $user_roles as $role => $title ) {
                if ( 'pending' == $role )
                    continue;
                $this->options['links'][$role][] = array('title' => 'Dashboard', 'url' => admin_url());
                $this->options['links'][$role][] = array('title' => 'Profile', 'url' => admin_url('profile.php'));
                $this->options['redirects'][$role] = array('login_url' => '', 'logout_url' => '');
            }

            if ( $save )
                $this->saveOptions();
        }

        function loadOptions() {

            $this->defaultOptions();

            $storedoptions = get_option('theme_my_login');
            if ( $storedoptions && is_array($storedoptions) ) {
                foreach ( $storedoptions as $key => $value ) {
                    $this->options[$key] = $value;
                }
            } else update_option('theme_my_login', $this->options);
        }

        function getOption($key) {
            if ( array_key_exists($key, $this->options) ) {
                return $this->options[$key];
            } else return null;
        }

        function setOption($key, $value) {
            $this->options[$key] = $value;
        }

        function saveOptions() {
            $oldvalue = get_option('theme_my_login');
            if ( $oldvalue == $this->options ) {
                return true;
            } else return update_option('theme_my_login', $this->options);
        }
        
        function install() {
            $previous_install = get_option('theme_my_login');
            if ( $previous_install ) {
                if ( version_compare($previous_install['version'], '4.4', '<') ) {
                    global $wp_roles;
                    if ( $wp_roles->is_role('denied') )
                        $wp_roles->remove_role('denied');
                }
            }
            
            $plugin_data = get_plugin_data(__FILE__);
            $this->setOption('version', $plugin_data['Version']);
            $this->saveOptions();
        }
        
        function uninstall() {
            delete_option('theme_my_login');
        }
    }

    if ( class_exists('ThemeMyLogin') ) {
        $ThemeMyLogin = new ThemeMyLogin();
        
        require_once (WP_PLUGIN_DIR . '/theme-my-login/includes/widget.php');
        
        if ( !function_exists('theme_my_login') ) :
        function theme_my_login($args = '') {
            global $ThemeMyLogin;
            $args = wp_parse_args($args);
            echo $ThemeMyLogin->shortcode($args);
        }
        endif;
        
        if ( !function_exists('wp_new_user_notification') ) :
        function wp_new_user_notification($user_id, $plaintext_pass = '') {
            global $ThemeMyLogin;
            
            $user = new WP_User($user_id);

            $user_login = stripslashes($user->user_login);
            $user_email = stripslashes($user->user_email);
            $user_role = reset($user->roles);

            // The blogname option is escaped with esc_html on the way into the database in sanitize_option
            // we want to reverse this for the plain text arena of emails.
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            
            if ( 'email' == $ThemeMyLogin->options['moderation'] && 'pending' == $user_role ) {
                global $wpdb;
                $key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
                if ( empty($key) ) {
                    $key = wp_generate_password(20, false);
                    $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
                }
                $confirm_url = add_query_arg(array('action' => 'activate', 'key' => $key, 'login' => $user_login), $ThemeMyLogin->getPermalink());
                $replace_this = array('/%blogname%/', '/%siteurl%/', '/%confirmurl%/', '/%user_login%/', '/%user_email%/', '/%user_pass%/', '/%user_ip%/');
                $replace_with = array($blogname, get_option('siteurl'), $confirm_url, $user_login, $user_email, $plaintext_pass, $_SERVER['REMOTE_ADDR']);
                $subject = $ThemeMyLogin->options['confirmation_email']['subject'];
                $message = $ThemeMyLogin->options['confirmation_email']['message'];
                
                if ( !empty($subject) )
                    $subject = preg_replace($replace_this, $replace_with, $subject);
                else
                    $subject = sprintf(__('[%s] Activate Your Account', 'theme-my-login'), $blogname);
                if ( !empty($message) )
                    $message = preg_replace($replace_this, $replace_with, $message);
                else {
                    $message  = sprintf(__('Thanks for registering at %s! To complete the activation of your account please click the following link: ', 'theme-my-login'), $blogname) . "\r\n\r\n";
                    $message .= $confirm_url;
                }
                $ThemeMyLogin->sendEmail($user_email, $subject, $message);
            } elseif ( 'admin' == $ThemeMyLogin->options['moderation'] && 'pending' == $user_role ) {
                $message  = sprintf(__('New user requires approval on your blog %s:', 'theme-my-login'), $blogname) . "\r\n\r\n";
                $message .= sprintf(__('Username: %s', 'theme-my-login'), $user_login) . "\r\n\r\n";
                $message .= sprintf(__('E-mail: %s', 'theme-my-login'), $user_email) . "\r\n\r\n";
                $message .= __('To approve or deny this user:', 'theme-my-login') . "\r\n";
                $message .= admin_url('users.php');

                @$ThemeMyLogin->sendEmail(get_option('admin_email'), sprintf(__('[%s] New User Awaiting Approval', 'theme-my-login'), $blogname), $message);
            } else {
                if ( !$ThemeMyLogin->options['registration_email']['admin_disable'] ) {
                    $message  = sprintf(__('New user registration on your blog %s:', 'theme-my-login'), $blogname) . "\r\n\r\n";
                    $message .= sprintf(__('Username: %s', 'theme-my-login'), $user_login) . "\r\n\r\n";
                    $message .= sprintf(__('E-mail: %s', 'theme-my-login'), $user_email) . "\r\n";

                    @$ThemeMyLogin->sendEmail(get_option('admin_email'), sprintf(__('[%s] New User Registration', 'theme-my-login'), $blogname), $message);
                }
                
                if ( empty($plaintext_pass) || $ThemeMyLogin->options['custom_pass'] )
                    return;
                    
                $replace_this = array('/%blogname%/', '/%siteurl%/', '/%user_login%/', '/%user_email%/', '/%user_pass%/', '/%user_ip%/');
                $replace_with = array($blogname, get_option('siteurl'), $user_login, $user_email, $plaintext_pass, $_SERVER['REMOTE_ADDR']);
                $subject = $ThemeMyLogin->options['registration_email']['subject'];
                $message = $ThemeMyLogin->options['registration_email']['message'];

                if ( !empty($subject) )
                    $subject = preg_replace($replace_this, $replace_with, $subject);
                else
                    $subject = sprintf(__('[%s] Your username and password', 'theme-my-login'), $blogname);
                if ( !empty($message) )
                    $message = preg_replace($replace_this, $replace_with, $message);
                else {
                    $message  = sprintf(__('Username: %s', 'theme-my-login'), $user_login) . "\r\n";
                    $message .= sprintf(__('Password: %s', 'theme-my-login'), $plaintext_pass) . "\r\n";
                    $message .= wp_login_url() . "\r\n";
                }
                $ThemeMyLogin->sendEmail($user_email, $subject, $message);
            }
        }
        endif;
    }
}

?>
