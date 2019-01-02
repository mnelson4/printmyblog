=== Print My Blog ===
Contributors: mnelson4
Tags: print, pdf, backup
Requires at least: 4.6
Stable tag: trunk
Tested up to: 5.0
Requires PHP: 5.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html


Create a paper/PDF copy of your entire blog in one click!

== Description ==

Create a paper/PDF copy of your entire blog in one click!
A paper copy will be readable in 100 years- long after your website is taken down and your digital backups are corrupted.
"Print My Blog" makes this really easy: no need to print your blog posts one-by-one, or print unnecessary stuff like your sidebar widgets or footer.
One click printing of your entire blog's contents in a format optimized for print.
Plugin alternative to [blog2print](https://www.blog2print.com), except you can print with your own printer or even your browser's print-to-pdf feature.

No upsells, no premium version. Entirely supported by donations.

= Why would I want to print my entire blog? =

Glad you asked:

* backup your memories in a format that has no technological dependencies: paper!
* easily share an entire blog offline
* create a book from your blog (although [Anthologize](https://wordpress.org/plugins/anthologize/) might be a better option for that)
* when shutting down a blog, convert its content to an easy-to-read format

= How does it work? =
Watch this 45 second video!

https://www.youtube.com/watch?v=puMi_CLxl3s&feature=youtu.be

It works with hundreds of blog posts (or pages, but not other custom post types yet), with Gutenberg, and page builders.
Only your posts’ content is printed, not your logo, site title, sidebar widgets, footer, etc. Just the stuff you care to read.

= But You’re Destroying Trees! =
Yes it can be a lot of paper. But

* there are paper-saving options, like using small text, multiple columns, and use smaller images (or removing them altogether)
* your latest package delivery used up about the same amount of tree
* you can recycle the pages if you’re done anyways
* lastly, you don’t need to actually print to paper. Google Chrome and other browsers allow you to instead print to PDF.

== Contribute ==

Want this plugin to be even better?

Here’s how you can help:

* [give it a good review](https://wordpress.org/support/plugin/print-my-blog/reviews/#new-post) and tell your friends
* help test. If you find a bug, please create an issue on [GitHub](https://github.com/mnelson4/printmyblog)
* help code. Create a pull request on [GitHub](https://github.com/mnelson4/printmyblog)
* help translate. Make this accessible to users everywhere using [WordPress' Glotpress](https://translate.wordpress.org/projects/wp-plugins/print-my-blog)
* help help. Answer other users’ questions in the [support section](https://wordpress.org/support/plugin/print-my-blog).

== What's the Big Deal? ==

The mission is: preserve your blog for decades to come in a low-tech format.

Your website might not be around forever, nor might WordPress. But by printing it, there’s hope your blog’s ideas and stories can live on.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/print-my-blog` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Go to Tools->Print To Blog
1. Press "Prepare Print Page"
1. When you see "Print Now" appear, click it.

== Troubleshooting ==

* the WP API (or REST API) needs to not be deactivated
* caching plugins should probably be deactivated before use

== Screenshots ==

1. Print Setup Page
2. Printing Page
3. The Blog is Ready for Printing!

== Changelog ==

= 1.2.2 =
* Bump minimum compatible version of WordPress to 4.6 (this will help with translations, and now is probably the easiest time to make the change)

= 1.2.1 =
* Add text domain for translators

= 1.2.0 =
* Add support for printing pages
* Fixed a bug where header tags don't appear when printing from Google Chrome
* Show categories, terms, and other custom taxonomies
* Add option to remove inline javascript from posts (defaults to remove them)
* Add option to slowdown post rendering (if it's too fast, images might not load)

= 1.1.6 =
* Improved layout of WP Video and (JetPack) Tiled Gallery shortcodes

= 1.1.5 =
* Move featured image and post excerpts into columns
* Improved image resizing by using inline styles instead of stylesheets
* Updated translated strings for image and text size option names
* Improved text resizing

= 1.1.4 =
* Replaced "Print Preview" with "View Printable Content"

= 1.1.3 =
* Improved compatibility with themes twentyeleven and twentyfourteen

= 1.1.2 = 
* Update minimum PHP version in readme.txt

= 1.1.1 =
* Fix image sizes
* Fix translation domains

= 1.1.0 =
* Added page setup options: columns, text size, page-break on new post, and image size

= 1.0.1 =
* Changes to readme

= 1.0.0 =
* Initial version.