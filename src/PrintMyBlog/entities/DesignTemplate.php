<?php


namespace PrintMyBlog\entities;


use Exception;
use Twine\forms\base\FormSectionProper;

class DesignTemplate {
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
		$this->levels                = isset($args['levels']) ? intval($args['levels']) : 1;
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
		return $this->dir;
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
		return $this->levels;
	}
}