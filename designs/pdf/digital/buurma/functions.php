<?php
// Add filters, action callback, and functions you want to use in your design.
// Note that this file only gets included when gnerating a new project, not on every pageload.
add_action(
	'pmb_pdf_generation_start',
	function(\PrintMyBlog\entities\ProjectGeneration $project_generation, \PrintMyBlog\orm\entities\Design $design){
		wp_add_inline_style(
			'pmb_print_common',
			'@page title-page /*body*/{
					background: url(' . $design->getPmbMeta('title_page_banner'). ') no-repeat,
						url(' . $design->getPmbMeta('background_embellishment') . ') center center no-repeat,
						linear-gradient(#cce5ff, #e6f2ff);
						@top-right {
				            content: "' . $design->getPmbMeta('org'). '";
				            color:white;
				        }
				}
				@page main /*article*/{
					background: url(' . $design->getPmbMeta('background_embellishment') . ') right bottom/150px no-repeat,
						linear-gradient(127deg, rgba(230,242,255,1) 0%, rgba(230,242,255,1) 92%, rgba(204,229,255,1) 100%);;
				}
				@page back-matter{
					url(' . $design->getPmbMeta('background_embellishment') . ') center center no-repeat,
						linear-gradient(#cce5ff, #e6f2ff);
				}
			/* MIKE STYLE */'
		);
	},
	10,
	2
);