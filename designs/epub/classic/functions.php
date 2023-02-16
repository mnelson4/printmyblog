<?php // phpcs:disable Files.SideEffects.FoundWithSymbols -- sorry, this file is meant for everything
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

add_filter(
    '\PrintMyBlog\services\generators\EpubGenerator::enqueueStylesAndScripts $css',
    function($css, $design, $project){
        $header_font_size = $design->getSetting('main_header_font_size');
        $font_size = $design->getSetting('font_size');

        $font = $design->getSetting('custom_font');
        if($font) {
            $css .= '
            @font-face{
                font-family: "' . pmb_get_filename($font, true) . '";
                font-style: normal;
                font-weight: normal;
                src : url(./fonts/' . pmb_get_filename($font) . ');
            }
            body{
                    font-family: "' . pmb_get_filename($font, true) . '", georgia, serif;
                    font-size: ' . $font_size . ';
            }
            ';
        }
        $header_font = $design->getSetting('custom_header_font');
        if($header_font) {
            $css .= '
            @font-face{
                font-family: "' . pmb_get_filename($header_font, true) . '";
                font-style: normal;
                font-weight: normal;
                src : url(./fonts/' . pmb_get_filename($header_font) . ');
            }
            h1, h2, h3, h4, h5, h6{
                    font-family: "' . pmb_get_filename($header_font, true) . '", arial, serif;
                }
            h1.pmb-title, h1.site-title{
                    font-size: ' . $header_font_size . ';
                }';
        }
        return $css;
    },
    10,
    3
);

/**
 * Enqueues scripts on print page.
 * @throws Exception
 */
function pmb_enqueue_classic_script()
{
    /**
     * @var $pmb_design \PrintMyBlog\orm\entities\Design
     */
    global $pmb_design;
    $font = $pmb_design->getSetting('custom_font');
    $header_font = $pmb_design->getSetting('custom_header_font');
    $fonts_to_embed = [];
    if($font){
        $fonts_to_embed[] = [
            'filename' => pmb_get_filename($font, false),
            'url' => $font
        ];
    }
    if($header_font){
        $fonts_to_embed[] = [
            'filename' => pmb_get_filename($header_font, false),
            'url' => $header_font
        ];
    }
    wp_localize_script(
        'pmb-design',
        'pmb_design_options',
        [
            'convert_videos' => (int)$pmb_design->getSetting('convert_videos'),
            'image_quality' => $pmb_design->getSetting('image_quality'),
            'domain' => pmb_site_domain(),
            'fonts_to_embed' => $fonts_to_embed
        ]
    );
}