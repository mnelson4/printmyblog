<?php

namespace PrintMyBlog\domain;

/**
 * Class DefaultSectionTemplates
 * @package PrintMyBlog\domain
 */
class DefaultSectionTemplates
{
    /**
     * Registers default section templates.
     */
    public function registerDefaultSectionTemplates()
    {
        pmb_register_section_template(
            'just_content',
            [
                'classic_digital',
                'buurma',
                'mayer',
                'classic_print',
                'classic_epub',
                'classic_word',
                'haller',
            ],
            function () {
                return [
                    'title' => __('Fullpage Content', 'print-my-blog'),
                    'fallback' => 'article',
                ];
            }
        );
        pmb_register_section_template(
            'center_content',
            [
                'classic_digital',
                'buurma',
                'mayer',
                'classic_print',
            ],
            function () {
                return [
                    'title' => __('Centered Content', 'print-my-blog'),
                    'fallback' => 'just_content',
                ];
            }
        );
        pmb_register_section_template(
            'single_column',
            [
                'mayer',
                'haller',
            ],
            function () {
                return [
                    'title' => __('Single Column', 'print-my-blog'),
                    'fallback' => '',
                ];
            }
        );
        pmb_register_section_template(
            'important_article',
            [
                'haller',
            ],
            function () {
                return [
                    'title' => __('Important', 'print-my-blog'),
                    'fallback' => '',
                ];
            }
        );
    }
}
