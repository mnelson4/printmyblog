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
        add_filter('wp_check_filetype_and_ext', [$this, 'filterCheckFileType'], 10, 3);
    }

    /**
     * Correct the mime types and extension for the font types.
     * Taken verbatim from the plugin custom-fonts's includes\class-bsf-custom-fonts-admin.php
     * @param $defaults
     * @param $file
     * @param $filename
     * @return mixed
     */
    public function filterCheckFileType( $defaults, $file, $filename )
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ('ttf' === $extension) {
            $defaults['type'] = 'application/x-font-ttf';
            $defaults['ext'] = 'ttf';
        }

        if ('otf' === $extension) {
            $defaults['type'] = 'application/x-font-otf';
            $defaults['ext'] = 'otf';
        }
        return $defaults;
    }

    /**
     * Allows uploading font files.
     * @param string $mimes
     * @return mixed
     */
    public function filterUploadMimeTypes($mimes)
    {
        // servers are inconsistent about what MIME type they detect font files to be. Which is why we also filter wp_check_filetype_and_ext.
        $mimes['ttf'] = 'application/x-font-ttf';
        $mimes['otf'] = 'application/x-font-otf';
        $mimes['wff'] = 'application/font-woff';
        $mimes['wff2'] = 'application/font-woff2';
        return $mimes;
    }
}