=== The Event Calendar: Private Category ===
Contributors: don4g, arifwn
Donate link: http://www.smeans.com/
Tags: the event calendar, private category
Requires at least: 3.3
Tested up to: 3.5.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add-on to The Events Calendar Plugin.

This plug-in allows event categories to be set as private and viewable only to logged-in users.

== Description ==

Add-on to [The Events Calendar Plugin](http://wordpress.org/extend/plugins/the-events-calendar/).

This plug-in allows event categories to be set as private and viewable only to logged-in users. All events that uses the private category will be displayed only to logged-in user.

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php do_action('plugin_name_hook'); ?>` in your templates

== Usage ==

1. Go to wp-admin > Events > Event Categories
2. Create a new category or edit existing one
3. Set the category as private by checking "Private Category" checkbox
4. Any event that uses that private category will be automatically marked as private and viewable only to logged in user.

== Changelog ==

= 1.0.1 =
* Display appropriate message if The Event Calendar not installed.

= 1.0 =
* Initial release.