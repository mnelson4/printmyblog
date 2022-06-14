<?php

namespace PrintMyBlog\domain;

use Exception;
use WP_Post;

/**
 * Class PrintPageUrlGenerator
 * @package PrintMyBlog\domain
 */
class PrintPageUrlGenerator
{
    /**
     * @var WP_Post
     */
    protected $post;
    /**
     * @var FrontendPrintSettings
     */
    protected $print_settings;

    /**
     * @param FrontendPrintSettings $printSettings
     */
    public function inject(FrontendPrintSettings $printSettings)
    {
        $this->print_settings = $printSettings;
    }

    /**
     * PrintPageUrlGenerator constructor.
     * @param WP_Post|int}string $post
     */
    public function __construct($post)
    {
        if (is_int($post) || is_string($post)) {
            $post = get_post($post);
        }
        if (! $post instanceof WP_Post) {
            $post = get_post();
        }
        $this->post = $post;
    }

    /**
     * Gets the basic URL parameters as an array for teh print page
     * @return array
     */
    public function getBaseArgs()
    {
        $base_args = [
            'print-my-blog' => '1',
            'post-type' => $this->post->post_type,
        ];
        // Loose comparison ok in case post_password is null or false.
        // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
        if ($this->post->post_password != '') {
            $base_args['statuses[]'] = 'password';
        } else {
            $base_args['statuses[]'] = $this->post->post_status;
        }
        if ($this->post->post_status === 'draft') {
            $base_args['include-draft-posts'] = true;
        }
        return $base_args;
    }

    /**
     * Gets the URL to the print page of this format.
     * @param string $slug
     * @return string URL
     * @throws Exception
     */
    public function getUrl($slug = 'print')
    {
        $args = array_merge(
            $this->getBaseArgs(),
            $this->print_settings->getPrintOptionsAndValues($slug)
        );
        $args['format'] = $slug;
        $args['pmb-post'] = $this->post->ID;
        if (defined('ICL_LANGUAGE_CODE')) {
            $args['lang'] = ICL_LANGUAGE_CODE;
        }
        $url = add_query_arg(
            apply_filters(
                '\PrintMyBlog\controllers\PmbFrontend->addPrintButton $base_args',
                $args,
                $this->post,
                $slug,
                $this->print_settings->formatSettings($slug)
            ),
            site_url()
        );
        return $url;
    }
}
