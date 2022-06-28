<?php

namespace PrintMyBlog\entities;

use PrintMyBlog\controllers\Admin;
use PrintMyBlog\orm\entities\Project;

/**
 * Class ProjectProgress
 * @package PrintMyBlog\entities
 * For logic relating to a project's progress.
 */
class ProjectProgress
{
    const META_NAME = 'progress_';

    const SETUP_STEP = 'setup';
    const CHOOSE_DESIGN_STEP_PREFIX = 'choose_';
    const CUSTOMIZE_DESIGN_STEP_PREFIX = 'customize_';
    const EDIT_CONTENT_STEP = 'edit_content';
    const EDIT_METADATA_STEP = 'edit_metdata';
    const GENERATE_STEP = 'generate';
    /**
     * @var Project
     */
    protected $project;

    /**
     * Cache the steps instead of recalculating it a ton.
     * @var string[]
     */
    protected $steps;

    /**
     * @var array
     */
    protected $step_to_subaction_mapping = [
        self::SETUP_STEP => Admin::SLUG_SUBACTION_PROJECT_SETUP,
        self::CHOOSE_DESIGN_STEP_PREFIX => Admin::SLUG_SUBACTION_PROJECT_CHANGE_DESIGN,
        self::CUSTOMIZE_DESIGN_STEP_PREFIX => Admin::SLUG_SUBACTION_PROJECT_CUSTOMIZE_DESIGN,
        self::EDIT_CONTENT_STEP => Admin::SLUG_SUBACTION_PROJECT_CONTENT,
        self::EDIT_METADATA_STEP => Admin::SLUG_SUBACTION_PROJECT_META,
        self::GENERATE_STEP => Admin::SLUG_SUBACTION_PROJECT_GENERATE,
    ];

    /**
     * ProjectProgress constructor.
     *
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Sets the project to its initial state.
     */
    public function initialize()
    {
        $steps = array_keys($this->getSteps());
        $step_progress = [];
        foreach ($steps as $step_name) {
            $step_progress[$step_name] = false;
        }
        foreach ($step_progress as $slug => $done) {
            $this->project->setPmbMeta($this->getStepMetaName($slug), $done);
        }
    }

    /**
     * @param string $step_slug
     * @return string
     */
    protected function getStepMetaName($step_slug)
    {
        return self::META_NAME . $step_slug;
    }

    /**
     * Returns an array where keys are step slugs, and values are their translated names for display.
     * @return string[]
     */
    public function getSteps()
    {
        if ($this->steps === null) {
            $steps = [
                self::SETUP_STEP => __('Setup', 'print-my-blog'),
            ];
            $formats_in_use = $this->project->getFormatsSelected();
            foreach ($formats_in_use as $format) {
                $steps[self::CHOOSE_DESIGN_STEP_PREFIX . $format->slug()] = sprintf(
                    // translators: %s design title
                    __('Choose %s Design', 'print-my-blog'),
                    $format->title()
                );
                $steps[self::CUSTOMIZE_DESIGN_STEP_PREFIX . $format->slug()] = sprintf(
                    // translators: %s design title
                    __('Customize %s Design', 'print-my-blog'),
                    $format->title()
                );
            }
            $steps[self::EDIT_CONTENT_STEP] = __('Edit Content', 'print-my-blog');
            $steps[self::EDIT_METADATA_STEP] = __('Edit Metadata', 'print-my-blog');
            $steps[self::GENERATE_STEP] = __('Generate Print Page', 'print-my-blog');
            $this->steps = $steps;
        }
        return $this->steps;
    }

    /**
     * Gets the step progress. Keys are step slugs, values are whether it's complete or not.
     * @return bool[]
     */
    public function getStepProgress()
    {
        $steps = array_keys($this->getSteps());
        $step_progress = [];
        foreach ($steps as $step) {
            $step_progress[$step] = (bool)$this->project->getPmbMeta($this->getStepMetaName($step));
        }
        return $step_progress;
    }

    /**
     * Finds the next incomplete step and marks it as complete
     */
    public function markNextStepComplete()
    {
        $next_step = $this->getNextStep();
        $this->markStepComplete($next_step);
    }

    /**
     * Marks the specified step as complete
     * @param string $step
     * @param bool $new_value
     */
    public function markStepComplete($step, $new_value = true)
    {
        $this->project->setPmbMeta($this->getStepMetaName($step), $new_value);
    }

    /**
     * @param string $format_slug
     * @param bool $new_value
     */
    public function markChooseDesignStepComplete($format_slug, $new_value = true)
    {
        $this->markStepComplete(self::CHOOSE_DESIGN_STEP_PREFIX . $format_slug, $new_value);
    }

    /**
     * @param string $format_slug
     * @param bool $new_value
     */
    public function markCustomizeDesignStepComplete($format_slug, $new_value = true)
    {
        $this->markStepComplete(self::CUSTOMIZE_DESIGN_STEP_PREFIX . $format_slug, $new_value);
    }

    /**
     * Gets the next incomplete step, or the last step if they're all done.
     * @return string
     */
    public function getNextStep()
    {
        $step_progress = $this->getStepProgress();
        foreach ($step_progress as $step_slug => $complete) {
            if (! $complete) {
                return $step_slug;
            }
        }
        return $step_slug;
    }

    /**
     * Indicates whether this step was already complete or not.
     * @param string $step_name
     * @return bool|null returns null if $step_name isn't a step in this project's progress
     */
    public function isStepComplete($step_name)
    {
        $steps = $this->getStepProgress();
        if (isset($steps[$step_name])) {
            return (bool)$steps[$step_name];
        }
        return null;
    }

    /**
     * @return array keys are step slugs, values are URLs
     */
    public function stepsToUrls()
    {
        $base_url_args = [
            'ID' => $this->project->getWpPost()->ID,
            'action' => Admin::SLUG_ACTION_EDIT_PROJECT,
        ];
        $mapping = [];
        foreach ($this->getSteps() as $step => $label) {
            $args = $this->mapStepToSubactionArgs($step);
            $mappings[$step] = add_query_arg(
                array_merge($base_url_args, $args),
                admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
            );
        }
        return $mappings;
    }
    /**
     * @param string $step
     * @return array {
     * @type $subaction string
     * @type $format string
     * }
     */
    public function mapStepToSubactionArgs($step)
    {
        if (strpos($step, self::CHOOSE_DESIGN_STEP_PREFIX) === 0) {
            $format = str_replace(self::CHOOSE_DESIGN_STEP_PREFIX, '', $step);
            $args['subaction'] = Admin::SLUG_SUBACTION_PROJECT_CHANGE_DESIGN;
            $args['format'] = $format;
        } elseif (strpos($step, self::CUSTOMIZE_DESIGN_STEP_PREFIX) === 0) {
            $format = str_replace(self::CUSTOMIZE_DESIGN_STEP_PREFIX, '', $step);
            $args['subaction'] = Admin::SLUG_SUBACTION_PROJECT_CUSTOMIZE_DESIGN;
            $args['format'] = $format;
        } else {
            switch ($step) {
                case self::SETUP_STEP:
                    $subaction = Admin::SLUG_SUBACTION_PROJECT_SETUP;
                    break;
                case self::EDIT_CONTENT_STEP:
                    $subaction = Admin::SLUG_SUBACTION_PROJECT_CONTENT;
                    break;
                case self::EDIT_METADATA_STEP:
                    $subaction = Admin::SLUG_SUBACTION_PROJECT_META;
                    break;
                case self::GENERATE_STEP:
                default:
                    $subaction = Admin::SLUG_SUBACTION_PROJECT_GENERATE;
            }
            $args['subaction'] = $subaction;
            $args['format'] = null;
        }
        return $args;
    }

    /**
     * @return array
     */
    public function getNextStepPageArgs()
    {
        return $this->mapStepToSubactionArgs($this->getNextStep());
    }

    /**
     * @param string $subaction
     * @param string $format
     *
     * @return string
     */
    public function mapSubactionToStep($subaction, $format = null)
    {
        $subaction_to_step = array_flip($this->step_to_subaction_mapping);
        if (isset($subaction_to_step[$subaction])) {
            $step = $subaction_to_step[$subaction];
            if ($format) {
                $step .= $format;
            }
        } else {
            $step = self::SETUP_STEP;
        }
        return $step;
    }
}
