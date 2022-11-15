<?php

namespace PrintMyBlog\domain;

use Dompdf\Renderer\Text;
use PrintMyBlog\entities\SectionTemplate;
use PrintMyBlog\helpers\ImageHelper;
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
use Twine\forms\inputs\WysiwygInput;
use Twine\forms\inputs\YesNoInput;
use Twine\forms\strategies\display\TextInputDisplay;
use Twine\forms\strategies\validation\FullHtmlValidation;
use Twine\forms\strategies\validation\TextValidation;

/**
 * Class DefaultDesignTemplates
 * @package PrintMyBlog\domain
 */
class DefaultDesignTemplates
{

    /**
     * @var ImageHelper
     */
    private $image_helper;

    /**
     * @param ImageHelper $image_helper
     */
    public function inject(ImageHelper $image_helper)
    {
        $this->image_helper = $image_helper;
    }

    /**
     * Registers design templates.
     */
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
                        return $this->getDefaultDesignForm()->merge($this->getDefaultPdfDesignForm())->merge(
                            new FormSection(
                                [
                                    'subsections' => [
                                        'image' => new FormSection(
                                            [
                                                'subsections' => $this->getImageSnapInputs(),
                                            ]
                                        ),
                                        'fonts' => new FormSectionDetails(
                                            [
                                                'html_summary' => __('Font Settings', 'print-my-blog'),
                                                'subsections' => [
                                                    'paragraph_indent' => new YesNoInput(
                                                        [
                                                            'default' => true,
                                                            'html_label_text' => __('Paragraph Indent', 'print-my-blog'),
                                                            'html_help_text' => __('Indent the first line of each new paragraph instead of adding a paragraph break.', 'print-my-blog'),
                                                        ]
                                                    ),
                                                ],
                                            ]
                                        ),
                                        'links' => new FormSectionDetails(
                                            [
                                                'html_summary' => __('Link, Page Reference, and Footnote Settings', 'print-my-blog'),
                                                'subsections' => [
                                                    'internal' => new FormSection(
                                                        [
                                                            'subsections' => [
                                                                'internal_links' => new SelectRevealInput(
                                                                    [
                                                                        'remove' => new InputOption(__('Remove', 'print-my-blog')),
                                                                        'parens' => new InputOption(__('Replace with page reference', 'print-my-blog')),
                                                                        'footnote' => new InputOption(__('Replace with footnote', 'print-my-blog')),
                                                                    ],
                                                                    [
                                                                        'default' => pmb_fs()->is_premium() ? 'footnote' : 'parens',
                                                                        'html_label_text' => __('Internal Hyperlinks', 'print-my-blog') . pmb_pro_print_service_only(__('Footnotes and page references only work with Pro PDF Service', 'print-my-blog')),
                                                                        'html_help_text' => __('How to display hyperlinks to content included in this project.', 'print-my-blog'),
                                                                    ]
                                                                ),
                                                                'parens' => new FormSection(
                                                                    [
                                                                        'subsections' => [
                                                                            'page_reference_text' => $this->getPageReferenceTextInput(),
                                                                        ],
                                                                    ]
                                                                ),
                                                                'footnote' => new FormSection(
                                                                    [
                                                                        'subsections' => [
                                                                            'internal_footnote_text' => $this->getInternalFootnoteTextInput(),
                                                                        ],
                                                                    ]
                                                                ),
                                                            ],
                                                        ]
                                                    ),
                                                    'external' => new FormSection(
                                                        [
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
                                                                        'html_help_text' => __('How to display hyperlinks to content not included in this project.', 'print-my-blog'),
                                                                    ]
                                                                ),
                                                                'footnote' => new FormSection(
                                                                    [
                                                                        'subsections' => [
                                                                            'footnote_text' => $this->getExternalFootnoteTextInput(),
                                                                        ],
                                                                    ]
                                                                ),
                                                            ],
                                                        ]
                                                    ),
                                                ],
                                            ]
                                        ),
                                    ],
                                ]
                            )
                        )->merge($this->getGenericDesignForm());
                    },
                    'project_form_callback' => function (Design $design) {
                        return $this->getDefaultProjectForm($design);
                    },
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
                        return $this->getDefaultDesignForm()->merge($this->getDefaultPdfDesignForm())->merge(
                            new FormSection(
                                [
                                    'subsections' => [
                                        'image' => new FormSection(
                                            [
                                                'subsections' => $this->getImageSnapInputs(),
                                            ]
                                        ),
                                        'links' => $this->getLinksInputs(),
                                    ],
                                ]
                            )
                        )->merge($this->getGenericDesignForm());
                    },
                    'project_form_callback' => function (Design $design) {
                        return $this->getDefaultProjectForm($design);
                    },
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
                        return (new FormSection(
                            [
                                'subsections' =>
                                [
                                    'title_page_banner_color' => new ColorInput(
                                        [
                                            'html_label_text' => __('Title Page Top-Banner Color', 'print-my-blog'),
                                            'html_help_text' => __('Image used at the top of the background on the title page.', 'print-my-blog'),
                                            'default' => '#02a5fd',
                                        ]
                                    ),
                                    'background_color' => new ColorInput(
                                        [
                                            'html_label_text' => __('Background Color', 'print-my-blog'),
                                            'html_help_text' => __(
                                                'A gradient between this color and white will be used in the page backgrounds',
                                                'print-my-blog'
                                            ),
                                            'default' => '#82d7ff',
                                        ]
                                    ),
                                    'org' => new TextInput(
                                        [
                                            'html_label_text' => __('Organization Name', 'print-my-blog'),
                                            'html_help_text' => __(
                                                'Shown in the title page. Eg "Institute of Print My Blog"',
                                                'print-my-blog'
                                            ),
                                        ]
                                    ),
                                    'background_embellishment' => new AdminFileUploaderInput(
                                        [
                                            'html_label_text' => __('Background Embellishment', 'print-my-blog'),
                                            'html_help_text' => __('Faded image used as a full-page background on the title page, and next to the page number on numbered pages.', 'print-my-blog'),
                                            'default' => plugins_url('designs/pdf/digital/buurma/assets/logo.svg', PMB_MAIN_FILE),
                                        ]
                                    ),
                                    'default_alignment' => $this->getDefaultAlignmentInput(),
                                    'internal_footnote_text' => $this->getInternalFootnoteTextInput(),
                                    'footnote_text' => $this->getExternalFootnoteTextInput(),
                                ],
                            ]
                        ))->merge($this->getGenericDesignForm());
                    },
                    'project_form_callback' => function (Design $design) {
                        return new FormSection(
                            [
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
                                    'date' => new DatepickerInput(
                                        [
                                            'html_label_text' => __('Date Issued', 'print-my-blog'),
                                            'html_help_text' => __('Text that appears under the byline', 'print-my-blog'),
                                        ]
                                    ),
                                    'cover_preamble' => new TextAreaInput(
                                        [
                                            'html_label_text' => __('Coverpage Preamble', 'print-my-blog'),
                                            'html_help_text' => __('Explanatory text that appears at the bottom of the cover page', 'print-my-blog'),
                                        ]
                                    ),
                                ],
                            ]
                        );
                    },
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
                        $design_form = (new FormSection(
                            [
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
                                            'html_help_text' => __('Check this to make post header information, like title, date, author, etc, appear inside columns; uncheck this to have it take up the full page width', 'print-my-blog'),
                                        ]
                                    ),
                                    'dividing_line' => new YesNoInput(
                                        [
                                            'html_label_text' => __('Show a Dividing Line Between Posts', 'print-my-blog'),
                                        ]
                                    ),
                                    'images_full_column' => new YesNoInput(
                                        [
                                            'html_label_text' => __('Full-Column Images', 'print-my-blog'),
                                            'html_help_text' => __('Resizes images to be the full column width (except ones with the CSS class "mayer-no-resize")', 'print-my-blog'),
                                        ]
                                    ),
                                    'no_extra_columns' => new YesNoInput(
                                        [
                                            'html_label_text' => __('Remove Extra Columns', 'print-my-blog'),
                                            'default' => true,
                                            'html_help_text' => __('Forces your content to only use two columns, even if the content itself was divided into more columns (eg using the "Columns" block)', 'print-my-blog'),
                                        ]
                                    ),
                                    'image' => new FormSection(
                                        [
                                            'subsections' => $this->getImageSnapInputs(),
                                        ]
                                    ),
                                ],
                            ]
                        ))->merge($this->getGenericDesignForm());
                        $design_form->findSection('image_placement')->removeOption('dynamic-resize');
                        $design_form->removeSubsection('dynamic-resize');
                        return $design_form;
                    },
                    'project_form_callback' => function (Design $design) {
                        $sections['byline'] = new TextInput(
                            [
                                'html_display_text' => __('Byline', 'print-my-blog'),
                                'html_help_text' => __('Project author(s)', 'print-my-blog'),
                            ]
                        );
                        $sections['cover_preamble'] = new TextAreaInput(
                            [
                                'html_label_text' => __('Coverpage Preamble', 'print-my-blog'),
                                'html_help_text' => __(
                                    'Explanatory text that appears at the bottom of the cover page',
                                    'print-my-blog'
                                ),
                            ]
                        );
                        return new FormSection(
                            [
                                'subsections' => $sections,
                            ]
                        );
                    },
                ];
            }
        );

        // it's ok to register this design even if the format isn't registered (which it isn't for the wp.org version)
        pmb_register_design_template(
            'classic_epub',
            function () {
                return [
                    'title'                 => __('Classic ePub', 'print-my-blog'),
                    'format'                => 'epub',
                    'dir'                   => PMB_DESIGNS_DIR . 'epub/classic',
                    'url' => plugins_url('designs/epub/classic', PMB_MAIN_FILE),
                    'default' => 'classic_epub',
                    'docs' => 'https://printmy.blog/user-guide/pdf-design/7-classic-word-document/',
                    'supports' => [
                        'front_matter',
                        'part',
                        'back_matter',
                    ],
                    'design_form_callback'  => function () {

                        $form = $this->getDefaultDesignForm()->merge($this->getGenericDesignForm());
                        $form->addSubsections(
                            [
                                'convert_videos' => new YesNoInput(
                                    [
                                        'html_label_text' => __('Convert Videos to Images and Links', 'print-my-blog'),
                                        'html_help_text' => __('Some eReaders don\'t show videos, in which case you may prefer to replace them with an image and a hyperlink to the online video content.', 'print-my-blog'),
                                        'default' => false,
                                    ]
                                ),
                            ],
                            'generic_sections'
                        );
                        return $form;
                    },
                    'project_form_callback' => function (Design $design) {
                        $project_form = $this->getDefaultProjectForm($design);
                        $project_form->merge(
                            new FormSection(
                                [
                                    'subsections' => [
                                        'post_name' => new TextInput(
                                            [
                                                'html_label_text' => __('File name', 'print-my-blog'),
                                            ]
                                        ),
                                        'byline' => new TextAreaInput(
                                            [
                                                'html_label_text' => __('ByLine', 'print-my-blog'),
                                                'html_help_text' => __('Project Author(s)', 'print-my-blog'),
                                            ]
                                        ),
                                        'post_content' => new TextAreaInput(
                                            [
                                                'html_label_text' => __('Description', 'print-my-blog'),
                                                'html_help_text' => __('Shown as eBook metadata.', 'print-my-blog'),
                                            ]
                                        ),
                                        'cover' => new AdminFileUploaderInput(
                                            [
                                                'html_label_text' => __('Cover Image', 'print-my-blog'),
                                                'html_help_text' => __('Cover image used on eBook file (does not necessarily appear inside project). Ideal dimensions are 2,560 x 1,600 pixels.', 'print-my-blog'),
                                                'default' => plugins_url('assets/images/icon-128x128.jpg', PMB_MAIN_FILE),
                                            ]
                                        ),
                                    ],
                                ]
                            )
                        );
                        return $project_form;
                    },
                ];
            }
        );

        // it's ok to register this design even if the format isn't registered (which it isn't for the wp.org version)
        pmb_register_design_template(
            'classic_word',
            function () {
                return [
                    'title'                 => __('Classic Word', 'print-my-blog'),
                    'format'                => DefaultFileFormats::WORD,
                    'dir'                   => PMB_DESIGNS_DIR . 'word/classic',
                    'url' => plugins_url('designs/word/classic', PMB_MAIN_FILE),
                    'default' => 'classic_word',
                    'docs' => 'https://printmy.blog/user-guide/', // update
                    'supports' => [
                        'front_matter',
                        'part',
                        'back_matter',
                    ],
                    'design_form_callback'  => function () {

                        $form = $this->getDefaultDesignForm()->merge($this->getGenericDesignForm());
                        $form->addSubsections(
                            [
                                'convert_videos' => new YesNoInput(
                                    [
                                        'html_label_text' => __('Convert Videos to Images and Links', 'print-my-blog'),
                                        'html_help_text' => __('Some Word Processors don\'t show videos, in which case you may prefer to replace them with an image and a hyperlink to the online video content.', 'print-my-blog'),
                                        'default' => true,
                                    ]
                                ),

                                'internal_links' => new SelectRevealInput(
                                    [
                                        'remove' => new InputOption(
                                            __('Remove', 'print-my-blog')
                                        ),
                                        'leave_external' => new InputOption(
                                            __('Leave as hyperlink to website', 'print-my-blog')
                                        ),
                                        'leave' => new InputOption(
                                            __('Leave as hyperlink to document', 'print-my-blog')
                                        ),
                                    ],
                                    [
                                        'default' => 'leave',
                                        'html_label_text' => __('Internal Hyperlinks', 'print-my-blog'),
                                        'html_help_text' => __('How to display hyperlinks to content included in this project.', 'print-my-blog'),
                                    ]
                                ),
                                'external_links' => new SelectRevealInput(
                                    [
                                        'remove' => new InputOption(
                                            __('Remove', 'print-my-blog')
                                        ),
                                        'leave' => new InputOption(
                                            __('Leave as hyperlink', 'print-my-blog')
                                        ),
                                    ],
                                    [
                                        'default' => 'leave',
                                        'html_label_text' => __('External Hyperlinks', 'print-my-blog'),
                                        'html_help_text' => __('How to display hyperlinks to content not included in this project.', 'print-my-blog'),
                                    ]
                                ),
                            ],
                            'generic_sections'
                        );
                        $form->getProperSubsection('generic_sections', false)->removeSubsection('powered_by');
                        return $form;
                    },
                    'project_form_callback' => function (Design $design) {
                        $project_form = $this->getDefaultProjectForm($design);
                        $project_form->merge(
                            new FormSection(
                                [
                                    'subsections' => [
                                        'post_name' => new TextInput(
                                            [
                                                'html_label_text' => __('File name', 'print-my-blog'),
                                            ]
                                        ),
                                        'byline' => new TextAreaInput(
                                            [
                                                'html_label_text' => __('ByLine', 'print-my-blog'),
                                                'html_help_text' => __('Project Author(s)', 'print-my-blog'),
                                            ]
                                        ),
                                    ],
                                ]
                            )
                        );
                        return $project_form;
                    },
                ];
            }
        );

        pmb_register_design_template(
            'haller',
            function () {
                return [
                    'title'           => __('Haller Tabloid Print PDF'),
                    'format'          => 'print_pdf',
                    'dir'             => PMB_DESIGNS_DIR . 'pdf/print/haller/',
                    'default' => 'haller',
                    'docs' => 'https://printmy.blog/user-guide/pdf-design/haller-tabloid-print-ready-pdf/',
                    'supports' => [
                        'front_matter',
                        'part',
                        'back_matter',
                    ],
                    'url' => plugins_url('designs/pdf/print/haller', PMB_MAIN_FILE),
                    'design_form_callback'  => function () {
                        $design_form = (new FormSection(
                            [
                                'subsections' => [
                                    'publication_title' => new TextInput(
                                        [
                                            'html_label_text' => __('Title of Publication', 'print-my-blog'),
                                            'html_help_text' => __('Shown in a large font on front page and in the top margin of every subsequent page.'),
                                            'default' => get_bloginfo('name'),
                                        ]
                                    ),
                                    'publication_subtitle' => new TextInput(
                                        [
                                            'html_label_text' => __('Subtitle of Publication', 'print-my-blog'),
                                            'html_help_text' => __('Shown under the name of the publication, in a slightly smaller font.', 'print-my-blog'),
                                            'default' => get_bloginfo('description'),
                                        ]
                                    ),
                                    'cover_preamble' => new TextInput(
                                        [
                                            'html_label_text' => __('Publication Preamble', 'print-my-blog'),
                                            'html_help_text' => __('Shown on the front page under the Title and Subtitle.', 'print-my-blog'),
                                        ]
                                    ),
                                    'images_full_column' => new YesNoInput(
                                        [
                                            'html_label_text' => __('Full-Column Images', 'print-my-blog'),
                                            'html_help_text' => __('Resizes images to be the full column width (except ones with the CSS class "mayer-no-resize")', 'print-my-blog'),
                                        ]
                                    ),
                                    'columns' => new SelectInput(
                                        [
                                            2 => new InputOption('2'),
                                            3 => new InputOption('3'),
                                            4 => new InputOption('4'),
                                        ],
                                        [
                                            'html_label_text' => __('Columns', 'print-my-blog'),
                                            'default' => 3,
                                            'html_help_text' => __('Number of columns to use for content.', 'print-my-blog'),
                                        ]
                                    ),
                                    'post_content' => $this->getPostContentInput(),
                                    'no_extra_columns' => new YesNoInput(
                                        [
                                            'html_label_text' => __('Remove Extra Columns', 'print-my-blog'),
                                            'default' => false,
                                            'html_help_text' => __('Forces your content to only use two columns, even if the content itself was divided into more columns (eg using the "Columns" block)', 'print-my-blog'),
                                        ]
                                    ),
                                    'page' => $this->getPageSubsection(),
                                    'links' => $this->getLinksInputs(),
                                ],
                            ]
                        ))->merge($this->getGenericDesignForm());
                        return $design_form;
                    },
                    'project_form_callback' => function (Design $design) {
                        $sections = [
                            'date' => new TextInput(
                                [
                                    'html_label_text' => __('Date', 'print-my-blog'),
                                    'html_help_text' => __('Shown on frontpage and in the top margin of all subsequent pages.', 'print-my-blog'),
                                ]
                            ),
                            'issue' => new TextInput(
                                [
                                    'html_label_text' => __('Issue Number', 'print-my-blog'),
                                    'html_help_text' => __('Shown on the frontpage and in the top margin of all subsequent pages (shortcodes supported).', 'print-my-blog'),
                                ]
                            ),
                            'frontpage_left_side' => new WysiwygInput(
                                [
                                    'html_label_text' => __('Frontpage Title Left Call-Out', 'print-my-blog'),
                                    'html_help_text' => __('HTML displayed to the left of the title on the frontpage (shortcodes supported).', 'print-my-blog'),
                                ]
                            ),
                            'frontpage_right_side' => new WysiwygInput(
                                [
                                    'html_label_text' => __('Frontpage Title Right Call-Out', 'print-my-blog'),
                                    'html_help_text' => __('HTML displayed to the right of the title on the frontpage', 'print-my-blog'),
                                ]
                            ),
                        ];

                        return new FormSection(
                            [
                                'subsections' => $sections,
                            ]
                        );
                    },
                ];
            }
        );
    }

    /**
     * @param Design $design
     * @return FormSection
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    public function getDefaultProjectForm(Design $design)
    {
        $sections = [];
        $header_content = $design->getSetting('header_content');
        if (in_array('subtitle', $header_content, true)) {
            $sections['subtitle'] = new TextInput(
                [
                    'html_label_text' => __('Subtitle', 'print-my-blog'),
                ]
            );
        }
        if (in_array('byline', $header_content, true)) {
            $sections['byline'] = new TextInput(
                [
                    'html_label_text' => __('Byline', 'print-my-blog'),
                    'html_help_text' => __('Project author(s)', 'print-my-blog'),
                ]
            );
        }
        if (in_array('url', $header_content, true)) {
            $sections['url'] = new TextInput(
                [
                    'default' => site_url(),
                    'html_label_text' => __('Source Location', 'print-my-blog'),
                    'html_help_text' => __('Shown on the title page under the subtitle. Could be your website’s URL, or anything else you like.', 'print-my-blog'),
                ]
            );
        }
        return new FormSection(
            [
                'subsections' => $sections,
            ]
        );
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
                                'date_printed',
                            ],
                            'html_label_text' => __('Title Page Content'),
                        ]
                    ),
                    'post_content' => $this->getPostContentInput(),
                ],
            ]
        );
    }

    /**
     * @return CheckboxMultiInput
     */
    protected function getPostContentInput()
    {
        return new CheckboxMultiInput(
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
            ],
            [
                'default' => [
                    'title',
                    'published_date',
                    'categories',
                    'featured_image',
                    'content',
                ],
                'html_label_text' => __('Post Content'),
                'html_help_text' => __('Content from each post to print.', 'print-my-blog'),
            ]
        );
    }

    /**
     * Gets a form specific to classic PDFs
     * @return FormSection
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    protected function getDefaultPdfDesignForm()
    {
        return new FormSection(
            [
                'subsections' => [
                    'page_per_post' => new YesNoInput(
                        [
                            'default' => true,
                            'html_label_text' => __('Each Post Begins on a New Page', 'print-my-blog'),
                            'html_help_text' => __('Whether to force posts to always start on a new page. Doing so makes the page more legible, but uses more paper.', 'print-my-blog'),
                        ]
                    ),
                    'fonts' => new FormSectionDetails(
                        [
                            'html_summary' => __('Font Settings', 'print-my-blog'),
                            'subsections' => [
                                'main_header_font_size' => new TextInput(
                                    [
                                        'default' => '4em',
                                        'html_label_text' => __('Title page and Part Header Font Size', 'print-my-blog'),
                                        'html_help_text' => sprintf(
                                            // translators: 1: opening anchor tag, 2: closing anchor tag, 3: opening anchor tag
                                            __('Font size used for the default title page’s and part’s header (all other headers’ sizes are derived from the main font size). Use any recognized %1$sCSS font-size keyword%2$s (like "large", "medium", "small") or a %3$slength in any units%2$s (eg "14pt", "50%%", or "10px").'),
                                            '<a href="https://www.w3schools.com/cssref/pr_font_font-size.asp" target="_blank">',
                                            '</a>',
                                            '<a href="https://www.w3schools.com/cssref/css_units.asp" target="_blank">'
                                        ),
                                    ]
                                ),
                                'header_font_style' => new FontInput(
                                    [
                                        'default' => 'arial',
                                        'html_label_text' => __('Header Font', 'print-my-blog'),
                                        'html_help_text' => __('Default font for header tags', 'print-my-blog'),
                                    ]
                                ),
                                'font_size' => new TextInput(
                                    [
                                        'default' => '10pt',
                                        'html_label_text' => __('Font Size', 'print-my-blog'),
                                        'html_help_text' => sprintf(
                                            // translators: 1: opening anchor tag, 2: closing anchor tag, 3: opening anchor tag
                                            __('Use any recognized %1$sCSS font-size keyword%2$s (like "large", "medium", "small") or a %3$slength in any units%2$s (eg "14pt", "50%%", or "10px").'),
                                            '<a href="https://www.w3schools.com/cssref/pr_font_font-size.asp" target="_blank">',
                                            '</a>',
                                            '<a href="https://www.w3schools.com/cssref/css_units.asp" target="_blank">'
                                        ),
                                    ]
                                ),
                                'font_style' => new FontInput(
                                    [
                                        'default' => 'times new roman',
                                        'html_label_text' => __('Font', 'print-my-blog'),
                                        'html_help_text' => __('Default font used in paragraphs, bulleted lists, tables, etc.'),
                                    ]
                                ),
                            ],
                        ]
                    ),
                    'image' => new FormSectionDetails(
                        [
                            'html_summary' => __('Image & Block Settings', 'print-my-blog'),
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
                                        'default' => 500,
                                    ]
                                ),
                                'default_alignment' => $this->getDefaultAlignmentInput(),
                            ],
                        ]
                    ),
                    'page' => $this->getPageSubsection(),
                ],
            ]
        );
    }

    /**
     * @return FormSectionDetails
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    protected function getPageSubsection()
    {
        return new FormSectionDetails(
            [
                'html_summary' => __('Page Settings', 'print-my-blog'),
                'subsections' => [
                    'page_width' => new TextInput(
                        [
                            'html_label_text' => __('Page Width', 'print-my-blog') . pmb_pro_print_service_best(__('Not supported by some browsers', 'print-my-blog')),
                            'html_help_text' => sprintf(
                                // translators: 1: opening anchor tag, 2: closing anchor tag
                                __('Use standard %1$sCSS units%2$s', 'print-my-blog'),
                                '<a href="https://www.w3schools.com/CSSref/css_units.asp">',
                                '</a>'
                            ),
                            'default' => '8.5in',
                        ]
                    ),
                    'page_height' => new TextInput(
                        [
                            'html_label_text' => __('Page Height', 'print-my-blog') . pmb_pro_print_service_best(__('Not supported by some browsers', 'print-my-blog')),
                            'html_help_text' => sprintf(
                                // translators: 1: opening anchor tag, 2: closing anchor tag
                                __('Use standard %1$sCSS units%2$s', 'print-my-blog'),
                                '<a href="https://www.w3schools.com/CSSref/css_units.asp">',
                                '</a>'
                            ),
                            'default' => '11in',
                        ]
                    ),
                ],
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
            // translators: 1: theme name.
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

        $image_sizes = $this->image_helper->getAllImageSizes();
        $image_quality_options = [
            '' => new InputOption(__('Don’t Change Image Quality', 'print-my-blog')),
        ];

        foreach ($image_sizes as $thumbnail_slug => $thumbnail_data) {
            // skip weird images with no height
            // also don't show non-cropped images, because finding their filename would require a trip to the server

            // Other devs may have expected loose comparisons so keep doing that.
            // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
            if (! $thumbnail_data['width'] || ! $thumbnail_data['height'] || $thumbnail_data['crop'] == false) {
                continue;
            }
            $dimensions = $thumbnail_data['width'] . 'x' . $thumbnail_data['height'];
            $image_quality_options[$dimensions] = new InputOption(
                sprintf(
                    // translators: %s image dimensions, like "120x120"
                    __('Resize to %s', 'print-my-blog'),
                    $dimensions
                )
            );
        }
        $image_quality_options['scaled'] = new InputOption(__('Full Size (on web)', 'print-my-blog'));
        $image_quality_options['uploaded'] = new InputOption(__('Uploaded Size (largest possible)', 'print-my-blog'));

        return apply_filters(
            'PrintMyBlog\domain\DefaultDesignTemplates->getGenericDesignForm',
            new FormSection(
                [
                    'subsections' => [
                        'image' => new FormSection(
                            [
                                'subsections' => [
                                    'image_quality' => new SelectInput(
                                        $image_quality_options,
                                        [
                                            'html_label_text' => __('Image Quality (in pixels)', 'print-my-blog'),
                                            'html_help_text' => sprintf(
                                                // translators: 1: opening anchor tag, 2L closing anchor tag.
                                                __('Lower quality means smaller file size, whereas higher quality means higher resolution images. Note: if images are missing from the generated PDF, the requested image size might not be available. Use the %1$s Regenerate Thumbnails plugin%2$s to create the missing image sizes.', 'print-my-blog'),
                                                '<a href="https://wordpress.org/plugins/regenerate-thumbnails/">',
                                                '</a>'
                                            ),
                                            'default' => '',
                                        ]
                                    ),
                                ],
                            ]
                        ),
                        'generic_sections' => new FormSection(
                            [
                                'subsections' =>
                                apply_filters(
                                    'PrintMyBlog\domain\DefaultDesignTemplates->getGenericDesignFormSections',
                                    [
                                        'use_theme' => new YesNoInput(
                                            [
                                                'html_label_text' => __('Apply Website Theme', 'print-my-blog'),
                                                'html_help_text' => $use_theme_help_text,
                                                'default' => false,
                                            ]
                                        ),
                                        'custom_css' => new TextAreaInput(
                                            [
                                                'html_label_text' => __('Custom CSS', 'print-my-blog'),
                                                'html_help_text'  => __('Styles to be applied only when printing projects using this design.', 'print-my-blog'),
                                            ]
                                        ),
                                        'powered_by' => new YesNoInput(
                                            [
                                                'html_label_text' => __('Add "Powered By"', 'print-my-blog') . ( $powered_by_in_pro_service ? pmb_hover_help(__('In compliance with WordPress.org guidelines, not added when printing from your browser.', 'print-my-blog')) : ''),
                                                'html_help_text' => __('Instructs the Pro PDF Service to add "Powered by Print My Blog Pro & WordPress" to your project. Does not appear when printing using your browser.', 'print-my-blog'),
                                                'default' => $powered_by_in_pro_service,
                                                'disabled' => $powered_by_in_pro_service,
                                            ]
                                        ),
                                    ]
                                ),
                            ]
                        ),
                    ],
                ]
            )
        );
    }

    /**
     * @return TextInput
     */
    public function getPageReferenceTextInput()
    {
        return new TextInput(
            [
                'html_label_text' => __('Page Reference Text', 'print-my-blog'),
                // translators: %s literally the string "%s"
                'html_help_text' => __('Text to use when replacing a hyperlink with a page reference. "%s" will be replaced with the page number.', 'print-my-blog'),
                // translators: %s page number
                'default' => __('(see page %s)', 'print-my-blog'),
                'validation_strategies' => [
                    // translators: %s literally the string %s.
                    new TextValidation(__('You must include "%s" in the page reference text so we know where to put the page number.', 'print-my-blog'), '~.*\%s.*~'),
                ],
            ]
        );
    }

    /**
     * @return TextInput
     */
    public function getInternalFootnoteTextInput()
    {
        return new TextInput(
            [
                'html_label_text' => __('Internal Footnote Text', 'print-my-blog'),
                // translators: %s literally the string %s.
                'html_help_text' => __('Text to use when replacing a hyperlink with a footnote. "%s" will be replaced with the page number.', 'print-my-blog'),
                // translators: %s literally the string %s.
                'default' => __('See page %s.', 'print-my-blog'),
                'validation_strategies' => [
                    // translators: %s literally the string %s.
                    new TextValidation(__('You must include "%s" in the footnote text so we know where to put the URL.', 'print-my-blog'), '~.*\%s.*~'),
                ],
            ]
        );
    }

    /**
     * @return TextInput
     */
    public function getExternalFootnoteTextInput()
    {
        return new TextInput(
            [
                'html_label_text' => __('External Footnote Text', 'print-my-blog'),
                // translators: %s literally the string %s.
                'html_help_text' => __('Text to use when replacing a hyperlink with a footnote. "%s" will be replaced with the URL', 'print-my-blog'),
                // translators: %s literally the string %s.
                'default' => __('See %s.', 'print-my-blog'),
                'validation_strategies' => [
                    // translators: %s literally the string %s.
                    new TextValidation(__('You must include "%s" in the footnote text so we know where to put the URL.', 'print-my-blog'), '~.*\%s.*~'),
                ],
            ]
        );
    }

    /**
     * @return SelectInput
     */
    public function getDefaultAlignmentInput()
    {
        return new SelectInput(
            [
                'none' => new InputOption(__('None', 'print-my-blog')),
                'center' => new InputOption(__('Center', 'print-my-blog')),
            ],
            [
                'html_label_text' => __('Default Image Alignment', 'print-my-blog'),
                'html_help_text' => __('Images normally default to "no alignment", which can look jumbled in printouts. Usually it’s best to automatically switch those to align to the center.', 'print-my-blog'),
                'default' => 'center',
            ]
        );
    }

    /**
     * @return SelectRevealInput
     */
    public function getImageSnapInput()
    {
        return new SelectRevealInput(
            [
                'default' => new InputOption(__('Don’t move', 'print-my-blog')),
                'snap' => new InputOption(__('Snap to the top or bottom of the page', 'print-my-blog')),
                'snap-unless-fit' => new InputOption(__('Only snap if it would otherwise cause a page break', 'print-my-blog')),
                'dynamic-resize' => new InputOption(__('Resize images if they don’t fit on the page')),
            ],
            [
                'html_label_text' => __('Image and Block Placement', 'print-my-blog') . pmb_pro_print_service_only(__('Image snapping and dynamic resizing only works using Pro Print.', 'print-my-blog')),
                'html_help_text' => __('To reduce whitespace around images, galleries, and tables, Print My Blog can adjust the placement of your content, or resize it according to the space on the page.', 'print-my-blog'),
                'default' => 'snap-unless-fit',
            ]
        );
    }

    /**
     * Gets the image placement input and its sister dynamic-resize input (which gets revealed when choosing to resize images)
     * @return FormInputBase[]
     */
    protected function getImageSnapInputs()
    {
        return [
            'image_placement' => $this->getImageSnapInput(),
            'dynamic-resize' => new FormSection(
                [
                    'subsections' => [
                        'dynamic_resize_min' => new TextInput(
                            [
                                'html_label_text' => __('Minimum Image Size (in pixels)', 'print-my-blog'),
                                'html_help_text' => __('Any images larger than this may be resized to fit onto the page, but they will be no smaller than this size.', 'print-my-blog'),
                                'default' => '300',
                            ]
                        ),
                    ],
                ]
            ),
        ];
    }

    /**
     * Gets a form section regarding how to handle links, footnotes, page refs, etc.
     * @return FormSectionDetails
     * @throws \Freemius_Exception
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    public function getLinksInputs()
    {
        return new FormSectionDetails(
            [
                'html_summary' => __('Link, Page Reference, and Footnote Settings', 'print-my-blog'),
                'subsections' => [

                    'internal' => new FormSection(
                        [
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
                                        'html_label_text' => __('Internal Hyperlinks', 'print-my-blog') . pmb_pro_print_service_best(__('Footnotes and page references only work with Pro PDF Service', 'print-my-blog')),
                                        'html_help_text' => __(
                                            'How to display hyperlinks to content included in this project.',
                                            'print-my-blog'
                                        ),
                                    ]
                                ),
                                'parens' => new FormSection(
                                    [
                                        'subsections' => [
                                            'page_reference_text' => $this->getPageReferenceTextInput(),
                                        ],
                                    ]
                                ),
                                'footnote' => new FormSection(
                                    [
                                        'subsections' => [
                                            'internal_footnote_text' => $this->getInternalFootnoteTextInput(),
                                        ],
                                    ]
                                ),
                            ],
                        ]
                    ),
                    'external' => new FormSection(
                        [
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
                                        ),
                                    ]
                                ),
                                'footnote' => new FormSection(
                                    [
                                        'subsections' => [
                                            'footnote_text' => $this->getExternalFootnoteTextInput(),
                                        ],
                                    ]
                                ),
                            ],
                        ]
                    ),
                ],
            ]
        );
    }
}
