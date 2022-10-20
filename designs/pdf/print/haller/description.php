<h2><?php esc_html_e('Purpose', 'print-my-blog'); ?></h2>
<p>
    <?php esc_html_e('Multi-column newspaper design designed for printing. Wide images can span multiple columns and always snap to the nearest page edge to make a visually-appealing layout.', 'print-my-blog'); ?>
</p>

<h2><?php esc_html_e('Features', 'print-my-blog'); ?></h2>
<ul class="pmb-list">
    <li><?php esc_html_e('The "Title page" is actually a special header on the front page containing your publication\'s title, issue number, date, and other optional prominent info (set on the design and project meta settings).', 'print-my-blog'); ?></li>
    <li><?php esc_html_e('Content is divided into two, three, or four columns.', 'print-my-blog'); ?><?php pmb_pro_print_service_best_e(__('Some browsers don‘t support multiple columns', 'print-my-blog')); ?></li>
    <li><?php esc_html_e('The top margin contains the front page (publication title, issue number, date, etc.).', 'print-my-blog');?> <?php pmb_pro_print_service_best_e(__('Most browsers add their own content to the page margins', 'print-my-blog')); ?></li>
    <li><?php esc_html_e('Designed for duplex printing with the left page mirroring the right page\'s margins.', 'print-my-blog');?></li>
    <li><?php esc_html_e('Content can be divided into parts. Part openings and important articles\' titles and cover images take up the full page width, whereas regular articles compactly fit into columns.', 'print-my-blog'); ?></li>

    <li><?php
        esc_html_e(
            'Each article may include post title, ID, author, published date, categories and tags, URL, featured image, excerpt, custom fields, and content.',
            'print-my-blog'
        );
        ?></li>
    <li><?php esc_html_e('If your content is already divided into columns, optionally automatically remove them to improve the layout.', 'print-my-blog'); ?></li>
    <li><?php esc_html_e('External hyperlinks (links to web pages not in the project) can be automatically converted to footnotes or removed', 'print-my-blog'); ?><?php pmb_pro_print_service_best_e(__('Footnotes require Pro', 'print-my-blog')); ?></li>
    <li><?php esc_html_e('Internal hyperlinks (links to content included in the project) can be automatically converted to footnotes, inline page references, or removed', 'print-my-blog'); ?><?php pmb_pro_print_service_best_e(__('Footnotes and page references require Pro PDF Service', 'print-my-blog')); ?></li>

</ul>
<h2><?php esc_html_e('Page Layout', 'print-my-blog'); ?></h2>
<ul class="pmb-list">
    <li><?php esc_html_e('Issue and page number are in the outside top margin, the publication\'s title is in the middle top margin, and the publication date is in the inside top margin. The optional publication subtitle is underneath the other top-margin content.', 'print-my-blog'); ?><?php pmb_pro_print_service_best_e(__('Most browsers add their own content to the page margins', 'print-my-blog')); ?></li>
</ul>
<h3><?php esc_html_e('Section Templates', 'print-my-blog');?></h3>
<p><?php esc_html_e('Choose different styles for articles included in your project:', 'print-my-blog'); ?></p>
<ul class="pmb-list">
    <li><?php esc_html_e('Default Template: shows all content specified by the "Post Content" design setting (e.g. the article\'s title and featured image) in columns.', 'print-my-blog');?></li>
    <li><?php esc_html_e('Fullpage Content: contains only the article\'s content (no title, featured image, etc.) all in a single, wide column.', 'print-my-blog');?></li>
    <li><?php esc_html_e('Single Column: shows all content specified by the "Post Content" design setting in a single column.', 'print-my-blog');?></li>
    <li><?php esc_html_e('Important: shows the article\'s featured image and title across all columns, but other content is shown in multiple columns.', 'print-my-blog');?></li>
</ul>
<p><?php esc_html_e('Parts start on a new page and include the part’s title, featured image, and content.', 'print-my-blog'); ?></p>
<h2><?php esc_html_e('Special Instructions', 'print-my-blog'); ?></h2>
<p><?php esc_html_e('Note that in order to improve page layout, all images and figures snap to the nearest page edge.', 'print-my-blog'); ?></p>
<p><?php esc_html_e('"Full width" images take up all columns and snap to the nearest page edge.','print-my-blog'); ?></p>
<p><?php esc_html_e('"Wide width" images take up 2 columns and snap to the nearest page edge.','print-my-blog'); ?> <?php pmb_pro_print_service_only_e(__('Only works with PMB’s Pro PDF Service.', 'print-my-blog')); ?></p>