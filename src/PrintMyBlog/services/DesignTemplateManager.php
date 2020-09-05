<?php


namespace PrintMyBlog\services;


use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\orm\entities\Design;

class DesignTemplateManager {
	/**
	 * @var $design_template_callbacks callable[]
	 */
	protected $design_template_callbacks;

	/**
	 * @var $design_templates DesignTemplate
	 */
	protected $design_templates;

	/**
	 * @param $slug
	 * @param callabel $callback
	 */
	public function registerDesignTemplateCallback($slug, $callback){
		$this->design_template_callbacks[$slug] = $callback;
	}

	/**
	 * @param $slug
	 *
	 * @return DesignTemplate
	 */
	public function getDesignTemplate($slug){
		if(! isset($this->design_templates[$slug])){
			if(! isset($this->design_template_callbacks[$slug])) {
				throw new Exception( 'There is no callback for the design template "' . $slug . '"' );
			}
			$design_template = call_user_func($this->design_template_callbacks[$slug]);
		}
		if(! $design_template instanceof DesignTemplate){
			throw new Exception('Did not find a proper DesignTemplate for slug "' . $slug . '"');
		}
		return $design_template;
	}
}