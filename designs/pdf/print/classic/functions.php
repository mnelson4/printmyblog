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

    // if they're using a custom font, we'll need to declare the font face then use that font.
    $header_font = $pmb_design->getSetting('header_font_style');
    if ($header_font === 'custom_header_font') {
        $custom_header_font = $pmb_design->getSetting('custom_header_font_style');
        // get the name from the file's name.
        $header_font = pmb_get_filename($custom_header_font, true);
        $css .= '
        @font-face{
                font-family: "' . $header_font . '";
                font-style: normal;
                font-weight: normal;
                src : url(' . $custom_header_font . ');
         }';

    }
    $main_font = $pmb_design->getSetting('font_style');
    if ($main_font === 'custom_font') {
        $custom_font = $pmb_design->getSetting('custom_font_style');
        $main_font = pmb_get_filename($custom_font, true);
        $css .= '
        @font-face{
                font-family: "' . $main_font . '";
                font-style: normal;
                font-weight: normal;
                src : url(' . $custom_font . ');
         }';

    }

    $css .= 'html{
            font-size:' . $pmb_design->getSetting('font_size') . ';
            font-family:' . $main_font . ';
       }
       .pmb-posts-header .site-title{
            font-size:' . $pmb_design->getSetting('main_header_font_size') . ';
        }
       .pmb-part h1.pmb-title{
            font-size:' . $pmb_design->getSetting('main_header_font_size') . ';
        }
       span.pmb-footnote{
            font-family:' . $main_font . ';
            font-size: calc(' . $pmb_design->getSetting('font_size') . ' * 2 / 3);
       }
       h1,h2,h3,h4,h5,h6{
            font-family:' . $header_font . ';
       }     
        @page{
            size: ' . $pmb_design->getSetting('page_width') . ' ' . $pmb_design->getSetting('page_height')
        . ';}
    /* 
        Make the preview appear about the same size as in the PDF. Besides making the preview better,
        Javascript code that\'s calculating element dimensions will be better too.
    */
    @media not print{
        .pmb-project-content{
            width: calc(' . $pmb_design->getSetting('page_width') . ' - 54pt - 54pt - 5pt);
        }
    }
        ';

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