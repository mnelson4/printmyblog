<?php
// Add filters, action callback, and functions you want to use in your design.
// Note that this file only gets included when gnerating a new project, not on every pageload.
add_action(
	'pmb_pdf_generation_start',
	function(\PrintMyBlog\entities\ProjectGeneration $project_generation, \PrintMyBlog\orm\entities\Design $design){

		wp_add_inline_style(
			'pmb_print_common',
			pmb_design_styles($design)
		);
		wp_localize_script(
			'pmb-design',
			'pmb_classic_options',
			[
				'external_links' => $design->getPmbMeta('external_links'),
				'internal_links' => $design->getPmbMeta('internal_links')
			]
		);
	},
	10,
	2
);