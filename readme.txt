=== Theme My Login ===
Contributors: jfarthing84
Donate link: http://www.jfarthing.com/donate
Tags: widget, login, registration, theme, custom, log in, register, sidebar, gravatar, redirection, e-mail
Requires at least: 2.5
Tested up to: 2.9-rare
Stable tag: trunk

Themes the WordPress login pages according to your theme.


== Description ==

This plugin themes the WordPress login, registration and forgot password pages according to your current theme. It replaces the wp-login.php file by using a page template from your theme. Also includes a widget for sidebar login.

= Features =
* Your registration, log in and password recovery pages will all match the rest of your website
* Includes a customizeable widget to login anywhere from your blog
* Redirect users upon log in based on their role
* Customize titles and messages for the registration, log in and password recovery forms
* Show gravatar to users who are logged in
* Assign custom links to users who are logged in based on their role
* Customize user emails for registration and/or password recovery
* Send user emails in HTML format
* Allow users to set their own password upon registration


== Installation ==

1. Upload the plugin to your 'wp-content/plugins' directory
1. Activate the plugin


== Frequently Asked Questions ==

None yet. Please visit http://www.jfarthing.com/forum for any support!


== Changelog ==

= 4.2 =
* Added the option to send e-mails in HTML format
* Fixed a bug that broke custom user role links if all links were deleted

= 4.1.2 =
* Added the ability to change main login page ID (Only needed for debugging)
* The login will now revert to default wp-login in the case of plugin failure

= 4.1.1 =
* Fixed a major bug dealing with saving options that broke the plugin
* Fixed a CSS bug causing interference with other interfaces that use jQuery UI Tabs

= 4.1 =
* Implemented custom user passwords
* Implemented custom e-mail from name & address
* Removed template tag & shortcode restriction on main login page

= 4.0 =
* Implemented custom links for logged in users based on role
* Implemented custom redirection upon log in based on role
* Implemented custom registration/password recovery emails
* Implemented true shortcode and template tag functionality
* Implemented true multi-instance functionality
* Implemented an easy-to-use jQuery tabbed administration menu
* Implemented both 'fresh' and 'classic' colors for administration menu

= 3.3.1 =
* Fixed a bug that broke password recovery due to the new system from WP 2.8.4

= 3.3 =
* Fixed a bug that disabled error display when GET variable 'loggedout' was set
* Added template tag access

= 3.2.8 =
* Fixed a security exploit regarding admin password reset addressed in WordPress 2.8.4

= 3.2.7 =
* Fixed a bug that determined how to create the widget

= 3.2.6 =
* Fixed a bug dealing with the version_compare() function
* Included French translation
* Included Spanish translation

= 3.2.5 =
* Fixed a bug that produced a 'headers aldready sent' error when uploading media
* Included Dutch translation

= 3.2.4 =
* Fixed the load_plugin_textdomain() call
* Added 'login_head' action hook

= 3.2.3 = 
* Fixed and updated many gettext calls for internationalization

= 3.2.2 =
* Added the option to leave widget links blank for default handling

= 3.2.1 =
* Fixed a XHTML validation issue

= 3.2 =
* Added the option to allow/disallow registration and password recovery within the widget
* Fixed a bug regarding color names within the CSS file that broke validation

= 3.1.1 =
* Fixed a bug that incorrectly determined current user role

= 3.1 =
* Added the ability to specify URL's for widget 'Dashboard' and 'Profile' links per user role
* Implemented WordPress 2.8 widget control for multiple widget instances
* Fixed a bug regarding the registration complete message

= 3.0.3 =
* Fixed a bug with the widget links

= 3.0.2 =
* Fixed a bug that didn't allow custom registration message to be displayed
* Fixed a few PHP unset variable notice's with a call to isset()

= 3.0.1 =
* Fixed a bug that caused a redirection loop when trying to access wp-login.php
* Fixed a bug that broke the widget admin interface
* Added the option to show/hide login page from page list

= 3.0 =
* Added a login widget

= 2.2 =
* Removed all "bloatware"

= 2.1 =
* Implemented login redirection based on user role

= 2.0.8 =
* Fixed a bug that broke the login with permalinks

= 2.0.7 =
* Fixed a bug that broke the Featured Content plugin

= 2.0.6 =
* Added the option to turn on/off subscriber profile theming

= 2.0.5 =
* Fixed a bug with default redirection and hid the login form from logged in users

= 2.0.4 =
* Fixed a bug regarding relative URL's in redirection

= 2.0.3 =
* Fixed various reported bugs and cleaned up code

= 2.0.2 =
* Fixed a bug that broke registration and broke other plugins using the_content filter

= 2.0.1 =
* Fixed a bug that redirected users who were not yet logged in to profile page

= 2.0 =
* Completely rewrote plugin to use page template, no more specifying template files & HTML

= 1.2 =
* Added capability to customize page titles for all pages affected by plugin

= 1.1.2 =
* Updated to allow customization of text below registration form

= 1.1.1 =
* Prepared plugin for internationalization and fixed a PHP version bug

= 1.1.0 =
* Added custom profile to completely hide the back-end from subscribers

= 1.0.1 =
* Made backwards compatible to WordPress 2.5+

= 1.0.0 =
* Initial release version