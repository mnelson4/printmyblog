<?php

namespace PrintMyBlog\domain;

use Dompdf\Renderer\Text;
use PrintMyBlog\entities\SectionTemplate;
use PrintMyBlog\orm\entities\Design;
use Twine\forms\base\FormSectionDetails;
use Twine\forms\base\FormSection;
use Twine\forms\helpers\InputOption;
use Twine\forms\inputs\AdminFileUploaderInput;
use Twine\forms\inputs\CheckboxMultiInput;
use Twine\forms\inputs\ColorInput;
use Twine\forms\inputs\DatepickerInput;
use Twine\forms\inputs\FloatInput;
use Twine\forms\inputs\FontInput;
use Twine\forms\inputs\FormInputBase;
use Twine\forms\inputs\IntegerInput;
use Twine\forms\inputs\SelectInput;
use Twine\forms\inputs\SelectRevealInput;
use Twine\forms\inputs\TextAreaInput;
use Twine\forms\inputs\TextInput;
use Twine\forms\inputs\YesNoInput;
use Twine\forms\strategies\display\TextInputDisplay;
use Twine\forms\strategies\validation\TextValidation;

class DefaultDesignTemplates
{
    public function registerDesignTemplates()
    {
        pmb_register_design_template(
            'classic_print',
            function () {
                return [
                    'title'                 => __('Classic Print PDf', 'print-my-blog'),
                    'format'                => 'print_pdf',
                    'dir'                   => PMB_DESIGNS_DIR . 'pdf/print/classic',
                    'url' => plugins_url('designs/pdf/print/classic', PMB_MAIN_FILE),
                    'default' => 'classic_print',
                    'docs' => 'https://printmy.blog/user-guide/pdf-design/classic-print-pdf-and-variations/',
                    'supports' => [
                        'front_matter',
                        'part',
                        'back_matter',
                    ],
                    'design_form_callback'  => function () {
                        return $this->getDefaultDesignForm()->merge(new FormSection([
                            'subsections' => [
                                'image' => new FormSection([
                                    'subsections' => [
                                        'image_placement' => $this->getImageSnapInput()
                                    ]
                                ]),
                                'fonts' => new FormSectionDetails([
                                    'html_summary' => __('Font Settings', 'print-my-blog'),
                                    'subsections' => [
                                        'paragraph_indent' => new YesNoInput([
                                            'default' => true,
                                            'html_label_text' => __('Paragraph Indent', 'print-my-blog'),
                                            'html_help_text' => __('Indent the first line of each new paragraph instead of adding a paragraph break.', 'print-my-blog')
                                        ]),
                                    ]
                                ]),
                                'links' => new FormSectionDetails([
                                    'html_summary' => __('Link, Page Reference, and Footnote Settings', 'print-my-blog'),
                                    'subsections' => [
                                        'internal' => new FormSection([
                                            'subsections' => [
                                                'internal_links' => new SelectRevealInput(
                                                    [
                                                        'remove' => new InputOption(__('Remove', 'print-my-blog')),
                                                        'parens' => new InputOption(__('Replace with page reference', 'print-my-blog')),
                                                        'footnote' => new InputOption(__('Replace with footnote', 'print-my-blog')),
                                                    ],
                                                    [
                                                        'default' => pmb_fs()->is_premium() ? 'footnote' : 'parens',
                                                        'html_label_text' => __('Internal Hyperlinks', 'print-my-blog') . pmb_pro_print_service_only(__('Footnotes and page references only work with Pro Print Service', 'print-my-blog')),
                                                        'html_help_text' => __('How to display hyperlinks to content included in this project.', 'print-my-blog')
                                                    ]
                                                ),
                                                'parens' => new FormSection([
                                                    'subsections' => [
                                                        'page_reference_text' => $this->getPageReferenceTextInput(),
                                                    ]
                                                ]),
                                                'footnote' => new FormSection([
                                                    'subsections' => [
                                                        'internal_footnote_text' => $this->getInternalFootnoteTextInput()
                                                    ]
                                                ])
                                            ]
                                        ]),
                                        'external' => new FormSection([
                                            'subsections' => [
                                                'external_links' => new SelectRevealInput(
                                                    [
                                                        'remove' => new InputOption(__('Remove', 'print-my-blog')),
                                                        'footnote' => new InputOption(
                                                            __('Replace with footnote', 'print-my-blog')
                                                        ),
                                                    ],
                                                    [
                                                        'default' => pmb_fs()->is_premium() ? 'footnote' : 'remove',
                                                        'html_label_text' => __('External Hyperlinks', 'print-my-blog') . pmb_pro_print_service_only(__('Footnotes require Pro', 'print-my-blog')),
                                                        'html_help_text' => __('How to display hyperlinks to content not included in this project.', 'print-my-blog')
                                                    ]
                                                ),
                                                'footnote' => new FormSection([
                                                    'subsections' => [
                                                        'footnote_text' => $this->getExternalFootnoteTextInput()
                                                    ]
                                                ])
                                            ]
                                        ])
                                    ]
                                ])
                            ]
                        ]))->merge($this->getGenericDesignForm());
                    },
                    'project_form_callback' => function (Design $design) {
                        return $this->getDefaultProjectForm($design);
                    }
                ];
            }
        );
        pmb_register_design_template(
            'classic_digital',
            function () {
                return [
                    'title'           => __('Classic Digital PDF'),
                    'format'          => 'digital_pdf',
                    'default' => 'classic_digital',
                    'dir'             => PMB_DESIGNS_DIR . 'pdf/digital/classic/',
                    'url' => plugins_url('designs/pdf/digital/classic', PMB_MAIN_FILE),
                    'docs' => 'https://printmy.blog/user-guide/pdf-design/classic-digital-pdf-settings/',
                    'supports' => [
                        'front_matter',
                        'back_matter',
                        'part',
                    ],
                    'design_form_callback'  => function () {
                        return $this->getDefaultDesignForm()->merge(new FormSection([
                            'subsections' => [
                                'image' => new FormSection([
                                    'subsections' => [
                                        'image_placement' => $this->getImageSnapInput()
                                    ]
                                ]),
                                'links' => new FormSectionDetails([
                                    'html_summary' => __('Link, Page Reference, and Footnote Settings', 'print-my-blog'),
                                    'subsections' => [

                                        'internal' => new FormSection([
                                            'subsections' => [
                                                'internal_links' => new SelectRevealInput(
                                                    [
                                                        'remove' => new InputOption(
                                                            __('Remove', 'print-my-blog')
                                                        ),
                                                        'leave' => new InputOption(
                                                            __('Leave as hyperlink', 'print-my-blog')
                                                        ),
                                                        'parens' => new InputOption(
                                                            __('Replace with page reference', 'print-my-blog')
                                                        ),
                                                        'footnote' => new InputOption(
                                                            __('Replace with footnote', 'print-my-blog')
                                                        ),
                                                    ],
                                                    [
                                                        'default' => pmb_fs()->is_premium() ? 'parens' : 'remove',
                                                        'html_label_text' => __('Internal Hyperlinks', 'print-my-blog') . pmb_pro_print_service_best(__('Footnotes and page references only work with Pro Print Service', 'print-my-blog')),
                                                        'html_help_text' => __(
                                                            'How to display hyperlinks to content included in this project.',
                                                            'print-my-blog'
                                                        )
                                                    ]
                                                ),
                                                'parens' => new FormSection([
                                                    'subsections' => [
                                                        'page_reference_text' => $this->getPageReferenceTextInput(),
                                                    ]
                                                ]),
                                                'footnote' => new FormSection([
                                                    'subsections' => [
                                                        'internal_footnote_text' => $this->getInternalFootnoteTextInput()
                                                    ]
                                                ])
                                            ]
                                        ]),
                                        'external' => new FormSection([
                                            'subsections' => [
                                                'external_links' => new SelectRevealInput(
                                                    [
                                                        'remove' => new InputOption(__('Remove', 'print-my-blog')),
                                                        'leave' => new InputOption(__('Leave as hyperlink', 'print-my-blog')),
                                                        'footnote' => new InputOption(
                                                            __('Replace with footnote', 'print-my-blog')
                                                        ),
                                                    ],
                                                    [
                                                        'default' => pmb_fs()->is_premium() ? 'footnote' : 'leave',
                                                        'html_label_text' => __('External Hyperlinks', 'print-my-blog') . pmb_pro_print_service_best(__('Footnotes require Pro', 'print-my-blog')),
                                                        'html_help_text' => __(
                                                            'How to display hyperlinks to content not included in this project.',
                                                            'print-my-blog'
                                                        )
                                                    ]
                                                ),
                                                'footnote' => new FormSection([
                                                    'subsections' => [
                                                        'footnote_text' => $this->getExternalFootnoteTextInput()
                                                    ]
                                                ])
                                            ]
                                        ])
                                    ]
                                ])
                            ],
                        ]))->merge($this->getGenericDesignForm());
                    },
                    'project_form_callback' => function (Design $design) {
                        return $this->getDefaultProjectForm($design);
                    }
                ];
            }
        );
        pmb_register_design_template(
            'buurma',
            function () {
                return [
                    'title'           => __('Buurma Digital PDF'),
                    'format'          => 'digital_pdf',
                    'dir'             => PMB_DESIGNS_DIR . 'pdf/digital/buurma/',
                    'default' => 'buurma',
                    'url' => plugins_url('designs/pdf/digital/buurma', PMB_MAIN_FILE),
                    'docs' => 'https://printmy.blog/user-guide/pdf-design/buurma-whitepaper-digital-pdf/',
                    'supports' => [
                        'front_matter',
                        'back_matter',
                    ],
                    'design_form_callback'  => function () {
                        return (new FormSection([
                            'subsections' =>
                                [
                                    'title_page_banner_color' => new ColorInput(
                                        [
                                            'html_label_text' => __('Title Page Top-Banner Color', 'print-my-blog'),
                                            'html_help_text' => __('Image used at the top of the background on the title page.', 'print-my-blog'),
                                            'default' => '#02a5fd'
                                        ]
                                    ),
                                    'background_color' => new ColorInput(
                                        [
                                            'html_label_text' => __('Background Color', 'print-my-blog'),
                                            'html_help_text' => __(
                                                'A gradient between this color and white will be used in the page backgrounds',
                                                'print-my-blog'
                                            ),
                                            'default' => '#82d7ff'
                                        ]
                                    ),
                                    'org' => new TextInput([
                                        'html_label_text' => __('Organization Name', 'print-my-blog'),
                                        'html_help_text' => __(
                                            'Shown in the title page. Eg "Institute of Print My Blog"',
                                            'print-my-blog'
                                        ),
                                    ]),
                                    'background_embellishment' => new AdminFileUploaderInput(
                                        [
                                            'html_label_text' => __('Background Embellishment', 'print-my-blog'),
                                            'html_help_text' => __('Faded image used as a full-page background on the title page, and next to the page number on numbered pages.', 'print-my-blog'),
                                            'default' => plugins_url('designs/pdf/digital/buurma/assets/logo.svg', PMB_MAIN_FILE)
                                            ]
                                    ),
                                    'default_alignment' => $this->getDefaultAlignmentInput(),
                                    'internal_footnote_text' => $this->getInternalFootnoteTextInput(),
                                    'footnote_text' => $this->getExternalFootnoteTextInput()
                                ],
                        ]))->merge($this->getGenericDesignForm());
                    },
                    'project_form_callback' => function (Design $design) {
                        return new FormSection([
                            'subsections' => [
                                'issue' => new TextInput(
                                    [
                                        'html_label_text' => __('Issue', 'print-my-blog'),
                                        'html_help_text' => __('Text that appears at the top-right of the cover'),
                                    ]
                                ),
                                'byline' => new TextAreaInput(
                                    [
                                        'html_label_text' => __('ByLine', 'print-my-blog'),
                                        'html_help_text' => __('Project Author(s)', 'print-my-blog'),
                                    ]
                                ),
                                'date' => new DatepickerInput([
                                    'html_label_text' => __('Date Issued', 'print-my-blog'),
                                    'html_help_text' => __('Text that appears under the byline', 'print-my-blog'),
                                ]),
                                'cover_preamble' => new TextAreaInput(
                                    [
                                        'html_label_text' => __('Coverpage Preamble', 'print-my-blog'),
                                        'html_help_text' => __('Explanatory text that appears at the bottom of the cover page', 'print-my-blog')
                                        ]
                                )
                            ]
                        ]);
                    }
                ];
            }
        );
        pmb_register_design_template(
            'mayer',
            function () {
                return [
                    'title'           => __('Mayer Digital PDF'),
                    'format'          => 'digital_pdf',
                    'dir'             => PMB_DESIGNS_DIR . 'pdf/digital/mayer/',
                    'default' => 'mayer',
                    'docs' => 'https://printmy.blog/user-guide/pdf-design/mayer-magazine-digital-pdf/',
                    'supports' => [
                        'front_matter',
                        'part',
                        'back_matter',
                    ],
                    'url' => plugins_url('designs/pdf/digital/mayer', PMB_MAIN_FILE),
                    'design_form_callback'  => function () {
                        return (new FormSection([
                            'subsections' => [
                                'page_per_post' => new YesNoInput(
                                    [
                                        'default' => false,
                                        'html_label_text' => __('Each Post Begins on a New Page', 'print-my-blog'),
                                        'html_help_text' => __('Whether to force posts to always start on a new page. Doing so makes the page more legible, but uses more paper.', 'print-my-blog'),
                                        ]
                                ),
                                'post_header_in_columns' => new YesNoInput(
                                    [
                                        'html_label_text' => __('Show Post Header inside Columns', 'print-my-blog'),
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
                                'no_extra_columns' => new YesNoInput([
                                    'html_label_text' => __('Remove Extra Columns', 'print-my-blog'),
                                    'default' => true,
                                    'html_help_text' => __('Forces your content to only use two columns, even if the content itself was divided into more columns (eg using the "Columns" block)', 'print-my-blog')
                                    ]),
                                'image' => new FormSection([
                                    'subsections' => [
                                        'image_placement' => $this->getImageSnapInput()
                                    ]
                                ]),
                            ],
                        ]))->merge($this->getGenericDesignForm());
                    },
                    'project_form_callback' => function (Design $design) {
                        $sections['byline'] = new TextInput([
                            'html_display_text' => __('Byline', 'print-my-blog'),
                            'html_help_text' => __('Project author(s)', 'print-my-blog')
                        ]);
                        $sections['cover_preamble'] = new TextAreaInput([
                            'html_label_text' => __('Coverpage Preamble', 'print-my-blog'),
                            'html_help_text' => __(
                                'Explanatory text that appears at the bottom of the cover page',
                                'print-my-blog'
                            )
                        ]);
                        return new FormSection([
                            'subsections' => $sections
                        ]);
                    }
                ];
            }
        );
    }

    public function getDefaultProjectForm(Design $design)
    {
        $sections = [];
        $header_content = $design->getSetting('header_content');
        if (in_array('subtitle', $header_content)) {
            $sections['subtitle'] = new TextInput([
                'html_label_text' => __('Subtitle', 'print-my-blog')
            ]);
        }
        if (in_array('byline', $header_content)) {
            $sections['byline'] = new TextInput([
                'html_label_text' => __('Byline', 'print-my-blog'),
                'html_help_text' => __('Project author(s)', 'print-my-blog')
            ]);
        }
        if (in_array('url', $header_content)) {
            $sections['url'] = new TextInput([
                    'default' => site_url(),
                    'html_label_text' => __('Source Location', 'print-my-blog'),
                    'html_help_text' => __('Shown on the title page under the subtitle. Could be your website’s URL, or anything else you like.', 'print-my-blog')
                    ]);
        }
        return new FormSection([
            'subsections' => $sections
        ]);
    }

    /**
     * Gets the default design form sections for classic default designs.
     * @return FormSection
     */
    public function getDefaultDesignForm()
    {
        return new FormSection(
            [
                'subsections' => [
                    'header_content' => new CheckboxMultiInput(
                        [
                            'title' => new InputOption(__('Project Title', 'print-my-blog')),
                            'subtitle' => new InputOption(__('Subtitle', 'print-my-blog')),
                            'byline' => new InputOption(__('Byline', 'print-my-blog')),
                            'url' => new InputOption(__('Site URL', 'print-my-blog')),
                            'date_printed' => new InputOption(__('Date Printed', 'print-my-blog')),
                        ],
                        [
                            'default' => [
                                'title',
                                'subtitle',
                                'url',
                                'date_printed'
                            ],
                            'html_label_text' => __('Title Page Content'),
                        ]
                    ),
                    'post_content' => new CheckboxMultiInput(
                        [
                            'title' => new InputOption(__('Post Title', 'print-my-blog')),
                            'id' => new InputOption(__('ID', 'print-my-blog')),
                            'author' => new InputOption(__('Author', 'print-my-blog')),
                            'published_date' => new InputOption(__('Published Date', 'print-my-blog')),
                            'categories' => new InputOption(__('Categories and Tags', 'print-my-blog')),
                            'url' => new InputOption(__('URL', 'print-my-blog')),


                            'featured_image' => new InputOption(__('Featured Image', 'print-my-blog')),
                            'excerpt' => new InputOption(__('Excerpt', 'print-my-blog')),
                            'meta' => new InputOption(__('Custom Fields', 'print-my-blog')),
                            'content' => new InputOption(__('Content', 'print-my-blog')),
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
                            'html_label_text' => __('Post Content'),
                            'html_help_text' => __('Content from each post to print.', 'print-my-blog')
                        ]
                    ),
                    'page_per_post' => new YesNoInput(
                        [
                            'default' => true,
                            'html_label_text' => __('Each Post Begins on a New Page', 'print-my-blog'),
                            'html_help_text' => __('Whether to force posts to always start on a new page. Doing so makes the page more legible, but uses more paper.', 'print-my-blog'),
                            ]
                    ),
                    'fonts' => new FormSectionDetails([
                        'html_summary' => __('Font Settings', 'print-my-blog'),
                        'subsections' => [
                            'main_header_font_size' => new TextInput(
                                [
                                    'default' => '4em',
                                    'html_label_text' => __('Title page and Part Header Font Size', 'print-my-blog'),
                                    'html_help_text' => sprintf(
                                        __('Font size used for the default title page’s and part’s header (all other headers’ sizes are derived from the main font size). Use any recognized %1$sCSS font-size keyword%2$s (like "large", "medium", "small") or a %3$slength in any units%2$s (eg "14pt", "50%%", or "10px").'),
                                        '<a href="https://www.w3schools.com/cssref/pr_font_font-size.asp" target="_blank">',
                                        '</a>',
                                        '<a href="https://www.w3schools.com/cssref/css_units.asp" target="_blank">'
                                    )
                                ]
                            ),
                            'header_font_style' => new FontInput([
                                'default' => 'arial',
                                'html_label_text' => __('Header Font', 'print-my-blog'),
                                'html_help_text' => __('Default font for header tags', 'print-my-blog')
                            ]),
                            'font_size' => new TextInput(
                                [
                                    'default' => '10pt',
                                    'html_label_text' => __('Font Size', 'print-my-blog'),
                                    'html_help_text' => sprintf(
                                        __('Use any recognized %1$sCSS font-size keyword%2$s (like "large", "medium", "small") or a %3$slength in any units%2$s (eg "14pt", "50%%", or "10px").'),
                                        '<a href="https://www.w3schools.com/cssref/pr_font_font-size.asp" target="_blank">',
                                        '</a>',
                                        '<a href="https://www.w3schools.com/cssref/css_units.asp" target="_blank">'
                                    )
                                ]
                            ),
                            'font_style' => new FontInput([
                                'default' => 'times new roman',
                                'html_label_text' => __('Font', 'print-my-blog'),
                                'html_help_text' => __('Default font used in paragraphs, bulleted lists, tables, etc.')
                            ]),
                        ]
                    ]),
                    'image' => new FormSectionDetails([
                        'html_summary' => __('Image Settings', 'print-my-blog'),
                        'subsections' => [
                            'image_size' => new IntegerInput(
                                [
                                    'html_label_text' => __('Maximum Image Height (in pixels)', 'print-my-blog'),
                                    'html_help_text' => sprintf(
                                        __(
                                            'Larger images will be resized to this, smaller images will be unchanged.',
                                            'print-my-blog'
                                        )
                                    ),
                                    'default' => 500
                                ]
                            ),
                            'default_alignment' => $this->getDefaultAlignmentInput(),
                        ]
                    ]),
                    'page' => new FormSectionDetails([
                        'html_summary' => __('Page Settings', 'print-my-blog'),
                        'subsections' => [
                            'page_width' => new TextInput([
                                'html_label_text' => __('Page Width', 'print-my-blog') . pmb_pro_print_service_best(__('Not supported by some browsers', 'print-my-blog')),
                                'html_help_text' => sprintf(
                                    __('Use standard %1$sCSS units%2$s', 'print-my-blog'),
                                    '<a href="https://www.w3schools.com/CSSref/css_units.asp">',
                                    '</a>'
                                ),
                                'default' => '8.5in'
                            ]),
                            'page_height' => new TextInput([
                                'html_label_text' => __('Page Height', 'print-my-blog') . pmb_pro_print_service_best(__('Not supported by some browsers', 'print-my-blog')),
                                'html_help_text' => sprintf(
                                    __('Use standard %1$sCSS units%2$s', 'print-my-blog'),
                                    '<a href="https://www.w3schools.com/CSSref/css_units.asp">',
                                    '</a>'
                                ),
                                'default' => '11in'
                            ]),
                        ]
                    ])
                ]
            ]
        );
    }

    /**
     * Returns generic form inputs which should usually appear on all design forms.
     * @return FormSection
     */
    public function getGenericDesignForm()
    {
        $theme = wp_get_theme();
        $use_theme_help_text = sprintf(
            __('Your theme, "%1$s", can be used in conjunction with your design. Themes are often not intended for print and can conflict with the design, but you may have content that looks broken without the theme.', 'print-my-blog'),
            $theme->name
        );
        if (! pmb_fs()->is_plan__premium_only('business')) {
            $use_theme_help_text = __('Note: this option is only supported for the business license.', 'print-my-blog') . '<br>' . $use_theme_help_text;
        }
        if (pmb_fs()->is_plan__premium_only('hobby')) {
            $powered_by_in_pro_service = false;
        } else {
            $powered_by_in_pro_service = true;
        }
        return new FormSection(
            [
                'subsections' => [
                    'generic_sections' => new FormSection([
                        'subsections' =>
                            apply_filters(
                                'PrintMyBlog\domain\DefaultDesignTemplates->getGenericDesignFormSections',
                                [
                                    'use_theme' => new YesNoInput([
                                        'html_label_text' => __('Apply Website Theme', 'print-my-blog'),
                                        'html_help_text' => $use_theme_help_text,
                                        'default' => false
                                    ]),
                                    'custom_css' => new TextAreaInput([
                                        'html_label_text' => __('Custom CSS', 'print-my-blog'),
                                        'html_help_text'  => __('Styles to be applied only when printing projects using this design.', 'print-my-blog')
                                        ]),
                                    'powered_by' => new YesNoInput([
                                        'html_label_text' => __('Add "Powered By" when using Pro Print Service', 'print-my-blog') . ( $powered_by_in_pro_service ? pmb_hover_help(__('Only added when using the Pro Print Service for Free and Hobbyist users. In compliance with WordPress.org guidelines, not added when printing from your browser.', 'print-my-blog')) : ''),
                                        'html_help_text' => __('Instructs the Pro Print Service to add "Powered by Print My Blog Pro & WordPress" in the footer of the first page. Does not appear when printing using your browser.', 'print-my-blog'),
                                        'default' => $powered_by_in_pro_service,
                                        'disabled' => $powered_by_in_pro_service
                                    ])
                                ]
                            )
                    ])
                ]
            ]
        );
    }

    public function getPageReferenceTextInput()
    {
        return new TextInput([
                    'html_label_text' => __('Page Reference Text', 'print-my-blog'),
                    'html_help_text' => __('Text to use when replacing a hyperlink with a page reference. "%s" will be replaced with the page number.', 'print-my-blog'),
                    'default' => __('(see page %s)', 'print-my-blog'),
                    'validation_strategies' => [
                        new TextValidation(__('You must include "%s" in the page reference text so we know where to put the page number.', 'print-my-blog'), '~.*\%s.*~')
                    ]
                ]);
    }

    public function getInternalFootnoteTextInput()
    {
        return new TextInput([
            'html_label_text' => __('Internal Footnote Text', 'print-my-blog'),
            'html_help_text' => __('Text to use when replacing a hyperlink with a footnote. "%s" will be replaced with the page number.', 'print-my-blog'),
            'default' => __('See page %s.', 'print-my-blog'),
            'validation_strategies' => [
                new TextValidation(__('You must include "%s" in the footnote text so we know where to put the URL.', 'print-my-blog'), '~.*\%s.*~')
            ]
        ]);
    }


    public function getExternalFootnoteTextInput()
    {
        return new TextInput([
            'html_label_text' => __('External Footnote Text', 'print-my-blog'),
            'html_help_text' => __('Text to use when replacing a hyperlink with a footnote. "%s" will be replaced with the URL', 'print-my-blog'),
            'default' => __('See %s.', 'print-my-blog'),
            'validation_strategies' => [
                new TextValidation(__('You must include "%s" in the footnote text so we know where to put the URL.', 'print-my-blog'), '~.*\%s.*~')
            ]
        ]);
    }

    /**
     * @return SelectInput
     */
    public function getDefaultAlignmentInput()
    {
        return new SelectInput(
            [
                'none' => new InputOption(__('None', 'print-my-blog')),
                'center' => new InputOption(__('Center', 'print-my-blog'))
            ],
            [
                'html_label_text' => __('Default Image Alignment', 'print-my-blog'),
                'html_help_text' => __('Images normally default to "no alignment", which can look jumbled in printouts. Usually it’s best to automatically switch those to align to the center.', 'print-my-blog'),
                'default' => 'center'
            ]
        );
    }

    public function getImageSnapInput()
    {
        return new SelectInput(
            [
                'default' => new InputOption(__('Don’t move', 'print-my-blog')),
                'snap' => new InputOption(__('Snap to the top or bottom of the page', 'print-my-blog')),
                'snap-unless-fit' => new InputOption(__('Only snap if it would otherwise cause a page break', 'print-my-blog'))
                ],
            [
                // translators: 1: "Best with Pro"
                'html_label_text' => __('Image Placement', 'print-my-blog') . pmb_pro_print_service_only(),
                'html_help_text' => __('To reduce whitespace around images, galleries, and tables, Print My Blog can move around in your content.', 'print-my-blog'),
                'default' => 'snap-unless-fit'
            ]
        );
    }
}
