<?php
/**
 * @var $designs \PrintMyBlog\orm\entities\Design[]
 * @var $chosen_design \PrintMyBlog\orm\entities\Design
 * @var $project \PrintMyBlog\orm\entities\Project|null
 * @var $format \PrintMyBlog\entities\FileFormat
 * @var $steps_to_urls array
 * @var $current_step string
 */

use PrintMyBlog\controllers\Admin;
use \PrintMyBlog\entities\DesignTemplate;

pmb_render_template(
    'partials/project_header.php',
    [
        'project' => $project,
        'page_title' => sprintf(
            __('Choose Design: %s', 'print-my-blog'),
            $format->coloredTitleAndIcon()
        ),
        'current_step' => $current_step,
        'steps_to_urls' => $steps_to_urls
    ]
);

pmb_render_template(
    'partials/select_designs.php',
    [
        'designs'=> $designs,
        'chosen_design' => $chosen_design,
        'active_text' => __('<span>Active:</span> %s', 'print-my-blog'),
        'select_button_text' => esc_html__('Use This Design', 'print-my-blog'),
        'select_button_aria_label' => esc_html__('Use the Design "%s"', 'print-my-blog'),
        'customize_button_aria_label' => esc_html__('Use and Customize the Design "%s"', 'print-my-blog'),
    ]
);
pmb_render_template('partials/project_footer.php');