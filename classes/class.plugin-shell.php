<?php

global $wp_version;

if ($wp_version < '2.6') {
    if ( !defined('WP_CONTENT_DIR') )
        define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
    if ( !defined('WP_CONTENT_URL') )
        define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
    if ( !defined('WP_PLUGIN_DIR') )
        define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
    if ( !defined('WP_PLUGIN_URL') )
        define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
}

if ( !class_exists('WPPluginShell')) {

    class WPPluginShell {
    
        var $plugin_title = 'My Plugin';
        var $plugin_textdomain = 'my-plugin';
        var $plugin_options_name = 'my_plugin';

        var $actions;
        var $filters;
        var $shortcodes;
        
        var $styles;
        var $scripts;
        var $admin_styles;
        var $admin_scripts;
        var $admin_pages;
        
        var $header_code = '';
        var $footer_code = '';
        
        var $options;
        
        var $mail_from;
        var $mail_content_type;
        
        var $wp_version;
        
        function WPPluginShell() {
            $this->__construct();
        }
        
        function __construct() {
            global $wp_version;
            
            $this->wp_version = $wp_version;

            $this->AddAction('wp_head', '_WPHead_');
            $this->AddAction('wp_footer', '_WPFooter_');
            $this->AddAction('wp_print_styles', '_WPPrintStyles_');
            $this->AddAction('wp_print_scripts', '_WPPrintScripts_');
            $this->AddAction('admin_print_styles', '_AdminPrintStyles_');
            $this->AddAction('admin_print_scripts', '_AdminPrintScripts_');
            $this->AddAction('admin_head', '_AdminHead_');
            $this->AddAction('admin_menu', '_AdminMenu_');
            
            $this->AddFilter('wp_mail_from', '_WPMailFrom_');
            $this->AddFilter('wp_mail_from_name', '_WPMailFromName_');
            $this->AddFilter('wp_mail_content_type', '_WPMailContentType_');
            
            $this->ActivateHooks('actions');
            $this->ActivateHooks('filters');
            $this->ActivateHooks('shortcodes');
            
        }
        
        /*
        function __call($method, $args) {
            print '';
        }
        */
        function SetPluginTitle($name) {
            $this->plugin_textdomain = sanitize_title($name);
            $this->plugin_title = __($name, $this->plugin_textdomain);
            $this->plugin_options_name = str_replace(' ', '_', strtolower($name));
        }
        
        function ActivateHooks($type = 'actions') {
            if (is_array($this->$type) && !empty($this->$type)) {
                foreach ( $this->$type as $key => $args ) {
                    $func = (is_array($args['func'])) ? $args['func'] : array(&$this, $args['func']);
                    if ( 'actions' == $type ) {
                        if ( version_compare($this->wp_version, $args['wp_version'], '>=') )
                            add_action($args['tag'], $func, $args['priority'], $args['args']);
                    } elseif ( 'filters' == $type ) {
                        if ( version_compare($this->wp_version, $args['wp_version'], '>=') )
                            add_filter($args['tag'], $func, $args['priority'], $args['args']);
                    } elseif ( 'shortcodes' == $type ) {
                        add_shortcode($args['tag'], $func);
                    }
                }
            }
        }
        
        function AddAction($tag, $func = false, $priority = 10, $args = 1, $wp_version = '2.5') {
            if (empty($func)) {
                $tmp = explode('_', $tag);
                foreach ($tmp as $k => $v)
                    $tmp[$k] = (in_array($v, array('wp', 'url'))) ? strtoupper($v) : ucwords($v);
                $func = implode($tmp);
            }
            $this->actions[] = array('tag' => $tag, 'func' => $func, 'priority' => $priority, 'args' => $args, 'wp_version' => $wp_version);
        }
        
        function AddFilter($tag, $func = false, $priority = 10, $args = 1, $wp_version = '2.5') {
            if (empty($func)) {
                $tmp = explode('_', $tag);
                foreach ($tmp as $k => $v)
                    $tmp[$k] = (in_array($v, array('wp', 'url'))) ? strtoupper($v) : ucwords($v);
                $func = implode($tmp);
            }
            $this->filters[] = array('tag' => $tag, 'func' => $func, 'priority' => $priority, 'args' => $args, 'wp_version' => $wp_version);
        }
        
        function AddShortcode($tag, $func = '') {
            if (empty($func)) {
                $tmp = explode('_', str_replace('-', '_', $tag));
                foreach ($tmp as $k => $v)
                    $tmp[$k] = (in_array($v, array('wp', 'url'))) ? strtoupper($v) : ucwords($v);
                $func = implode($tmp) . 'Shortcode';
            }
            $this->shortcodes[] = array('tag' => $tag, 'func' => $func);
        }
        
        function AddStyle($handle, $src = false, $deps = array(), $ver = false, $media = false) {
            $this->styles[] = array('handle' => $handle, 'src' => $src, 'deps' => $deps, 'ver' => $ver, 'media' => $media);
        }
        
        function AddScript($handle, $src = false, $deps = array(), $ver = false, $in_footer = false) {
            $this->scripts[] = array('handle' => $handle, 'src' => $src, 'deps' => $deps, 'ver' => $ver, 'in_footer' => $in_footer);
        }
        
        function AddAdminStyle($handle, $src = false, $deps = array(), $ver = false, $media = false) {
            $this->admin_styles[] = array('handle' => $handle, 'src' => $src, 'deps' => $deps, 'ver' => $ver, 'media' => $media);
        }

        function AddAdminScript($handle, $src = false, $deps = array(), $ver = false, $in_footer = false) {
            $this->admin_scripts[] = array('handle' => $handle, 'src' => $src, 'deps' => $deps, 'ver' => $ver, 'in_footer' => $in_footer);
        }
        
        function AddAdminPage($page, $page_title = '', $menu_title = '', $access_level = 8, $file = '', $function = '', $icon_url = '') {
            $page_title = (empty($page_title)) ? $this->plugin_title : __($page_title, $this->plugin_textdomain);
            $menu_title = (empty($menu_title)) ? (empty($page_title)) ? $this->plugin_title : __($page_title, $this->plugin_textdomain) : __($menu_title, $this->plugin_textdomain);
            $access_level = (empty($access_level)) ? 8 : $access_level;
            if ( empty($file) && empty($function) )
                $function = str_replace(' ', '', ucwords(str_replace('-', ' ', sanitize_title($page_title))));
            $file = (empty($file)) ? (empty($function)) ? __FILE__ : sanitize_title($page_title) : $file;
            $function = (empty($function)) ? '' : $function;
        
            $this->admin_pages[] = array('page' => $page, 'page_title' => $page_title, 'menu_title' => $menu_title, 'access_level' => $access_level, 'file' => $file, 'function' => $function, 'icon_url' => $icon_url);
        }
        
        function AddToHeader($code) {
            $this->header_code .= $code;
        }
        
        function AddToFooter($code) {
            $this->footer_code .= $code;
        }
        
        function SetMailFrom($email = '', $name = '') {
            if (!empty($email))
                $this->mail_from['email'] = $email;
            if (!empty($name))
                $this->mail_from['name'] = $name;
        }
        
        function SetMailContentType($format) {
            if (!empty($format))
                $this->mail_content_type = $format;
        }
        
        function _WPHead_() {
            if ( version_compare($this->wp_version, '2.6', '<') ) {
                if ( is_array($this->styles) && !empty($this->styles) ) {
                    foreach ( $this->styles as $key => $args )
                        if (empty($args['ver']))
                            $args['ver'] = $this->wp_version;
                        echo '<link rel="stylesheet" id="'.$args['handle'].'-css" href="'.$args['src'].'?ver='.$args['ver'].'" type="text/css" media="'.$args['media'].'" />'."\n";
                }
            }
            echo $this->header_code;
        }
        
        function _WPFooter_() {
            echo $this->footer_code;
        }

        function _WPPrintStyles_() {
            if ( !is_admin() )
            $this->_handle_enqueues('style', $this->styles);
        }
        
        function _WPPrintScripts_() {
            if ( !is_admin() )
                $this->_handle_enqueues('script', $this->scripts);
        }
        
        function _AdminPrintStyles_() {
            $this->_handle_enqueues('style', $this->admin_styles);
        }
        
        function _AdminPrintScripts_() {
            $this->_handle_enqueues('script', $this->admin_scripts);
        }
        
        function _AdminHead_() {
            if ( version_compare($this->wp_version, '2.6', '<') ) {
                if ( is_array($this->admin_styles) && !empty($this->admin_styles) ) {
                    foreach ( $this->admin_styles as $key => $args ) {
                        if (empty($args['ver']))
                            $args['ver'] = $this->wp_version;
                        echo '<link rel="stylesheet" id="'.$args['handle'].'-css" href="'.$args['src'].'?ver='.$args['ver'].'" type="text/css" media="'.$args['media'].'" />'."\n";
                    }
                }
            }
        }
        
        function _AdminMenu_() {
            if ( is_array($this->admin_pages) && !empty($this->admin_pages) ) {
                foreach ( $this->admin_pages as $key => $args ) {
                    extract($args);
                
                    $function = (empty($function)) ? '' : array(&$this, $function);

                    if ( version_compare($this->wp_version, '2.7', '>=') ) {
                        if ('menu' == $page)
                            add_menu_page($page_title, $menu_title, $access_level, $file, $function, $icon_url = '');
                        elseif ('object' == $page)
                            add_object_page($page_title, $menu_title, $access_level, $file, $function, $icon_url = '');
                        elseif ('utility' == $page)
                            add_utility_page($page_title, $menu_title, $access_level, $file, $function, $icon_url = '');
                        elseif ('dashboard' == $page)
                            add_dashboard_page($page_title, $menu_title, $access_level, $file, $function);
                        elseif ('posts' == $page)
                            add_posts_page($page_title, $menu_title, $access_level, $file, $function);
                        elseif ('media' == $page)
                            add_media_page($page_title, $menu_title, $access_level, $file, $function);
                        elseif ('links' == $page)
                            add_links_page($page_title, $menu_title, $access_level, $file, $function);
                        elseif ('pages' == $page)
                            add_pages_page($page_title, $menu_title, $access_level, $file, $function);
                        elseif ('comments' == $page)
                            add_comments_page($page_title, $menu_title, $access_level, $file, $function);
                    } else {
                        if (in_array($page, array('menu', 'object', 'utility', 'dashboard', 'posts', 'media', 'links', 'pages', 'comments')))
                            add_menu_page($page_title, $menu_title, $access_level, $file, $function);
                    }
                    if ('management' == $page)
                        add_management_page($page_title, $menu_title, $access_level, $file, $function);
                    elseif ('options' == $page)
                        add_options_page($page_title, $menu_title, $access_level, $file, $function);
                    elseif ('theme' == $page)
                        add_theme_page($page_title, $menu_title, $access_level, $file, $function);
                    elseif ('users' == $page)
                        add_users_page($page_title, $menu_title, $access_level, $file, $function);
                    else
                        add_submenu_page($page, $page_title, $menu_title, $access_level, $file, $function);
                }
            }
        }
        
        function _WPMailFrom_($from_email) {
            return (empty($this->mail_from['email'])) ? $from_email : $this->mail_from['email'];
        }

        function _WPMailFromName_($from_name) {
            return (empty($this->mail_from['name'])) ? $from_name : $this->mail_from['name'];
        }
        
        function _WPMailContentType_($format) {
            return (empty($this->mail_content_type)) ? $format : $this->mail_content_type;
        }

        function _handle_enqueues($type, $to_enqueue) {
            if ( is_array($to_enqueue) && !empty($to_enqueue) ) {
                foreach ( $to_enqueue as $key => $args ) {
                    if ('style' == $type)
                        wp_enqueue_style($args['handle'], $args['src'], $args['deps'], $args['ver'], $args['media']);
                    elseif ('script' == $type)
                        wp_enqueue_script($args['handle'], $args['src'], $args['deps'], $args['ver'], $args['in_footer']);
                }
            }
        }

        function LoadOptions($options = '') {
        
            if (is_array($options) && !empty($options))
                $this->options = $options;
            elseif (is_callable(array(&$this, 'InitOptions')))
                $this->InitOptions();
        
            $storedoptions = get_option( $this->plugin_options_name );
            if ( $storedoptions && is_array( $storedoptions ) ) {
                foreach ( $storedoptions as $key => $value ) {
                    $this->options[$key] = $value;
                }
            } else update_option( $this->plugin_options_name, $this->options );
        }

        function GetOption( $key ) {
            if ( array_key_exists( $key, $this->options ) ) {
                return $this->options[$key];
            } else return null;
        }

        function SetOption( $key, $value ) {
            $this->options[$key] = $value;
        }

        function SaveOptions() {
            $oldvalue = get_option( $this->plugin_options_name );
            if( $oldvalue == $this->options ) {
                return true;
            } else return update_option( $this->plugin_options_name, $this->options );
        }
        
    }
    
}

?>
