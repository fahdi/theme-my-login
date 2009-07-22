=== Theme My Login ===
Contributors: jfarthing84
Donate link: http://www.jfarthing.com/donate
Tags: widget, login, registration, theme, custom, log in, register, sidebar, gravatar
Requires at least: 2.5
Tested up to: 2.8.2
Stable tag: trunk

Themes the WordPress login pages according to your theme.


== Description ==

This plugin themes the WordPress login, registration and forgot password pages according to your current theme. It replaces the wp-login.php and profile.php by using a page template from your theme. Also includes a widget for sidebar login.


== Installation ==

1. Upload the plugin to your 'wp-content/plugins' directory
1. Activate the plugin


== Frequently Asked Questions ==

None yet. Please visit http://www.jfarthing.com/forum for any support!


== Changelog ==

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