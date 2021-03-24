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
    public function register($slug, $callback){
        $this->callbacks[$slug] = $callback;
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