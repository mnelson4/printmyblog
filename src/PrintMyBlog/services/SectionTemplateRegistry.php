<?php


namespace PrintMyBlog\services;


use PrintMyBlog\entities\SectionTemplate;
use PrintMyBlog\system\Context;

class SectionTemplateRegistry
{
    protected $callbacks;
    /**
     * @var SectionTemplate[]
     */
    protected $instances;
    /**
     * @var DesignTemplateRegistry
     */
    private $design_template_registry;

    public function inject(DesignTemplateRegistry $design_template_registry){
        $this->design_template_registry = $design_template_registry;
    }

    public function register($slug, $design_templates, $callback){
        $this->callbacks[$slug] = $callback;
        foreach($design_templates as $design_template_slug){
            $design = $this->design_template_registry->getDesignTemplate($design_template_slug);
            $design->addCustomTemplate($slug);
        }
    }

    /**
     * @param $slug
     * @return SectionTemplate|null
     */
    public function get($slug){
        if(! isset($this->instances[$slug])){
            $this->instances[$slug] = $this->createNew($slug, $this->callbacks[$slug]);
        }
        return $this->instances[$slug];
    }

    public function getAll(){
        foreach($this->callbacks as $slug => $callback){
            if(!isset($this->instances[$slug])){
                $this->get($slug);
            }
        }
        return $this->instances;
    }

    /**
     * @param $slug
     * @param $args_callback see PrintMyBlog\entities\SectionTemplate::__construct to see what should be passed in
     * @return object
     */
    protected function createNew($slug, $args_callback){
        $template = Context::instance()->useNew(
            'PrintMyBlog\entities\SectionTemplate',
            [
                call_user_func($args_callback)
            ]
        );
        $template->constructFinalize($slug);
        return $template;
    }
}