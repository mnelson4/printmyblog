<h2><?php esc_html_e('Purpose', 'print-my-blog');?></h2>
<p>
    <?php esc_html_e('Designed to look good when viewed from a device or printing to paper. The two-column layout leads to more compact content, especially good if your content doesn’t require the full page width', 'print-my-blog');?><?php pmb_pro_print_service_best_e(__('Most browsers will only print a white background', 'print-my-blog'));?>
</p>

<h2><?php esc_html_e('Features', 'print-my-blog');?></h2>
<ul class="pmb-list">
    <li><?php esc_html_e('Main matter is divided into two columns, while front matter uses the full page width.', 'print-my-blog');?><?php pmb_pro_print_service_best_e(__('Some browsers don‘t support multiple columns', 'print-my-blog'));?></li>
    <li><?php esc_html_e('Content can be divided into parts with a special part opening area using the full page width.', 'print-my-blog');
    ?></li>

    <li><?php esc_html_e('The title page can include a title and preamble. Other content can immediately follow, without needing a page break.', 'print-my-blog');?></li>
    <li><?php esc_html_e('Each article includes just its title and content, and an optional dividing line to help distinguish between articles.',
            'print-my-blog');?></li>
    <li><?php esc_html_e('If your content is already divided into columns, optionally automatically remove them to improve the layout.', 'print-my-blog');?></li>
    <li><?php esc_html_e('Optionally begin each post on a new page, or be shown immediately following the previous one.', 'print-my-blog');?></li>
    <li><?php esc_html_e('Optionally have each article title take up the full page width, or show them inside columns.',
            'print-my-blog');?></li>
    <li><?php esc_html_e('Internal links (hyperlinks to content included in the project) are replaced with page references.',
            'print-my-blog');?><?php pmb_pro_print_service_only_e();?></li>
    <li><?php esc_html_e('External links (hyperlinks to content on the internet) are left as-is.', 'print-my-blog');
        ?></li>
    <li>
</ul>
<h2><?php esc_html_e('Page Layout', 'print-my-blog');?></h2>
<ul class="pmb-list">
    <li><?php esc_html_e('Most content is divided between two columns.', 'print-my-blog');?></li>
     <li><?php esc_html_e('Page numbers in bottom-right corner, and part titles in the top-left. Front-matter’s pages are numbered with Roman numerals', 'print-my-blog');?><?php pmb_pro_print_service_best_e(__('Most browsers add their own content to the page margins', 'print-my-blog'));?></li>
</ul>
<h2><?php esc_html_e('Special Instructions', 'print-my-blog');?></h2>
<p><?php esc_html_e('Front matter defaults to using the full page width. To force something to be divided into columns, add the CSS class "mayer-columns" inside your content.',
        'print-my-blog');?></p>
<p><?php esc_html_e('Main matter defaults to being divided into columns. To force something to use the full page width, add the CSS class "mayer-wide" inside the content.', 'print-my-blog');?></p>