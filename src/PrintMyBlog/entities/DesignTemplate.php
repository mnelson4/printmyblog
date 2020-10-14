<?php


namespace PrintMyBlog\entities;


use Exception;
use PrintMyBlog\exceptions\TemplateDoesNotExist;
use PrintMyBlog\services\FileFormatRegistry;
use Twine\forms\base\FormSectionProper;

class DesignTemplate {

	const IMPLIED_DIVISION_MAIN_MATTER = 'main';
	const IMPLIED_DIVISION_PROJECT = 'project';
	const IMPLIED_DIVISION_FRONT_MATTER = 'front_matter';
	const IMPLIED_DIVISION_BACK_MATTER = 'back_matter';

	const DIVISION_ARTICLE = 'article';
	const DIVISION_PART = 'part';
	const DIVISION_VOLUME = 'volume';
	const DIVISION_ANTHOLOGY = 'anthology';

	const TEMPLATE_TITLE_PAGE ='title_page';
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
	 * @var FormSectionProper
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
	 * @var FileFormat
	 */
	protected $format;
	/**
	 * @var array strings indicating support for various features. Eg 'front_matter', 'back_matter', 'part', 'volume',
	 * 'anthology', 'just_content', etc.
	 */
	protected $supports = array();

	/**
	 * DesignTemplate constructor.
	 *
	 * @param $slug
	 * @param $args {
	 * @type string title
	 * @type string format
	 * @type string dir
	 * @type callable design_form_callback
	 * @type callable project_form_callback
	 * }
	 */
	public function __construct($slug, $args){
		$this->slug                  = $slug;
		$this->title                 = $args['title'];
		$this->format_slug           = (string)$args['format'];
		$this->dir                   = (string)$args['dir'];
		$this->default_design_slug   = (string)$args['default'];
		$this->url                   = (string)$args['url'];
		if(isset($args['supports'])){
			$this->supports = (array)$args['supports'];
		}
		$this->design_form_callback  = $args['design_form_callback'];
		$this->project_form_callback = $args['project_form_callback'];
	}

	public function inject(FileFormatRegistry $file_format_registry){
		$this->file_format_registry = $file_format_registry;
	}
	/**
	 * @return string
	 */
	public function getFormatSlug() {
		return $this->format_slug;
	}

	/**
	 * @return FileFormat
	 */
	public function getFormat(){
		if(! $this->format instanceof FileFormat){
			$this->format = $this->file_format_registry->getFormat($this->getFormatSlug());
		}
		return $this->format;
	}

	/**
	 * @return mixed
	 */
	public function getSlug() {
		return $this->slug;
	}

	/**
	 * @return mixed
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Gets the filepath to the root directory containing the design templates files.
	 * @return string
	 */
	public function getDir(){
		return trailingslashit($this->dir);
	}

	/**
	 * @return string
	 */
	public function getDirForTemplates(){
		return $this->getDir() . 'templates/';
	}

	/**
	 * @return string
	 */
	public function getUrl(){
		return trailingslashit($this->url);
	}

	/**
	 * @return string
	 */
	public function getAssetsUrl(){
		return $this->getUrl() . 'assets/';
	}

	/**
	 * @return FormSectionProper
	 */
	public function getDesignFormTemplate()
	{
		if(! $this->design_form instanceof FormSectionProper){
			$this->design_form = call_user_func($this->design_form_callback);
			if(! $this->design_form instanceof FormSectionProper){
				throw new Exception('No Design form was specified for design template ' . $this->slug);
			}
		}
		return $this->design_form;
	}

	/**
	 * Gets the callback that should return the FormSectinProper to be used for defining project meta.
	 * @return callable
	 */
	public function getProjectCallback(){
		return $this->project_form_callback;
	}

	/**
	 * Returns how many nesting levels or divisions this design allows.
	 * 1 means its flat sections, no nesting; 2 means it has parts-and-sections; 3 means books-parts-sections,
	 * 4 means books-parts-sections-subsections, etc.
	 * @return int
	 */
	public function getLevels(){
		if($this->levels === null){

			if($this->supports('anthology')) {
				$this->levels = 4;
			} else if($this->supports('volume')){
				$this->levels = 3;
			} else if($this->supports('part')){
				$this->levels = 2;
			} else{
				$this->levels = 1;
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
	public function getTemplatePathToDivision($division, $beginning = true, $use_fallback = true){
		// add an underscore to the transition if its not the article template.
		if(! $this->templateFileExists($division, $beginning, false) && $use_fallback){
			if(! $this->getFormat()->getDefaultDesignTemplate()->templateFileExists($division, $beginning, false)){
				throw new TemplateDoesNotExist($this->calculateTemplatePathToDivision($division, $beginning));
			}
			// try the default design template, but don't infinitely keep trying fallbacks
			return $this->getFormat()->getDefaultDesignTemplate()->getTemplatePathToDivision($division, $beginning, false);
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
	protected function calculateTemplatePathToDivision($division,$beginning){
		if ( ! $beginning){
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
	public function templateFileExists($division, $beginning = true, $use_fallback = false){
		$exists = file_exists($this->calculateTemplatePathToDivision($division, $beginning));
		if(! $exists && $use_fallback){
			$exists = $this->getFormat()->getDefaultDesignTemplate()->calculateTemplatePathToDivision($division, $beginning);
		}
		return $exists;
	}

	/**
	 * Determines if the design template supports a type of division.
	 * @param string $division see DesignTemplate::validDivisions()
	 *
	 * @return bool
	 */
	public function supports($division){
		return $division === self::IMPLIED_DIVISION_MAIN_MATTER
		|| $this->templateFileExists($division)
		       || in_array($division, $this->supports);
	}

	/**
	 * Gets the slug of the default design
	 * @return string
	 */
	public function getDefaultDesignSlug(){
		return $this->default_design_slug;
	}

	/**
	 * Gets the list of all valid divisions. These
	 * @return string[]
	 */
	public static function validDivisions(){
		return [
			self::DIVISION_ARTICLE,
			self::DIVISION_PART,
			self::DIVISION_VOLUME,
			self::DIVISION_ANTHOLOGY,
		];
	}

	public static function validDivisionsIncludingImplied(){
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
	public static function validPlacements(){
		return [
			self::IMPLIED_DIVISION_FRONT_MATTER,
			self::IMPLIED_DIVISION_MAIN_MATTER,
			self::IMPLIED_DIVISION_BACK_MATTER
		];
	}
}