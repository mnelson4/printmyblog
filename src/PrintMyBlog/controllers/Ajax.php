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
class Ajax extends BaseController
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
        $this->addUnauthenticatedCallback(
        	'pmb_fetch_rest_api_url',
	        'handleFetchRestApiUrl'
        );
        $this->addUnauthenticatedCallback(
        	'pmb_project_status',
	        'handleProjectStatus'
        );
	    add_action('wp_ajax_pmb_save_project_main', [$this, 'handleSaveProjectMain' ]);
    }

    protected function addUnauthenticatedCallback($ajax_action, $method_name){
	    $callback = [$this, $method_name];
	    add_action('wp_ajax_' . $ajax_action, $callback);
	    add_action('wp_ajax_nopriv_' . $ajax_action, $callback);
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
	 * Proceeds with loading printing a project and returns a response indicating the status.
	 */
    public function handleProjectStatus()
    {
    	// Find project by ID.
	    $project = $this->project_manager->getById($_GET['ID']);

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

	public function handleSaveProjectMain(){
		// Get the ID
		$project_id = $_REQUEST['ID'];
		// Check permission
		if(check_admin_referer('pmb-project-edit') && current_user_can('edit_project', $project_id)){
			// Save it
			$project = $this->project_manager->getById($project_id);
			$success = $project->setTitle($_REQUEST['pmb_title']);
			$project->setFormatsSelected($_REQUEST['pmb_format']);
		}
		// Say it worked
		if(is_wp_error($success)){
			wp_send_json_error($success);
		} else {
			wp_send_json_success();
		}
	}
}
