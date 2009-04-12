=== Theme My Login ===
Contributors: jfarthing84
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3836253
Tags: themed login, custom login, login redirection
Requires at least: 2.5
Tested up to: 2.7.1
Stable tag: 2.1

Themes the WordPress login pages according to your theme. Also allows you to redirect users based on their role upon login.


== Description ==

This plugin themes the WordPress login, register, forgot password and profile pages according to your current theme. It replaces the wp-login.php and profile.php by using a page template from your theme. <strong>Since 2.1</strong>: Theme My Login now allows you to redirect users based on their role upon login!

== Installation ==

Upload the Theme My Login plugin to your 'wp-content/plugins' directory and activate it. The 'Theme My Login' settings will apear under the 'Settings' menu in the WordPress admin. You will need to go in to these settings and set them according to your theme layout. Here is a desciption of what needs to go into each setting.


-- Redirection Settings

	* Subscriber Login Redirect - You can change this to any location on your site that you would like to redirect subscribers to upon login. This must be an absolute URL.

	* Contributor Login Redirect - You can change this to any location on your site that you would like to redirect contributors to upon login. This must be an absolute URL.

	* Author Login Redirect - You can change this to any location on your site that you would like to redirect authors to upon login. This must be an absolute URL.

	* Editor Login Redirect - You can change this to any location on your site that you would like to redirect editors to upon login. This must be an absolute URL.

	* Administrator Login Redirect - You can change this to any location on your site that you would like to redirect administrators to upon login. This must be an absolute URL.

	* Redirect on Logout - You can change this to any location you would like all users to be redirected to upon logout.


-- Template Settings

	* Theme Subscribers Profile - Check this option if you would like to theme subscriber's profiles. This is useful to hide the back-end from people who can't write posts anyway. <strong>WARNING</strong>: This will disable any user menus created by plugins.

	* Register Page Title - You can change this to whatever title you want for the registration page. You can use %blogname% for your blog name.

	* Register Text - You can change this to whatever text you want to appear above your registration form. This defaults to 'Register'.

	* Register Message - You can change this to whatever you want to appear below your registration form.

	* Login Page Title - You can change this to whatever title you want to for the login page. You can use %blogname% for your blog name.

	* Login Text - You can change this to whatever text you want to appear above your login form.

	* Lost Password Page Title - You can change this to whatever title you want for the lost password page. You can use %blogname% for your blog name.

	* Lost Password Text - You can change this to whatever text you want to appear above your lost password form. This defaults to 'Lost Password'.

	* Profile Page Title - You can change this to whatever title you want for the subscriber profile page. You can use %blogname% for your blog name.

	* Profile Text - You can change this to whatever text you want to appear above the user profile form.


Now you can save your changes and go test out your new themed login and registration pages. That's all!


== Version History ==

* 2.1.0 - 2009-04-12 - Implemented login redirection based on user role
* 2.0.8 - 2009-04-11 - Fixed a bug that broke the login with permalinks
* 2.0.7 - 2009-04-10 - Fixed a bug that broke the Featured Content plugin
* 2.0.6 - 2009-04-08 - Added the option to turn on/off subscriber profile theming
* 2.0.5 - 2009-04-04 - Fixed a bug with default redirection and hid the login form from logged in users
* 2.0.4 - 2009-04-03 - Fixed a bug regarding relative URL's in redirection
* 2.0.3 - 2009-04-02 - Fixed various reported bugs and cleaned up code
* 2.0.2 - 2009-03-31 - Fixed a bug that broke registration and broke other plugins using the_content filter
* 2.0.1 - 2009-03-30 - Fixed a bug that redirected users who were not yet logged in to profile page
* 2.0.0 - 2009-03-27 - Completely rewrote plugin to use page template, no more specifying template files & HTML
* 1.2.0 - 2009-03-26 - Added capability to customize page titles for all pages affected by plugin
* 1.1.2 - 2009-03-20 - Updated to allow customization of text below registration form
* 1.1.1 - 2009-03-16 - Prepared plugin for internationalization and fixed a PHP version bug
* 1.1.0 - 2009-03-14 - Added custom profile to completely hide the back-end from subscribers
* 1.0.1 - 2009-03-14 - Made backwards compatible to WordPress 2.5+
* 1.0.0 - 2009-03-13 - Initial release version