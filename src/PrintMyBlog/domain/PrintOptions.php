<?php

namespace PrintMyBlog\domain;

/**
 * Class PrintOptions
 *
 * Description
 *
 * @package     Print My Blog
 * @author         Mike Nelson
 * @since         $VID:$
 *
 */
class PrintOptions
{
    public function headerContentOptions()
    {
        return [
            'show_site_title' => [
                'label' => esc_html__('Site Title', 'print-my-blog'),
                'default' => true
            ],
            'show_site_tagline' => [
                'label' => esc_html__('Tagline', 'print-my-blog'),
                'default' => true
            ],
            'show_site_url' => [
                'label' => esc_html__('Site URL', 'print-my-blog'),
                'default' => true
            ],
            'show_filters' => [
                'label' => esc_html__('Filters Used', 'print-my-blog'),
                'default' => true,
                'help' => esc_html__('E.g. selected categories, taxonomies, and date range.', 'print-my-blog')
            ],
            'show_date_printed' => [
                'label' => esc_html__('Date Printed', 'print-my-blog'),
                'default' => true
            ],
            'show_credit' => [
                'label' => esc_html__('Credit Print My Blog Plugin', 'print-my-blog'),
                'default' => true,
                'help' => sprintf(
                    // @translators: 1: heart emoji
                    esc_html__('Show some love and tell your readers about Print My Blog %1$s', 'print-my-blog'),
                    '❤️'
                )
            ],
        ];
    }

    public function postContentOptions()
    {
        return [
            'show_title' => [
                'label' => esc_html__('Title', 'print-my-blog'),
                'default' => true
            ],
            'show_id' => [
                'label' => esc_html__('ID', 'print-my-blog'),
                'default' => false,
            ],
            'show_author' => [
                'label' => esc_html__('Author', 'print-my-blog'),
                'default' => false,
            ],
            'show_url' => [
                'label' => esc_html__('URL', 'print-my-blog'),
                'default' => false
            ],
            'show_date' => [
                'label' => esc_html__('Published Date', 'print-my-blog'),
                'default' => true,
            ],
            'show_categories' => [
                'label' => esc_html__('Categories and Tags', 'print-my-blog'),
                'default' => true
            ],
            'show_featured_image' => [
                'label' => esc_html__('Featured Image', 'print-my-blog'),
                'default' => true
            ],
            'show_excerpt' => [
                'label' => esc_html__('Excerpt', 'print-my-blog'),
                'default' => false
            ],
            'show_content' => [
                'label' => esc_html__('Content', 'print-my-blog'),
                'default' => true,
            ],
            'show_comments' => [
                'label' => esc_html__('Comments', 'print-my-blog'),
                'default' => false
            ],
            'show_divider' => [
                'label' => esc_html__('Extra dividing line at end of post', 'print-my-blog'),
                'default' => false
            ]
        ];
    }

    public function pageLayoutOptions()
    {
        return [
            'post_page_break' => [
                'label' => esc_html__('Each Post Begins on a New Page','print-my-blog' ),
                'default' => true,
                'help' => esc_html__('Whether to force posts to always start on a new page. Doing so makes the page more legible, but uses more paper.','print-my-blog' )
            ],
            'columns' => [
                'label' =>  esc_html__('Columns','print-my-blog' ),
                'default' => 1,
                'options' => [
                    1 =>  esc_html__('1','print-my-blog' ),
                    2 => esc_html__('2', 'print-my-blog'),
                    3 => esc_html__('3', 'print-my-blog')
                ],
                'help' => esc_html__('The number of columns of text on each page. Not supported by some web browsers.','print-my-blog' )
            ],
            'font_size' => [
                'label'=> esc_html__('Font Size','print-my-blog' ),
                'default' => 'normal',
                'options' => [
                    'tiny' => esc_html__('Tiny (1/2 size)','print-my-blog' ),
                    'small' => esc_html__('Small (3/4 size)', 'print-my-blog'),
                    'normal'=> esc_html__('Normal (theme default)', 'print-my-blog'),
                    'large' => esc_html__('Large (slightly larger than normal)', 'print-my-blog')
                ]
            ],
            'image_size' => [
                'label' => esc_html__('Image Size','print-my-blog' ),
                'default' => 'medium',
                'options' => [
                    'full' => esc_html__('Full (theme default)','print-my-blog' ),
                    'large' => esc_html__('Large (3/4 size)','print-my-blog' ),
                    'medium' => esc_html__('Medium (1/2 size)', 'print-my-blog'),
                    'small'=> esc_html__('Small (1/4 size)', 'print-my-blog'),
                    'none' => esc_html__('None (hide images)', 'print-my-blog')
                ],
                'help' => esc_html__('If you want to save paper, choose a smaller image size, or hide images altogether.','print-my-blog' )
            ],
            'links'=> [
                'label' => esc_html__('Include Hyperlinks','print-my-blog' ),
                'default' => 'include',
                'options' => [
                    'include' => esc_html__('Include','print-my-blog' ),
                    'remove' => esc_html__('Remove','print-my-blog' ),
                    'parens'=> esc_html__('Replace with URL in Parenthesis','print-my-blog' )
                ],
                'help' => esc_html__('Note: PDFs generated in Firefox and Safari automatically remove hyperlinks. If you want your PDF to include hyperlinks, please use another browser.', 'print-my-blog')
            ]
        ];
    }

    /**
     * @since $VID:$
     * @return array
     */
    public function troubleshootingOptions()
    {
        return [
            'rendering_wait' => [
                'label' => esc_html__('Post Rendering Wait-Time','print-my-blog' ),
                'default' => 200,
                'after_input' => esc_html__('ms', 'print-my-blog'),
                'help' => esc_html__('Milliseconds to wait between rendering posts. If posts are rendered too quickly on the page, sometimes images won’t load properly. ','print-my-blog' )
            ],
            'include_inline_js' => [
                'label' => esc_html__('Include Inline Javascript','print-my-blog' ),
                'default' => false,
                'help' => esc_html__('Sometimes posts contain inline javascript which can cause errors and stop the page from rendering.','print-my-blog' )
            ],
            'shortcodes' => [
                'label' => esc_html__('Include Unrendered Shortcodes','print-my-blog' ),
                'default' => false,
                'help' => esc_html__('If you left shortcodes from deactivated deactivated plugins or themes in your posts, they are automatically removed from printouts. Check this to leave them.','print-my-blog' )
            ]
        ];
    }

    protected function defaultOverrides($format){
        $overrides = [];
        switch($format){
            case 'pdf':
                break;
            case 'ebook':
                break;
            case 'print':
            default:
                $overrides['links'] = 'parens';
        }
        return $overrides;
    }

    /**
     * Returns the print options
     * @return array
     */
    public function allPrintOptions()
    {
        return array_merge(
            $this->troubleshootingOptions(),
            $this->pageLayoutOptions(),
            $this->headerContentOptions(),
            $this->postContentOptions()
        );
    }

    /**
     * @since $VID:$
     * @param string $format
     * @return array keys are option names, values are their default values
     */
    public function allPrintOptionDefaults($format = 'print'){
        $all = $this->allPrintOptions();
        $defaults = [];
        foreach($all as $name => $details){
            $defaults[$name] = $details['default'];
        }
        return array_merge(
            $defaults,
            $this->defaultOverrides($format)
        );
    }
}
// End of file PrintOptions.php
// Location: ${NAMESPACE}/PrintOptions.php
