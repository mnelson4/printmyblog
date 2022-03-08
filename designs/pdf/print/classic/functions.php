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
    global $pmb_design;
    $css = pmb_design_styles($pmb_design)
        . "body{
                font-size:" . $pmb_design->getSetting('font_size') . ";
                font-family:" . $pmb_design->getSetting('font_style') . ";
           }
           .pmb-posts-header .site-title{
                font-size:" . $pmb_design->getSetting('main_header_font_size'). ";
            }
           .pmb-part h1.pmb-title{
                font-size:" . $pmb_design->getSetting('main_header_font_size'). ";
            }
           span.pmb-footnote{
                font-family:" . $pmb_design->getSetting('font_style') . ";
                font-size: calc(" . $pmb_design->getSetting('font_size') . " * 2 / 3);
           }
           h1,h2,h3,h4,h5,h6{
                font-family:" . $pmb_design->getSetting('header_font_style') . ";
           }     
			@page{
				size: " . $pmb_design->getSetting('page_width') . ' ' . $pmb_design->getSetting('page_height')
        ."}
        /* 
            Make the preview appear about the same size as in the PDF. Besides making the preview better,
            Javascript code that's calculating element dimensions will be better too.
        */
        @media not print{
            .pmb-project-content{
                width: calc(" . $pmb_design->getSetting('page_width') . " - 54pt - 54pt);
            }
        }
			";
    if($pmb_design->getPmbMeta('paragraph_indent')){
        $css .= ' .pmb-article .post-inner p{
                        text-indent:3em;
                        margin:0;
                    }';
    }
    // if we're removing images, figures in image blocks become crazy tall because they're display:table-caption.
    // but set them just to a regular block and they look fine.
    if($pmb_design->getPmbMeta('image_size') == 0){
        $css .= '.wp-block-image figure > figcaption{ display:block;}';
    }

    wp_add_inline_style(
        'pmb_print_common',
        $css
    );
    wp_localize_script(
        'pmb-design',
        'pmb_design_options',
        [
            'external_links' => $pmb_design->getSetting('external_links'),
            'internal_links' => $pmb_design->getSetting('internal_links'),
            'image_size' => $pmb_design->getSetting('image_size'),
            'default_alignment' => $pmb_design->getSetting('default_alignment'),
            'internal_footnote_text' => $pmb_design->getSetting('internal_footnote_text'),
            'external_footnote_text' => $pmb_design->getSetting('footnote_text'),
            'image_quality' => $pmb_design->getSetting('image_quality'),
            'dynamic_resize_min' => $pmb_design->getSetting('dynamic_resize_min'),
            'domain' => pmb_site_domain()
        ]
    );
}