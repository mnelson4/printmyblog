<?php


namespace PrintMyBlog\entities;


class SectionTemplate
{
    protected $title;
    protected $fallback;
    protected $slug;
    public function __construct($title,$fallback){
        $this->title = $title;
        $this->fallback = $fallback;
    }

    public function constructFinalize($slug){
        $this->slug = $slug;
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