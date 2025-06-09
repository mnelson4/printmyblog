<?php

namespace PrintMyBlog\domain;

use PrintMyBlog\system\Context;
use WP_Post;

/**
 * Class PrintButtons
 * @package PrintMyBlog\domain
 */
class PrintButtons
{

    /**
     * @var FrontendPrintSettings
     */
    private $print_settings;

    /**
     * @param FrontendPrintSettings $print_settings
     */
    public function inject(FrontendPrintSettings $print_settings)
    {
        $this->print_settings = $print_settings;
    }

    /**
     * @param null $post
     * @return string
     * @throws \Exception
     */
    public function getHtmlForPrintButtons($post = null)
    {
        if (! is_singular()) {
            return '<!-- PMB print buttons is only displayed on a single post/page URLs-->';
        }
        if (is_int($post) || is_string($post)) {
            $post = get_post($post);
        }
        if (! $post instanceof WP_Post) {
            $post = get_post();
        }
        if ((! $post instanceof WP_Post || ! $post->ID || ! in_array($post->post_type, ['post', 'page'], true))) {
            return '<!-- PMB print buttons are not displayed because there is no valid post of post type "post" or "page"-->';
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
            $this->print_settings->openNewTab() ? $target = ' target="_blank"' : $target = "";
            $html .= sprintf(
                ' <a href="%s" class="button button-secondary wp-block-button__link" rel="nofollow"%s>%s</a>',
                esc_url($url_generator->getUrl($slug)),
                $target,
                esc_html($this->print_settings->getFrontendLabel($slug))
            );
        }
        $html .= '</div>';
        return $html;
    }
}
