<?php

namespace PrintMyBlog\services;

use PrintMyBlog\orm\managers\ExternalResourceManager;
use stdClass;
use Twine\services\filesystem\File;
use Twine\services\filesystem\Folder;
use WP_Error;

/**
 * Class ExternalResourceCache
 * @package PrintMyBlog\services
 */
class ExternalResourceCache
{
    /**
     * @var ExternalResourceManager
     */
    private $external_resouce_manager;

    /**
     * @param ExternalResourceManager $external_resource_manager
     */
    public function inject(ExternalResourceManager $external_resource_manager)
    {
        $this->external_resouce_manager = $external_resource_manager;
    }

    /**
     * @return string
     */
    protected function getCacheDir()
    {
        $uploads_dir = wp_upload_dir();
        return $uploads_dir['basedir'] . '/pmb/cache/';
    }

    /**
     * @return string
     */
    protected function getCacheUrl()
    {
        $uploads_dir = wp_upload_dir();
        return $uploads_dir['baseurl'] . '/pmb/cache/';
    }

    /**
     * @param string $external_url
     * @return string|null|false URL of copied resource, null if not yet copied, or false if there was an error
     */
    public function writeAndMapFile($external_url)
    {
        $start_of_querystring = strpos($external_url, '?');
        if (! $start_of_querystring === false) {
            $querystring = substr($external_url, $start_of_querystring);
            $external_url_sans_querystring = substr($external_url, 0, $start_of_querystring);
        } else {
            $querystring = '';
            $external_url_sans_querystring = $external_url;
        }

        $copy_filename = sanitize_file_name($external_url_sans_querystring);

        $extension = pathinfo($external_url, PATHINFO_EXTENSION);
        if (! $extension) {
            $copy_filename .= '.avif';
        }

        $folder = $this->getCacheDir();

        $response = wp_remote_get(
            $external_url,
            [
                'sslverify' => false,
                'timeout' => 15,
                'user-agent' => 'PostmanRuntime/7.26.8',
                'httpversion' => '1.1',
            // streaming the file directly to the FS sounds more efficient, but it actually still goes into memory and seems buggy
            // 'stream' => true,
            // 'filename'=> $folder . $copy_filename,
            ]
        );
        if (is_array($response) && $response['response']['code'] === 200 && ! $response instanceof WP_Error) {
            $filepath = $folder . '/' . $copy_filename;
            $content = $response['body'];
            $file = new File($filepath);
            $file->write($content);
            $this->external_resouce_manager->map($external_url, $copy_filename . $querystring);
        } else {
            $this->external_resouce_manager->map($external_url, null);
        }

        return $this->getLocalUrlFromExternalUrl($external_url);
    }

    /**
     * @param string $external_url
     * @return string|null|false null if not yet cached; false if there was an error caching it
     */
    public function getLocalUrlFromExternalUrl($external_url)
    {
        $external_resource = $this->external_resouce_manager->getByExternalUrl($external_url);
        if ($external_resource) {
            if ($external_resource->getCopyFilename()) {
                return $this->getCacheUrl() . $external_resource->getCopyFilename();
            }
            return false;
        }
        return null;
    }

    /**
     * Gets the current mapping from external resources to copied resource URLs
     * @return array|stdClass if it's empty (so WP's localize_script will still make it a JS object)
     */
    public function getMapping()
    {
        foreach ($this->external_resouce_manager->getAllMapping() as $external_resource) {
            $cached_filename = $external_resource->getCopyFilename();
            if ($cached_filename) {
                $cached_file_url = $this->getCacheUrl() . $external_resource->getCopyFilename();
            } else {
                $cached_file_url = null;
            }
            $mapping_absolute_urls[$external_resource->getExternalUrl()] = $cached_file_url;
        }
        if (empty($mapping_absolute_urls)) {
            return new stdClass();
        }
        return $mapping_absolute_urls;
    }

    /**
     * Arrays of domains to treat as local and to not cache locally.
     * @return array
     */
    public function domainsToNotMap()
    {
        return apply_filters(
            'PrintMyBlog\services\ExternalResourceCache->domainsToNotMap()',
            [
                str_replace(['http://', 'https://'], '', site_url()),
                '.wp.com',
                'data:',
            ]
        );
    }

    /**
     * Clears the external resource cache.
     */
    public function clear()
    {
        $this->external_resouce_manager->clear();
        $folder = new Folder($this->getCacheDir());
        $folder->delete();
    }
}
