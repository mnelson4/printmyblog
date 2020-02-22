<?php

namespace PrintMyBlog\controllers;

use PrintMyBlog\domain\FrontendPrintSettings;
use PrintMyBlog\domain\PrintNowSettings;
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
            $print_settings = new FrontendPrintSettings();
            $print_settings->load();
            if ($print_settings->showButtons()) {
                $base_args = array(
                        'post-type' => 'post',
                        'include-private-posts' => '1',
                        'show_site_title' => '1',
                        'show_site_tagline' => '1',
                        'show_site_url' => '1',
                        'show_date_printed' => '1',
                        'show_title' => '1',
                        'show_date' => '1',
                        'show_categories' => '1',
                        'show_featured_image' => '1',
                        'show_content' => '1',
                        'post-page-break' => 'on',
                        'columns' => '1',
                        'font-size' => 'normal',
                        'image-size' => 'medium',
                        'links' => 'include',
                        'rendering-wait' => '10',
                        'print-my-blog' => '1',
                        'format' => '%s',
                        'pmb-post' => '%d',
                    );
                $html = '<div class="pmb-print-this-page wp-block-button">';
                foreach ($print_settings->formats() as $slug => $settings) {
                    if (! $print_settings->isActive($slug)) {
                        continue;
                    }
                    $args = $base_args;
                    $args['format'] = $slug;
                    $args['pmb-post'] = $post->ID;
                    if ($slug === 'print') {
                        $args['links'] = 'parens';
                    }
                    $url = add_query_arg(
                        $args,
                        site_url()
                    );
                    $html .= sprintf(
                        ' <a href="%s" class="button button-secondary wp-block-button__link">%s</a>',
                        esc_url($url),
                        esc_html($print_settings->getFrontendLabel($slug))
                    );
                }
                $html .= '</div>';
                $add_to_top = apply_filters('\PrintMyBlog\controllers\PmbFrontend->addPrintButton $add_to_top',true, $post);
                if($add_to_top){
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
