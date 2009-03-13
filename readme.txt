=== Theme My Login ===
Contributors: jfarthing84
Donate link: http://webdesign.jaedub.com
Tags: wordpress, login, register, theme, form
Requires at least: 2.7
Tested up to: 2.7.1
Stable tag: trunk

This plugin makes your registration and login pages look just like the rest of your website.

== Description ==

This plugin makes your registration and login pages look just like the rest of your website by replacing the wp-login.php page with a function that includes the header files, footer files, and all of the HTML in between that you specify in the 'Theme My Login' settings.

== Installation ==

Upload the Theme My Login plugin to your 'wp-content/plugins' directory and activate it. The 'Theme My Login' settings will apear under the 'Settings' menu in the WordPress admin. You will need to go in to these settings and set them according to your theme layout. Here is a desciption of what needs to go into each setting.

1. Redirect on Login - You can change this to any location you would like the user to be redirected to upon login. This defaults to 'wp-admin/'.

2. Redirect on Logout - You can change this to any location you would like the user to be redirected to upon logout. This defaults to 'wp-login.php?loggedout=true'.

3. Register Text - You can change this to whatever text you want to appear above your registration form. This defaults to 'Register'.

4. Login Text - You can change this to whatever text you want to appear above your login form. This defaults to 'Log In'.

5. Forgot Password Text - You can change this to whatever text you want to appear above your forgot password form. This defaults to 'Forgot Password'.

6. Template Header Files - Enter each header file used in your template, one per line. Typically, this is only header.php, but you can figure this out by clicking Appearance->Editor->Main Index Template. If the only function call you see is get_header() before the HTML then it's likely this is the only file you need to enter.

7. Template HTML After Header - Enter the HTML that appears between the get_header() function and the page code. You can probably figure this out by clicking Appearance->Editor->Main Index Template. The HTML you need to copy is everything between the last ?> in the top of the file and the line that looks something like this: <?php endif; ?> and the line that may look like this: <?php get_sidebar(); ?>. Keep in mind that if you are using a template that doesn't fit the typical scheme, you will need to experiment a bit to get this right.

8. Template HTML Before Footer - Enter the HTML that appears between the page code and the get_sidebar()/get_footer() functions. You can probably figure this out by clicking Appearance->Editor->Main Index Template. The HTML you need to copy is everything between the last ?> in the top of the file and the line that looks something like this: <?php if (have_posts()) : ?>. Keep in mind that if you are using a template that doesn't fit the typical scheme, you will need to experiment a bit to get this right.

9. Template Footer Files - Enter each footer file used in your template, one per line. Typically this is sidebar.php and footer.php. You can figure this out by clicking Appearance->Editor->Main Index Template. If you see the function calls get_sidebar() and get_footer() then you should be able to leave the defaults alone.

Now you can save your changes and go test out your new themed login and registration pages. That's all! 

http://webdesign.jaedub.com