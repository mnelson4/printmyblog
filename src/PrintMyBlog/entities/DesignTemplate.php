<?php

namespace PrintMyBlog\entities;

use Exception;
use PrintMyBlog\exceptions\TemplateDoesNotExist;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\managers\DesignManager;
use PrintMyBlog\services\FileFormatRegistry;
use PrintMyBlog\services\SectionTemplateRegistry;
use Twine\forms\base\FormSection;

class DesignTemplate
{

    const IMPLIED_DIVISION_MAIN_MATTER = 'main';
    const IMPLIED_DIVISION_PROJECT = 'project';
    const IMPLIED_DIVISION_FRONT_MATTER = 'front_matter';
    const IMPLIED_DIVISION_BACK_MATTER = 'back_matter';

    const DIVISION_ARTICLE = 'article';
    const DIVISION_PART = 'part';
    const DIVISION_VOLUME = 'volume';
    const DIVISION_ANTHOLOGY = 'anthology';

    const TEMPLATE_TITLE_PAGE = 'title_page';
    const TEMPLATE_JUST_CONTENT = 'just_content';

    protected $format_slug;
    protected $slug;
    protected $title;
    /**
     * @var string filepath to where the design's files are located
     */
    protected $dir;

    /**
     * @var callable
     */
    protected $design_form_callback;

    /**
     * @var string
     */
    protected $default_design_slug;

    /**
     * @var FormSection
     */
    protected $design_form;
    /**
     * @var callable
     */
    protected $project_form_callback;
    /**
     * @var int
     */
    protected $levels;
    /**
     * URL of the design templates directory.
     * @var string
     */
    protected $url;
    /**
     * @var FileFormatRegistry
     */
    protected $file_format_registry;
    /**
     * @var DesignManager
     */
    protected $design_manager;
    /**
     * @var FileFormat
     */
    protected $format;
    /**
     * @var array strings indicating support for various features. Eg 'front_matter', 'back_matter', 'part', 'volume',
     * 'anthology', etc.
     */
    protected $supports = array();
    /**
     * @var SectionTemplate[] keys are slugs
     */
    protected $custom_templates = array();
    /**
     * @var string
     */
    protected $docs;
    /**
     * @var SectionTemplateRegistry
     */
    private $section_template_registry;

    /**
     * DesignTemplate constructor.
     *
     * @param $slug unique string identifying this design template
     * @param $args {
     * @type string title sometimes shown to users
     * @type string format "print_pdf" or "digital_pdf"
     * @type string dir directory of the design (folder that ocntains its `functions.php`)
     * @type callable design_form_callback returns \Twine\forms\base\FormSection to use on the "Customize Design" step
     * @type callable project_form_callback returns a \Twine\forms\base\FormSection that will be merged with that of
     * other designs used for the project on the "Edit Metadata" step.
     * @type string docs URL of documentation
     * @type string[] $supports can include 'front_matter', 'back_matter','part','volume','anthology'
     * @type string[] $custom_templates includes slugs of custom templates registered with pmb_register_section_template()
     * @type string $default_design_slug string of the default design that uses this design template
     * }
     */
    public function __construct($slug, $args)
    {
        $this->slug                  = $slug;
        $this->title                 = $args['title'];
        $this->format_slug           = (string)$args['format'];
        $this->dir                   = (string)$args['dir'];
        $this->default_design_slug   = (string)$args['default'];
        $this->url                   = (string)$args['url'];
        if (isset($args['docs'])) {
            $this->docs = (string)$args['docs'];
        }
        if (isset($args['supports'])) {
            $this->supports = (array)$args['supports'];
        }
        if (isset($args['custom_templates'])) {
            $this->custom_templates = $args['custom_templates'];
        }
        $this->design_form_callback  = $args['design_form_callback'];
        $this->project_form_callback = $args['project_form_callback'];
    }

    public function inject(FileFormatRegistry $file_format_registry, DesignManager $design_manager, SectionTemplateRegistry $section_template_registry)
    {
        $this->file_format_registry = $file_format_registry;
        $this->design_manager = $design_manager;
        $this->section_template_registry = $section_template_registry;
    }
    /**
     * @return string
     */
    public function getFormatSlug()
    {
        return $this->format_slug;
    }

    /**
     * @return FileFormat
     */
    public function getFormat()
    {
        if (! $this->format instanceof FileFormat) {
            $this->format = $this->file_format_registry->getFormat($this->getFormatSlug());
        }
        return $this->format;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Gets the filepath to the root directory containing the design templates files.
     * @return string
     */
    public function getDir()
    {
        return trailingslashit($this->dir);
    }

    /**
     * @return string
     */
    public function getDirForTemplates()
    {
        return $this->getDir() . 'templates/';
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return trailingslashit($this->url);
    }

    /**
     * @return string
     */
    public function getAssetsUrl()
    {
        return $this->getUrl() . 'assets/';
    }

    /**
     * @return FormSection
     */
    public function getDesignFormTemplate()
    {
        if (! $this->design_form instanceof FormSection) {
            $this->design_form = $this->getNewDesignFormTemplate();
        }
        return $this->design_form;
    }

    /**
     * @return FormSection
     * @throws Exception
     */
    public function getNewDesignFormTemplate()
    {
        $form = call_user_func($this->design_form_callback);
        if (! $form instanceof FormSection) {
            throw new Exception('No Design form was specified for design template ' . $this->slug);
        }
        return $form;
    }

    /**
     * Gets the callback that should return the FormSectinProper to be used for defining project meta.
     * @return callable
     */
    public function getProjectCallback()
    {
        return $this->project_form_callback;
    }

    /**
     * Returns how many nesting levels or divisions this design allows.
     * 0 means its flat sections, no nesting; 1 means it has parts-and-sections; 2 means books-parts-sections,
     * 3 means books-parts-sections-subsections, etc.
     * @return int
     */
    public function getLevels()
    {
        if ($this->levels === null) {
            if ($this->supports('anthology')) {
                $this->levels = 3;
            } elseif ($this->supports('volume')) {
                $this->levels = 2;
            } elseif ($this->supports('part')) {
                $this->levels = 1;
            } else {
                $this->levels = 0;
            }
        }
        return $this->levels;
    }

    /**
     * Gets the path to where a template file is. If it doesn't exist, returns
     * Makes no guarantee that the file exists.
     *
     * @param string $division see DesignTemplate::validDivisions()
     * @param bool $beginning
     */
    public function getTemplatePathToDivision($division, $beginning = true, $use_fallback = true)
    {
        // add an underscore to the transition if its not the article template.
        if (! $this->templateFileExists($division, $beginning, false) && $use_fallback) {
            // check if it's a custom template and has a filepath. In that case, use it
            // find out if the template is custom, if so ask it for its filepath
            if ($this->supportsCustomTemplate($division)) {
                $section_template = $this->section_template_registry->get($division);
                if ($section_template instanceof SectionTemplate && $section_template->hasFilepath()) {
                    return $section_template->getFilepath();
                }
            }
            if (! $this->getFormat()->getDefaultDesignTemplate()->templateFileExists($division, $beginning, false)) {
                throw new TemplateDoesNotExist($this->calculateTemplatePathToDivision($division, $beginning));
            }
            // try the default design template, but don't infinitely keep trying fallbacks
            return $this->getFormat()->getDefaultDesignTemplate()->getTemplatePathToDivision(
                $division,
                $beginning,
                false
            );
        }
        return $this->calculateTemplatePathToDivision($division, $beginning);
    }

    /**
     * Calculates where thie template file SHOULD be in this design template, if it exists at all.
     * @param $division
     * @param $beginning
     *
     * @return string
     * @throws Exception
     */
    protected function calculateTemplatePathToDivision($division, $beginning)
    {
        if (! $beginning) {
            $division .= '_end';
        }
        return  $this->getDirForTemplates() . $division . '.php';
    }

    /**
     * @param $division
     * @param string $beginning
     *
     * @return bool
     */
    public function templateFileExists($division, $beginning = true, $use_fallback = false)
    {
        $exists = file_exists($this->calculateTemplatePathToDivision($division, $beginning));
        if (! $exists && $use_fallback) {
            $exists = $this->getFormat()->getDefaultDesignTemplate()->calculateTemplatePathToDivision(
                $division,
                $beginning
            );
        }
        return $exists;
    }

    /**
     * Determines if the design template supports a type of division.
     * @param string $division see DesignTemplate::validDivisions()
     *
     * @return bool
     */
    public function supports($division)
    {
        return $division === self::IMPLIED_DIVISION_MAIN_MATTER
        || $this->templateFileExists($division)
               || in_array($division, $this->supports);
    }

    /**
     * Gets the slug of the default design
     * @return string
     */
    public function getDefaultDesignSlug()
    {
        return $this->default_design_slug;
    }

    /**
     * Gets the list of all valid divisions. These
     * @return string[]
     */
    public static function validDivisions()
    {
        return [
            self::DIVISION_ARTICLE,
            self::DIVISION_PART,
            self::DIVISION_VOLUME,
            self::DIVISION_ANTHOLOGY,
        ];
    }

    public function divisionLabelSingular($level)
    {
        $display = [
             __('article', 'print-my-blog'),
             __('part', 'print-my-blog'),
            __('volume', 'print-my-blog'),
            __('anthology', 'print-my-blog')
        ];
        return $display[$level];
    }

    public static function validDivisionsIncludingImplied()
    {
        return array_merge(
            [
                self::IMPLIED_DIVISION_PROJECT
            ],
            self::validPlacements(),
            self::validDivisions()
        );
    }

    /**
     * All the valid values for the 'placement' column on the project sections table.
     * @return string[]
     */
    public static function validPlacements()
    {
        return [
            self::IMPLIED_DIVISION_FRONT_MATTER,
            self::IMPLIED_DIVISION_MAIN_MATTER,
            self::IMPLIED_DIVISION_BACK_MATTER
        ];
    }

    /**
     * @return string[]
     */
    public function getCustomTemplates()
    {
        return $this->custom_templates;
    }

    /**
     * Adds the template to the list of custom template slugs used by this design template.
     * It is assumed this custom template has already been registered with pmb_register_section_template()
     * @param $custom_template_slug
     */
    public function addCustomTemplate($custom_template_slug)
    {
        $this->custom_templates[] = $custom_template_slug;
    }

    /**
     * Indicates whether or not this custom section template is supported by this design template
     * @param string $custom_template_slug
     * @return bool
     */
    public function supportsCustomTemplate($custom_template_slug)
    {
        $supported_custom_templates = $this->getCustomTemplates();
        return in_array($custom_template_slug, $supported_custom_templates);
    }

    /**
     * Tell us the slug of the section template you want to use, and we'll tell you the slug of the closest
     * available template for this design template. It might be what you requested, or one of its fallbacks.
     * @param string $desired_template_slug
     * @return string template slug or empty string if we should use the default
     */
    public function resolveSectionTemplateToUse($desired_template_slug)
    {
        while ($desired_template_slug) {
            if ($this->supports($desired_template_slug) || $this->supportsCustomTemplate($desired_template_slug)) {
                return $desired_template_slug;
            }

            $section_template = $this->section_template_registry->get($desired_template_slug);
            $desired_template_slug = $section_template->fallbackSlug();
        }
        return '';
    }

    /**
     * Gets the default design object
     * @return Design|null
     */
    public function getDefaultDesign()
    {
        return $this->design_manager->getBySlug($this->getDefaultDesignSlug());
    }

    /**
     * @return string
     */
    public function getDocs()
    {
        return $this->docs;
    }

    /**
     * @param string $docs
     */
    public function setDocs($docs)
    {
        $this->docs = $docs;
    }
}
