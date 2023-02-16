<?php


namespace PrintMyBlog\system;

/**
 * Class FileUploads
 * @package PrintMyBlog\system
 */
class FileUploads
{
    /**
     * Sets up wahtever filters this class listens for
     */
    public function setup()
    {
        add_filter('upload_mimes', [$this, 'filterUploadMimeTypes']);
    }

    /**
     * Allows uploading font files.
     * @param string $mimes
     * @return mixed
     */
    public function filterUploadMimeTypes($mimes)
    {
        $mimes['ttf'] = 'application/x-font-ttf';
        $mimes['otf'] = 'application/x-font-opentype';
        $mimes['wff'] = 'application/font-woff';
        $mimes['wff2'] = 'application/font-woff2';
        return $mimes;
    }
}