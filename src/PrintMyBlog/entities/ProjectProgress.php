<?php

namespace PrintMyBlog\entities;

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
     * ProjectProgress constructor.
     *
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

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
                self::SETUP_STEP => __('Setup', 'print-my-blog')
            ];
            $formats_in_use = $this->project->getFormatsSelected();
            foreach ($formats_in_use as $format) {
                $steps[self::CHOOSE_DESIGN_STEP_PREFIX . $format->slug()] = sprintf(
                    __('Choose %s Design', 'print-my-blog'),
                    $format->title()
                );
                $steps[self::CUSTOMIZE_DESIGN_STEP_PREFIX . $format->slug()] = sprintf(
                    __('Customize %s Design', 'print-my-blog'),
                    $format->title()
                );
            }
            $steps[self::EDIT_CONTENT_STEP] = __('Edit Content', 'print-my-blog');
            $steps[self::EDIT_METADATA_STEP] = __('Edit Metadata', 'print-my-blog');
            $steps[self::GENERATE_STEP] = __('Generate File', 'print-my-blog');
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
     */
    public function markStepComplete($step, $new_value = true)
    {
        $this->project->setPmbMeta($this->getStepMetaName($step), $new_value);
    }

    public function markChooseDesignStepComplete($format_slug, $new_value = true)
    {
        $this->markStepComplete(self::CHOOSE_DESIGN_STEP_PREFIX . $format_slug, $new_value);
    }

    /**
     * @param string $format_slug
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
}
