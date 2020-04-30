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
            'site_title' => [
                'label' => esc_html__('Site Title', 'print-my-blog'),
                'default' => true
            ],
            'site_tagline' => [
                'label' => esc_html__('Tagline', 'print-my-blog'),
                'default' => true
            ],
            'site_url' => [
                'label' => esc_html__('Site URL', 'print-my-blog'),
                'default' => true
            ],
            'filters' => [
                'label' => esc_html__('Filters Used', 'print-my-blog'),
                'default' => true,
                'help' => esc_html__('E.g. selected categories, taxonomies, and date range.', 'print-my-blog')
            ],
            'date_printed' => [
                'label' => esc_html__('Date Printed', 'print-my-blog'),
                'default' => true
            ],
            'credit' => [
                'label' => esc_html__('Credit Print My Blog Plugin', 'print-my-blog'),
                'default' => true,
                'help' => esc_html__('Says the printout was made using Print My Blog', 'print-my-blog')
            ],
        ];
    }

    public function postContentOptions()
    {
        return [
            'title' => [
                'label' => esc_html__('Title', 'print-my-blog'),
                'default' => true
            ],
            'id' => [
                'label' => esc_html__('ID', 'print-my-blog'),
                'default' => false,
            ],
            'author' => [
                'label' => esc_html__('Author', 'print-my-blog'),
                'default' => false,
            ],
            'url' => [
                'label' => esc_html__('URL', 'print-my-blog'),
                'default' => false
            ],
            'date' => [
                'label' => esc_html__('Published Date', 'print-my-blog'),
                'default' => true,
            ],
            'categories' => [
                'label' => esc_html__('Categories and Tags', 'print-my-blog'),
                'default' => true
            ],
            'featured_image' => [
                'label' => esc_html__('Featured Image', 'print-my-blog'),
                'default' => true
            ],
            'excerpt' => [
                'label' => esc_html__('Excerpt', 'print-my-blog'),
                'default' => false
            ],
            'content' => [
                'label' => esc_html__('Content', 'print-my-blog'),
                'default' => true,
            ],
            'comments' => [
                'label' => esc_html__('Comments', 'print-my-blog'),
                'default' => false
            ],
            'divider' => [
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
}
// End of file PrintOptions.php
// Location: ${NAMESPACE}/PrintOptions.php
