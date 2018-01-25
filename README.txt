=== CBX WP Write Log ===
Contributors: codeboxr,manchumahara
Donate link: https://codeboxr.com
Tags: log, debug
Requires at least: 3.0.1
Tested up to: 3.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple dev plugin that helps to write something to debug log file

== Description ==

A simple dev plugin that helps to write something to wordpress debug file using a simple function

Usages:

`
if ( ! function_exists( 'write_log' ) ) {
	write_log('testing a variable output');
	write_log($name);
}
`

For any query [contact us](https://codeboxr.com/contact-us/)

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `cbxwpwritelog` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. call function `<?php write_log('your content, variable or object here'); ?>` in your templates or plugin anywhere where you need to

== Frequently Asked Questions ==



== Screenshots ==



== Changelog ==

= 1.0.0 =
* Initial version released
