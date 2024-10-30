=== Plugin Name ===
Contributors: oliverfernandez, nerder
Tags: Marfeel, Mobile, Monetization, Advertising, Smartphone, Tablet
Requires at least: 3.2
Tested up to: 4.9.4
Stable tag: 1.8.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin provides an easy activation of Marfeel produced Google AMP pages when using WordPress. For more information about Marfeel, please visit http://www.marfeel.com

== Description ==

This plugin provides an easy activation of Marfeel produced Google AMP pages when using WordPress. For more information about Marfeel, please visit http://www.marfeel.com

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Done! You can check under Settings -> Marfeel if the activation was successful.

== Frequently Asked Questions ==

= How do I activate this plugin? =

You have to be a Marfeel client first. If you are interested in Marfeel, please send us an email to hello@marfeel.com

= I get an error message in my Settings page =

Please write us to support@marfeel.com to address your problem.

== Changelog ==

= 1.8.7 =
* Rename plugin in favor of MarfeelPress

= 1.8.5 =
* don't generate amphtml links for empty pages and posts

= 1.8.4 =
* Add support for relative permalinks

= 1.8.3 =
* Change curls for internal wp methods
* Change 404 error code for 410 GONE
* Fix minor bugs

= 1.8.2 =
* Fix bug on 404 generation

= 1.8.1 =
* Add custom 404 when amp url is wrong

= 1.8.0 =
* Remove [bc/b].marfeelcache.com as CNAME fallback (Breaking Change)

= 1.7.8 =
* Bugfix

= 1.7.7 =
* Fix bug on amp endpoint

= 1.7.6 =
* Change domain from *.marfeel.com to *.marfeelcache.com

= 1.7.5 =
* Fix minor bugs

= 1.7.4 =
* Fix bug on final slash not respecting user configuration
* Drop support for custom pages

= 1.7.3 =
* Improve support for PHP 5.3
* Add support for custom permalinks

= 1.7.2 =
* Support for New Relic (info: https://github.com/ampproject/amphtml/issues/2380 )
* Bug fixing

= 1.7.1 =
* Add feedback system
* Bug fixing
* Fix typos

= 1.7.0 =
* Add new activation method features
* Improved Marfeel link detection
* Add support on-demand for CNAME
* Bug fixing

= 1.6.2 =
* Bug fixing

= 1.6.1 =
* Ease the installation progress
* AMP pages out of the box without further configurations
* Bug fixing
* Code cleanup

= 1.6.0 =
* Add support with conflictive AMP plugins
* Bug fixing
* Code cleanup

= 1.5.4 =
* Correction on the article URL detection

= 1.5.3 =
* Adding support for multipaged articles

= 1.5.2 =
* Bug fix for old supported domains

= 1.5.1 =
* Bug fix when domain need to be set manually

= 1.5.0 =
* Added support to custom AMP domains (i.e. amp.example.com)

= 1.0.3 =
* Updated description

= 1.0.2 =
* Added option to manually configure plugin settings when auto detection fails.

= 1.0.1 =
* Improved Marfeel servers detection

= 1.0 =
* AMP pages. Once the plugin is activated, it will add the AMP link tag inside the HEAD. For more information about this, please visit: https://www.ampproject.org/docs/guides/discovery.html
