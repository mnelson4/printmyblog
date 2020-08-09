<?php
namespace Twine\services\filesystem;

/**
 * Class FileWriter
 * Give it a filepath, and it can make sure that file/folder exists.
 *
 * @package Twine\services\filesystem
 */
class FileWriter {

	/**
	 * @var string filepath to folder
	 */
	protected $file_path;

	/**
	 * @var resource
	 */
	protected $file_handle;

	/**
	 * Folder containing the file
	 *
	 * @var string $folder_path
	 */
	protected $folder_path;

	/**
	 * FileWriter constructor.
	 *
	 * @param $filepath
	 */
	public function __construct($filepath){
		$this->file_path   = $this->standardizeFilepath($filepath);
		$this->folder_path = dirname($this->file_path);
	}

	/**
	 * Writes the content to the file. Note: it defaults to appending, so if the file already exists, the content
	 * will be *added* to it; it will not overwrite it.
	 * @param $content
	 */
	public function write($content){
		$this->ensureFolderExists();
		fwrite($this->getFileHandle(), $content);
	}

	/**
	 * Deletes the file.
	 *
	 * @return bool success. If the file didn't exist anyways, also returns true.
	 */
	public function delete()
	{
		if($this->fileExists()){
			return unlink($this->file_path);
		}
		return true;
	}
	/**
	 * @return resource
	 */
	protected function getFileHandle()
	{
		if( ! $this->file_handle){
			$this->file_handle = fopen($this->file_path, 'a+');
		}
		return $this->file_handle;
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
	 * Gets the parent folder. If provided with file, gets the folder that contains it.
	 * If provided a folder, gets its parent folder.
	 * @param string $file_or_folder_path
	 * @return string parent folder, ENDING with a directory separator
	 */
	protected function getParentFolder()
	{
		$file_or_folder_path = $this->folder_path;
		// find the last /, ignoring a / on the very end
		// eg if given "/var/something/somewhere/", we want to get "somewhere"'s
		// parent folder, "/var/something/"
		$ds = strlen($file_or_folder_path) > 1
			? strrpos($file_or_folder_path, '/', -2)
			: strlen($file_or_folder_path);
		return substr($file_or_folder_path, 0, $ds + 1);
	}

	/**
	 * @return string
	 */
	public function getFilePath(){
		return $this->file_path;
	}

	/**
	 *
	 * @return string
	 */
	public function getFolderPath()
	{
		return $this->folder_path;
	}

	/**
	 * ensure_folder_exists_and_is_writable
	 * ensures that a folder exists and is writable, will attempt to create folder if it does not exist
	 * Also ensures all the parent folders exist, and if not tries to create them.
	 * Also, if this function creates the folder, adds a .htaccess file and index.html file
	 * @return bool false if folder isn't writable; true if it exists and is writeable,
	 */
	public function ensureFolderExists()
	{
		if( ! $this->folderExists()){
			mkdir($this->getFolderPath(),'0777', true);
		}
	}

	/**
	 * @return bool
	 */
	public function folderExists()
	{
		return is_dir($this->getFolderPath());
	}

	/**
	 * @return bool
	 */
	public function fileExists()
	{
		return is_file($this->getFilePath());
	}
}