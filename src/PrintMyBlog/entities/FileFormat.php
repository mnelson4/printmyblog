<?php


namespace PrintMyBlog\entities;


use PrintMyBlog\services\generators\ProjectFileGeneratorBase;
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
		$this->generator = $data['generator'];
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
}