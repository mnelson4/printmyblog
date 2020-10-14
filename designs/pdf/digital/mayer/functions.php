<?php
// Add filters, action callback, and functions you want to use in your design.
// Note that this file only gets included when gnerating a new project, not on every pageload.
add_action(
	'pmb_pdf_generation_start',
	function(\PrintMyBlog\entities\ProjectGeneration $project_generation, \PrintMyBlog\orm\entities\Design $design){
		$css = pmb_design_styles($design);
		if($design->getSetting('post_header_in_columns')){
			$css .= ' .pmb-main-matter{columns:2}';
		} else {
			$css .= ' .entry-content{columns:2}';
		}
		if($design->getSetting('dividing_line')){
			$css .= ' .entry-content{border-bottom:1px solid gray;}';
		}
		if($design->getSetting('images_full_column')){
			$css .=' figure.wp-caption:not(.mayer-noresize), figure.wp-block-image:not(.mayer-no-resize), img:not(.mayer-no-resize){width:100%;height:auto;}';
		}
		wp_add_inline_style(
			'pmb_print_common',
			$css
		);
//		wp_localize_script(
//			'pmb-design',
//			'pmb_classic_options',
//			[
//				'external_links' => $design->getSetting('external_links'),
//				'internal_links' => $design->getSetting('internal_links')
//			]
//		);
	},
	10,
	2
);