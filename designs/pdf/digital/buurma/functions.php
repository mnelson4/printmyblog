<?php
// Add filters, action callback, and functions you want to use in your design.
// Note that this file only gets included when gnerating a new project, not on every pageload.
add_action(
	'pmb_pdf_generation_start',
	function(\PrintMyBlog\entities\ProjectGeneration $project_generation, \PrintMyBlog\orm\entities\Design $design){
		$css = pmb_design_styles($design);
		wp_add_inline_style(
			'pmb_print_common',
			$css . '@page title-page /*body*/{
					background: url(' . $design->getSetting('title_page_banner'). ') no-repeat,
						url(' . $design->getSetting('background_embellishment') . ') center center no-repeat,
						linear-gradient(#cce5ff, #e6f2ff);
					background-size:auto,40%;
					@top-right {
			            content: "' . $design->getSetting('org'). '";
			            color:white;
			        }
				}
				@page front-matter{
					background: url(' . $design->getSetting('background_embellishment') . ') right bottom/150px no-repeat,
						linear-gradient(127deg, rgba(230,242,255,1) 0%, rgba(230,242,255,1) 80%, rgba(204,229,255,1) 100%);
				}
				@page main /*article*/{
					background: url(' . $design->getSetting('background_embellishment') . ') right bottom/150px no-repeat,
						linear-gradient(127deg, rgba(230,242,255,1) 0%, rgba(230,242,255,1) 80%, rgba(204,229,255,1) 100%);
				}
				@page back-matter{
					background: url(' . $design->getSetting('background_embellishment') . ') center center no-repeat,
						linear-gradient(#cce5ff, #e6f2ff);
					background-size:80%;
				}
			/* MIKE STYLE */'
		);
	},
	10,
	2
);