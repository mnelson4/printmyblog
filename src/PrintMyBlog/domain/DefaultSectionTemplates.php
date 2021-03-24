<?php


namespace PrintMyBlog\domain;


class DefaultSectionTemplates
{
    public function registerDefaultSectionTemplates(){
        pmb_register_section_template(
            'just_content',
            [
                'title' => __('Fullpage Content', 'print-my-blog'),
                'fallback' => 'article',
            ]
        );
        pmb_register_section_template(
            'center_content',
            [
                'title' => __('Centered Content', 'print-my-blog'),
                'fallback' => 'just_content'
            ]
        );
    }
}