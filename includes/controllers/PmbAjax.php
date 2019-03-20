<?php

namespace PrintMyBlog\controllers;

use mnelson4\RestApiDetector\RestApiDetector;
use mnelson4\RestApiDetector\RestApiDetectorError;
use Twine\controllers\BaseController;

/**
 * Class PmbAjax
 *
 * Handles AJAX requests
 *
 * @package     Event Espresso
 * @author         Mike Nelson
 * @since         1.0.0
 *
 */
class PmbAjax extends BaseController
{
    /**
     * Sets hooks that we'll use in the admin.
     * @since 1.0.0
     */
    public function setHooks()
    {
        $callback = [$this, 'handleFetchRestApiUrl'];
        add_action('wp_ajax_pmb_fetch_rest_api_url', $callback);
        add_action('wp_ajax_no_priv_pmb_fetch_rest_api_url', $callback);
    }


    public function handleFetchRestApiUrl()
    {
        try {
            $rest_api_detector = new RestApiDetector(esc_url_raw($_POST['site']));
        } catch (RestApiDetectorError $error) {
                wp_send_json_error(
                    [
                        'error' => $error->stringCode(),
                        'message' => $error->getMessage()
                    ]
                );
        }
        wp_send_json_success(
            [
                'name' => $rest_api_detector->getName(),
                'site' => $rest_api_detector->getSite(),
                'proxy_for' => $rest_api_detector->getRestApiUrl(),
                'is_local' => $rest_api_detector->isLocal()
            ]
        );
    }

}