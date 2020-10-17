<?php


namespace PrintMyBlog\entities;


use PrintMyBlog\services\DesignTemplateRegistry;
use Exception;
use Twine\forms\helpers\ImproperUsageException;

class FileFormat {
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
	 * ProjectFormat constructor.
	 *
	 * @param string title
	 * @param array $data {
	 * @type string $slug title slugified
	 * }
	 */
	public function __construct($data = []){
		if(isset($data['title'])){
			$this->title = $data['title'];
		}
		if(! isset($data['generator'])){
			throw new ImproperUsageException(__('No generator class specified for format "%s"', 'print-my-blog'), $this->slug());
		}
		if( isset($data['default'])){
			$this->default_design_template_slug = (string)$data['default'];
		}
		$this->generator = $data['generator'];
	}

	public function inject(DesignTemplateRegistry $design_template_registry){
		$this->design_template_registry = $design_template_registry;
	}

	public function title()
	{
		return $this->title;
	}

	/**
	 * Finalizes making the object ready-for-use by setting the slug.
	 * This is done because the manager knows the slug initially and this doesn't.
	 * @param $slug
	 */
	public function construct_finalize($slug){
		$this->slug = $slug;
		if( ! $this->title){
			$this->title = $slug;
		}
	}

	/**
	 * @return string
	 */
	public function slug(){
		return $this->slug;
	}

	/**
	 * Gets the project generator classname.
	 * @return string
	 */
	public function generatorClassname(){
		return $this->generator;
	}

	/**
	 * @return string
	 */
	public function defaultDesignTemplateSlug(){
		return $this->default_design_template_slug;
	}

	/**
	 * @return DesignTemplate
	 * @throws Exception
	 */
	public function getDefaultDesignTemplate(){
		if( ! $this->default_design_template instanceof DesignTemplate){
			$this->default_design_template = $this->design_template_registry->getDesignTemplate($this->defaultDesignTemplateSlug());
		}
		return $this->default_design_template;
	}
}