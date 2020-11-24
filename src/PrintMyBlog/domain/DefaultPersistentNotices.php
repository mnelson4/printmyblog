<?php


namespace PrintMyBlog\domain;
use WPTRT\AdminNotices\Notice;

class DefaultPersistentNotices
{
    /**
     * @return array[] Notice
     */
    public function getNotices(){
        return [

            new Notice(
                'pmb_pro_notice',
                __('You are using Print My Blog Free', 'print-my-blog'),
                '<a class="button button-primary" href="'
                . esc_attr(admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH))
                . '">'
                . esc_html__('Try the Pro Demo', 'print-my-blog')
                . '</a>',
                $this->getOptionsForScreen( 'print-my-blog_page_print-my-blog-now')
            ),
            new Notice(
                'pmb_free_notice',
                __('You are using the Pro Demo', 'print-my-blog'),
                '<a class="button" href="'
                . esc_attr(admin_url(PMB_ADMIN_PAGE_PATH))
                . '">'
                . esc_html__('Try the Free Tool', 'print-my-blog')
                . '</a>',
                $this->getOptionsForScreen( 'toplevel_page_print-my-blog-projects')
            ),
            new Notice(
                'pmb_edit_content',
                __('How to Edit Project Content', 'print-my-blog'),
                 '<div class="pmb-two-column-notice"><div class="pmb-image-column"><iframe style="width:100%" height="315" src="https://www.youtube.com/embed/wVVvmv2bqqk" 
        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>'
                . '<div class="pmb-text-column">'
                 . '<ol>'
                 . '<li>' . __('Find content from WordPress you want to add on the left, and add it to your project on the right by dragging', 'print-my-blog') . '</li>'
                 . '<li>' . __('Front matter, main matter, and back matter are mostly convenient for organizing your content, but your chosen design can style them differently') . '</li>'
                 . '<li>' . __('Nest content inside others to create parts', 'print-my-blog') . '</li>'
                 . '</ol>'
                 . '</div></div>',

                $this->getOptionsForProjectSubaction('content')
            ),

        ];
    }
    protected function getNoticeDefaultOptions(){
        return [
            'scope' => 'user',
            'type' => 'info',
            'capability' => 'read',
            ];
    }

    protected function getOptionsForScreen($screen_id){
        $options = $this->getNoticeDefaultOptions();
        $options['screens'] = [$screen_id];
        return $options;
    }

    protected function getOptionsForProjectSubaction($subaction){
        $options = $this->getOptionsForScreen(
            'toplevel_page_print-my-blog-projects'
        );
        $options['query_args'] = [
            'subaction' => $subaction
        ];
        return $options;
    }
}