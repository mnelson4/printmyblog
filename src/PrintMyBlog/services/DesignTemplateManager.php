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
			$this->design_templates[$slug] = call_user_func($this->design_template_callbacks[$slug]);
		}
		if(! $this->design_templates[$slug] instanceof DesignTemplate){
			throw new Exception('Did not find a proper DesignTemplate for slug "' . $slug . '"');
		}
		return $this->design_templates[$slug];
	}

	/**
	 * Gets all the registered design templates
	 * @return DesignTemplate[]
	 */
	public function getDesignTemplates()
	{
		foreach($this->design_template_callbacks as $slug => $callback){
			if(! isset($this->design_templates[$slug]) || ! $this->design_templates[$slug] instanceof DesignTemplate){
				$this->design_templates[$slug] = call_user_func($this->design_template_callbacks[$slug]);
			}
		}
		return $this->design_templates;
	}
}