<?php
namespace PrintMyBlog\domain;


use Dompdf\Renderer\Text;
use PrintMyBlog\orm\entities\Design;
use Twine\forms\base\FormSectionDetails;
use Twine\forms\base\FormSectionProper;
use Twine\forms\inputs\AdminFileUploaderInput;
use Twine\forms\inputs\CheckboxMultiInput;
use Twine\forms\inputs\DatepickerInput;
use Twine\forms\inputs\FloatInput;
use Twine\forms\inputs\FormInputBase;
use Twine\forms\inputs\IntegerInput;
use Twine\forms\inputs\SelectInput;
use Twine\forms\inputs\TextAreaInput;
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
					'dir'                   => PMB_DESIGNS_DIR . 'pdf/print/classic',
					'url' => plugins_url('designs/pdf/print/classic', PMB_MAIN_FILE),
					'default' => 'classic_print',
					'supports' => [
						'front_matter',
						'part',
						'just_content',
						'back_matter',
					],
					'design_form_callback'  => function () {
						return $this->getDefaultDesignForm()->merge(new FormSectionProper( [
							'subsections' => [
								'links' => new FormSectionProper([
									'subsections'=> [
										'internal_links' => new SelectInput(
											[
												'remove' => __('Remove', 'print-my-blog'),
												'parens' => __('Replace with page reference', 'print-my-blog'),
												'footnote' => __('Replace with footnote', 'print-my-blog') . pmb_pro_only(),
											],
											[
												'default' => pmb_pro() ? 'footnote' : 'parens',
												'html_label_text' => __('Internal Hyperlinks', 'print-my-blog'),
												'html_help_text' => __('How to display hyperlinks to content included in this project.')
											]
										),
										'external_links' => new SelectInput(
											[
												'remove' => __('Remove', 'print-my-blog'),
												'footnote' => __('Replace with footnote', 'print-my-blog') . pmb_pro_only(),
											],
											[
												'default' => pmb_pro() ? 'footnote' : 'remove',
												'html_label_text' => __('External Hyperlinks', 'print-my-blog'),
												'html_help_text' => __('How to display hyperlinks to content not included in this project.')
											]
										)
									]
								])
							]
						] ))->merge($this->getGenericDesignForm());
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
						return $this->getDefaultDesignForm()->merge(new FormSectionProper( [
							'subsections' => [
								'image' => new FormSectionProper([
									'subsections' => [
										'image_placement' => new SelectInput([
											'default' => __('Don’t move', 'print-my-blog'),
											'snap' => __('Snap to the top or bottom of the page', 'print-my-blog'),
											'snap-unless-fit' => __('Only snap if the image would cause a page break', 'print-my-blog')
										],
										[
											'html_label_text' => __('Image Placement', 'print-my-blog'),
											'html_help_text' => __('To reduce whitespace around images, Print My Blog can move images around in your content.', 'print-my-blog'),
											'default' => 'snap-unless-fit'
										])
									]
								]),
								'links' => new FormSectionProper([
									'subsections'=> [
										'internal_links' => new SelectInput(
											[
												'remove' => __('Remove', 'print-my-blog'),
												'parens' => __('Replace with page reference', 'print-my-blog') . pmb_pro_only(),
												'footnote' => __('Replace with footnote', 'print-my-blog') . pmb_pro_only(),
											],
											[
												'default' => pmb_pro() ? 'parens' : 'remove',
												'html_label_text' => __('Internal Hyperlinks', 'print-my-blog'),
												'html_help_text' => __('How to display hyperlinks to content included in this project.')
											]
										),
										'external_links' => new SelectInput(
											[
												'remove' => __('Remove', 'print-my-blog'),
												'leave' => __('Leave as hyperlink', 'print-my-blog'),
												'footnote' => __('Replace with footnote', 'print-my-blog') . pmb_pro_only(),
											],
											[
												'default' => pmb_pro() ? 'footnote' : 'leave',
												'html_label_text' => __('External Hyperlinks', 'print-my-blog'),
												'html_help_text' => __('How to display hyperlinks to content not included in this project.')
											]
										)
									]
								])
							],
						] ))->merge($this->getGenericDesignForm());
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
						return (new FormSectionProper( [
							'subsections' =>
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
						] ))->merge($this->getGenericDesignForm());
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
										'html_help_text' => __('List of Aauthors', 'print-my-blog'),
									]
								),
								'date' => new DatepickerInput([
									'html_label_text' => __('Date Issued', 'print-my-blog'),
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
					'dir'             => PMB_DESIGNS_DIR . 'pdf/digital/mayer/',
					'default' => 'mayer',
					'supports' => [
						'front_matter',
						'part'
					],
					'url' => plugins_url('designs/pdf/digital/mayer', PMB_MAIN_FILE),
					'design_form_callback'  => function() {
						return (new FormSectionProper( [
							'subsections' => [
								'post_header_in_columns' => new YesNoInput(
									[
										'html_label_text' => __('Show Post Header inside Columns','print-my-blog'),
										'html_help_text' => __('Check this to make post header information, like title, date, author, etc, appear inside columns; uncheck this to have it take up the full page width', 'print-my-blog')
									]
								),
								'dividing_line' => new YesNoInput([
									'html_label_text' => __('Show a Dividing Line Between Posts', 'print-my-blog'),
								]),
								'images_full_column' => new YesNoInput([
									'html_label_text' => __('Full-Column Images', 'print-my-blog'),
									'html_help_text' => __('Resizes images to be the full column width (except ones with the CSS class "mayer-no-resize"', 'print-my-blog')
								]),
								'image' => new FormSectionProper([
									'subsections' => [
										'image_placement' => new SelectInput([
											'default' => __('Do Not Adjust Image Placement', 'print-my-blog'),
											'snap' => __('Snap to Page Top or Bottom', 'print-my-blog'),
											'snap-unless-fit' => __('Intelligent Snap to Page Top or Bottom', 'print-my-blog')
										],
										[
											'html_label_text' => __('Image Placement', 'print-my-blog'),
											'html_help_text' => __('To reduce whitespace around images, Print My Blog can move images around in your content.', 'print-my-blog'),
											'default' => 'snap-unless-fit'
										])
									]
								]),
							],
						] ))->merge($this->getGenericDesignForm());
					},
					'project_form_callback' => function(Design $design) {
						$sections['title'] = new TextInput(
							[
								'html_display_text' => __('Title', 'print-my-blog'),
							]
						);
						$sections['intro'] = new TextAreaInput([
							'html_display_text' => __('Introduction', 'print-my-blog'),
							'html_help_text' => __('A highlighted description of this project, shown just underneath the title.', 'print-my-blog')
						]);
						return new FormSectionProper( [
							'subsections' => $sections
						] );
					}
				];
			}
		);
	}

	public function getDefaultProjectForm(Design $design){
		$sections = [];
		$header_content = $design->getSetting('header_content');
		if(in_array('title', $header_content)){
			$sections['title'] = new TextInput();
		}
		if(in_array('subtitle', $header_content)){
			$sections['subtitle'] = new TextInput();
		}
		if(in_array('url',$header_content)){
			$sections['url'] = new TextInput(
				[
					'default' => site_url(),
					'html_label_text' => __('Source Location', 'print-my-blog'),
					'html_help_text' => __('Shown on the title page under the subtitle. Could be your website’s URL, or anything else you like.', 'print-my-blog')
				]
			);
		}
		return new FormSectionProper( [
			'subsections' => $sections
		] );
	}

	/**
	 * Gets the default design form sections for classic default designs.
	 * @return FormSectionProper
	 */
	public function getDefaultDesignForm()
	{
		return new FormSectionProper(
			[
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
								'subtitle',
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
							//'comments' => __('Comments', 'print-my-blog'),
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
					'font_size' => new TextInput(
						[
							'default' => '12pt',
							'html_label_text' => __('Font Size', 'print-my-blog'),
							'html_help_text' => sprintf(
								__('Use any recognized %1$sCSS font-size keyword%2$s (like "large", "medium", "small") or a %3$slength in any units%2$s (eg "14pt", "50%%", or "10px").'),
								'<a href="https://www.w3schools.com/cssref/pr_font_font-size.asp" target="_blank">',
								'</a>',
								'<a href="https://www.w3schools.com/cssref/css_units.asp" target="_blank">'
							)
						]
					),
					'image' => new FormSectionProper([
						'subsections' => [
							'image_size' => new IntegerInput(
								[
									'html_label_text' => __('Maximum Image Height (in pixels)', 'print-my-blog'),
									'html_help_text' => sprintf(
										__('Larger images will be resized to this, smaller images will be unchanged.', 'print-my-blog')
									),
									'default' => 500
								]
							),
						]
					]),
					'page' => new FormSectionDetails([
						'html_summary' => __('Page Options', 'print-my-blog'),
						'subsections' => [
							'page_width' => new TextInput([
								'html_label_text' => __('Page Width', 'print-my-blog'),
								'html_help_text' => sprintf(
									__('Use standard %1$sCSS units%2$s', 'print-my-blog'),
									'<a href="https://www.w3schools.com/CSSref/css_units.asp">',
									'</a>'
								),
								'default' => '210mm'
							]),
							'page_height' => new TextInput([
								'html_label_text' => __('Page Height', 'print-my-blog'),
								'html_help_text' => sprintf(
									__('Use standard %1$sCSS units%2$s', 'print-my-blog'),
									'<a href="https://www.w3schools.com/CSSref/css_units.asp">',
									'</a>'
								),
								'default' => '297mm'
							]),
						]
					])
				]
			]
		);
	}

	/**
	 * Returns generic form inputs which should usually appear on all design forms.
	 * @return FormSectionProper
	 */
	public function getGenericDesignForm()
	{
		$theme = wp_get_theme();

		return new FormSectionProper(
			[
				'subsections' => [
					'generic_sections' => new FormSectionProper(
						[
							'subsections' =>
								apply_filters(
									'PrintMyBlog\domain\DefaultDesignTemplates->getGenericDesignFormSections',
									[
//										'use_theme'  => new YesNoInput( [
//											'html_label_text' => __( 'Include Theme CSS', 'print-my-blog' ),
//											'html_help_text'  => sprintf(
//												__( '%s’s CSS may interfere with the generated file’s CSS, so it’s usually best to not include it.', 'print-my-blog' ),
//												$theme->get( 'Name' )
//											)
//										] ),
										'custom_css' => new TextAreaInput( [
											'html_label_text' => __( 'Custom CSS', 'print-my-blog' ),
											'html_help_text'  => __( 'Styles to be applied only when printing projects using this design.', 'print-my-blog' )
										] )
									]

								)
						]
					)
				]
			]
		);
	}
}