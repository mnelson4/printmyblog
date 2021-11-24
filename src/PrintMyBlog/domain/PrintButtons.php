<?php

namespace PrintMyBlog\domain;

use PrintMyBlog\system\Context;
use WP_Post;

class PrintButtons
{

    /**
     * @var FrontendPrintSettings
     */
    private $print_settings;

    /**
     * @param FrontendPrintSettings $printSettings
     */
    public function inject(FrontendPrintSettings $printSettings)
    {
        $this->print_settings = $printSettings;
    }

    /**
     * @param null $post
     * @return string
     * @throws \Exception
     */
    public function getHtmlForPrintButtons($post = null)
    {
        if(is_int($post) || is_string($post)){
            $post = get_post($post);
        }
        if(! $post instanceof WP_Post){
            $post = get_post();
        }
        if(! $post instanceof WP_Post || ! $post->ID || ! in_array($post->post_type, ['post', 'page'])){
            return '<!-- PMB print buttons is only displayed on a single post/page URLs-->';
        }
        /**
         * @var $url_generator PrintPageUrlGenerator
         */
        $url_generator = Context::instance()->useNew('PrintMyBlog\domain\PrintPageUrlGenerator', [$post]);

        $html = '<div class="pmb-print-this-page wp-block-button">';
        foreach ($this->print_settings->formats() as $slug => $settings) {
            if (! $this->print_settings->isActive($slug)) {
                continue;
            }
            $html .= sprintf(
                ' <a href="%s" class="button button-secondary wp-block-button__link">%s</a>',
                esc_url($url_generator->getUrl($slug)),
                esc_html($this->print_settings->getFrontendLabel($slug))
            );
        }
        $html .= '</div>';
        return $html;
    }
}
