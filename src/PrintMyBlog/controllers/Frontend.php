<?php

namespace PrintMyBlog\controllers;

use PrintMyBlog\domain\FrontendPrintSettings;
use PrintMyBlog\domain\PrintButtons;
use PrintMyBlog\domain\PrintNowSettings;
use PrintMyBlog\domain\PrintOptions;
use PrintMyBlog\system\Context;
use Twine\controllers\BaseController;
use WP_Post;

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
class Frontend extends BaseController
{
    public function setHooks()
    {
        add_filter(
            'the_content',
            array($this, 'addPrintButton'),
            /**
             * Allows changing the priority of the print buttons to place them above or below other content
             * automatically added.
             */
            apply_filters(
                'PrintMyBlog\controllers\PmbFrontend->setHooks $priority',
                10
            )
        );
    }
    public function addPrintButton($content)
    {
        global $post;
        if (! $post instanceof WP_Post || ! in_array($post->post_type, ['post','page'])) {
            return $content;
        }
        $pmb_print_settings = Context::instance()->reuse('PrintMyBlog\domain\FrontendPrintSettings');

        $postmeta_override = get_post_meta($post->ID, 'pmb_buttons', true);
        $active_post_types = $pmb_print_settings->activePostTypes();
        if (
            /**
             * Lets you override if Print My Blog adds print buttons or not.
             *
             * @param bool $show whether to show print buttons on this post/page or not
             * @param WP_Post $post
             * @param FrontendPrintSettings $pmb_print_settings
             * @param string $active_post_types 'show' to always show buttons on this post, 'hide' to hide on this post,
             *                                  'default', null, or false to use the default settings.
             * default settings.
             */
            apply_filters(
                'PrintMyBlog\controllers\PmbFrontend->addPrintButtons $show_buttons',
                (
                isset($active_post_types[$post->post_type])
                && $active_post_types[$post->post_type]
                && (is_single() || $post->post_type === 'page')
                && ! post_password_required($post)
                && $postmeta_override !== 'hide'
                )
                || $postmeta_override === 'show',
                $post,
                $pmb_print_settings,
                $postmeta_override
            )
        ) {
            $html = Context::instance()->reuse('PrintMyBlog\domain\PrintButtons')->getHtmlForPrintButtons($post);
            $add_to_top = apply_filters(
                '\PrintMyBlog\controllers\PmbFrontend->addPrintButton $add_to_top',
                $pmb_print_settings->showButtonsAbove(),
                $post
            );
            if ($add_to_top) {
                return $html . $content;
            } else {
                return $content . $html;
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
        $print_page = new LegacyPrintPage();
        return $print_page->templateRedirect($template);
    }

    //phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function enqueue_scripts()
    {
    }
}
