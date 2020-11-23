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
                '<a href="'
                . esc_attr(admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH))
                . '">'
                . esc_html__('Try the Pro Demo', 'print-my-blog')
                . '</a>',
                $this->getOptionsForScreen( 'print-my-blog_page_print-my-blog-now')
            ),
            new Notice(
                'pmb_free_notice',
                __('You are using the Pro Demo', 'print-my-blog'),
                '<a href="'
                . esc_attr(admin_url(PMB_ADMIN_PAGE_PATH))
                . '">'
                . esc_html__('Try the Free Tool', 'print-my-blog')
                . '</a>',
                $this->getOptionsForScreen( 'toplevel_page_print-my-blog-projects')
            ),
            new Notice(
                'pmb_edit_content',
                __('First Time Help', 'print-my-blog'),
                '<p>Here is how you use it...</p>',

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