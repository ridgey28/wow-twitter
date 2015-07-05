=== WOW-Twitter ===
Contributors: ridgey28
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HTDDUX2FL4DS6
Tags: Social Media, Twitter, Widget, Badge, Admin
Requires at least: 3.1
Tested up to: 4.2.2
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


A highly configurable Twitter Plugin for WordPress with automatic backup feature and much more!

== Description ==

The WOW-Twitter plugin for WordPress features a widget that you can place in your sidebar/ footer etc and you can also add a badge, containing your twitter bio and follow me button that can be placed underneath your posts in a single page.

The admin area features customisable options to control how many tweets you want to fetch and whether or not to include retweets and replies. You can also control and display the date and time feature from the admin area.  Your data is cached and stored in Json format parsed at a time interval you can set yourself.

There is also a backup feature that runs automatically so if the Twitter API is down your Tweets or badge will always be displayed.

The plugin will log and display any errors encountered in the admin area and your tweets will be displayed from a backup file automatically.

The plugin uses the latest Secure Twitter 1.1 API

== Changelog ==

V1.1

Updated certificate

Updated tmhOAuth script

Integrated automatic update from github within WP dashboard with https://github.com/YahnisElsts/plugin-update-checker


== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page. From Version 1.1 you can automatically install from GitHub within the WordPress admin but will require manual installation first.

== Frequently Asked Questions ==

= What are the minimum requirements needed to run this plugin? =

The library has been tested with PHP 5.3+ and relies on CURL and hash_hmac. The vast majority of hosting providers include these libraries and run with PHP 5.1+.

The code makes use of hash_hmac, which was introduced in PHP 5.1.2. If your version of PHP is lower than this you should ask your hosting provider for an update.

= How do I create a app on Twitter? =

Follow this handy tutorial on my [Blog](http://www.worldoweb.co.uk/2012/create-a-twitter-app-for-the-new-api")

= Why do I not see the total amount of Tweets I set? =

Unfortunately if you have set Exclude Replies and Retweets to False these are filtered from the results total fetched from Twitter.  Currently there is no alternative other than to set your **Maximum Tweets** to a higher amount!

= How many tweets can I fetch? =

Currently the maximum allowed is 180

= Can I add Twitter to my post, pages or Theme? =

There is a shortcode to add your Twitter Stream to your posts, pages and also your theme.  The shortcode to enter into WP admin editor for posts and pages is '[wow_twitter]'.  Follow the advice on [WP Codex](http://codex.wordpress.org/Function_Reference/do_shortcode) about itegrating the shortcode into your theme.

= Can I style it myself with CSS? =

You can style to your own taste using '#wow_twitter .tweet'.  The widget uses minimal CSS and can be styled using '.widget_wow_twitter_stream'.  There is also an option to remove the built in CSS file in the admin settings so that you can style it in your style.css.

== Screenshots ==

1. Administration area with Customisable Options.

2. Widget Area with \'Time Since\' Style enabled.

3. Badge displayed under post in single file.

== Upgrade Notice ==
Updated to 1.1
