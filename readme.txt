=== Print My Blog ===
Contributors: mnelson4
Tags: print, PDF, backup, eBook, ePub, MOBI
Requires at least: 4.6
Stable tag: trunk
Tested up to: 5.2
Requires PHP: 5.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://opencollective.com/print-my-blog


Print your blog to paper, PDF, or eBook in one click!

== Description ==

**Print your blog to paper, PDF, or eBook in one click!**

* **Print your blog** to read offline.
* **Create a paper backup** (book or printout) to read when your blog is taken offline.
* **Create a PDF, ePub, or MOBI file** as a human-readable, portable backup. Send it to friends, store it in the cloud or a hard drive, or even archive it with a historical organization.

[](http://coderisk.com/wp/plugin/print-my-blog/RIPS-Bs3Z7ec5hQ)

**Give your story life outside your blog!**

= No Upsells, Instead Sponsor Our Non-Profit =

If you like this plugin as much as paid software, you can sponsor our registered non-profit open collective. You’ll

* get a tax receipt (for claiming business expenses)
* be recognized for your contribution, and
* reimburse contributors for their time spent (not just the original plugin author).

[Learn more about how our open collective works, what your recommended donation is, and how it will help.](https://opencollective.com/print-my-blog)

= Watch the 2 Minute Demo =

https://youtu.be/shOjx-Ijung


= Features =

* loads all your blog’s posts into a single web page so you can print them from your web browser (to paper, PDF, ePub, or anything your web browser supports)
* supports printing thousands of blog posts in one click (the record is over 3000 posts)
* prints posts and pages
* does not print ink-guzzlers like site logo, sidebar widgets, or footer
* avoids page breaks inside images, between images and captions, and even right after headers; generally makes the content print-ready
* uses your theme’s and plugins’ styles (so Gutenberg and page builders are supported)
* growing support for plugin and theme shortcodes and Gutenberg blocks
* print your entire blog, or filter by date, category, and tags
* optionally include private and password-protected posts
* optionally prints comments
* optionally places each post on a new page
* resize text
* resize images or remove them altogether
* optionally removes hyperlinks
* optionally includes post’s excerpt
* place the “Print My Blog” Gutenberg block on a page and allow site visitors to print your blog too
* no watermark in print-out, and attribution optional,
* no upsells, advertising, or data collection (we can't even know which sites use this software)
* free, open-source software, so you can use it for whatever you like without fear of changing terms of use,
customize it to fit your needs (although we'd curious to hear what you've done with it), and even redistribute it. There is no lengthy legal document describing how you're giving up your rights by using this software!

Want more? [Tell us what matters to you on GitHub](https://github.com/mnelson4/printmyblog/issues).

= Example Use-Cases =

**Time to prune your website’s content?** You can print it, annotate and sort through a physical stack of paper.

**Disconnecting offline for a bit?** Print your blog to paper, or even an ePub file to read from your Kindle or phone.

**Shutting down your site?** In addition to making a regular backup (which can only be read by recreating your entire site, which may be difficult as the software it requires gets more dated), make a PDF backup of it.

**Wonder what will happen to your blog’s stories after your gone?** Your blog might not be online forever, but you can use Print My Blog to distribute readable copies of it to those you care about.

Check out some of the reviews to see pretty exciting uses for Print My Blog.

= Our Mission =

Besides just converting your blog to a different format, the plugin’s mission is to preserve your blog for decades to come in a low-tech format.
Your website might not be around forever, nor might WordPress. But by printing it, there’s hope your blog’s ideas and stories can live on.

= Alternatives to Print My Blog =

If this doesn't meet your needs, there are good paid and free alternatives.

[Dead Easy Family History](https://deadeasyfamilyhistory.org/print-my-blog) runs a hosted version of this same plugin, so you can print your blog without even installing this. Especially useful for WordPress**.com** users, or those who can't install the plugin on their site.
(Its free, but won't use your blog's styles).
[Anthologize](https://wordpress.org/plugins/anthologize/) is another great plugin for customizing your blog's content before exporting to an e-book format. Also free.
[bloxp](http://www.bloxp.com/) converts your blog into an e-book with any type of blog (not just WordPress). Fewer, but different, options. Supported by donations.
[blogbooker](https://blogbooker.com/) prints a book, or creates a PDF, from your blog using their pre-made styles. Paid service.
[blog2print](https://www.blog2print.com/) ditto, but temporarily requests your username and password. Paid service.

= Contributing =

If you find it useful, please:

* [make the recommended donation to the non-profit](https://opencollective.com/print-my-blog)
* [give it a 5 star review]((https://wordpress.org/support/plugin/print-my-blog/reviews/#new-post))
* [translate it into your language using WordPress' GlotPress]((https://translate.wordpress.org/projects/wp-plugins/print-my-blog))
* report bugs and suggest features on [GitHub](https://github.com/mnelson4/printmyblog) or [WordPress Support Forum]((https://wordpress.org/support/plugin/print-my-blog))

[Translators and code contributors can be reimbursed for their time](https://opencollective.com/print-my-blog/expenses/new).

[Read plugin updates and see how donations are being used on our non-profit Open Collective.](https://opencollective.com/print-my-blog)

Best Wishes Preserving Your Blog!

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/print-my-blog` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Go to Tools->Print My Blog
1. Press "Prepare Print Page". Your blog's content will be loaded into the following page.
1. When you see the "Print" button appear, click it. Your blog will be printed from your browser.

== Frequently Asked Questions ==

= How do I create a PDF using Print My Blog? =
This plugin just puts the content onto a single web page so your browser can print it or create a PDF from it. Because your browser is what creates the PDF, it depends on the browser. Check out the instructions that apply to you:

Google Chrome

https://youtu.be/ngId20fP5I8

Mozilla Firefox

https://youtu.be/ekwg2a1FPV0

Safari: it is not recommended to create a PDF from mobile Safari. The PDF won't have pages, and includes content intended for web-only.

= How do I create an eBook using Print My Blog? =
This plugin puts your blog's content onto a single web page, and you can then use the [dotEPUB browser extension](https:dotepub.com) to turn it into an eBook.

https://youtu.be/M5ZZN3KsqOA

= Some images aren't appearing =

Print My Blog can work too fast for some webservers, meaning the webservers refuse to load all the content, most noticeably some images.
In order to fix this, you need to tell Print My Blog to slow down. On the configuration page, show options, then scroll
down to show Troubleshooting options, and increase "Post Rendering Wait-Time" (eg to 2000 milliseconds, or whatever
value you need to in order to get all the images to load).

= The Print Page is stuck on "Loading Content" and I can't filter by categories or terms =

You may have disabled the WP REST API. (Eg by using "All in One WP Security" plugin's "Disallow Unauthorized REST API Requests" or "Disable REST API" plugin). Print My Blog uses the WP REST API to load your posts into the print-page, so please ensure the WP REST API is enabled.

= How do I remove post content from the printout? =

There may be text, images, and other stuff that looks great when viewed from a screen, but doesn't make sense to have in the printout. Eg, related posts, share buttons, etc.
To remove them from printouts, [add the CSS class](https://technicalsupport.blog/2019/01/04/how-to-add-a-custom-css-class-to-gutenberg-blocks/) "pmb-screen-only".

Also, if it's content automatically added by a plugin or theme, please let us know in the support forum, and we can hide that content in the next release of Print My Blog.

https://www.youtube.com/watch?v=IMBxCp7AVPE&feature=youtu.be

= How do I make post content only appear in the printout? =

[Add the CSS class](https://technicalsupport.blog/2019/01/04/how-to-add-a-custom-css-class-to-gutenberg-blocks/) "pmb-print-only". See the above question for more details.

= How do I show or hide background colors in the printout? =

Your theme may use different styles when printed, including setting the background to white in order to conserve ink.

But if not, your browser can also remove background colors.

* For Google Chrome: when printing, [click on "More Options", then uncheck "Background graphics"](https://drive.google.com/file/d/1V5c9XJbBGhSeRlmGxqZBRv5n4CTWZyag/view?usp=sharing)

* For Firefox: when viewing the print preview, [click "Page Setup" then uncheck "Print Background (colors & images)".](https://drive.google.com/open?id=1VoVCC6ehqd8vZyO89PQq5hZbQ4u94vBK)

* For Firefox Extension "Print to PDF": [click on "Add-ons", then click "Print to PDF", then "Options", and uncheck "Whether the background colors should be shown"](https://drive.google.com/open?id=1WjCS84LLOL6v0ccjASjJ3c_vaUmgIfTO)

== Screenshots ==

1. Print Setup Page
2. Printing Page
3. The Blog is Ready for Printing!

== Changelog ==

= 1.11.1 August 15 2019 =
* Fixed incorrect translation domain

= 1.11.0 August 15 2019 =
* Add option to exclude private and password-protected posts
* Fix datepicker pop-ups not appearing correctly on HTTPS sites

= 1.10.0 August 9 2019 =
* Add better support for creating ePub and MOBI eBooks using dotEPUB
* Print setup page now asks for desired format, then tailors the print page for it

= 1.9.6 July 30 2019 =
* Add "site" CSS class to top-level div on print page, as styles may use it for styling

= 1.9.5  July 24 2019 =
* Add a suggestion to use the browser's print preview

= 1.9.4  July 18 2019 =
* Allow thumbnail and medium images to be inline

= 1.9.3 July 9 2019 =
* Add a link to explain how to print to PDF
* Add link to cancel printout

= 1.9.2 July 5 2019 =
* Fix some strings which weren't translatable

= 1.9.1 June 26 2019 =
* Fix Print My Blog WordPress News posts not dismissable

= 1.9.0 June 26 2019 =
* Add Print My Blog to WordPress News dashboard widget (and make it dismissable)

= 1.8.0 June 14 2019 =
* Allow filtering posts by date
* Print meta line now mentions the post type, categories, and dates used in filtering

= 1.7.1 May 29 2019 =
* Include private and future posts in printouts for privileged users

= 1.7.0 May 21 2019 =
* Hide Adcontrol and Sharedaddy content in printouts.
* Add CSS classes to facilitate only showing content when on a screen ("pmb-screen-only"), or only showing in printouts ("pmb-print-only").
* Put post date, url, categories, etc, onto different lines by default (add custom CSS `.pmb-post-meta{display:inline;}` to instead show them inline).

= 1.6.8 April 30 2019 =
* Escape site parameter when printing it out.

= 1.6.7 April 26 2019 =
* Fixed a bug with WP REST Proxy while trying to sanitize the site parameter.

= 1.6.6 April 26 2019 =
* Fixed category and taxonomy selectors so they don't interfere with each other.
* If REST Proxy plugin isn't active, ignore thee site parameter. Props to Magnus Klaaborg Stubman for finding it and practicing responsible disclosure (see http://dumpco.re/bugs/wp-plugin-print-my-blog-ssrf).
* Add wp_footer() in the print-page footer.
* Do a better job of handling polluted AJAX responses.

= 1.6.5 April 8 2019 =
* For logged-in users, try to show protected and private posts content.
* Allow users who can read "private posts" to use Print My Blog from the admin.

= 1.6.4 April 1 2019 =
* Fixed a bug that made WP REST API Proxy integration only work when logged in.

= 1.6.3 March 29 2019 =
* Fixed a 1.6.0 bug that made this not work for wordpress.com sites.

= 1.6.2 March 20 2019 =
* Fixed a new bug (introduced in 1.6.0) that prevented WP REST Proxy from working correctly.

= 1.6.1 March 20 2019 =
* Removed some PHP7-only code.

= 1.6.0 March 20 2019 =
* Add filtering by post taxonomies (categories, terms, and custom taxonomies).
* Foo Gallery support.
* Handle polluted JSON responses.

= 1.5.0 Feb 27 2019 =
* Allow adding or removing any part of post content.
* Optionally add a divider.
* Enfold theme compatibility.

= 1.4.0 Feb 20 2019 =
* Allow printing comments.
* Tweaked "What do you think?" text.

= 1.3.5 Feb 12 2019 =
* Make showing printout meta info (blog's URL, date of printing, and that it was done with this plugin) optional.
* Allow removing hyperlinks from content.

= 1.3.4 Feb 8 2019 =
* Add links to support, review, and sponsor.

= 1.3.3 Feb 3 2019 =
* Fix fatal error when function "register_block_type" isn't defined.

= 1.3.2 Jan 31 2019 =
* Remove ellipsis from "Initializing...".
* Added assets/styles/plugin-compatibility.css for CSS that is for compatibility with specific plugins (so far that's [Yuzo Related Posts](
https://wordpress.org/plugins/yuzo-related-post/) and [I Recommend This](https://wordpress.org/plugins/i-recommend-this/)).

= 1.3.1 Jan 30 2019 =
* Move pretend page down a bit so we dont hide the page title.
* Remove ellipsis because they look ugly in RTL languages.

= 1.3.0 Jan 30 2019 =
* Improved print page to look more like a print preview.
* Use submit inputs instead of buttons because themes generally style them better.
* Add link to make donations.

= 1.2.4 Jan 15 2019 =
* Add Gutenberg Block so site visitors can print the blog.
* Increased post rendering from 500ms to 200ms per post.
* Moved WP REST Proxy area outside of advanced area.

= 1.2.3 Jan 7 2019 =
* Fixed a bug from 1.2.0 that caused text resizing to not load.
* Fixed some featured images not loading because of unusual REST API response.
* Fixed JetPack's Tiled Galleries by enqueuing its stylesheet.

= 1.2.2 Jan 1 2019 =
* Bump minimum compatible version of WordPress to 4.6 (this will help with translations, and now is probably the easiest time to make the change).

= 1.2.1 Jan 1 2019 =
* Add text domain for translators.

= 1.2.0 Jan 1 2019 =
* Add support for printing pages.
* Fixed a bug where header tags don't appear when printing from Google Chrome.
* Show categories, terms, and other custom taxonomies.
* Add option to remove inline javascript from posts (defaults to remove them).
* Add option to slowdown post rendering (if it's too fast, images might not load).

= 1.1.6 Dec 17 2018 =
* Improved layout of WP Video and (JetPack) Tiled Gallery shortcodes.

= 1.1.5 Dec 17 2018 =
* Move featured image and post excerpts into columns.
* Improved image resizing by using inline styles instead of stylesheets.
* Updated translated strings for image and text size option names.
* Improved text resizing.

= 1.1.4 Dec 8 2018 =
* Replaced "Print Preview" with "View Printable Content".

= 1.1.3 Dec 8 2018 =
* Improved compatibility with themes twentyeleven and twentyfourteen.

= 1.1.2 Nov 5 2018 =
* Update minimum PHP version in readme.txt.

= 1.1.1 Nov 2 2018 =
* Fix image sizes.
* Fix translation domains.

= 1.1.0 Nov 2 2018 =
* Added page setup options: columns, text size, page-break on new post, and image size.

= 1.0.1 Nov 1 2018 =
* Changes to readme.

= 1.0.0 Nov 1 2018 =
* Initial version.