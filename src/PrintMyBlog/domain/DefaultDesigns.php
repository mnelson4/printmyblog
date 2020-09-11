<?php


namespace PrintMyBlog\domain;


use PrintMyBlog\entities\DesignTemplate;

class DefaultDesigns {
	public function registerDefaultDesigns()
	{
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
			'classic_digital',
			'classic_digital_pdf',
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
	}
}