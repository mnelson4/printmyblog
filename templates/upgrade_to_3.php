<div class="wrap nosubsub">
    <p class="pmb-center"><?php esc_html_e('It\'s finally here...', 'print-my-blog');?></p>
    <h1 class="pmb-big-header">🎉<?php esc_html_e( 'Print My Blog – Pro Print', 'print-my-blog' ); ?>🎉</h1>
    <p class="pmb-center"><?php esc_html_e('The old features are still here: "Print Now" was just renamed "Quick Print", and the Print Buttons are still available under "Settings".', 'print-my-blog');?></p>
    <p class="pmb-center"><?php esc_html_e('Pro Print makes more functional and visually-appealing PDFs using your web browser for free, or the new paid Pro Print Service.', 'print-my-blog');?></p>

        <?php
            $features = [
                    [
                            __('Automatic Table of Contents', 'print-my-blog'),
                            __('Add a table of contents to your project, with links and page numbers.', 'print-my-blog') . pmb_pro_print_service_best(__('Pro Print Service adds page numbers.', 'print-my-blog'))
                    ],
                    [
                        __('Automatic page references and footnotes', 'print-my-blog'),
                        __('Hyperlinks can be automatically converted into references to the page the linked-to content is located, or a footnote with the web address print-out.', 'print-my-blog') . pmb_pro_print_service_only(),
                    ],
                    [
                        __('Proper margins, page numbers and running heads', 'print-my-blog'),
                        __('Clean margins, alternating page numbers, roman numerals for front matter, running chapter heads, and more.', 'print-my-blog') . pmb_pro_print_service_best()
                    ],
                    [
                        __('Support Custom Post Types', 'print-my-blog'),
                        __('Content from other plugins, like WooCommerce Products, or your LMS\'s course materials, can be included in projects.', 'print-my-blog')
                    ],
                    [
                        __('Drag-and-Drop Content Organizer', 'print-my-blog'),
                        __('Drop posts, pages, and other post types into projects in any order. Even organize them into parts.', 'print-my-blog')
                    ],
                    [
                        __('Premade, Reusable, Customizable Designs', 'print-my-blog'),
                        __('Print My Blog\'s Designs are like WordPress Themes: each has its own look and customization options. Reuse the same design across projects. Or even create your own design using HTML, CSS, and Javascript', 'print-my-blog') . pmb_pro_print_service_best(__('Pro Print Service lets designs fully control the margins, page backgrounds, and los of other aspects.', 'print-my-blog'))
                    ],
                    [
                            __('Saveable Projects', 'print-my-blog'),
                            __('Save where you left off.', 'print-my-blog')
                    ],
                    [
                            __('Front matter, back matter, parts, and print materials', 'print-my-blog'),
                        __('Add front and back matter to books, organize the content into parts, and add special "print material" posts to use only in your projects', 'print-my-blog')
                    ]
            ];
        /**
         * @var $design_managers \PrintMyBlog\orm\managers\DesignManager
         */
            $design_managers = \PrintMyBlog\system\Context::instance()->reuse('PrintMyBlog\orm\managers\DesignManager');
            $print_classic = $design_managers->getBySlug('classic_print');
            $buurma = $design_managers->getBySlug('buurma');
            $mayer = $design_managers->getBySlug('mayer');
            $designs = [$print_classic, $buurma, $mayer];
            ?>
    <div class="pmb-content-boxes">
        <?php
            foreach($designs as $design){
                ?>
                <div class="pmb-content-box-wrap">
                    <div class="pmb-content-box-inner">
                        <?php
                        echo pmb_design_preview($design);
                        ?>
                    </div>
                </div>
                <?php
            }
        ?>
    </div>

    <div>
        <div class="pmb-constrained-content">
            <h1><?php esc_html_e('Features Include:', 'print-my-blog');?></h1>
                <?php foreach($features as $feature_data){
                    ?>
                    <div class="pmb-row">
                        <h2><?php echo $feature_data[0];?></h2>
                        <p><?php echo $feature_data[1];?></p>
                    </div>
                <?php
                }
                ?>

            <div class="pmb-party pmb-center" style="padding:2em; margin-bottom:2em;">
                <h2><?php printf(__('Use promocode "%s" for 50%% off any lifetime license until June 18th', 'print-my-blog'), 'release');?></h2>
                <a class="button" href="<?php echo esc_attr(pmb_fs()->get_upgrade_url());?>"><?php esc_html_e('Buy Now', 'print-my-blog');?></a>
            </div>
        </div>
    </div>

    <div class="pmb-center">
    <a href="<?php echo admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH);?>" class="button button-primary"><?php esc_html_e('Try Pro Print Now', 'print-my-blog');?></a>
    <a href="https://printmy.blog" target="_blank" class="button"><?php esc_html_e('Learn More on printmy.blog', 'print-my-blog');?></a>
    </div>
    <div>
        <div class="pmb-constrained-content">
            <p><?php printf(__('Please %1$slet me know%2$s if you have any questions!', 'print-my-blog'), '<a href="htts://printmy.blog/contact" target="_blank">', '</a>');?></p>
            <p><?php esc_html_e('Best wishes, Mike Nelson, Print My Blog Developer', 'print-my-blog');?> 🧡</p>
        </div>
    </div>

</div>
