<?php

namespace PrintMyBlog\controllers;

use mnelson4\RestApiDetector\RestApiDetector;
use mnelson4\RestApiDetector\RestApiDetectorError;
use PrintMyBlog\db\PartFetcher;
use PrintMyBlog\orm\ProjectManager;
use Twine\controllers\BaseController;
use WP_Query;

/**
 * Class PmbAjax
 *
 * Handles AJAX requests
 *
 * @package     Print My Blog
 * @author         Mike Nelson
 * @since         1.0.0
 *
 */
class PmbAjax extends BaseController
{
	/**
	 * @var ProjectManager
	 */
	protected $project_manager;

	/**
	 * @param ProjectManager $project_manager
	 */
	public function inject(ProjectManager $project_manager){
		$this->project_manager = $project_manager;
	}
    /**
     * Sets hooks that we'll use in the admin.
     * @since 1.0.0
     */
    public function setHooks()
    {
        $callback = [$this, 'handleFetchRestApiUrl'];
        add_action('wp_ajax_pmb_fetch_rest_api_url', $callback);
        add_action('wp_ajax_nopriv_pmb_fetch_rest_api_url', $callback);
    }


    public function handleFetchRestApiUrl()
    {
        try {
            $rest_api_detector = new RestApiDetector(isset($_POST['site']) ? esc_url_raw($_POST['site']): '');
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


    /**
     * Fetches posts based on request data for possible inclusion in a project. Echos JSON
     * @since $VID:$
     */
    public function handleFetchPostOptionss(){
        global $wpdb;
        $results = $wpdb->get_results('SELECT * FROM ' . $wpdb->posts);
        echo wp_json_encode($results);
        exit;
    }

	/**
	 * Proceeds with loading printing a project and returns a response indicating the status.
	 */
    public function handleLoadStep()
    {
    	// Find project by ID.
	    $project = $this->project_manager->getById($_GET['project_id']);

	    // Find if it's already been generated, if so return that.
	    if(! $project->generated()){
		    $done = $project->generateHtmlFile();
		    if( $done ) {
			    $url = $project->generatedHtmlFileUrl();
		    } else {
		    	$url = null;
		    }
	    } else {
		    $url = $project->generatedHtmlFileUrl();
		}

	    // If we're all done, return the file.
	    $response = [
	    	'url' => $url
	    ];

		wp_send_json($response);
		exit;
    }
}
