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
	}
}