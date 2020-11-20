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
            $this->instances[$slug] = Context::instance()->useNew(
                'PrintMyBlog\entities\SectionTemplate',
                [
                    $slug,
                    call_user_func($this->callbacks[$slug])
                ]
            );
            $this->instances[$slug]->constructFinalize($slug);
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
}