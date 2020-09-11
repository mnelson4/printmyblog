<?php
namespace PrintMyBlog\domain;


use PrintMyBlog\orm\entities\Design;
use Twine\forms\base\FormSectionProper;
use Twine\forms\inputs\CheckboxMultiInput;
use Twine\forms\inputs\FormInputBase;
use Twine\forms\inputs\SelectInput;
use Twine\forms\inputs\TextInput;
use Twine\forms\inputs\YesNoInput;

class DefaultDesignTemplates {
	public function registerDesignTemplates()
	{
		$default_design_form_sections = $this->getDefaultDesignFormSections();
		pmb_register_design_template(
			'classic_print',
			function() {
				return [
					'title'                 => __( 'Classic Print PDf', 'print-my-blog' ),
					'format'                => 'print_pdf',
					'dir'                   => PMB_DEFAULT_DESIGNS_DIR . '/classic_digital',
					'design_form_callback'  => function () {
						$sections = $this->getDefaultDesignFormSections();
						$sections['internal_links'] = new SelectInput(
							[
								'remove' => __('Remove', 'print-my-blog'),
								'parens' => __('Replace with URL in parentheses', 'print-my-blog'),
								'page_ref' => __('Replace with inline page reference', 'print-my-blog') . pmb_pro_only(),
								'footnote' => __('Replace with footnote', 'print-my-blog') . pmb_pro_only(),
							],
							[
								'default' => pmb_pro() ? 'footnote' : 'remove',
								'html_label_text' => __('Internal Hyperlinks', 'print-my-blog'),
								'html_help_text' => __('How to display hyperlinks to content included in this project.')
							]
						);
						$sections['external_links'] = new SelectInput(
							[
								'remove' => __('Remove', 'print-my-blog'),
								'parens' => __('Replace with URL in parentheses', 'print-my-blog'),
								'footnote' => __('Replace with footnote', 'print-my-blog') . pmb_pro_only(),
							],
							[
								'default' => pmb_pro() ? 'footnote' : 'remove',
								'html_label_text' => __('External Hyperlinks', 'print-my-blog'),
								'html_help_text' => __('How to display hyperlinks to content not included in this project.')
							]
						);
						return new FormSectionProper( [
							'subsections' => $sections
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
							'subsections' => $this->getDefaultDesignFormSections()
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

	/**
	 * Gets the default design form sections for classic default designs.
	 * @return FormInputBase[]
	 */
	protected function getDefaultDesignFormSections()
	{
		return [
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
			),
			'page_per_post' => new YesNoInput(
				[
					'default' => true,
					'html_label_text' => __('Each Post Begins on a New Page', 'print-my-blog'),
					'html_help_text' => __('Whether to force posts to always start on a new page. Doing so makes the page more legible, but uses more paper.','print-my-blog'),
				]
			),
			'columns' => new SelectInput(
				[
					1 => '1',
					2 => '2',
					3 => '3'
				],
				[
					'default' => 1,
					'html_label_text' => __('The number of columns of text on each page.', 'print-my-blog')
				]
			),
			'font_size' => new SelectInput(
				[
					'tiny' => __('Tiny', 'print-my-blog'),
					'small' => __('Small', 'print-my-blog'),
					'normal' => __('Normal', 'print-my-blog'),
					'large' => __('Large', 'print-my-blog')
				],
				[
					'default' => 'normal',
					'html_label_text' => __('Font Size', 'print-my-blog'),
				]
			),
			'image_size' => new SelectInput(
				[
					'none' => __('None (hide images)', 'print-my-blog'),
					'small' => __('Small (1/4 size)', 'print-my-blog'),
					'medium' => __('Medium (1/2 size)', 'print-my-blog'),
					'large' => __('Large (3/4 size)', 'print-my-blog'),
					'full' => __('Full (theme default)', 'print-my-blog')
				]
			)
		];
	}
}