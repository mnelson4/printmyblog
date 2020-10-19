<?php


namespace PrintMyBlog\services;


use Exception;
use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\managers\DesignManager;
use PrintMyBlog\system\CustomPostTypes;
use WP_Post;

/**
 * Class DesignRegistry
 * For registering default designs to be created during activation.
 * @package PrintMyBlog\services
 */
class DesignRegistry {

	/**
	 * @var $design_callbacks callable[][] top-level keys are design template slugs, next-level keys are design slugs
	 * whose values are callbacks that return the args to pass into createNewDesign()
	 */
	protected $design_callbacks;
	/**
	 * @var DesignManager
	 */
	protected $design_manager;
	/**
	 * @var DesignTemplateRegistry
	 */
	protected $design_template_registry;


	/**
	 * @param DesignManager $design_manager
	 * @param DesignTemplateRegistry $design_template_registry
	 */
	public function inject(
		DesignManager $design_manager,
		DesignTemplateRegistry $design_template_registry
	){
		$this->design_manager           = $design_manager;
		$this->design_template_registry = $design_template_registry;
	}

	/**
	 * @param $design_template_slug string
	 * @param $design_slug string
	 * @param $callback callable returns an array to be passed into createNewDesign
	 */
	public function registerDesignCallback($design_template_slug, $design_slug, $callback){
		$this->design_callbacks[$design_template_slug][$design_slug] = $callback;
	}

	/**
	 * @param string $design_template_slug
	 * @param string $design_slug
	 * @param callable $callback that returns an array of the following format {
	 *
	 * @type string $title,
	 * @type string $description
	 * @type string $featured_image
	 *
	 *}
	 */
	protected function createNewDesign($design_template_slug, $design_slug, $callback){
		$design_template = $this->design_template_registry->getDesignTemplate($design_template_slug);
		$args = call_user_func($callback, $design_template);
		$design_post_id = wp_insert_post([
			'post_title'   => $args['title'],
			'post_name'    => $design_slug,
			'post_type'    => CustomPostTypes::DESIGN,
			'post_content' => $args['description'],
			'post_status' => 'publish'
		]);
		if(! $design_post_id){
			throw new Exception( 'There was an error inserting the design post "' . $design_slug . '" with ' . var_export($args,true));
		}
		/* @var $design Design */
		$design = $this->design_manager->getById($design_post_id);
		$design->setPmbMeta('format', $design_template->getFormatSlug());
		$design->setPMbMeta('design_template', $design_template->getSlug());

		// Set preview images
		if(isset($args['previews'])){
			$count = 1;
			foreach((array)$args['previews'] as $preview_data){
				$design->setPmbMeta('preview_' . $count . '_url', $preview_data['url']);
				$design->setPmbMeta('preview_' . $count . '_desc', $preview_data['desc']);
				$count++;
			}
		}

		// Set all the settings from the form too, taking into account the defaults set on the design itself.
		$design_form = $design->getDesignForm();
		if(isset($args['design_defaults'])){
			$design_form->populate_defaults($args['design_defaults']);
		}
		foreach($design_form->input_values(true,true) as $setting_name => $normalized_value){
			$design->setSetting($setting_name, $normalized_value);
		}

	}

	/**
	 * Loops through all the registered default designs and creates a design for them.
	 */
	public function createRegisteredDesigns()
	{
		// loop through all the registered designs
		foreach($this->design_callbacks as $design_template_slug => $designs){
			foreach($designs as $design_slug => $args_callback){
				$design_post = $this->design_manager->getBySlug($design_slug);
				if(! $design_post instanceof Design){
					$this->createNewDesign($design_template_slug, $design_slug, $this->design_callbacks[$design_template_slug][$design_slug]);
				}
			}
		}

		// and make sure there is a design post for each of them
	}
}