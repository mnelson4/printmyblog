<?php // phpcs:disable Files.SideEffects.FoundWithSymbols -- sorry, this file is meant for everything
// Add filters, action callback, and functions you want to use in your design.
// Note that this file only gets included when gnerating a new project, not on every pageload.
add_action(
    'pmb_pdf_generation_start',
    function (\PrintMyBlog\entities\ProjectGeneration $project_generation, \PrintMyBlog\orm\entities\Design $design) {
        global $pmb_design;
        $pmb_design = $design;
        add_action('wp_enqueue_scripts', 'pmb_enqueue_buurma_script', 1001);
        add_filter(
            'PrintMyBlog\services\generators\PdfGenerator->printScripts prince_js_vars',
            'pmb_buurma_modify_prince_vars'
        );
    },
    10,
    2
);

/**
 * Enqueues scripts.
 * @throws Exception
 */
function pmb_enqueue_buurma_script()
{
    global $pmb_design;
    $css = pmb_design_styles($pmb_design);
    $svg_doer = new \PrintMyBlog\services\SvgDoer();
    $svg_data = $svg_doer->getSvgDataAsColor(
        $pmb_design->getDesignTemplate()->getDir() . 'assets/banner.svg',
        $pmb_design->getSetting('title_page_banner_color')
    );
    $bg_color = $pmb_design->getSetting('background_color');
    $color_guru = new \PrintMyBlog\services\ColorGuru();
    wp_add_inline_style(
        'pmb_print_common',
        $css . '
					/* BUURMA DESIGN INLINE CSS */
					@page title-page /*body*/{
					background: url("' . $svg_data . '") no-repeat,
						url("' . $pmb_design->getSetting('background_embellishment') . '") center center no-repeat,
						linear-gradient(' . $color_guru->convertHextToRgba($bg_color, 1) . ', ' . $color_guru->convertHextToRgba($bg_color, .3) . ');
					background-size:
						/* banner */ 100% 150px, 
						/* logo */ 40%, 
						/* gradient */ 40%;
					@top-right {
			            content: "' . $pmb_design->getSetting('org') . '";
			            color:white;
			        }
				}
				@page front-matter{
					background: url(' . $pmb_design->getSetting('background_embellishment') . ') right bottom/150px no-repeat,
						linear-gradient(127deg, ' . $color_guru->convertHextToRgba($bg_color, .3) . ' 0%, ' . $color_guru->convertHextToRgba($bg_color, .3) . ' 80%, ' . $color_guru->convertHextToRgba($bg_color, 1) . ' 100%);
				}
				@page main /*article*/{
					background: url(' . $pmb_design->getSetting('background_embellishment') . ') right bottom/150px no-repeat,
						linear-gradient(127deg, ' . $color_guru->convertHextToRgba($bg_color, .3) . ' 0%, ' . $color_guru->convertHextToRgba($bg_color, .3) . ' 80%, ' . $color_guru->convertHextToRgba($bg_color, 1) . ' 100%);
				}
				@page back-matter{
					background: url(' . $pmb_design->getSetting('background_embellishment') . ') center center no-repeat,
						linear-gradient(' . $color_guru->convertHextToRgba($bg_color, 1) . ', ' . $color_guru->convertHextToRgba($bg_color, .3) . ');
					background-size:80%, 100%;
				}'
    );
    wp_localize_script(
        'pmb-design',
        'pmb_design_options',
        pmb_design_settings($pmb_design)
    );
}

/**
 * Tell the generator we use a post-per-page so layout can be more efficient.
 * @param array $prince_js_vars
 * @return array
 */
function pmb_buurma_modify_prince_vars($prince_js_vars)
{
    $prince_js_vars['page_per_post'] = true;
    return $prince_js_vars;
}