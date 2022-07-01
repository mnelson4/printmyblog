<h2><?php esc_html_e('Purpose', 'print-my-blog'); ?></h2>
<p><?php esc_html_e('Similar to Print My Blog Quick Print\'s other "Classic" designs, except optimized for reading by Microsoft Word. Useful when you need to deliver your content as a Microsoft Word Document (like to an editor), or for using as an intermediate format for delivery to other applications (like for importing into Adobe InDesign).', 'print-my-blog'); ?></p>
<p><?php esc_html_e('A table of contents is generated and inserted at the requested location. Once in Microsoft Word, right click on the table of contents and click "Update Field"; or press Ctrl + A, then F9.', 'print-my-blog'); ?></p>
<p><?php esc_html_e('Images may be bundled into the Word Document file so they can be used offline or if the website hosting the images goes offline.', 'print-my-blog'); ?></p>
<p><?php esc_html_e('Image galleries are rearranged into a simple list of images which displays better on smaller devices.', 'print-my-blog'); ?></p>
<p><?php
    printf(
    // translators: 1: opening HTML tag, 2: closing HTML tag
        esc_html__('If there are any features or options you want, %1$splease get in touch.%2$s', 'print-my-blog'),
        '<a href="' . esc_url_raw(admin_url(PMB_ADMIN_HELP_PAGE_PATH)) . '">',
        '</a>'
    );
    ?></p>

<h2><?php esc_html_e('Features', 'print-my-blog'); ?></h2>
<ul class="pmb-list">
    <li><?php esc_html_e('Supports nesting articles (like posts and pages) into “parts”', 'print-my-blog'); ?></li>
    <li><?php esc_html_e('External hyperlinks (links to web pages not in the project) may be left as working hyperlinks or removed', 'print-my-blog'); ?></li>
    <li><?php esc_html_e('Internal hyperlinks (links to content included in the project) may be converted into links to the appropriate page of the document, left as hyperlinks to the website, or removed.', 'print-my-blog'); ?>
    </li>
    <li><?php esc_html_e('The default title page can include: Project Title, Subtitle, Site URL, Date Printed, Credit to Print My Blog', 'print-my-blog'); ?>
    </li>
    <li><?php esc_html_e('Each article can include: Title, ID, Author, URL, Date Published, Categories and Tags, Featured Image, Excerpt, Custom Fields, Content', 'print-my-blog'); ?>
    </li>
    <li><?php esc_html_e('All images are automatically included in the Word Document file (instead of merely pointing to the external image source). This makes the filesize larger, but allows users to view images while disconnected from the Internet.', 'print-my-blog'); ?>
    </li>
    <li><?php esc_html_e('Small images are automatically centered', 'print-my-blog'); ?></li>
    <li><?php esc_html_e('Image galleries are converted into regular list of images, which look better on small screens.', 'print-my-blog'); ?></li>
</ul>

<h2><?php esc_html_e('Page Layout', 'print-my-blog'); ?></h2>
<p><?php esc_html_e('Each article begins on a new page.', 'print-my-blog'); ?></p>
