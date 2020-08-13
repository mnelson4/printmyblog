<?php

namespace PrintMyBlog\domain;

use WP_Post;

class PrintButtons
{
    public function getHtmlForPrintButtons($post = null)
    {
        global $pmb_print_settings;

        if (is_int($post) || is_string($post)) {
            $post = get_post($post);
        }
        if (! $post instanceof WP_Post) {
            $post = get_post();
        }
        // We can use this filter a lot, so just load the print settings object once.
        if (! $pmb_print_settings instanceof FrontendPrintSettings) {
            $pmb_print_settings = new FrontendPrintSettings(new PrintOptions());
            $pmb_print_settings->load();
        }

        $base_args = [
            'print-my-blog' => '1',
            'post-type' => $post->post_type,
        ];
        if ($post->post_password != '') {
            $base_args['statuses[]'] = 'password';
        } else {
            $base_args['statuses[]'] = $post->post_status;
        }
        if ($post->post_status === 'draft') {
            $base_args['include-draft-posts'] = true;
        }
        $html = '<div class="pmb-print-this-page wp-block-button">';
        foreach ($pmb_print_settings->formats() as $slug => $settings) {
            if (! $pmb_print_settings->isActive($slug)) {
                continue;
            }
            $args = array_merge(
                $base_args,
                $pmb_print_settings->getPrintOptionsAndValues($slug)
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
                esc_html($pmb_print_settings->getFrontendLabel($slug))
            );
        }
        $html .= '</div>';
        return $html;
    }
}
