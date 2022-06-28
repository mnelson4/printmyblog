<?php

namespace Twine\services\filesystem;

/**
 * Class Folder
 * @package Twine\services\filesystem
 */
class Folder extends ThingOnServer
{
    /**
     * @var Folder
     */
    protected $parent;

    /**
     * @var File
     */
    protected $index_file;

    /**
     * @return bool
     */
    public function exists()
    {
        if ($this->exists === null) {
            $this->exists = is_dir($this->getPath());
        }
        return $this->exists;
    }

    /**
     * @return string
     */
    public function parentFolderPath()
    {
        return dirname($this->getPath());
    }

    /**
     * @return Folder
     */
    public function parentFolder()
    {
        if (! $this->parent instanceof Folder) {
            $this->parent = new Folder($this->parentFolderPath());
        }
        return $this->parent;
    }

    /**
     * If folder doesn't exist, creates it.
     */
    public function ensureExists()
    {
        if (! $this->exists()) {
            $this->parentFolder()->ensureExists();
            mkdir($this->getPath());
            chmod($this->getPath(), 0777);
            $this->exists = true;
            $this->secure();
        }
    }

    /**
     * Make the folder safer by ensuring there is an index file.
     */
    public function secure()
    {
        if (! $this->indexFile()->fileExists()) {
            $this->indexFile()->write('No listing this directory.');
        }
    }

    /**
     * @return File
     */
    protected function indexFile()
    {
        if (! $this->index_file) {
            $this->index_file = new File($this->indexFilePath());
        }
        return $this->index_file;
    }

    /**
     * Returns the path where the index file should be.
     * @return string
     */
    protected function indexFilePath()
    {
        return $this->getPath() . '/index.html';
    }

    /**
     * @return bool success
     */
    public function delete()
    {
        array_map('unlink', glob($this->getPath() . '/*.*'));
        return rmdir($this->getPath());
    }
}
