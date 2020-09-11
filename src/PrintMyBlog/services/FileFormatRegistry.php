<?php


namespace PrintMyBlog\services;


use PrintMyBlog\entities\FileFormat;

class FileFormatRegistry {
	/**
	 * @var FileFormat[]
	 */
	protected $formats;

	/**
	 * @param $slug
	 *
	 * @return FileFormat
	 */
	public function getFormat($slug){
		if(isset($this->formats[$slug])){
			return $this->formats[$slug];
		}
		return null;
	}
	/**
	 * Gets the declared project formats
	 * @return FileFormat[]
	 */
	public function getFormats()
	{
		return $this->formats;
	}

	/**
	 * @param slug $slug
	 * @param array $args
	 */
	public function registerFormat($slug, $args){
		$format = new FileFormat($args);
		$format->construct_finalize($slug);
		$this->formats[$slug] = $format;
	}
}