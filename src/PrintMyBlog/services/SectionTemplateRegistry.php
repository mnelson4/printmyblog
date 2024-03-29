<?php

namespace PrintMyBlog\services;

use PrintMyBlog\entities\SectionTemplate;
use PrintMyBlog\system\Context;

/**
 * Class SectionTemplateRegistry
 * @package PrintMyBlog\services
 */
class SectionTemplateRegistry
{
    /**
     * @var array[][]
     */
    protected $callback_args;
    /**
     * @var SectionTemplate[]
     */
    protected $instances;
    /**
     * @var DesignTemplateRegistry
     */
    private $design_template_registry;

    /**
     * Injected by Context.
     * @param DesignTemplateRegistry $design_template_registry
     */
    public function inject(DesignTemplateRegistry $design_template_registry)
    {
        $this->design_template_registry = $design_template_registry;
    }

    /**
     * Registers the section template for use.
     * @param string $slug
     * @param string[] $design_templates
     * @param callable $callback
     * @throws \PrintMyBlog\exceptions\DesignTemplateDoesNotExist
     */
    public function register($slug, $design_templates, $callback)
    {
        $this->callback_args[$slug] = $callback;
        foreach ($design_templates as $design_template_slug) {
            $design = $this->design_template_registry->getDesignTemplate($design_template_slug);
            $design->addCustomTemplate($slug);
        }
    }

    /**
     * @param string $slug
     * @return SectionTemplate|null
     */
    public function get($slug)
    {
        if (! isset($this->instances[$slug])) {
            $this->instances[$slug] = $this->createNew($slug, $this->callback_args[$slug]);
        }
        return $this->instances[$slug];
    }

    /**
     * @return SectionTemplate[]
     */
    public function getAll()
    {
        foreach ($this->callback_args as $slug => $callback) {
            if (! isset($this->instances[$slug])) {
                $this->get($slug);
            }
        }
        return $this->instances;
    }

    /**
     * @param string $slug
     * @param array $args_callback see PrintMyBlog\entities\SectionTemplate::__construct to see what should be passed in
     * @return object
     */
    protected function createNew($slug, $args_callback)
    {
        $template = Context::instance()->useNew(
            'PrintMyBlog\entities\SectionTemplate',
            [
                call_user_func($args_callback),
            ]
        );
        $template->constructFinalize($slug);
        return $template;
    }
}
