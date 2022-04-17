<?php


namespace PrintMyBlog\services;


use Twine\services\filesystem\File;
use WP_Error;

class ExternalResourceCache
{
    const option_name = 'pmb_external_resource_map';
    /**
     * @var array|boolean keys are external resouce URLs, values are their internal resource names; if boolean
     * FALSE then it hasn't been initialized yet.
     */
    private $mapping = false;

    /**
     * Stores the mapping so it can be saved later during the request
     * @param string $external_resource_url absolute URL
     * @param string|null $internal_copy_url (relative to wp-content/uploads/pmb/)
     */
    protected function mapExternalUrlToFilename($external_resource_url, $internal_copy_path){
        $this->initMap();
        $this->mapping[$external_resource_url] = $internal_copy_path;
    }

    /**
     * Gets the filename (relative to wp-content/uploads/pmb/) of the local resource from the URL to the external,
     * @param $external_resource_url
     * @return string|null
     */
    protected function getCopiedFilenameFromExternalUrl($external_resource_url){
        $this->initMap();
        if(isset($this->mapping[$external_resource_url])){
            return $this->mapping[$external_resource_url];
        }
        return null;
    }

    /**
     * Gets the absolute URL of the copy of the external resource
     * @param $external_resource_url
     * @return string
     */
    public function getLocalUrlFromExternalUrl($external_resource_url){
        $filename = $this->getCopiedFilenameFromExternalUrl($external_resource_url);
        if( ! $filename){
            return null;
        }
        return $this->getCacheUrl() . $filename;
    }

    /**
     * Gets the absolute filepath from of the local copy of the external resource
     * @param $external_resource_url
     * @return string
     */
    protected function getLocalFullPathFromExternalUrl($external_resource_url){
        $filename = $this->getCopiedFilenameFromExternalUrl($external_resource_url);
        if( ! $filename){
            return null;
        }
        return $this->getCacheDir() . $filename;
    }

    /**
     * @return string
     */
    protected function getCacheDir(){
        $uploads_dir = wp_upload_dir();
        return $uploads_dir['basedir'] . '/pmb/cache/';
    }

    /**
     * @return string
     */
    protected function getCacheUrl(){
        $uploads_dir = wp_upload_dir();
        return $uploads_dir['baseurl'] . '/pmb/cache/';
    }

    /**
     * @param $external_url
     * @return string|null URL of copied resource, or null if there was an error
     */
    public function writeAndMapFile($external_url){
        $copy_filename = sanitize_file_name($external_url);
        $folder = $this->getCacheDir();

        $response = wp_remote_get($external_url);
        if(is_array($response) && ! $response instanceof WP_Error){
            $filepath = $folder . '/' . $copy_filename;
            $content = $response['body'];
            $file = new File($filepath);
            $file->write($content);

        }
        $this->mapExternalUrlToFilename($external_url, $copy_filename);
        return $this->getLocalUrlFromExternalUrl($external_url);
    }

    /**
     * Gets the current mapping from external resources to copied resource URLs
     * @return array
     */
    public function getMapping(){
        $this->initMap();
        $mapping_absolute_urls = [];
        foreach($this->mapping as $external_url => $copied_filename){
            $mapping_absolute_urls[$external_url] = $this->getCacheUrl() . $copied_filename;
        }
        return $mapping_absolute_urls;
    }


    protected function initMap(){
        if( $this->mapping === false){
            $this->mapping = (array)get_option(self::option_name, []);
        }
    }

    /**
     * Updates the mapping using mapping data saved on this class
     */
    public function saveMapping(){
        update_option(self::option_name, $this->mapping);
    }

    /**
     * Arrays of domains to treat as local and to not cache locally.
     * @return array
     */
    public function domainsToNotMap(){
        return apply_filters(
            'PrintMyBlog\services\ExternalResourceCache->domainsToNotMap()',
            [
                site_url(),
                '.wp.com',
            ]
        );
    }
}