<?php

namespace PrintMyBlog\controllers;

use PrintMyBlog\domain\FrontendPrintSettings;
use PrintMyBlog\domain\PrintNowSettings;
use PrintMyBlog\domain\PrintOptions;
use Twine\controllers\BaseController;

/**
 * Class PmbFrontend
 *
 * Sets up generic frontend logic.
 *
 * @package     Print My Blog
 * @author         Mike Nelson
 * @since         $VID:$
 *
 */
class PmbFrontend extends BaseController
{
    public function setHooks()
    {
        add_filter(
            'the_content',
            array($this, 'addPrintButton')
        );
    }
    public function addPrintButton($content)
    {
        global $post;
        if ($post->post_type === 'post' && is_single() && ! post_password_required($post)) {
            $print_settings = new FrontendPrintSettings(new PrintOptions());
            $print_settings->load();
            if ($print_settings->showButtons()) {
                $base_args = array(
                    'print-my-blog' => '1',
                    'post-type' => 'post'
                );
                $html = '<div class="pmb-print-this-page wp-block-button">';
                foreach ($print_settings->formats() as $slug => $settings) {
                    if (! $print_settings->isActive($slug)) {
                        continue;
                    }
                    $args = array_merge(
                        $base_args,
                        $print_settings->getPrintOptions($slug)
                    );
                    $args['format'] = $slug;
                    $args['pmb-post'] = $post->ID;
                    $url = add_query_arg(
                        apply_filters(
                            '\PrintMyBlog\controllers\PmbFrontend->addPrintButton $base_args',
                            $args,
                            $post,
                            $slug,
                            $settings
                        ),
                        site_url()
                    );
                    $html .= sprintf(
                        ' <a href="%s" class="button button-secondary wp-block-button__link">%s</a>',
                        esc_url($url),
                        esc_html($print_settings->getFrontendLabel($slug))
                    );
                }
                $html .= '</div>';
                $add_to_top = apply_filters(
                    '\PrintMyBlog\controllers\PmbFrontend->addPrintButton $add_to_top',
                    true,
                    $post
                );
                if ($add_to_top) {
                    return $html . $content;
                } else {
                    return $content . $html;
                }
            }
        }
        return $content;
    }

    /**
     * Determines if the request is for our page generator page, and if so, uses our template for it.
     * @since 1.0.0
     * @deprecated 2.2.3. Instead use `PrintMyBlog/controllers/PmbPrintPage::templateRedirect`
     */
    public function templateRedirect($template)
    {
        $print_page = new PmbPrintPage();
        return $print_page->templateRedirect($template);
    }

    //phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function enqueue_scripts()
    {
    }
}
