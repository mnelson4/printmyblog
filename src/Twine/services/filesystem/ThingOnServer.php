<?php

namespace Twine\services\filesystem;

abstract class ThingOnServer
{
    /**
     * @var string
     */
    protected $path;

    /**
     * Lets us cache whether or not this file/folder exists, so we don't have to keep checking repeatedly during the
     * same request.
     * @var bool|null
     */
    protected $exists;

    public function __construct($path)
    {
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
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return bool success
     */
    public abstract function delete();
}
