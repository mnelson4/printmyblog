<?php


namespace PrintMyBlog\orm\entities;

use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\services\DesignTemplateRegistry;
use Twine\forms\base\FormSectionProper;
use Twine\forms\inputs\FormInputBase;
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
	protected $design_form;

	public function inject(DesignTemplateRegistry $design_template_manager){
		$this->design_template_manager = $design_template_manager;
	}

	/**
	 * @return DesignTemplate
	 */
	public function getDesignTemplate(){
		if( ! $this->design_template instanceof DesignTemplate){
			$this->design_template = $this->design_template_manager->getDesignTemplate($this->getPmbMeta('design_template'));
		}
		return $this->design_template;
	}

	public function getSetting($setting_name) {
		// tries to get the setting from a postmeta
		$setting = $this->getPmbMeta( $setting_name );
		if ( $setting !== null ) {
			return $setting;
		}
		// otherwise falls back to using the default in the form
		if ( $setting_name === 'design_template' ) {
			throw new Exception(
				sprintf( 'Could not determine design template for the design "%s". The postmeta is missing.',
					$this->getWpPost()->post_title )
			);
		}
		$form    = $this->getDesignForm();
		$section = $form->findSection( $setting_name );
		if ( $section instanceof FormInputBase ) {
			return $section->get_default();
		}
		return null;
	}

	/**
	 * @param $setting_name string
	 * @param $value mixed
	 */
	public function setSetting($setting_name, $value){
		$this->setPmbMeta($setting_name, $value);
	}

	/**
	 * @return FormSectionProper
	 */
	public function getDesignForm(){
		if(! $this->design_form instanceof FormSectionProper){
			$design_template = $this->getDesignTemplate();
			$this->design_form = clone $design_template->getDesignFormTemplate();
			$defaults = [];
			foreach($this->design_form->inputs_in_subsections() as $input){
				$saved_default = $this->getSetting($input->name());
				if($saved_default !== null){
					$defaults[$input->name()] = $saved_default;
				}
			}
			$this->design_form->populate_defaults($defaults);
		}
		return $this->design_form;
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