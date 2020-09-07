<?php
namespace PrintMyBlog\domain;


use PrintMyBlog\orm\entities\Design;
use Twine\forms\base\FormSectionProper;
use Twine\forms\inputs\TextInput;
use Twine\forms\inputs\YesNoInput;

class DefaultDesignTemplates {
	public function registerDesignTemplates()
	{
//		pmb_register_design_template(
//			'classic_print',
//			[
//				'title' => __('Classic', 'print-my-blog'),
//				'format' => 'print_pdf',
//				'dir' => PMB_DEFAULT_DESIGNS_DIR . '/classic_print',
//				'url' => PMB_DEFAULT_DESIGNS_URL . '/classic_print',
//				'design_options' =>
//			]
//		);
		pmb_register_design_template(
			'classic_digital',
			function() {
				return [
					'title'           => __( 'Classic Digital PDF' ),
					'format'          => 'digital_pdf',
					'dir'             => PMB_DEFAULT_DESIGNS_DIR . 'classic_digital/',
					'design_form_callback'  => function() {
						return new FormSectionProper( [
							'subsections' => [
								'show_title' => new YesNoInput()
							]
						] );
					},
					'project_form_callback' => function(Design $design) {
						$sections = [];
						if($design->getPmbMeta('show_title')){
							$sections['title'] = new TextInput();
						}
						return new FormSectionProper( [
							'subsections' => $sections
						] );
					}
				];
			}
		);
		// bloggy
		// magaziney
		// bookey
//		pmb_register_design_template(
//			'buurma',
//			[
//
//			]
//		);
	}
}