<?php
// phpcs:disable Files.SideEffects.FoundWithSymbols -- sorry, this file is meant for everything
// Add filters, action callback, and functions you want to use in your design.
// Note that this file only gets included when generating a new project, not on every pageload.
add_action(
    'pmb_pdf_generation_start',
    function (\PrintMyBlog\entities\ProjectGeneration $project_generation, \PrintMyBlog\orm\entities\Design $design) {
        global $pmb_design;
        $pmb_design = $design;
        add_action('wp_enqueue_scripts', 'pmb_enqueue_classic_script', 1001);
    },
    10,
    2
);

/**
 * Enqueues scripts in the print page.
 * @throws Exception
 */
function pmb_enqueue_classic_script()
{
    global $pmb_design;

    $css = pmb_design_styles($pmb_design);

    if ($pmb_design->getPmbMeta('paragraph_indent')) {
        $css .= ' .pmb-article .post-inner p:not(.has-text-align-center){
                        text-indent:3em;
                        margin:0;
                    }';
    }
    if($pmb_design->getSetting('page_per_post')){
        $css .= '
            .pmb-section-wrapper:not(.pmb-first-section-wrapper){
                break-before:page;
        }';
    }
    if ($pmb_design->getSetting('dividing_line')) {
        $css .= '.pmb-main-matter .entry-content{border-bottom:1px solid gray;box-decoration-break: slice;}';
    }
    // if we're removing images, figures in image blocks become crazy tall because they're display:table-caption.
    // but set them just to a regular block and they look fine.
    // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison -- we want '0' to match 0.
    if ($pmb_design->getPmbMeta('image_size') == 0) {
        $css .= '.wp-block-image figure > figcaption{ display:block;}';
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