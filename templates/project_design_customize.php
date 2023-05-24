<?php
/**
 * @var $form \Twine\forms\base\FormSection
 * @var $form_url string
 * @var $design \PrintMyBlog\orm\entities\Design
 * @var $steps_to_urls array
 * @var $current_step string
 * @var $project \PrintMyBlog\orm\entities\Project
 */
// outputs the form for the design

// save button, and textbox to make into a new design

pmb_render_template(
    'partials/project_header.php',
    [
        'project' => $project,
        'page_title' => sprintf(
            __('Customize %s Design: %s', 'print-my-blog'),
            $design->getDesignTemplate()->getFormat()->coloredTitleAndIcon(),
            $design->getWpPost()->post_title
        ),
        'current_step' => $current_step,
        'steps_to_urls' => $steps_to_urls
    ]
);
pmb_render_template(
    'partials/customize_design.php',
    [
        'design'=> $design,
        'form' => $form,
        'form_url' => $form_url
    ]
);
pmb_render_template('partials/project_footer.php');
