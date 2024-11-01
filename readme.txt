=== Simple Running Log ===
Contributors: kevmarsden
Tags: running, log, running_log, dashboard, admin, simple_running_log, widget, sidebar
Requires at least: 3.0
Tested up to: 4.4
Stable tag:  1.7
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Creates a running log sidebar widget which is updated from the Admin Dashboard.  

== Description ==

This plugin creates a new table in your WordPress database to store data for a running log. The running log database is updated from your WordPress Admin Dashboard and then running log data displays in a sidebar widget. You don't have to link to another website anymore!  

The plugin has been tested up to WordPress 4.0 with various themes. If you find any bugs, leave a comment in the support tab or contact me on Twitter at [@kevincmarsden](http://www.twitter.com/kevincmarsden)

== Installation ==

1. Download the folder
2. Unzip and upload the simplerunninglog folder to your **../wp-content/plugins/ folder**
3. Go to the Plugins menu on your dashboard. Activate the Simple Running Log plugin
4. Go to Appearance -> Widgets and move the new Running Log widget to the sidebar

== Screenshots ==

1. This is the Simple Running Log sideboard widget in theme Twenty Twelve. The widget will conform to the theme's style.
2. This is widget on the Admin Dashboard which is used to update the running log.

== Changelog ==

= Version 1.7 =
* Removed deprecated mysql functions
* Added $wpdb->prepare to sanitize data better
* Cleaned up code
= Version 1.6 =
* Added jQuery UI date picker
* Updated table structure
= Version 1.5 =
* Added weekly mileage to sidebar widget
= Version 1.4 = 
* Added uninstall.php
* Added nonces for security
* Cleaned up problems with wpdb
= Version 1.3 = 
* Removed time entry from Dashboard
= Version 1.2 = 
* Fixes minor bugs and confirms that the plugin is tested up to 3.6
= Version 1.1 = 
* Updates a bug in the query which displays the total mileage for the month.

== Frequently Asked Questions ==

= The data in my sidebar log is incorrect =

First check the Timezone settings in the Admin Settings menu. If the timezone is correct, then perhaps the data was entered with an incorrect date. It's possible to manually check the data using phpmyadmin or another MySQL visualizer depending on your host.  

= The Training Log Widget doesn’t have any options =

There currently aren’t any options for the widget, so all you need to do is add it to a widget location and then you’re all set.
