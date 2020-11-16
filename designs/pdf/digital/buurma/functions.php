<?php
// Add filters, action callback, and functions you want to use in your design.
// Note that this file only gets included when gnerating a new project, not on every pageload.
add_action(
	'pmb_pdf_generation_start',
	function(\PrintMyBlog\entities\ProjectGeneration $project_generation, \PrintMyBlog\orm\entities\Design $design){
		$css = pmb_design_styles($design);
		$svg_doer = new \PrintMyBlog\services\SvgDoer();
		$svg_data = $svg_doer->getSvgDataAsColor(
			$design->getDesignTemplate()->getDir() . 'assets/banner.svg',
			$design->getSetting('title_page_banner_color')
		);
		$bg_color = $design->getSetting('background_color');
		$color_guru = new \PrintMyBlog\services\ColorGuru();
		wp_add_inline_style(
			'pmb_print_common',
			$css . '
					/* BUURMA DESIGN INLINE CSS */
					@page title-page /*body*/{
					background: url("' . $svg_data. '") no-repeat,
						url("' . $design->getSetting('background_embellishment') . '") center center no-repeat,
						linear-gradient(' . $color_guru->convertHextToRgba($bg_color,1). ', ' . $color_guru->convertHextToRgba($bg_color, .3). ');
					background-size:
						/* banner */ 100% 150px, 
						/* logo */ 40%, 
						/* gradient */ 40%;
					@top-right {
			            content: "' . $design->getSetting('org'). '";
			            color:white;
			        }
				}
				@page front-matter{
					background: url(' . $design->getSetting('background_embellishment') . ') right bottom/150px no-repeat,
						linear-gradient(127deg, ' . $color_guru->convertHextToRgba($bg_color,.3). ' 0%, ' . $color_guru->convertHextToRgba($bg_color,.3). ' 80%, ' . $color_guru->convertHextToRgba($bg_color,1). ' 100%);
				}
				@page main /*article*/{
					background: url(' . $design->getSetting('background_embellishment') . ') right bottom/150px no-repeat,
						linear-gradient(127deg, ' . $color_guru->convertHextToRgba($bg_color,.3). ' 0%, ' . $color_guru->convertHextToRgba($bg_color,.3). ' 80%, ' . $color_guru->convertHextToRgba($bg_color,1). ' 100%);
				}
				@page back-matter{
					background: url(' . $design->getSetting('background_embellishment') . ') center center no-repeat,
						linear-gradient(' . $color_guru->convertHextToRgba($bg_color,1). ', ' . $color_guru->convertHextToRgba($bg_color, .3). ');
					background-size:80%, 100%;
				}'
		);
		wp_localize_script(
			'pmb-design',
			'pmb_design_options',
			[
//				'external_links' => $design->getSetting('external_links'),
//				'internal_links' => $design->getSetting('internal_links'),
//				'image_size' => $design->getSetting('image_size'),
				'default_alignment' => $design->getSetting('default_alignment'),
			]
		);
	},
	10,
	2
);