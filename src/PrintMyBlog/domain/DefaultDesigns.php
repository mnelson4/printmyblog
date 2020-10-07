<?php


namespace PrintMyBlog\domain;


use PrintMyBlog\entities\DesignTemplate;

class DefaultDesigns {
	public function registerDefaultDesigns()
	{

		pmb_register_design(
			'classic_digital',
			'classic_digital',
			function(DesignTemplate $design_template){
				return [
					'title' => __('Classic Digital PDF', 'print-my-blog'),
					'description' => __('Look inspired by Print My Blogs original, containing a quick printout heading and compact design.', 'print-my-blog'),
					'featured_image' => plugins_url($design_template->getDir() . '/preview.png'),
					'design_defaults' => [
						'use_title' => true,
					],
					'project_defaults' => [
						'title' => get_bloginfo('name')
					]
				];
			}
		);
		pmb_register_design(
			'classic_print',
			'editorial_review',
			function(DesignTemplate $design_template){
				return [
					'title' => __('Editorial Review', 'print-my-blog'),
					'description' => __('Perfect for editing and reviewing your content! Compact to conserve paper, lots of meta-information, and double-spaced text to allow for editor’s notes.', 'print-my-blog'),
					'featured_image' => plugins_url($design_template->getDir() . '/preview.png'),
					'design_defaults' => [
						'header_content' => [
							'title',
							'subtitle',
							'url',
							'date_printed',
						],
						'post_content' => [
							'title',
							'id',
							'author',
							'url',
							'published_date',
							'categories',
							'featured_image',
							'excerpt',
							'content',
						],
						'page_per_post' => true,
						'font_size' => 'small',
						'image_size' => 'small',

					],
					'project_defaults' => [
						'title' => get_bloginfo('name')
					],
					'custom_css' => 'p{line-height:2;}'
				];
			}
		);
		pmb_register_design(
			'classic_print',
			'classic_print_pdf',
			function(DesignTemplate $design_template){
				return [
					'title' => __('Classic Print PDF', 'print-my-blog'),
					'description' => __('Look inspired by Print My Blogs original, containing a quick printout heading and compact design.', 'print-my-blog'),
					'featured_image' => plugins_url($design_template->getDir() . '/preview.png'),
					'design_defaults' => [
						'use_title' => true,
					],
					'project_defaults' => [
						'title' => get_bloginfo('name')
					]
				];
			}
		);
		pmb_register_design(
			'classic_print',
			'economical_print_pdf',
			function(DesignTemplate $design_template){
				return [
					'title' => __('Economical Print PDF', 'print-my-blog'),
					'description' => __('Compact design meant to save paper but still deliver all the content.', 'print-my-blog'),
					'featured_image' => plugins_url($design_template->getDir() . '/preview.png'),
					'design_defaults' => [
						'header_content' => [
							'title',
							'url',
							'date_printed',
						],
						'post_content' => [
							'title',
							'featured_image',
							'content',
						],
						'page_per_page' => false,
						'columns' => 2,
						'font_size' => 'small',
						'image_size' => 'small',
						// purposefully leave hyperlink defaults dynamic
					],
					'project_defaults' => [
						'title' => get_bloginfo('name')
					]
				];
			}
		);
		pmb_register_design(
			'classic_print',
			'tree_saver_print_pdf',
			function(DesignTemplate $design_template){
				return [
					'title' => __('Tree-Saver Print PDF', 'print-my-blog'),
					'description' => __('As compact as possible to save paper.', 'print-my-blog'),
					'featured_image' => plugins_url($design_template->getDir() . '/preview.png'),
					'design_defaults' => [
						'header_content' => [
							'title',
							'url',
							'date_printed',
						],
						'post_content' => [
							'title',
							'content',
						],
						'page_per_page' => false,
						'columns' => 3,
						'font_size' => 'tiny',
						'image_size' => 'none',
						// use defaults of design template for hyperlinks
					],
					'project_defaults' => [
						'title' => get_bloginfo('name')
					]
				];
			}
		);
		pmb_register_design(
			'buurma',
			'buurma',
			function(DesignTemplate $design_template){
				$current_user = wp_get_current_user();
				if($current_user instanceof WP_User && $current_user->exists()){
					$name = $current_user->first_name . ' ' . $current_user->last_name;
				} else {
					$name = '';
				}
				return [
					'title' => __('Buurma Whitepaper', 'print-my-blog'),
					'description' => __('Digital PDF perfect for a branded whitepaper.', 'print-my-blog'),
					'featured_image' => plugins_url($design_template->getDir() . '/preview.png'),
					'design_defaults' => [
					],
					'project_defaults' => [
						'title' => get_bloginfo('name'),
						'byline' => $name,
						'issue' => __('Issue 01', 'print-my-blog'),
						'cover_preamble' => __('Text explaining the purpose of the paper and gives a brief summary of it, so folks know they’re reading the right thing.', 'print-my-blog')
					]
				];
			}
		);
		pmb_register_design(
			'mayer',
			'mayer',
			function(DesignTemplate $design_template){
				return [
					'title' => __('Mayer Magazine', 'print-my-blog'),
					'description' => __('Digital 2-column magazine', 'print-my-blog'),
					'featured_image' => plugins_url($design_template->getDir() . '/preview.png'),
					'design_defaults' => [
						'header_content' => [
							'title',
							'subtitle',
							'url',
							'date_printed',
						],
						'post_content' => [
							'title',
							'id',
							'author',
							'url',
							'published_date',
							'categories',
							'featured_image',
							'excerpt',
							'content',
						],
						'page_per_post' => false,
						'post_header_in_columns' => false
					],
					'project_defaults' => [
						'title' => get_bloginfo('name'),
					]
				];
			}
		);
	}
}