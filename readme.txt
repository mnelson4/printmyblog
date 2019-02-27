=== Print My Blog ===
Contributors: mnelson4
Tags: print, pdf, backup
Requires at least: 4.6
Stable tag: trunk
Tested up to: 5.1
Requires PHP: 5.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://opencollective.com/print-my-blog


Create a paper/PDF copy of your entire blog in one click!

== Description ==

Print your  blog in 1 click! Use your own web browser to create a paper, PDF, or even e-book copy.
A paper copy may  be readable in 100 years- long after your website is taken down and your digital backups are corrupted.
"Print My Blog" makes this really easy: no need to print your blog posts one-by-one, or print unnecessary stuff like your sidebar widgets or footer.
One click printing of your entire blog's contents in a format optimized for print.
Plugin alternative to [blog2print](https://www.blog2print.com) and [blogbooker](https://blogbooker.com/), except you can print with your own printer or even your browser's print-to-pdf feature.

No upsells, no premium version.

= Why would I want to print my entire blog? =

Glad you asked:

* backup your memories in a format that has no technological dependencies: paper! (Or other portable formats like PDF or ePub.)
* easily share an entire blog offline
* create a book from your blog (although [Anthologize](https://wordpress.org/plugins/anthologize/) might be a better option for that)
* when shutting down a blog, convert its content to an easy-to-read format

= What Does It Do? =
Watch this 2 minute video.

https://youtu.be/shOjx-Ijung

It works with thousands of blog posts (or pages), with Gutenberg, and page builders.
Only your posts’ content is printed, not your logo, site title, sidebar widgets, footer, etc. Just the stuff you care to read.

There is also a Print My Blog block which you can place on a page which will allow your site visitors to print the entire blog too.\

Features:

* loads all your blog's posts into a single page so you can print them from your web browser
* supports printing thousands of blog posts in one click (the record is over 3000 posts)
* print posts and pages
* does not print ink-guzzlers like site logo, sidebar widgets, or footer
* uses your theme's and plugins' styles (so Gutenberg and page builders are supported)
* optionally print comments
* optionally place each post on a new page
* resize text
* resize images or remove them altogether
* no watermark in print-out (it does say you used this plugin, but that can be removed)
* remove hyperlinks
* optionally include post's excerpt
* place the "Print My Blog" Gutenberg block on a page and allow site visitors to print your blog

Please see the [GitHub issue tracker](https://github.com/mnelson4/printmyblog/issues?q=is%3Aissue+is%3Aopen+sort%3Aupdated-desc) for other features requested.

= How Does It Work? =

After activating the plugin and clicking "Prepare Print Page"
1. your blog's posts are fetched in a way that thousands can be put on the page at once
(using the WP REST API)
2. a few instructions are given to the browser on how to print the page nicely (using CSS)
3. your web browser takes care of printing.

It turns out web browsers are pretty good at printing to paper. They're also good at saving the web page to a PDF file (eg Google Chrome)
 and even creating e-books (they may an add-on for this.)

= But You’re Destroying Trees! =
Yes it can be a lot of paper. But

* it might not be that much compared to how much paper you've used on books, cereal boxes, and package deliveries (and it's recyclable anyway)
* there are paper-saving options, like using small text, multiple columns, and smaller images (or no images at all)
* lastly, you don’t need to actually print to paper. Google Chrome and other browsers allow you to instead print to PDF.

== What's the Big Deal? ==

The mission is: preserve your blog for decades to come in a low-tech format.

Your website might not be around forever, nor might WordPress. But by printing it, there’s hope your blog’s ideas and stories can live on.

== Contribute ==

Here’s how you can help this plugin to continue to exist and improve:

* [give it a good review](https://wordpress.org/support/plugin/print-my-blog/reviews/#new-post) and tell your friends
* give feedback on [GitHub](https://github.com/mnelson4/printmyblog) or [WordPress support forum](https://wordpress.org/support/plugin/print-my-blog) about what features you want or bugs you find
* help translate. Make this accessible to users everywhere using [WordPress' Glotpress](https://translate.wordpress.org/projects/wp-plugins/print-my-blog)
* help help. Answer other users’ questions in the [support section](https://wordpress.org/support/plugin/print-my-blog).
* fund development. [Make an optional donation](https://opencollective.com/print-my-blog) to support ongoing development.

Please join our [Open Collective](https://opencollective.com/print-my-blog) to watch and contribute to the plugin’s maintenance and development.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/print-my-blog` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Go to Tools->Print To Blog
1. Press "Prepare Print Page". Your blog's content will be loaded into the following page.
1. When you see the "Print" button appear, click it. Your blog will be printed from your browser.



== Troubleshooting ==

* the WP API (or REST API) needs to not be deactivated
* caching plugins should probably be deactivated before use
* recommended web browsers: Firefox, Google Chrome.  

== Screenshots ==

1. Print Setup Page
2. Printing Page
3. The Blog is Ready for Printing!

== Changelog ==

= 1.5.0 Feb 27 2019=
* Allow adding or removing any part of post content
* Optionally add a divider
* Enfold theme compatibility

= 1.4.0 Feb 20 2019=
* Allow printing comments
* Tweaked "What do you think?" text

= 1.3.5 Feb 12 2019=
* Make showing printout meta info (blog's URL, date of printing, and that it was done with this plugin) optional
* Allow removing hyperlinks from content.

= 1.3.4 Feb 8 2019=
* Add links to support, review, and sponsor.

= 1.3.3 Feb 3 2019=
* Fix fatal error when function "register_block_type" isn't defined

= 1.3.2 Jan 31 2019=
* Remove ellipsis from "Initializing..."
* Added assets/styles/plugin-compatibility.css for CSS that is for compatibility with specific plugins (so far that's [Yuzo Related Posts](
https://wordpress.org/plugins/yuzo-related-post/) and [I Recommend This](https://wordpress.org/plugins/i-recommend-this/))

= 1.3.1 Jan 30 2019=
* Move pretend page down a bit so we dont hide the page title
* Remove ellipsis because they look ugly in RTL languages

= 1.3.0 Jan 30 2019=
* Improved print page to look more like a print preview
* Use submit inputs instead of buttons because themes generally style them better
* Add link to make donations

= 1.2.4 Jan 15 2019=
* Add Gutenberg Block so site visitors can print the blog
* Increased post rendering from 500ms to 200ms per post
* Moved WP REST Proxy area outside of advanced area

= 1.2.3 Jan 7 2019=
* Fixed a bug from 1.2.0 that caused text resizing to not load
* Fixed some featured images not loading because of unusual REST API response
* Fixed JetPack's Tiled Galleries by enqueuing its stylesheet

= 1.2.2 Jan 1 2019=
* Bump minimum compatible version of WordPress to 4.6 (this will help with translations, and now is probably the easiest time to make the change)

= 1.2.1 Jan 1 2019=
* Add text domain for translators

= 1.2.0 Jan 1 2019=
* Add support for printing pages
* Fixed a bug where header tags don't appear when printing from Google Chrome
* Show categories, terms, and other custom taxonomies
* Add option to remove inline javascript from posts (defaults to remove them)
* Add option to slowdown post rendering (if it's too fast, images might not load)

= 1.1.6 Dec 17 2018=
* Improved layout of WP Video and (JetPack) Tiled Gallery shortcodes

= 1.1.5 Dec 17 2018=
* Move featured image and post excerpts into columns
* Improved image resizing by using inline styles instead of stylesheets
* Updated translated strings for image and text size option names
* Improved text resizing

= 1.1.4 Dec 8 2018=
* Replaced "Print Preview" with "View Printable Content"

= 1.1.3 Dec 8 2018=
* Improved compatibility with themes twentyeleven and twentyfourteen

= 1.1.2 Nov 5 2018=
* Update minimum PHP version in readme.txt

= 1.1.1 Nov 2 2018=
* Fix image sizes
* Fix translation domains

= 1.1.0 Nov 2 2018=
* Added page setup options: columns, text size, page-break on new post, and image size

= 1.0.1 Nov 1 2018=
* Changes to readme

= 1.0.0 Nov 1 2018=
* Initial version.