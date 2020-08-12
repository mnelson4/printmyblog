<?php


namespace Twine\services\filesystem;


class Folder extends ThingOnServer {
	/**
	 * @var Folder
	 */
	protected $parent;

	/**
	 * @var File
	 */
	protected $index_file;

	public function __construct($folder_path){
		parent::__construct($folder_path);
	}

	/**
	 * @return bool
	 */
	public function exists()
	{
		return is_dir($this->getPath());
	}
	public function parentFolderPath()
	{
		return dirname($this->getPath());
	}

	/**
	 * @return Folder
	 */
	public function parentFolder()
	{
		if( ! $this->parent instanceof Folder){
			$this->parent = new Folder($this->parentFolderPath());
		}
		return $this->parent;
	}

	public function ensureExists()
	{
		if( ! $this->exists()){
			$this->parentFolder()->ensureExists();
			mkdir($this->getPath());
			chmod($this->getPath(),'0700');
			$this->secure();
		}
	}

	public function secure()
	{
		if( ! $this->indexFile()->fileExists()){
			$this->indexFile()->write('No listing this directory.');
		}
	}

	/**
	 * @return File
	 */
	protected function indexFile()
	{
		if( ! $this->index_file) {
			$this->index_file = new File( $this->indexFilePath() );
		}
		return $this->index_file;
	}

	protected function indexFilePath()
	{
		return $this->getPath() . '/index.html';
	}
}