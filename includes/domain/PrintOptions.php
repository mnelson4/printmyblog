<?php
namespace PrintMyBlog\domain;
/**
 * Class PrintOptions
 *
 * Description
 *
 * @package     Event Espresso
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

}
// End of file PrintOptions.php
// Location: ${NAMESPACE}/PrintOptions.php
