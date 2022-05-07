<?php
// Add filters, action callback, and functions you want to use in your design.
// Note that this file only gets included when generating a new project, not on every pageload.
add_action(
	'pmb_pdf_generation_start',
	function(\PrintMyBlog\entities\ProjectGeneration $project_generation, \PrintMyBlog\orm\entities\Design $design){
	    global $pmb_design;
	    $pmb_design = $design;
	    add_action('wp_enqueue_scripts', 'pmb_enqueue_classic_script', 1001);
	},
	10,
	2
);

function pmb_enqueue_classic_script(){
    /**
     * @var $pmb_design \PrintMyBlog\orm\entities\Design
     */
    global $pmb_design;
    wp_add_inline_style(
        'pmb_print_common',
        $pmb_design->getSetting('custom_css')
    );
    wp_localize_script(
        'pmb-design',
        'pmb_design_options',
        [
            'convert_videos' => (int)$pmb_design->getSetting('convert_videos'),
            'image_quality' => $pmb_design->getSetting('image_quality'),
            'domain' => pmb_site_domain()
        ]
    );
}