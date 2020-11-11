<?php
// Add filters, action callback, and functions you want to use in your design.
// Note that this file only gets included when gnerating a new project, not on every pageload.
add_action(
	'pmb_pdf_generation_start',
	function(\PrintMyBlog\entities\ProjectGeneration $project_generation, \PrintMyBlog\orm\entities\Design $design){
		wp_add_inline_style(
			'pmb_print_common',
			pmb_design_styles($design)
			. "body{font-size:" . $design->getSetting('font_size') . ";}
			@page{
				size: " . $design->getSetting('page_width') . ' ' . $design->getSetting('page_height') . "}
			}
			"
		);
		wp_localize_script(
			'pmb-design',
			'pmb_design_options',
			[
				'external_links' => $design->getSetting('external_links'),
				'internal_links' => $design->getSetting('internal_links'),
				'image_size' => $design->getSetting('image_size'),
				'default_alignment' => $design->getSetting('default_alignment'),
			]
		);
	},
	10,
	2
);