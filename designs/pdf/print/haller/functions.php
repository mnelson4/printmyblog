<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols -- sorry, this file is meant for everything
// Add filters, action callback, and functions you want to use in your design.
// Note that this file only gets included when gnerating a new project, not on every pageload.
add_action(
    'pmb_pdf_generation_start',
    function (\PrintMyBlog\entities\ProjectGeneration $project_generation, \PrintMyBlog\orm\entities\Design $design) {
        global $pmb_design;
        $pmb_design = $design;
        add_action('wp_enqueue_scripts', 'pmb_enqueue_haller_script', 1001);
    },
    10,
    2
);

/**
 * Adds scripts to print page
 * @throws Exception
 */
function pmb_enqueue_haller_script()
{
    global $pmb_design;
    $css = pmb_design_styles($pmb_design);
    $columns = (int)$pmb_design->getSetting('columns');
    if ($pmb_design->getSetting('post_header_in_columns')) {
        $css .= '.pmb-main-matter{columns:' . $columns . '}';
    }
    if ($pmb_design->getSetting('images_full_column')) {
        $css .= ' figure.wp-caption:not(.mayer-noresize, .emoji), figure.wp-block-image:not(.mayer-no-resize, .emoji), .pmb-posts .pmb-image img:not(.mayer-no-resize, .emoji), img:not(.mayer-no-resize, .emoji){width:100% !important;height:auto;}';
    }
    if ($pmb_design->getSetting('no_extra_columns')) {
        $css .= '.pmb-section:not(.pmb-single-column) .wp-block-columns{display:block;}';
    }
    wp_add_inline_style(
        'pmb_print_common',
        $css
    );
    wp_localize_script(
        'pmb-design',
        'pmb_design_options',
        pmb_design_settings($pmb_design)
    );
}