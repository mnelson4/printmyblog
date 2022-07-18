<?php

namespace PrintMyBlog\entities;

use PrintMyBlog\services\DesignTemplateRegistry;
use Exception;
use PrintMyBlog\services\generators\ProjectFileGeneratorBase;
use Twine\forms\helpers\ImproperUsageException;

/**
 * Class FileFormat
 * @package PrintMyBlog\entities
 */
class FileFormat
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $desc;

    /**
     * @var string
     */
    protected $default_design_template_slug;
    /**
     * @var DesignTemplate
     */
    protected $default_design_template;
    /**
     * @var DesignTemplateRegistry
     */
    protected $design_template_registry;
    /**
     * @var mixed
     */
    protected $generator;

    /**
     * @var string dashicon used for format. See https://developer.wordpress.org/resource/dashicons/
     */
    protected $icon;

    /**
     * @var mixed
     */
    protected $color;

    /**
     * @var string
     */
    protected $extension;

    /**
     * @var bool Whether this version of PMB has all the necessary files to support this format
     */
    protected $supported = true;

    /**
     * @var string text used to upsell if not supported in this version.
     */
    protected $upsell = '';

    /**
     * ProjectFormat constructor.
     *
     * @param array $data {
     * @type string $title
     * @type string $slug title slugified
     * @type string $desc
     * @type ProjectFileGeneratorBase $generator
     * @type string $default design template
     * @type string $color
     * @type string $icon
     * @type string $extension
     * }
     * @throws ImproperUsageException
     */
    public function __construct($data = [])
    {
        if (isset($data['title'])) {
            $this->title = $data['title'];
        }
        if (isset($data['desc'])) {
            $this->desc = $data['desc'];
        }
        if (! isset($data['generator'])) {
            throw new ImproperUsageException(
                // translators: %s format slug
                __('No generator class specified for format "%s"', 'print-my-blog'),
                $this->slug()
            );
        }
        if (isset($data['default'])) {
            $this->default_design_template_slug = (string)$data['default'];
        }
        $this->generator = $data['generator'];
        if (isset($data['icon'])) {
            $this->icon = $data['icon'];
        }
        if (isset($data['color'])) {
            $this->color = $data['color'];
        } else {
            $this->color = 'black';
        }
        if (isset($data['extension'])) {
            $this->extension = $data['extension'];
        } else {
            $this->extension = 'pdf';
        }
        if (array_key_exists('supported', $data)) {
            $this->supported = $data['supported'];
        }
        if (array_key_exists('upsell', $data)) {
            $this->upsell = $data['upsell'];
        }
    }

    /**
     * @param DesignTemplateRegistry $design_template_registry
     */
    public function inject(DesignTemplateRegistry $design_template_registry)
    {
        $this->design_template_registry = $design_template_registry;
    }

    /**
     * @return string
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function titleAndIcon()
    {
        return $this->title() . '<span class="pmb-icon dashicons ' . $this->icon() . '"></span>';
    }

    /**
     * @return string
     */
    public function coloredTitleAndIcon()
    {
        $html = '<span class="pmb-emphasis" style="background-color:' . $this->color() . '">' . $this->titleAndIcon() . '</span>';
        if(! $this->supported() && $this->upsell()){
            $html .= pmb_pro_print_service_only($this->upsell());
        }
        return $html;
    }

    /**
     * Finalizes making the object ready-for-use by setting the slug.
     * This is done because the manager knows the slug initially and this doesn't.
     * @param string $slug
     */
    public function constructFinalize($slug)
    {
        $this->slug = $slug;
        if (! $this->title) {
            $this->title = $slug;
        }
    }

    /**
     * @return string
     */
    public function slug()
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function desc()
    {
        return $this->desc;
    }

    /**
     * Gets the project generator classname.
     * @return string
     */
    public function generatorClassname()
    {
        return $this->generator;
    }

    /**
     * @return string
     */
    public function defaultDesignTemplateSlug()
    {
        return $this->default_design_template_slug;
    }

    /**
     * @return DesignTemplate
     * @throws Exception
     */
    public function getDefaultDesignTemplate()
    {
        if (! $this->default_design_template instanceof DesignTemplate) {
            $this->default_design_template = $this->design_template_registry->getDesignTemplate(
                $this->defaultDesignTemplateSlug()
            );
        }
        return $this->default_design_template;
    }

    /**
     * @return string|null
     */
    public function icon()
    {
        return $this->icon;
    }

    /**
     * @return string|null
     */
    public function color()
    {
        return $this->color;
    }

    /**
     * @return string
     */
    public function extension()
    {
        return $this->extension;
    }

    /**
     * Returns true if this version of PMB has all the necessary files to create files using this format.
     * (Eg some large Javascript files are excluded from some versions of PMB which means some formats can't be
     * created in this version.)
     * @return bool
     */
    public function supported()
    {
        return $this->supported;
    }

    /**
     * @return string
     */
    public function upsell()
    {
        return $this->upsell;
    }
}
