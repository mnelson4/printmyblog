<?php

namespace Twine\services\filesystem;

/**
 * Class FileWriter
 * Give it a filepath, and it can make sure that file/folder exists.
 *
 * @package Twine\services\filesystem
 */
class File extends ThingOnServer
{


    /**
     * @var resource
     */
    protected $file_handle;

    /**
     * Folder containing the file
     *
     * @var Folder $folder
     */
    protected $folder;

    /**
     * Writes the content to the file. Note: it defaults to appending, so if the file already exists, the content
     * will be *added* to it; it will not overwrite it.
     * @param string $content
     */
    public function write($content)
    {
        $this->ensureFolderExists();
        // todo: use WP Filesystem
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
        fwrite($this->getFileHandle(), $content);
    }

    /**
     * Deletes the file.
     *
     * @return bool success. If the file didn't exist anyways, also returns true.
     */
    public function delete()
    {
        if ($this->fileExists()) {
            return unlink($this->path);
        }
        return true;
    }
    /**
     * @return resource
     */
    protected function getFileHandle()
    {
        if (! $this->file_handle) {
            // todo: replace with wp filesystem
            //phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
            $this->file_handle = fopen($this->path, 'a+');
        }
        return $this->file_handle;
    }

    /**
     *
     * @return Folder
     */
    public function getFolder()
    {
        if (! $this->folder instanceof Folder) {
            $this->folder = new Folder(dirname($this->getPath()));
        }
        return $this->folder;
    }

    /**
     * Ensure_folder_exists_and_is_writable
     * ensures that a folder exists and is writable, will attempt to create folder if it does not exist
     * Also ensures all the parent folders exist, and if not tries to create them.
     * Also, if this function creates the folder, adds a .htaccess file and index.html file
     */
    public function ensureFolderExists()
    {
        return $this->getFolder()->ensureExists();
    }

    /**
     * @return bool
     */
    public function folderExists()
    {
        return $this->getFolder()->exists();
    }

    /**
     * @return bool
     */
    public function fileExists()
    {
        if ($this->exists === null) {
            $this->exists = is_file($this->getPath());
        }
        return $this->exists;
    }
}
