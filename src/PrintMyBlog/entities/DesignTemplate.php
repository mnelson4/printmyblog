<?php


namespace PrintMyBlog\entities;


class DesignTemplate {
	protected $format;
	protected $slug;
	protected $title;
	/**
	 * @var filepath to where the design's files are located
	 */
	protected $directory;

	/**
	 * @var URI to where the design's files (like JS, CSS, and images) are located.
	 */
	protected $uri;
	protected $design_options;
	protected $project_data;

	public function __construct($title, $slug, $format, $args){
		$this->title = $title;
		$this->slug = $slug;
		$this->format = (string)$format;
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
}