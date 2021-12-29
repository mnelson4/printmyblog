<?php

namespace PrintMyBlog\domain;

class DefaultSectionTemplates
{
    public function registerDefaultSectionTemplates()
    {
        pmb_register_section_template(
            'just_content',
            [
                'classic_digital',
                'buurma',
                'mayer',
                'classic_print',
                'classic_epub'
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
                    'fallback' => 'just_content'
                ];
            }
        );
        pmb_register_section_template(
            'single_column',
            [
                'mayer'
            ],
            function () {
                return [
                    'title' => __('Single Column', 'print-my-blog'),
                    'fallback' => ''
                ];
            }
        );
    }
}
