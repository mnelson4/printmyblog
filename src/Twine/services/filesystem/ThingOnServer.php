<?php


namespace Twine\services\filesystem;


class ThingOnServer {
	/**
	 * @var string
	 */
	protected $path;
	public function __construct($path){
		$this->path = $this->standardizeFilepath($path);
	}
	/**
	 * Ensures the slashes are all unix-style, which PHP is happy with even on Windows.
	 * @param $file_path
	 *
	 * @return string
	 */
	protected function standardizeFilepath($file_path)
	{
		return str_replace(array( '\\', '/' ), '/', $file_path);
	}

	/**
	 * @return string
	 */
	public function getPath(){
		return $this->path;
	}
}