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

}
// End of file PrintOptions.php
// Location: ${NAMESPACE}/PrintOptions.php
