<?php
namespace PrintMyBlog\domain;


use PrintMyBlog\orm\entities\Design;
use Twine\forms\base\FormSectionProper;
use Twine\forms\inputs\CheckboxMultiInput;
use Twine\forms\inputs\TextInput;
use Twine\forms\inputs\YesNoInput;

class DefaultDesignTemplates {
	public function registerDesignTemplates()
	{
		pmb_register_design_template(
			'classic_print',
			function() {
				return [
					'title'                 => __( 'Classic Print PDf', 'print-my-blog' ),
					'format'                => 'print_pdf',
					'dir'                   => PMB_DEFAULT_DESIGNS_DIR . '/classic_digital',
					'design_form_callback'  => function () {
						return new FormSectionProper( [
							'subsections' => [
								'show_title' => new YesNoInput()
							]
						] );
					},
					'project_form_callback' => function ( Design $design ) {
						$sections = [];
						if ( $design->getPmbMeta( 'show_title' ) ) {
							$sections['title'] = new TextInput();
						}

						return new FormSectionProper( [
							'subsections' => $sections
						] );
					}
				];
			}
		);
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
								'header_content' => new CheckboxMultiInput(
									[
										'title' => __('Project Title', 'print-my-blog'),
										'subtitle' => __('Subtitle', 'print-my-blog'),
										'url' => __('Site URL', 'print-my-blog'),
										'date_printed' => __('Date Printed', 'print-my-blog'),
										'credit_pmb' => __('Credit Print My Blog', 'print-my-blog')
									],
									[
										'default' => [
											'title',
											'description',
											'url',
											'date_printed'
										],
										'html_label_text' => __('Project Header Content to Print'),
										'html_help_text' => __('Appears at the top of the first page.', 'print-my-blog')
									]
								),
								'post_content' => new CheckboxMultiInput(
									[
										'title' => __('Post Title', 'print-my-blog'),
										'id' => __('ID', 'print-my-blog'),
										'author' => __('Author', 'print-my-blog'),
										'url' => __('URL', 'print-my-blog'),
										'published_date' => __('Published Date', 'print-my-blog'),
										'categories' => __('Categories and Tags', 'print-my-blog'),
										'featured_image' => __('Featured Image', 'print-my-blog'),
										'excerpt' => __('Excerpt', 'print-my-blog'),
										'content' => __('Content', 'print-my-blog'),
										'comments' => __('Comments', 'print-my-blog'),
									],
									[
										'default' => [
											'title',
											'published_date',
											'categories',
											'featured_image',
											'content'
										],
										'html_label_text' => __('Post Content to Print'),
										'html_help_text' => __('Content from each post to print.', 'print-my-blog')
									]
								)
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