<?php


namespace PrintMyBlog\orm\managers;


use Twine\orm\managers\PostWrapperManager;

class DesignManager extends PostWrapperManager {
	/**
	 * @var $design_manager_callbacks callable[]
	 */
	protected $design_manager_callbacks;

	/**
	 * @var $designs Design[]
	 */
	protected $designs;
	public function registerDesignCallback($slug, $design_template_slug, $callback){

	}

	/**
	 * @param $slug
	 * @return Design
	 */
	public function getDesign($slug){
		if(! isset($this->designs[$slug])){

		}
	}
}