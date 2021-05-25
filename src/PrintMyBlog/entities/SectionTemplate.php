<?php

namespace PrintMyBlog\entities;

class SectionTemplate
{
    protected $title;
    protected $fallback;
    protected $slug;
    /**
     * The ProjectFileGeneratorBase's should use a template file with the same name as the slug in the templates
     * directory of the design. Eg for the Buurma Design, and we're using the section template 'just_content',
     * if printmyblog/designs/pdf/digital/buurma/templates/just_content.php exists, use that (even if a filepath is defined).
     * But if not, use the filepath. If that doesn't exist, fallback to the file format's default design's file.
     * Ie, printmyblog/designs/pdf/digital/classic/templates/just_content.php. If that doesn't exist, fallback to the
     * default division template in this design (printmyblog/designs/pdf/buurma/templates/article.php) and lastly fallback
     * to the default design's default template (printmyblog/designs/pdf/classic/tempalte/article.php).
     * @var string
     */
    protected $filepath;

    /**
     * SectionTemplate constructor.
     * @param $data array {
     * @type string $title
     * @type string $fallback slug of section template to fallback to
     * @type string $filepath the filepath of the section template if it doesn't exist in the design template's
     * "templates" folder
     */
    public function __construct($data)
    {
        if (isset($data['title'])) {
            $this->title = $data['title'];
        }
        if (isset($data['fallback'])) {
            $this->fallback = $data['fallback'];
        }
        if(isset($data['filepath'])){
            $this->filepath = $data['filepath'];
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

    /**
     * @return string
     */
    public function getFilepath(){
        return $this->filepath;
    }

    /**
     * Returns true if the section template has a filepath defined.
     * @return bool
     */
    public function hasFilepath(){
        return (bool)$this->filepath;
    }
}
