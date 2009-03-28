=== Theme My Login ===
Contributors: jfarthing84
Donate link: http://webdesign.jaedub.com
Tags: wordpress, login, register, theme, form, james kelly
Requires at least: 2.5
Tested up to: 2.7.1
Stable tag: 2.0

This plugin themes the WordPress login, register, forgot password and profile pages to look like the rest of your website.


== Description ==

This plugin themes the WordPress login, register, forgot password and profile pages to look like the rest of your website. It replaces the wp-login.php and profile.php by using a page template from your theme.


== Installation ==

Upload the Theme My Login plugin to your 'wp-content/plugins' directory and activate it. The 'Theme My Login' settings will apear under the 'Settings' menu in the WordPress admin. You will need to go in to these settings and set them according to your theme layout. Here is a desciption of what needs to go into each setting.

1. Redirect on Login - You can change this to any location you would like the user to be redirected to upon login. This defaults to 'wp-admin/'.

2. Redirect on Logout - You can change this to any location you would like the user to be redirected to upon logout. This defaults to 'wp-login.php?loggedout=true'.

3. Register Page Title - You can change this to whatever title you want for the registration page. You can use %blogname% for your blog name. This defaults to 'Register &rsaquo;'.

4. Register Text - You can change this to whatever text you want to appear above your registration form. This defaults to 'Register'.

5. Register Message - You can change this to whatever you want to appear below your registration form. This defaults to 'A password will be e-mailed to you.'

6. Login Page Title - You can change this to whatever title you want to for the login page. You can use %blogname% for your blog name. This defaults to 'Log In &rsaquo;'.

7. Login Text - You can change this to whatever text you want to appear above your login form. This defaults to 'Log In'.

8. Lost Password Page Title - You can change this to whatever title you want for the lost password page. You can use %blogname% for your blog name. This defaults to 'Lost Password &rsaquo;'.

9. Lost Password Text - You can change this to whatever text you want to appear above your lost password form. This defaults to 'Lost Password'.

10. Profile Page Title - You can change this to whatever title you want for the subscriber profile page. You can use %blogname% for your blog name. This defaults to 'Profile &rsqauo;'.

11. Profile Text - You can change this to whatever text you want to appear above the user profile form. This defaults to 'Your Profile'.

Now you can save your changes and go test out your new themed login and registration pages. That's all!


== Version History ==

* 1.0.0 - 2009-03-13 - Initial release version
* 1.0.1 - 2009-03-14 - Made backwards compatible to WordPress 2.5+
* 1.1.0 - 2009-03-14 - Added custom profile to completely hide the back-end from subscribers
* 1.1.1 - 2009-03-16 - Prepared plugin for internationalization and fixed a PHP version bug
* 1.1.2 - 2009-03-20 - Updated to allow customization of text below registration form
* 1.2.0 - 2009-03-26 - Added capability to customize page titles for all pages affected by plugin
* 2.0.0 - 2009-03-27 - Completely rewrote plugin to use page template, no more specifying template files & HTML