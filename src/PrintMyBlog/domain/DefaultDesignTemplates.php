<?php


namespace PrintMyBlog\domain;


class DefaultDesignTemplates {
	public function registerDesignTemplates()
	{
		pmb_register_design_template(
			'classic_print',
			[
				'title' => __('Classic', 'print-my-blog'),
				'format' => 'print_pdf',
				'dir' => PMB_DEFAULT_DESIGNS_DIR . '/classic',
				'url' => PMB_DEFAULT_DESIGNS_URL . '/classic',
				'options' => [

				]
			]
		);
		pmb_register_design_template(
			'class_digital'
		);
		// bloggy
		// magaziney
		// bookey
		pmb_register_design_template(
			'buurma',
			[

			]
		);
	}
}