<?php


namespace PrintMyBlog\orm\entities;

use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\services\DesignTemplateRegistry;
use Twine\forms\base\FormSectionProper;
use Twine\orm\entities\PostWrapper;

/**
 * Class ProjectDesign
 * Describes a design, an instance of the design template
 * @package PrintMyBlog\domain
 */
class Design extends PostWrapper {
	/**
	 * @var DesignTemplate
	 */
	protected $design_template;
	/**
	 * @var DesignTemplateRegistry
	 */
	protected $design_template_manager;

	/**
	 * @var FormSectionProper
	 */
	protected $project_form;

	public function inject(DesignTemplateRegistry $design_template_manager){
		$this->design_template_manager = $design_template_manager;
	}

	/**
	 * @return DesignTemplate
	 */
	public function getDesignTemplate(){
		if( ! $this->design_template instanceof DesignTemplate){
			$this->design_template = $this->design_template_manager->getDesignTemplate($this->getPmbMeta('template'));
		}
		return $this->design_template;
	}

	/**
	 * Gets the form that defines properties to be set on the project, based on the chosen design.
	 * @return FormSectionProper
	 */
	public function getProjectForm(){
		if( ! $this->project_form instanceof FormSectionProper){
			$this->project_form = call_user_func($this->getDesignTemplate()->getProjectCallback(), $this);
		}
		return $this->project_form;
	}
}