<?php

namespace PrintMyBlog\domain;

use mnelson4\AdminNotices\Notice;

/**
 * Class DefaultPersistentNotices
 * @package PrintMyBlog\domain
 */
class DefaultPersistentNotices
{
    /**
     * @return Notice[] Notice
     */
    public function getNotices()
    {
        // don't show any of these notices on the welcome page, please. Give them a moment.
        // This is just deciding whether to hide notifications on the welcome page. Nonce is overkill.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['welcome']) || isset($_GET['upgrade_to_3'])) {
            return [];
        }
        return [

            new Notice(
                'pmb_pro_notice',
                __('About Quick Print', 'print-my-blog'),
                '<p>'
                . sprintf(
                    // translators: 1: html tag, 2: html tag, 3: html tag
                    __('This is the quick-and-easy option, best for making printouts for your own records. For something more professional and full-featured, use %1$sPro Print%2$s. It has free and paid options. %3$sSee full feature comparison.%2$s', 'print-my-blog'),
                    '<a href="'
                    . esc_attr(admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH))
                    . '">',
                    '</a>',
                    '<a href="https://printmy.blog/free-vs-pro/" target="_blank">'
                )
                . '</p>',
                $this->getOptionsForScreen('print-my-blog_page_print-my-blog-now')
            ),
            new Notice(
                'pmb_free_notice',
                __('About Pro Print', 'print-my-blog'),
                '<p>'
                . sprintf(
                    // translators: 1: html tag, 2: html tag
                    __('Pro Print is the best way to make professional-quality documents. You will be able to print them for free using your browser, or upgrade to use Print My Blog Pro\'s full features. %1$sSee full feature comparison.%2$s', 'print-my-blog'),
                    '<a href="https://printmy.blog/free-vs-pro/" target="_blank">',
                    '</a>'
                )
                . '</p><p>'
                . sprintf(
                    // translators: 1: html tag, 2: html tag
                    __('If you just want something quick, use %1$sQuick Print%2$s instead.', 'print-my-blog'),
                    '<a href="' . esc_attr(admin_url(PMB_ADMIN_PAGE_PATH)) . '">',
                    '</a>'
                )
                . '</p>',
                array_merge(
                    $this->getOptionsForScreen('toplevel_page_print-my-blog-projects'),
                    [
                        'query_args' => [
                            'subaction' => null,
                            'action' => null,
                        ],
                    ]
                )
            ),
            new Notice(
                'pmb_choose_design',
                __('Project Designs are like WordPress Themes', 'print-my-blog'),
                '<p>' . __('Each has a different look and options that can be customized.', 'print-my-blog') . '</p>'
                . '<p>' . __('The "Classic" design is the most similar Print My Blog’s Quick Print, so it’s a good default choice.', 'print-my-blog') . '</p>'
                . '<p>' . __('Click on the preview image for more details, and feel free to come back to this page later if you want to try a different design.', 'print-my-blog') . '</p>'
                . '<p><a href="https://printmy.blog/user-guide/pro/getting-started/4-choose-a-design/" target="_blank">'
                . __('Read the User Guide', 'print-my-blog')
                . '</a></p>',
                $this->getOptionsForProjectSubaction('choose_design')
            ),
            new Notice(
                'pmb_customize_design',
                __('Each Design has Different Options and is Reusable', 'print-my-blog'),
                '<p>' . __('Below are your chosen design’s customization options.', 'print-my-blog') . '</p>'
                . '<p>' . __('If you don’t see an option you need, you may want to go back and choose a different design, or ask the design’s author for it.', 'print-my-blog') . '</p>'
                . '<p>' . __('Note: designs are reused between projects. So customizations to this design will be reused by other projects using this same design', 'print-my-blog') . '</p>'
                . '<p><a href="https://printmy.blog/user-guide/pro/getting-started/5-customize-the-design/" target="_blank">'
                . __('Read the User Guide', 'print-my-blog')
                . '</a></p>',
                $this->getOptionsForProjectSubaction('customize_design')
            ),
            new Notice(
                'pmb_edit_content2',
                __('How to Edit Project Content', 'print-my-blog'),
                '<div class="pmb-two-column-notice"><div class="pmb-image-column"><iframe style="width:100%" height="315" src="https://www.youtube.com/embed/un7EnpDG2qs" 
        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>'
                . '<div class="pmb-text-column">'
                . '<ol>'
                . '<li>' . __('Find the articles (posts, pages, other post types) from WordPress you want to add on the left, and add it to your project on the right by dragging', 'print-my-blog') . '</li>'
                . '<li>' . __('Place articles in either front matter, body, or back matter, according to how you want your project organized. Each design can style them differently (eg front matter is often is numbered with roman numerals)') . '</li>'
                . '<li>' . __('Nest content inside others to create parts', 'print-my-blog') . '</li>'
                . '</ol>'
                . '<p><a href="https://printmy.blog/user-guide/pro/getting-started/6-choose-project-content/" target="_blank">'
                . __('Read the User Guide', 'print-my-blog')
                . '</a></p>'
                . '</div></div>',
                $this->getOptionsForProjectSubaction('content')
            ),
            new Notice(
                'pmb_edit_meta',
                __('Everything Else About Your Project is Stored Here', 'print-my-blog'),
                '<p>' . __('Different formats and designs may require other information about your project.', 'print-my-blog') .
                '</p>'
                . '<p>' . __('Some examples of metadata are: title page info, file info, or copyright data.', 'print-my-blog') . '</p>'
                . '<p>' . __('So although your choice of design may affect what metadata is required, it is not shared with other projects.', 'print-my-blog')
                . '</p>'
                . '<p><a href="https://printmy.blog/user-guide/pro/getting-started/7-enter-project-metadata/" target="_blank">'
                . __('Read the User Guide', 'print-my-blog')
                . '</a></p>',
                $this->getOptionsForProjectSubaction('metadata')
            ),
            new Notice(
                'pmb_generate',
                __('Generate and View Your File', 'print-my-blog'),
                '<p>' .
                    __('Your project is ready to be generated! Clicking "Generate" will compile your content into a print-page.', 'print-my-blog') .
                '</p>'
                . '<p>'
                . sprintf(
                    // translators: 1: opening anchor tag, 2: closing anchor tag, 3: opening anchor tag, 4: opening anchor tag
                    __('Read the User Guide on %1$supdating%2$s, %3$sgenerating the pro file%2$s, and %4$sgetting help%2$s.', 'print-my-blog'),
                    '<a href="https://printmy.blog/user-guide/pro/getting-started/9-update-the-project/" target="_blank">',
                    '</a>',
                    '<a href="https://printmy.blog/user-guide/pro/getting-started/10-generate-the-paid-pdf/" target="_blank">',
                    '<a href="https://printmy.blog/user-guide/pro/getting-started/11-getting-help/" target="_blank">'
                )
                . '</p>',
                $this->getOptionsForProjectSubaction('generate')
            ),
            new Notice(
                'pmb_print_materials',
                __('Posts just for Print My Blog', 'print-my-blog'),
                '<p>' . __('Print My Blog "Print Materials" are like private posts. They aren’t visible to site visitors, but you can use them in your Pro Print Projects.', 'print-my-blog') . '</p>',
                $this->getOptionsForScreen('edit-pmb_content')
            ),

        ];
    }

    /**
     * @return string[]
     */
    protected function getNoticeDefaultOptions()
    {
        return [
            'scope' => 'user',
            'type' => 'info',
            'capability' => 'read',
        ];
    }

    /**
     * @param string $screen_id
     * @return string[]
     */
    protected function getOptionsForScreen($screen_id)
    {
        $options = $this->getNoticeDefaultOptions();
        $options['screens'] = [$screen_id];
        return $options;
    }

    /**
     * @param string $subaction
     * @return string[]
     */
    protected function getOptionsForProjectSubaction($subaction)
    {
        $options = $this->getOptionsForScreen(
            'toplevel_page_print-my-blog-projects'
        );
        $options['query_args'] = [
            'subaction' => $subaction,
        ];
        return $options;
    }
}
