<?php
namespace PrintMyBlog\domain;


use Dompdf\Renderer\Text;
use PrintMyBlog\orm\entities\Design;
use Twine\forms\base\FormSectionProper;
use Twine\forms\inputs\AdminFileUploaderInput;
use Twine\forms\inputs\CheckboxMultiInput;
use Twine\forms\inputs\DatepickerInput;
use Twine\forms\inputs\FormInputBase;
use Twine\forms\inputs\SelectInput;
use Twine\forms\inputs\TextAreaInput;
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
					'dir'                   => PMB_DESIGNS_DIR . '/pdf/print/classic',
					'url' => plugins_url('designs/print_pdf/classic', PMB_MAIN_FILE),
					'default' => 'classic_print',
					'levels' => 2,
					'design_form_callback'  => function () {
						$sections = array_merge(
							$this->getDefaultDesignFormSections(),
							$this->getGenericDesignFormSections()
						);
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
						return $this->getDefaultProjectForm($design);
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
					'default' => 'classic_digital',
					'dir'             => PMB_DESIGNS_DIR . 'pdf/digital/classic/',
					'url' => plugins_url('designs/pdf/digital/classic', PMB_MAIN_FILE),
					'supports' => [
						'front_matter',
						'back_matter',
						'just_content',
						'part',
					],
					'design_form_callback'  => function() {
						return new FormSectionProper( [
							'subsections' => array_merge(
								$this->getDefaultDesignFormSections(),
								$this->getGenericDesignFormSections()
							),
						] );
					},
					'project_form_callback' => function(Design $design) {
						return $this->getDefaultProjectForm($design);
					}
				];
			}
		);
		pmb_register_design_template(
			'buurma',
			function() {
				return [
					'title'           => __( 'Buurma Digital PDF' ),
					'format'          => 'digital_pdf',
					'dir'             => PMB_DESIGNS_DIR . 'pdf/digital/buurma/',
					'default' => 'buurma',
					'url' => plugins_url('designs/pdf/digital/buurma', PMB_MAIN_FILE),
					'supports' => [
						'front_matter',
						'back_matter',
						'just_content',
					],
					'design_form_callback'  => function() {
						return new FormSectionProper( [
							'subsections' => array_merge(
								[
									'title_page_banner' => new AdminFileUploaderInput(
										[
											'html_label_text' => __('Title Page Top-Banner', 'print-my-blog'),
											'html_help_text' => __('Image used as the top of the background on the title page.', 'print-my-blog'),
											'default' => plugins_url('designs/pdf/digital/buurma/assets/banner.png', PMB_MAIN_FILE)
										]
									),
									'org' => new TextInput([
										'html_label_text' => __('Organization Name', 'print-my-blog'),
										'html_help_text' => __('Shown in the title page. Eg "Institute of Print My Blog"'),
									]),
									'background_embellishment' => new AdminFileUploaderInput(
										[
											'html_label_text' => __('Background Embellishment', 'print-my-blog'),
											'html_help_text' => __('Faded image used as a full-page background on the title page, and next to the page number on numbered pages.', 'print-my-blog'),
											'default' => plugins_url('designs/pdf/digital/buurma/assets/logo.svg', PMB_MAIN_FILE)
										]
									),
								],
								$this->getGenericDesignFormSections()
							),
						] );
					},
					'project_form_callback' => function(Design $design) {
						return new FormSectionProper( [
							'subsections' => [
								'issue' => new TextInput(
									[
										'html_label_text' => __('Issue', 'print-my-blog'),
										'html_help_text' => __('Text that appears at the top-right of the cover'),
									]
								),
								'title' => new TextInput(
									[
										'html_label_text' => __('Title', 'print-my-blog'),
									]
								),
								'byline' => new TextAreaInput(
									[
										'html_label_text' => __('By Line', 'print-my-blog'),
										'html_help_text' => __('List of authors', 'print-my-blog'),
									]
								),
								'date' => new DatepickerInput([
									'html_label_text' => __('Date issued', 'print-my-blog'),
									'html_help_text' => __('Text that appears under the byline', 'print-my-blog'),
								]),
								'cover_preamble' => new TextAreaInput(
									[
										'html_label_text' => __('Coverpage Preamble', 'print-my-blog'),
										'html_help_text' => __('Explanatory text that appears at the bottom of the cover page','print-my-blog')
									]
								)
							]
						] );
					}
				];
			}
		);
		pmb_register_design_template(
			'mayer',
			function() {
				return [
					'title'           => __( 'Mayer Digital PDF' ),
					'format'          => 'digital_pdf',
					'dir'             => PMB_DESIGNS_DIR . 'classic_digital/',
					'default' => 'mayer',
					'levels' => 2,
					'design_form_callback'  => function() {
						$sections = array_merge(
							[
								'post_header_in_columns' => new YesNoInput(
									[
										'html_label_text' => __('Show Post Header inside Columns','print-my-blog'),
										'html_help_text' => __('Check this to make post header information, like title, date, author, etc, appear inside columns; uncheck this to have it take up the full page width', 'print-my-blog')
									]
								)
							],
							$this->getDefaultDesignFormSections(),
							$this->getGenericDesignFormSections()
						);
						// all images will take up the full column width
						unset($sections['image_size']);
						return new FormSectionProper( [
							'subsections' => $sections,
						] );
					},
					'project_form_callback' => function(Design $design) {
						return $this->getDefaultProjectForm($design);
					}
				];
			}
		);
	}

	public function getDefaultProjectForm(Design $design){
		$sections = [];
		$header_content = $design->getPmbMeta('header_content');
		if(in_array('title', $header_content)){
			$sections['title'] = new TextInput();
		}
		if(in_array('subtitle', $header_content)){
			$sections['subtitle'] = new TextInput();
		}
		return new FormSectionProper( [
			'subsections' => $sections
		] );
	}

	/**
	 * Gets the default design form sections for classic default designs.
	 * @return FormInputBase[]
	 */
	public function getDefaultDesignFormSections()
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

	/**
	 * Returns generic form inputs which should usually appear on all design forms.
	 * @return FormSectionBase[]
	 */
	public function getGenericDesignFormSections()
	{
		$theme = wp_get_theme();

		return apply_filters(
			'PrintMyBlog\domain\DefaultDesignTemplates->getGenericDesignFormSections',
			[
				'use_theme' => new YesNoInput([
					'html_label_text' => __('Include Theme CSS', 'print-my-blog'),
					'html_help_text' => sprintf(
						__('%s’s CSS may interfere with the generated file’s CSS, so it’s usually best to not include it.', 'print-my-blog'),
						$theme->get('Name')
					)
				]),
				'custom_css' => new TextAreaInput([
					'html_label_text' => __('Custom CSS', 'print-my-blog'),
					'html_help_text' => __('Styles to be applied only when printing projects using this design.', 'print-my-blog')
				])
			]
		);
	}
}