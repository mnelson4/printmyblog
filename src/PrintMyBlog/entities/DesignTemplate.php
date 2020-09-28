<?php


namespace PrintMyBlog\entities;


use Exception;
use Twine\forms\base\FormSectionProper;

class DesignTemplate {

	const IMPLIED_DIVISION_MAIN = 'main';
	const IMPLIED_DIVISION_PROJECT = 'project';
	const IMPLIED_DIVISION_FRONTMATTER = 'front_matter';

	const DIVISION_ARTICLE = 'article';
	const DIVISION_PART = 'part';
	const DIVISION_VOLUME = 'volume';
	const DIVISION_ANTHOLOGY = 'anthology';
	const DIVISION_BACK_MATTER = 'back_matter';

	protected $format;
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
		$this->format                = (string)$args['format'];
		$this->dir                   = (string)$args['dir'];
		$this->design_form_callback  = $args['design_form_callback'];
		$this->project_form_callback = $args['project_form_callback'];
	}

	/**
	 * @return string
	 */
	public function getFormat() {
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
		return plugins_url($this->getDir());
	}

	/**
	 * @return FormSectionProper
	 */
	public function getDesignForm()
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
	 * Gets the path to where a template file SHOULD be, if it were to exist.
	 * Makes no guarantee that the file exists.
	 *
	 * @param string $division see DesignTemplate::validDivisions()
	 * @param bool $beginning
	 */
	public function getTemplatePathToDivision($division, $beginning = true){
		// add an underscore to the transition if its not the article template.
		if ( ! $beginning){
			$division .= '_end';
		}
		return $this->getDirForTemplates() . $division . '.php';
	}

	/**
	 * @param $division
	 * @param string $beginning
	 *
	 * @return bool
	 */
	public function templateFileExists($division, $beginning = true){
		return file_exists($this->getTemplatePathToDivision($division, $beginning));
	}

	/**
	 * Determines if the design template supports a type of division.
	 * @param string $division see DesignTemplate::validDivisions()
	 *
	 * @return bool
	 */
	public function supports($division){
		return $this->templateFileExists($division, 'begin');
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
			self::DIVISION_BACK_MATTER
		];
	}

	public static function validDivisionsIncludingImplied(){
		return array_merge(
			[
				self::IMPLIED_DIVISION_MAIN,
				self::IMPLIED_DIVISION_FRONTMATTER,
				self::IMPLIED_DIVISION_PROJECT
			],
			self::validDivisions()
		);
	}

}