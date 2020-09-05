<?php


namespace PrintMyBlog\entities;


class Format {
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
	public function __construct($title, $data = []){
		$this->title = $title;
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
	}

	/**
	 * @return string
	 */
	public function slug(){
		return $this->slug;
	}
}