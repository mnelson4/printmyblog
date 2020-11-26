<?php

namespace PrintMyBlog\services;

use Exception;
use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\managers\DesignManager;
use PrintMyBlog\system\CustomPostTypes;
use WP_Post;
use Twine\helpers\Array2;

/**
 * Class DesignRegistry
 * For registering default designs to be created during activation.
 * @package PrintMyBlog\services
 */
class DesignRegistry
{

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
    ) {
        $this->design_manager           = $design_manager;
        $this->design_template_registry = $design_template_registry;
    }

    /**
     * @param $design_template_slug string
     * @param $design_slug string
     * @param $callback callable returns an array to be passed into createNewDesign
     */
    public function registerDesignCallback($design_template_slug, $design_slug, $callback)
    {
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
    protected function createNewDesign($design_template_slug, $design_slug, $callback)
    {
        list($design_template, $args) = $this->getTemplateAndArgs($design_template_slug, $callback);
        $design_post_id = wp_insert_post([
            'post_title'   => $args['title'],
            'post_name'    => $design_slug,
            'post_type'    => CustomPostTypes::DESIGN,
            'post_content' => $args['description'],
            'post_excerpt' => Array2::setOr($args, 'quick_description',''),
            'post_status' => 'publish'
        ]);
        if (! $design_post_id) {
            throw new Exception(
                'There was an error inserting the design post "'
                . $design_slug
                . '" with '
                . var_export($args, true)
            );
        }
        /* @var $design Design */
        $design = $this->design_manager->getById($design_post_id);
        $design->setPmbMeta('format', $design_template->getFormatSlug());
        $design->setPmbMeta('design_template', $design_template->getSlug());
        $this->setArgsForDesign($design, $args);
    }

    /**
     * @param string $design_template_slug
     * @param callable $design_slug
     * @return array containing a DesignTemplate and an array of args
     * @throws Exception
     */
    protected function getTemplateAndArgs($design_template_slug, $callback){
        $design_template = $this->design_template_registry->getDesignTemplate($design_template_slug);
        $args = call_user_func($callback, $design_template);
        return [$design_template, $args];
    }

    protected function updateDesign(Design $design, $design_template_slug, $callback)
    {
        list($design_template, $args) = $this->getTemplateAndArgs($design_template_slug, $callback);
        wp_update_post(
            [
                'ID' => $design->getWpPost()->ID,
                'post_excerpt' => Array2::setOr($args, 'quick_description',''),
                'post_content' => $args['description']
            ]
        );
        $this->setArgsForDesign($design, $args);
    }

    protected function setArgsForDesign(Design $design, $args)
    {
        if(isset($args['author'])){
            foreach($args['author'] as $field => $value){
                $design->setPmbMeta('author_' . $field, $value);
            }
        }
        // Set preview images
        if (isset($args['previews'])) {
            $count = 1;
            foreach ((array)$args['previews'] as $preview_data) {
                $design->setPmbMeta('preview_' . $count . '_url', $preview_data['url']);
                $design->setPmbMeta('preview_' . $count . '_desc', $preview_data['desc']);
                $count++;
            }
        }

        // Set all the settings from the form too, taking into account the defaults set on the design itself.
        $design_form = $design->getDesignForm();
        if (isset($args['design_defaults'])) {
            $design_form->populateDefaults($args['design_defaults']);
        }
        foreach ($design_form->inputValues(true, true) as $setting_name => $normalized_value) {
            $design->setSetting($setting_name, $normalized_value);
        }
    }

    /**
     * Loops through all the registered default designs and creates a design for them.
     */
    public function createRegisteredDesigns()
    {
        // loop through all the registered designs
        foreach ($this->design_callbacks as $design_template_slug => $designs) {
            foreach ($designs as $design_slug => $args_callback) {
                $design = $this->design_manager->getBySlug($design_slug);
                if ( $design instanceof Design) {
                    $this->updateDesign($design, $design_template_slug, $args_callback);
                } else {
                    $this->createNewDesign(
                        $design_template_slug,
                        $design_slug,
                        $this->design_callbacks[$design_template_slug][$design_slug]
                    );
                }
            }
        }
    }
}
