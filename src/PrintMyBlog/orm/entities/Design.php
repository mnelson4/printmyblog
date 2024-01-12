<?php

namespace PrintMyBlog\orm\entities;

use Exception;
use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\exceptions\DesignTemplateDoesNotExist;
use PrintMyBlog\services\DesignTemplateRegistry;
use Twine\forms\base\FormSection;
use Twine\forms\inputs\FormInputBase;
use Twine\orm\entities\PostWrapper;

/**
 * Class ProjectDesign
 * Describes a design, an instance of the design template
 * @package PrintMyBlog\domain
 */
class Design extends PostWrapper
{
    /**
     * @var DesignTemplate
     */
    protected $design_template;
    /**
     * @var DesignTemplateRegistry
     */
    protected $design_template_manager;

    /**
     * @var FormSection
     */
    protected $project_form;

    /**
     * @var FormSection
     */
    protected $design_form;

    /**
     * @param DesignTemplateRegistry $design_template_manager
     */
    public function inject(DesignTemplateRegistry $design_template_manager)
    {
        $this->design_template_manager = $design_template_manager;
    }

    /**
     * @return DesignTemplate
     * @throws DesignTemplateDoesNotExist
     */
    public function getDesignTemplate()
    {
        if (! $this->design_template instanceof DesignTemplate) {
            $this->design_template = $this->design_template_manager->getDesignTemplate(
                $this->getPmbMeta('design_template')
            );
        }
        return $this->design_template;
    }

    /**
     * @return bool
     */
    public function designTemplateExists()
    {
        try {
            $this->getDesignTemplate();
            return true;
        } catch (DesignTemplateDoesNotExist $e) {
            return false;
        }
    }

    /**
     * Gets the saved metadata and falls back to the default. If the setting doesn't exist, returns null.
     * @param string $setting_name
     * @return mixed|null
     * @throws Exception
     */
    public function getSetting($setting_name)
    {
        // tries to get the setting from a postmeta
        $setting = $this->getPmbMeta($setting_name);
        if ($setting !== null) {
            return $setting;
        }
        // otherwise falls back to using the default in the form
        if ($setting_name === 'design_template') {
            throw new Exception(
                sprintf(
                    'Could not determine design template for the design "%s". The postmeta is missing.',
                    $this->getWpPost()->post_title
                )
            );
        }
        $form    = $this->getDesignForm();
        $section = $form->findSection($setting_name);
        if ($section instanceof FormInputBase) {
            return $section->getDefault();
        }
        return null;
    }

    /**
     * Gets settings for this design
     * @return array keys are setting names, values are their values (either from postmetas or defaults from form defaults)
     * @throws Exception
     */
    public function getSettings()
    {
        $form = $this->getDesignForm();
        $settings = [];
        foreach ($form->inputsInSubsections('name') as $setting_name => $input) {
            $settings[$setting_name] = $this->getSetting($setting_name);
        }
        return $settings;
    }

    /**
     * @param string $setting_name
     * @param mixed $value
     */
    public function setSetting($setting_name, $value)
    {
        $this->setPmbMeta($setting_name, $value);
    }

    /**
     * @return FormSection
     */
    public function getDesignForm()
    {
        if (! $this->design_form instanceof FormSection) {
            $design_template = $this->getDesignTemplate();
            $this->design_form = $design_template->getNewDesignFormTemplate();
            $defaults = [];
            foreach ($this->design_form->inputsInSubsections() as $input) {
                $saved_default = $this->getSetting($input->name());
                if ($saved_default !== null) {
                    $defaults[$input->name()] = $saved_default;
                }
            }
            $this->design_form->populateDefaults($defaults);
        }
        return $this->design_form;
    }

    /**
     * Gets the form that defines properties to be set on the project, based on the chosen design.
     * @return FormSection
     */
    public function getProjectForm()
    {
        if (! $this->project_form instanceof FormSection) {
            $this->project_form = call_user_func($this->getDesignTemplate()->getProjectCallback(), $this);
        }
        return $this->project_form;
    }

    /**
     * @return array numerically indexed, each item being an array with keys 'url' and 'desc'
     */
    public function getPreviews()
    {
        $index = 1;
        return [
            $this->getPreview(1),
            $this->getPreview(2),
        ];
    }

    /**
     * @param int $index
     *
     * @return array with keys url and desc
     */
    public function getPreview($index)
    {
        return [
            'url' => $this->getPmbMeta('preview_' . $index . '_url'),
            'desc' => $this->getPmbMeta('preview_' . $index . '_desc'),
        ];
    }

    /**
     * Returns true if this is the default slug for its design template.
     * @return bool
     */
    public function isDefault()
    {
        return $this->getWpPost()->post_name === $this->getDesignTemplate()->getDefaultDesignSlug();
    }

    /**
     * If this is the default design, returns true.
     * @return Design|null|bool
     */
    public function getCustomizationOf()
    {
        if ($this->isDefault()) {
            return true;
        }
        return $this->getDesignTemplate()->getDefaultDesign();
    }
}
