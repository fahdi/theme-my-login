<?php
/*
Plugin Name: Theme My Login
Plugin URI: http://www.jfarthing.com/wordpress-plugins/theme-my-login-plugin
Description: Themes the WordPress login, registration and forgot password pages according to your theme.
Version: 4.3.4
Author: Jeff Farthing
Author URI: http://www.jfarthing.com
Text Domain: theme-my-login
*/

global $wp_version;

require_once ('classes/class.wp-login.php');

if (!class_exists('ThemeMyLogin')) {
    class ThemeMyLogin {

        var $version = '4.3.4';
        var $options = array();
        var $permalink = '';
        var $instances = 0;

        function ThemeMyLogin() {
            
            load_plugin_textdomain('theme-my-login', '/wp-content/plugins/theme-my-login/language');

            register_activation_hook (__FILE__, array( &$this, 'Activate' ));
            register_deactivation_hook (__FILE__, array( &$this, 'Deactivate' ));

            add_action('init', array(&$this, 'Init'));
            add_action('template_redirect', array(&$this, 'TemplateRedirect'));

            add_action('register_form', array(&$this, 'RegisterForm'));
            add_action('registration_errors', array(&$this, 'RegistrationErrors'));
            
            add_action('authenticate', array(&$this, 'Authenticate'), 100, 3);
            
            add_filter('allow_password_reset', array(&$this, 'AllowPasswordReset'), 10, 2);

            add_filter('wp_head', array(&$this, 'WPHead'));
            add_filter('wp_title', array(&$this, 'WPTitle'));
            add_filter('the_title', array(&$this, 'TheTitle'));
            add_filter('wp_list_pages', array(&$this, 'WPListPages'));
            add_filter('wp_list_pages_excludes', array(&$this, 'WPListPagesExcludes'));
            add_filter('login_redirect', array(&$this, 'LoginRedirect'), 10, 3);
            add_filter('site_url', array(&$this, 'SiteURL'), 10, 2);
            add_filter('retrieve_password_title', array(&$this, 'RetrievePasswordTitle'), 10, 2);
            add_filter('retrieve_password_message', array(&$this, 'RetrievePasswordMessage'), 10, 3);
            add_filter('password_reset_title', array(&$this, 'PasswordResetTitle'), 10, 2);
            add_filter('password_reset_message', array(&$this, 'PasswordResetMessage'), 10, 3);
            
            add_shortcode('theme-my-login-page', array(&$this, 'ThemeMyLoginPageShortcode'));
            add_shortcode('theme-my-login', array(&$this, 'ThemeMyLoginShortcode'));
            
            add_action('admin_init', array(&$this, 'AdminInit'));
            add_action('admin_menu', array(&$this, 'AdminMenu'));
            
            add_filter('user_row_actions', array(&$this, 'UserRowActions'), 10, 2);
            
            $this->LoadOptions();
            
            if ( empty($this->options['page_id']) ) {
                $login_page = get_page_by_title('Login');
                $this->options['page_id'] = ( $login_page ) ? $login_page->ID : 0;
                $this->SaveOptions();
            }
        }
        
        function Init() {
            global $WPLogin, $pagenow;
            
            $this->permalink = get_permalink($this->options['page_id']);

            switch ( $pagenow ) {
                case 'wp-register.php':
                case 'wp-login.php':
                    if ( !empty($this->options['page_id']) ) {
                        $redirect_to = add_query_arg($_GET, $this->permalink);
                        wp_redirect($redirect_to);
                        exit();
                    }
                break;
            }
            
            if ( $this->options['use_css'] )
                wp_enqueue_style('theme-my-login', plugins_url('/theme-my-login/css/theme-my-login.css'));
                
            $WPLogin = new WPLogin('theme-my-login', $this->options);
        }
        
        function AdminInit() {
            global $user_ID, $wp_version, $pagenow;
            
            if ( 'options-general.php' == $pagenow ) {
            
                $page = isset($_GET['page']) ? $_GET['page'] : '';

                switch ( $page ) {
                
                    case 'theme-my-login/admin/admin.php' :
                    
                        wp_enqueue_script('theme-my-login-admin', plugins_url('/theme-my-login/js/theme-my-login-admin.js'));
                        
                        if ( version_compare($wp_version, '2.8', '>=') ) {
                            wp_enqueue_script('jquery-ui-tabs');
                        } else {
                            wp_deregister_script('jquery');
                            wp_deregister_script('jquery-ui-core');
                            wp_deregister_script('jquery-ui-tabs');
                            wp_enqueue_script('jquery', plugins_url('/theme-my-login/js/jquery/jquery.js'), false, '1.7.2');
                            wp_enqueue_script('jquery-ui-core', plugins_url('/theme-my-login/js/jquery/ui.core.js'), array('jquery'), '1.7.2');
                            wp_enqueue_script('jquery-ui-tabs', plugins_url('/theme-my-login/js/jquery/ui.tabs.js'), array('jquery', 'jquery-ui-core'), '1.7.2');
                        }
            
                        wp_enqueue_style('theme-my-login-admin', plugins_url('/theme-my-login/css/theme-my-login-admin.css'));
                        
                        if ( version_compare($wp_version, '2.7', '>=') ) {
                            $admin_color = get_usermeta($user_ID, 'admin_color');
                            if ( 'classic' == $admin_color ) {
                                wp_enqueue_style('jquery-colors-classic', plugins_url('/theme-my-login/css/wp-colors-classic/wp-colors-classic.css'));
                            } else {
                                wp_enqueue_style('jquery-colors-fresh', plugins_url('/theme-my-login/css/wp-colors-fresh/wp-colors-fresh.css'));
                            }
                        } elseif ( version_compare($wp_version, '2.6', '>=') ) {
                            wp_enqueue_style('jquery-colors-classic', plugins_url('/theme-my-login/css/wp-colors-classic/wp-colors-classic.css'));
                        }
                        break;
                        
                }

            } elseif ( 'page.php' == $pagenow && (isset($_REQUEST['post']) && $this->options['page_id'] == $_REQUEST['post']) ) {
                    add_action('admin_notices', array(&$this, 'PageEditNotice'));
            } elseif ( 'users.php' == $pagenow && $this->options['moderate_users'] ) {
                if ( isset($_GET['action']) && in_array($_GET['action'], array('approve', 'deny')) ) {

                    check_admin_referer('moderate-user');

                    $user = isset($_GET['user']) ? $_GET['user'] : '';
                    if ( !$user )
                        wp_die(__('You can&#8217;t edit that user.'));

                    if ( !current_user_can('edit_user', $user) )
                        wp_die(__('You can&#8217;t edit that user.'));
                        
                    switch ( $_GET['action'] ) {
                        case 'approve' :
                            $user = new WP_User($user);
                            $user->set_role('subscriber');

                            $subject = $this->options['user_approval_email']['subject'];
                            $message = $this->options['user_approval_email']['message'];
                            
                            if ( !$this->options['custom_pass'] ) {
                                $plaintext_pass = wp_generate_password();
                                wp_set_password($plaintext_pass, $user->ID);
                            }
                            
                            $replace_this = array('/%blogname%/', '/%siteurl%/', '/%user_login%/', '/%user_email%/', '/%user_pass%/');
                            $replace_with = array(get_option('blogname'), get_option('siteurl'), $user->user_login, $user->user_email, $plaintext_pass);

                            if ( !empty($subject) )
                                $subject = preg_replace($replace_this, $replace_with, $subject);
                            else
                                $subject = sprintf(__('[%s] Registration Approved'), get_option('blogname'));
                            if ( !empty($message) )
                                $message = preg_replace($replace_this, $replace_with, $message);
                            else {
                                $message  = sprintf(__('You have been approved to access %s '."\r\n\r\n"), get_option('blogname'));
                                $message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n";
                                if ( !$this->options['custom_pass'] )
                                    $message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
                                $message .= "\r\n";
                                $message .= site_url('wp-login.php', 'login') . "\r\n";
                            }
                            tml_apply_mail_filters();
                            @wp_mail($user->user_email, $subject, $message);
                            tml_remove_mail_filters();
                            
                            add_action('admin_notices', array(&$this, 'ApprovalNotice'));
                            break;

                        case 'deny' :
                            $user = new WP_User($user);
                            $user->set_role('denied');
                            
                            $subject = $this->options['user_denial_email']['subject'];
                            $message = $this->options['user_denial_email']['message'];
                            $replace_this = array('/%blogname%/', '/%siteurl%/', '/%user_login%/', '/%user_email%/');
                            $replace_with = array(get_option('blogname'), get_option('siteurl'), $user->user_login, $user->user_email);

                            if ( !empty($subject) )
                                $subject = preg_replace($replace_this, $replace_with, $subject);
                            else
                                $subject = sprintf(__('[%s] Registration Denied'), get_option('blogname'));
                            if ( !empty($message) )
                                $message = preg_replace($replace_this, $replace_with, $message);
                            else
                                $message = sprintf(__('You have been denied access to %s'), get_option('blogname'));

                            tml_apply_mail_filters();
                            @wp_mail($user->user_email, $subject, $message);
                            tml_remove_mail_filters();
                            
                            add_action('admin_notices', array(&$this, 'DenialNotice'));
                            break;

                    }
                }
            }
        }
        
        function PageEditNotice() {
            echo '<div class="error"><p>' . __('NOTICE: This page is integral to the operation of Theme My Login. <strong>DO NOT</strong> edit the title or remove the short code from the contents.') . '</p></div>';
        }
        
        function ApprovalNotice() {
            echo '<div id="message" class="updated fade"><p>' . __('User approved.') . '</p></div>';
        }
        
        function DenialNotice() {
            echo '<div id="message" class="updated fade"><p>' . __('User denied.') . '</p></div>';
        }
        
        function AdminMenu() {
            add_options_page(__('Theme My Login', 'theme-my-login'), __('Theme My Login', 'theme-my-login'), 8, 'theme-my-login/admin/admin.php');
        }
        
        function UserRowActions($actions, $user_object) {
            global $current_user;
            
            if ( $this->options['moderate_users'] ) {
                $user_role = reset($user_object->roles);
                if ( $current_user->ID != $user_object->ID ) {
                    if ( 'pending' == $user_role ) {
                        $approve['approve-user'] = '<a href="' . add_query_arg( 'wp_http_referer', urlencode( esc_url( stripslashes( $_SERVER['REQUEST_URI'] ) ) ), wp_nonce_url("users.php?action=approve&amp;user=$user_object->ID", 'moderate-user') ) . '">Approve</a>';
                        $approve['deny-user'] = '<a href="' . add_query_arg( 'wp_http_referer', urlencode( esc_url( stripslashes( $_SERVER['REQUEST_URI'] ) ) ), wp_nonce_url("users.php?action=deny&amp;user=$user_object->ID", 'moderate-user') ) . '">Deny</a>';
                    } elseif ( 'denied' == $user_role ) {
                        $approve['approve-user'] = '<a href="' . add_query_arg( 'wp_http_referer', urlencode( esc_url( stripslashes( $_SERVER['REQUEST_URI'] ) ) ), wp_nonce_url("users.php?action=approve&amp;user=$user_object->ID", 'moderate-user') ) . '">Approve</a>';
                    } else {
                        $approve['deny-user'] = '<a href="' . add_query_arg( 'wp_http_referer', urlencode( esc_url( stripslashes( $_SERVER['REQUEST_URI'] ) ) ), wp_nonce_url("users.php?action=deny&amp;user=$user_object->ID", 'moderate-user') ) . '">Deny</a>';
                    }
                    $actions = array_merge($approve, $actions);
                }
            }
            return $actions;
        }

        function TemplateRedirect() {
            if ( is_page($this->options['page_id']) ) {
                $action = ( isset($_GET['action']) ) ? $_GET['action'] : '';
                if ( is_user_logged_in() && 'logout' != $action ) {
                    wp_redirect(get_option('home'));
                    exit();
                }
            }
        }
        
        function RegisterForm($instance) {
            if ( $this->options['custom_pass'] ) {
                ?>
            <p><label for="pass1-<?php echo $instance; ?>"><?php _e('Password:');?></label>
            <input autocomplete="off" name="pass1" id="pass1-<?php echo $instance; ?>" class="input" size="20" value="" type="password" /><br />
            <label for="pass2-<?php echo $instance; ?>"><?php _e('Confirm Password:');?></label>
            <input autocomplete="off" name="pass2" id="pass2-<?php echo $instance; ?>" class="input" size="20" value="" type="password" /></p>
                <?php
            }
        }

        function RegistrationErrors($errors){
            if ( $this->options['custom_pass'] ) {
                if (empty($_POST['pass1']) || $_POST['pass1'] == '' || empty($_POST['pass2']) || $_POST['pass2'] == ''){
                    $errors->add('empty_password', __('<strong>ERROR</strong>: Please enter a password.'));
                } elseif ($_POST['pass1'] !== $_POST['pass2']){
                    $errors->add('password_mismatch', __('<strong>ERROR</strong>: Your passwords do not match.'));
                } elseif (strlen($_POST['pass1'])<6){
                    $errors->add('password_length', __('<strong>ERROR</strong>: Your password must be at least 6 characters in length.'));
                } else {
                    $_POST['user_pw'] = $_POST['pass1'];
                }
            }

            return $errors;
        }
        
        function Authenticate($user, $username, $password) {
            global $wpdb;
            
            if ( is_a($user, 'WP_User') ) {
                $user_role = reset($user->roles);
                if ( in_array($user_role, array('pending', 'denied')) ) {
                    if ( $this->options['redirects'][$user_role]['login_url'] ) {
                        wp_safe_redirect($this->options['redirects'][$user_role]['login_url']);
                        exit();
                    } else {
                        return new WP_Error('pending', '<strong>ERROR</strong>: Your registration has not yet been approved.');
                    }
                }
            }
            return $user;
        }
        
        function AllowPasswordReset($allow, $user_id) {
            $user = new WP_User($user_id);
            $user_role = reset($user->roles);
            if ( in_array($user_role, array('pending', 'denied')) )
                $allow = false;
                
            return $allow;
        }

        function WPHead() {
            do_action('login_head');
        }

        function WPTitle($title) {
            global $WPLogin;
            
            if ( is_page($this->options['page_id']) ) {

                $action = ( isset($WPLogin->options['action']) ) ? $WPLogin->options['action'] : '';
                if ( 'tml-main' == $WPLogin->instance || empty($WPLogin->instance) )
                    $action = $WPLogin->action;

                if ( is_user_logged_in() )
                    return str_replace('Login', $this->options['logout_title'], $title);
                    
                switch ($action) {
                    case 'register':
                        return str_replace('Login', $this->options['register_title'], $title);
                        break;
                    case 'lostpassword':
                    case 'retrievepassword':
                    case 'resetpass':
                    case 'rp':
                        return str_replace('Login', $this->options['lost_pass_title'], $title);
                        break;
                    case 'login':
                    default:
                        return str_replace('Login', $this->options['login_title'], $title);
                }
            } return $title;
        }
        
        function TheTitle($title) {
            global $WPLogin;
            
            if ( is_admin() )
                return $title;
            
            if ( $title == 'Login' ) {

                if ( is_user_logged_in() )
                    return $this->options['logout_title'];

                $action = ( isset($WPLogin->options['action']) ) ? $WPLogin->options['action'] : '';
                if ( 'tml-main' == $WPLogin->instance || empty($WPLogin->instance) )
                    $action = $WPLogin->action;
                    
                switch ($action) {
                    case 'register':
                        return $this->options['register_title'];
                        break;
                    case 'lostpassword':
                    case 'retrievepassword':
                    case 'resetpass':
                    case 'rp':
                        return $this->options['lost_pass_title'];
                        break;
                    case 'login':
                    default:
                        return $this->options['login_title'];
                }
            } return $title;
        }
        
        function WPListPages($pages) {
            global $wp_version, $WPLogin;
            
            if ( $this->options['show_page'] && is_user_logged_in() ) {
                $redirect = $WPLogin->GuessURL();
                $logout_url = ( version_compare($wp_version, '2.7', '>=') ) ? wp_logout_url($redirect) : site_url('wp-login.php?action=logout&redirect_to='.$redirect, 'login');
                $pages = str_replace($this->permalink, $logout_url, $pages);
            }
            return $pages;
        }
        
        function WPListPagesExcludes($excludes) {
            if ( !$this->options['show_page'] )
                $excludes[] = $this->options['page_id'];
            return $excludes;
        }
        
        function LoginRedirect($redirect_to, $request, $user) {
            global $pagenow;
            
            $schema = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
            $self =  $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            
            if ( empty($redirect_to) || admin_url() == $redirect_to) {
                if ( empty($request) )
                    $redirect_to = ( 'wp-login.php' == $pagenow ) ? $_SERVER['HTTP_REFERER'] : $self;
                else
                    $redirect_to = $request;
            }
            
            if ( is_object($user) && !is_wp_error($user) ) {
                $user_role = reset($user->roles);
                $redirects = $this->options['redirects'];
                if ( '' != $redirects[$user_role]['login_url'] ) {
                    if ( $this->options['override_redirect'] )
                        return $redirects[$user_role]['login_url'];
                }
            }

            return $redirect_to;
        }
        
        function SiteURL($url, $path) {
            global $wp_rewrite;
            
            $schema = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
            $self =  $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            
            if ( !empty($this->options['page_id']) ) {
                if ( preg_match('/wp-login.php/', $url) ) {
                    $parsed_url = parse_url($url);
                    if ( isset($parsed_url['query']) )
                        $url = $wp_rewrite->using_permalinks() ? $this->permalink.'?'.$parsed_url['query'] : $this->permalink.'&'.$parsed_url['query'];
                    else
                        $url = $this->permalink;

                    $self = remove_query_arg('redirect_to');
                    $url = add_query_arg('redirect_to', $self, $url);
                }
            }
            return $url;
        }
        
        function RetrievePasswordTitle($title, $user) {
            if ( !empty($this->options['retrieve_pass_email']['subject']) ) {
                $replace_this = array('/%blogname%/', '/%siteurl%/', '/%reseturl%/', '/%user_login%/', '/%user_email%/', '/%user_ip%/');
                $replace_with = array(get_option('blogname'), get_option('siteurl'), site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login'), $user->user_login, $user->user_email, $_SERVER['REMOTE_ADDR']);
                $title = preg_replace($replace_this, $replace_with, $this->options['retrieve_pass_email']['subject']);
            }
            return $title;
        }
        
        function RetrievePasswordMessage($message, $key, $user) {
            if ( !empty($this->options['retrieve_pass_email']['message']) ) {
                $replace_this = array('/%blogname%/', '/%siteurl%/', '/%reseturl%/', '/%user_login%/', '/%user_email%/', '/%key%/', '/%user_ip%/');
                $replace_with = array(get_option('blogname'), get_option('siteurl'), site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login'), $user->user_login, $user->user_email, $key, $_SERVER['REMOTE_ADDR']);
                $message = preg_replace($replace_this, $replace_with, $this->options['retrieve_pass_email']['message']);
            }
            return $message;
        }
        
        function PasswordResetTitle($title, $user) {
            if ( !empty($this->options['reset_pass_email']['subject']) ) {
                $replace_this = array('/%blogname%/', '/%siteurl%/', '/%user_login%/', '/%user_email%/', '/%user_ip%/');
                $replace_with = array(get_option('blogname'), get_option('siteurl'), $user->user_login, $user->user_email, $_SERVER['REMOTE_ADDR']);
                $title = preg_replace($replace_this, $replace_with, $this->options['reset_pass_email']['subject']);
            }
            return $title;
        }
        
        function PasswordResetMessage($message, $new_pass, $user) {
            if ( !empty($this->options['reset_pass_email']['message']) ) {
                $replace_this = array('/%blogname%/', '/%siteurl%/', '/%user_login%/', '/%user_email%/', '/%user_pass%/', '/%user_ip%/');
                $replace_with = array(get_option('blogname'), get_option('siteurl'), $user->user_login, $user->user_email, $new_pass, $_SERVER['REMOTE_ADDR']);
                $message = preg_replace($replace_this, $replace_with, $this->options['reset_pass_email']['message']);
            }
            return $message;
        }

        function ThemeMyLoginShortcode($args = array()) {
            global $WPLogin;

            $options = wp_parse_args($args, $this->options);

            $instance = ( isset($options['instance']) ) ? $options['instance'] : $this->NewInstance();

            return $WPLogin->Display($instance, $options);
        }
        
        
        function ThemeMyLoginPageShortcode($args = array()) {
            $args['instance'] = 'tml-main';
            $args['default_action'] = 'login';
            $args['show_title'] = '0';
            $args['before_widget'] = '';
            $args['after_widget'] = '';
            return $this->ThemeMyLoginShortcode($args);
        }
        
        function NewInstance() {
            $this->instances++;
            return 'tml-' . $this->instances;
        }
        
        function Activate() {
            $insert = array(
                'post_title' => 'Login',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => 1,
                'post_content' => '[theme-my-login-page]',
                'comment_status' => 'closed',
                'ping_status' => 'closed'
                );

            $theme_my_login = get_page_by_title('Login');
            if ( !$theme_my_login ) {
                $page_id = wp_insert_post($insert);
            } else {
                $page_id = $theme_my_login->ID;
                $insert['ID'] = $page_id;
                $insert['post_content'] = str_replace('[theme-my-login]', '[theme-my-login-page]', $theme_my_login->post_content);
                wp_update_post($insert);
            }

            $this->options['page_id'] = $page_id;
            $this->options['version'] = $this->version;
            update_option('theme_my_login', $this->options);
            
            add_role('pending', 'Pending', array());
            add_role('denied', 'Denied', array());
        }

        function Deactivate() {
            if ( $this->options['uninstall'] ) {
                delete_option('theme_my_login');
                delete_option('widget_theme-my-login');
                wp_delete_post($this->options['page_id']);
                remove_role('pending');
                remove_role('denied');
            }
        }
        
        function InitOptions($save = false) {

            // General
            $this->options['page_id']               = 0;
            $this->options['uninstall']             = 0;
            $this->options['show_page']             = 0;
            $this->options['custom_pass']           = 0;
            $this->options['email_from']            = '';
            $this->options['email_from_name']       = '';
            $this->options['email_content_type']    = 'text/plain';
            $this->options['use_css']               = 1;
            $this->options['override_redirect']     = 1;

            // Titles
            $this->options['welcome_title']         = __('Welcome') . ', %display_name%';
            $this->options['login_title']           = __('Log In');
            $this->options['register_title']        = __('Register');
            $this->options['lost_pass_title']       = __('Lost Password');
            $this->options['logout_title']          = __('Log Out');

            // Messages
            $this->options['register_message']      = __('A password will be e-mailed to you.');
            $this->options['success_message']       = __('Registration complete. Please check your e-mail.');
            $this->options['lost_pass_message']     = __('Please enter your username or e-mail address. You will receive a new password via e-mail.');

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
            $this->options['registration_email']    = array('subject' => '', 'message' => '', 'admin_disable' => 0, 'user_disable' => 0);
            $this->options['user_approval_email']   = array('subject' => '', 'message' => '');
            $this->options['user_denial_email']     = array('subject' => '', 'message' => '');

            // Links & Redirects
            global $wp_roles;
            if ( empty($wp_roles) )
                $wp_roles = new WP_Roles();
            $user_roles = $wp_roles->get_names();
            foreach ( $user_roles as $role => $title ) {
                $this->options['links'][$role][] = array('title' => 'Dashboard', 'url' => admin_url());
                $this->options['links'][$role][] = array('title' => 'Profile', 'url' => admin_url('profile.php'));
                $this->options['redirects'][$role] = array('login_url' => '', 'logout_url' => '');
            }

            if ( $save )
                update_option('theme_my_login', $this->options);
                
        }

        function LoadOptions($options = '') {

            $this->InitOptions();

            $storedoptions = get_option('theme_my_login');
            if ( $storedoptions && is_array( $storedoptions ) ) {
                foreach ( $storedoptions as $key => $value ) {
                    $this->options[$key] = $value;
                }
            } else update_option('theme_my_login', $this->options);
        }

        function GetOption($key) {
            if ( array_key_exists($key, $this->options) ) {
                return $this->options[$key];
            } else return null;
        }

        function SetOption($key, $value) {
            $this->options[$key] = $value;
        }

        function SaveOptions() {
            $oldvalue = get_option('theme_my_login');
            if( $oldvalue == $this->options ) {
                return true;
            } else return update_option('theme_my_login', $this->options);
        }
    }
}

if (class_exists('ThemeMyLogin')) {
    global $wp_version;
    
    $ThemeMyLogin = new ThemeMyLogin();

    if ( version_compare($wp_version, '2.8', '>=') ) {
        require_once (WP_PLUGIN_DIR . '/theme-my-login/classes/class.widget-new.php');
    } else {
        require_once (WP_PLUGIN_DIR . '/theme-my-login/classes/class.widget-old.php');
    }
    
    if ( !function_exists('theme_my_login') ) :
    function theme_my_login($args = '') {
        global $ThemeMyLogin;
        
        echo $ThemeMyLogin->ThemeMyLoginShortcode($args);
    }
    endif;

    if ( !function_exists('wp_new_user_notification') ) :
    function wp_new_user_notification($user_id, $plaintext_pass = '') {
        global $ThemeMyLogin, $wp_version;

        $user = new WP_User($user_id);

        $user_login = stripslashes($user->user_login);
        $user_email = stripslashes($user->user_email);

        if ( $ThemeMyLogin->options['moderate_users'] ) {
            $message  = sprintf(__('New user requires approval on your blog %s:'), get_option('blogname')) . "\r\n\r\n";
            $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
            $message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n\r\n";
            $message .= __('To approve or deny this user:', 'theme-my-login') . "\r\n";
            $message .= admin_url('users.php');

            @wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Awaiting Approval'), get_option('blogname')), $message);

            $user->set_role('pending');
        } else {
            if ( !$ThemeMyLogin->options['registration_email']['admin_disable'] ) {
                $message  = sprintf(__('New user registration on your blog %s:'), get_option('blogname')) . "\r\n\r\n";
                $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
                $message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

                @wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), get_option('blogname')), $message);
            }

            if ( empty($plaintext_pass) )
                return;

            if ( !$ThemeMyLogin->options['registration_email']['user_disable'] ) {
                $subject = $ThemeMyLogin->options['registration_email']['subject'];
                $message = $ThemeMyLogin->options['registration_email']['message'];
                $replace_this = array('/%blogname%/', '/%siteurl%/', '/%user_login%/', '/%user_email%/', '/%user_pass%/', '/%user_ip%/');
                $replace_with = array(get_option('blogname'), get_option('siteurl'), $user->user_login, $user->user_email, $plaintext_pass, $_SERVER['REMOTE_ADDR']);

                if ( !empty($subject) )
                    $subject = preg_replace($replace_this, $replace_with, $subject);
                else
                    $subject = sprintf(__('[%s] Your username and password'), get_option('blogname'));
                if ( !empty($message) )
                    $message = preg_replace($replace_this, $replace_with, $message);
                else {
                    $message  = sprintf(__('Username: %s'), $user_login) . "\r\n";
                    $message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
                    $message .= ( version_compare($wp_version, '2.7', '>=') ) ? wp_login_url() . "\r\n" : site_url('wp-login.php', 'login') . "\r\n";
                }
                tml_apply_mail_filters();
                wp_mail($user_email, $subject, $message);
                tml_remove_mail_filters();
            }
        }
    }
    endif;
    
    if ( !function_exists('wp_generate_password') ) :
    function wp_generate_password($length = 12, $special_chars = true) {
        global $ThemeMyLogin;
        
        if ( $ThemeMyLogin->options['custom_pass'] && isset($_POST['user_pw']) && '' != $_POST['user_pw'] )
            return stripslashes($_POST['user_pw']);

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        if ( $special_chars )
            $chars .= '!@#$%^&*()';

        $password = '';
        for ( $i = 0; $i < $length; $i++ )
            $password .= substr($chars, wp_rand(0, strlen($chars) - 1), 1);
        return $password;
    }
    endif;
    
    if ( !function_exists('tml_wp_mail_from') ) :
    function tml_wp_mail_from($from) {
        global $ThemeMyLogin;
        return empty($ThemeMyLogin->options['email_from']) ? $from : $ThemeMyLogin->options['email_from'];
    }
    endif;
    
    if ( !function_exists('tml_wp_mail_from_name') ) :
    function tml_wp_mail_from_name($from_name) {
        global $ThemeMyLogin;
        return empty($ThemeMyLogin->options['email_from_name']) ? $from_name : $ThemeMyLogin->options['email_from_name'];
    }
    endif;
    
    if ( !function_exists('tml_wp_mail_content_type') ) :
    function tml_wp_mail_content_type() {
        global $ThemeMyLogin;
        return $ThemeMyLogin->options['email_content_type'];
    }
    endif;
    
    if ( !function_exists('tml_apply_mail_filters') ) :
    function tml_apply_mail_filters() {
        $filters = array('wp_mail_from', 'wp_mail_from_name', 'wp_mail_content_type');
        foreach ( $filters as $filter )
            add_filter($filter, 'tml_'.$filter);
    }
    endif;
    
    if ( !function_exists('tml_remove_mail_filters') ) :
    function tml_remove_mail_filters() {
        $filters = array('wp_mail_from', 'wp_mail_from_name', 'wp_mail_content_type');
        foreach ( $filters as $filter )
            remove_filter($filter, 'tml_'.$filter);
    }
    endif;

}

?>
