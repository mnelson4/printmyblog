<?php

namespace PrintMyBlog\entities;

class SectionTemplate
{
    protected $title;
    protected $fallback;
    protected $slug;
    public function __construct($data)
    {
        if (isset($data['title'])) {
            $this->title = $data['title'];
        }
        if (isset($data['fallback'])) {
            $this->fallback = $data['fallback'];
        }
    }

    public function constructFinalize($slug)
    {
        $this->slug = $slug;
        if (! $this->title) {
            $this->title = $slug;
        }
    }

    /**
     * @return string translated
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * Returns the slug of the section template to fallback to
     * @return string
     */
    public function fallbackSlug()
    {
        return $this->fallback;
    }

    public function slug()
    {
        return $this->slug;
    }
}
