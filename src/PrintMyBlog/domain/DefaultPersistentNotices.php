<?php

namespace PrintMyBlog\domain;

use WPTRT\AdminNotices\Notice;

class DefaultPersistentNotices
{
    /**
     * @return Notice[] Notice
     */
    public function getNotices()
    {
        return [

            new Notice(
                'pmb_pro_notice',
                __('You are using Print My Blog Free', 'print-my-blog'),
                '<p><a class="button button-primary" href="'
                . esc_attr(admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH))
                . '">'
                . esc_html__('Switch to the Pro Demo', 'print-my-blog')
                . '</a></p><p>'
                // phpcs:disable Generic.Files.LineLength.TooLong
                . __('Try all the pro features (table of contents, project organizer, custom post types) without signing up or installing anything. The files will just contain a watermark until purhcase.')
                // phpcs:enable Generic.Files.LineLength.TooLong
                . '</p>',
                $this->getOptionsForScreen('print-my-blog_page_print-my-blog-now')
            ),
            new Notice(
                'pmb_free_notice',
                __('You are using the Pro Demo', 'print-my-blog'),
                '<p><a class="button" href="'
                . esc_attr(admin_url(PMB_ADMIN_PAGE_PATH))
                . '">'
                . esc_html__('Switch to Free', 'print-my-blog')
                . '</a></p><p>'
                // phpcs:disable Generic.Files.LineLength.TooLong
                . __('It won‘t cost you anything and will help you print thousands of posts or pages at once. It just doesn‘t have quite as many other features.', 'print-my-blog')
                // phpcs:enable Generic.Files.LineLength.TooLong
                . '</p>',
                $this->getOptionsForScreen('toplevel_page_print-my-blog-projects')
            ),
            new Notice(
                'pmb_edit_content',
                __('How to Edit Project Content', 'print-my-blog'),
                // phpcs:disable Generic.Files.LineLength.TooLong
                '<div class="pmb-two-column-notice"><div class="pmb-image-column"><iframe style="width:100%" height="315" src="https://www.youtube.com/embed/wVVvmv2bqqk" 
        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>'
                . '<div class="pmb-text-column">'
                 . '<ol>'
                 . '<li>' . __('Find content from WordPress you want to add on the left, and add it to your project on the right by dragging', 'print-my-blog') . '</li>'
                 . '<li>' . __('Front matter, main matter, and back matter are mostly convenient for organizing your content, but your chosen design can style them differently') . '</li>'
                 . '<li>' . __('Nest content inside others to create parts', 'print-my-blog') . '</li>'
                // phpcs:enable Generic.Files.LineLength.TooLong
                 . '</ol>'
                 . '</div></div>',
                $this->getOptionsForProjectSubaction('content')
            ),

        ];
    }
    protected function getNoticeDefaultOptions()
    {
        return [
            'scope' => 'user',
            'type' => 'info',
            'capability' => 'read',
            ];
    }

    protected function getOptionsForScreen($screen_id)
    {
        $options = $this->getNoticeDefaultOptions();
        $options['screens'] = [$screen_id];
        return $options;
    }

    protected function getOptionsForProjectSubaction($subaction)
    {
        $options = $this->getOptionsForScreen(
            'toplevel_page_print-my-blog-projects'
        );
        $options['query_args'] = [
            'subaction' => $subaction
        ];
        return $options;
    }
}
