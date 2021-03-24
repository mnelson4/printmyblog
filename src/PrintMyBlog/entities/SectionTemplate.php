<?php


namespace PrintMyBlog\entities;


class SectionTemplate
{
    protected $title;
    protected $fallback;
    protected $slug;
    public function __construct($data){
        if(isset($data['title'])){
            $this->title = $data['title'];
        }
        if(isset($data['fallback'])){
            $this->fallback = $data['fallback'];
        }
    }

    public function constructFinalize($slug){
        $this->slug = $slug;
        if(! $this->title){
            $this->title = $slug;
        }
    }

    /**
     * @return string translated
     */
    public function title(){
        return $this->title;
    }

    /**
     * @return string
     */
    public function fallback(){
        return $this->fallback;
    }

    public function slug(){
        return $this->slug;
    }
}