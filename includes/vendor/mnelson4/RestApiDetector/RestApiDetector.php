<?php

namespace mnelson4\RestApiDetector;
use WP_Error;

/**
 * Class RestApiDetector
 *
 * Finds the REST API base URL for the site requested. Works with both self-hosted sites and WordPress.com sites.
 *
 *
 * @package     Event Espresso
 * @author         Mike Nelson
 * @since         $VID:$
 *
 */
class RestApiDetector
{
    protected $site;
    protected $name;
    protected $description;
    protected $rest_api_url;
    protected $local;
    protected $initialized = false;

    /**
     * RestApiDetector constructor.
     * @param $site
     * @throws RestApiDetectorError
     */
    public function __construct($site)
    {
        $this->setSite($site);
        $this->getSiteInfo();
    }

    /**
     * Gets the site name and URL (works if they provide the "site" query param too,
     * being the URL, including schema, of a self-hosted or WordPress.com site)
     * @since $VID:$
     * @throws RestApiDetectorError
     */
    public function getSiteInfo()
    {
        // check for a site request param
        if(empty($this->getSite())){
            $this->setName(get_bloginfo('name'));
            $this->setDescription(get_bloginfo('description'));
            $this->setRestApiUrl(get_rest_url());
            $this->setSite(get_bloginfo('url'));
            $this->setLocal(true);
            return;
        }
        // If they forgot to add http(s), add it for them.
        if(strpos($this->getSite(), 'http://') === false && strpos($this->getSite(), 'https://') === false) {
            $this->setSite( 'http://' . $this->getSite());
        }
        // if there is one, check if it exists in wordpress.com, eg "retirementreflections.com"
        $site = sanitize_text_field($this->getSite());


        // Let's see if it's self-hosted...
        $data = $this->getSelfHostedSiteInfo($site);
        if($data === false){
            $data = $this->getWordPressComSiteInfo($site);
        }
        // Alright, there was no link to the REST API index. But maybe it's a WordPress.com site...
        return $data;
    }

    /**
     * Tries to get the site's name, description, and URL, assuming it's self-hosted.
     * Returns a true on success, false if the site works but wasn't a self-hosted WordPress site, or
     * throws an exception if the site is self-hosted WordPress but had an error.
     * @since $VID:$
     * @param $site
     * @return bool false if the site exists but it's not a self-hosted WordPress site.
     * @throws RestApiDetectorError
     */
    protected function getSelfHostedSiteInfo($site){
        $response = $this->sendHttpGetRequest($site);
        if (is_wp_error($response)) {
            throw new RestApiDetectorError($response);
        }
        $response_body = wp_remote_retrieve_body($response);
        $wp_api_url = null;
        $matches = array();
        if( ! preg_match(
            //looking for somethign like "<link rel='https://api.w.org/' href='http://wpcowichan.org/wp-json/' />"
                '<link rel=\'https\:\/\/api\.w\.org\/\' href=\'(.*)\' \/>',
                $response_body,
                $matches
            )
            ||  count($matches) !== 2) {
            // The site exists, but it's not self-hosted.
            return false;
        }
        // grab from site index
        $wp_api_url = $matches[1];
        $response = $this->sendHttpGetRequest($wp_api_url);
        if (is_wp_error($response)) {
            // The WP JSON index existed, but didn't work. Let's tell the user.
            throw new RestApiDetectorError($response);
        }
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body,true);
        if (! is_array($response_data)) {
            throw new RestApiDetectorError(
                new WP_Error('no_json', __('The self-hosted WordPress site has an error in its REST API data.', 'print-my-blog'))
            );
        }
        if (isset($response_data['code'], $response_data['message'])) {
            throw new RestApiDetectorError(
                new WP_Error($response_data['code'], $response_data['message'])
            );
        }
        if(isset($response_data['name'], $response_data['description'])){
            $this->setName($response_data['name']);
            $this->setDescription($response_data['description']);
            $this->setRestApiUrl($wp_api_url . 'wp/v2/');
            $this->setLocal(false);
            return true;
        }
        // so we didn't get an error or a proper response, but it's JSON? That's really weird.
        throw new RestApiDetectorError(
            new WP_Error('unknown_response', __('The self-hosted WordPress site responded with an unexpected response.', 'print-my-blog'))
        );
    }

    /**
     * Tries to get the site name, description and URL from a site on WordPress.com.
     * Returns true success, or throws a RestApiDetectorError. If the site doesn't appear to be on WordPress.com
     * also has an error.
     * @since $VID:$
     * @param $site
     * @return bool
     * @throws RestApiDetectorError
     */
    protected function getWordPressComSiteInfo($site){
        $domain = str_replace(array('http://','https://'),'',$site);
        $response = $this->sendHttpGetRequest(
            'https://public-api.wordpress.com/rest/v1.1/sites/' . $domain
        );

        // let's see what WordPress.com has to say about this site...
        if (is_wp_error($response)) {
            throw new RestApiDetectorError($response);
        }
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        if (! is_array($response_data)) {
            throw new RestApiDetectorError(
                new WP_Error('no_json', __('The WordPress.com site has an error in its REST API data.', 'print-my-blog'))
            );
        }
        if(isset($response_data['error'], $response_data['message'])) {
            if($response_data['error'] === 'unknown_blog') {
                throw new RestApiDetectorError(
                    new WP_Error('not_wordpress', esc_html__('The URL you provided does not appear to be a WordPress website', 'print-my-blog'))
                );
            }
            throw new RestApiDetectorError(
                new WP_Error($response_data['error'], $response_data['message'])
            );
        }
        if (isset($response_data['name'], $response_data['description'])) {
            $this->setName($response_data['name']);
            $this->setDescription(($response_data['description']));
            $this->setRestApiUrl('https://public-api.wordpress.com/wp/v2/sites/' . $domain);
            $this->setLocal(false);
            return true;
        }
        // so we didn't get an error or a proper response, but it's JSON? That's really weird.
        throw new RestApiDetectorError(
            new WP_Error('unknown_response', __('The WordPress.com site responded with an unexpected response.', 'print-my-blog'))
        );
    }

    /**
     * @since $VID:$
     * @param $url
     * @return array|WP_Error
     */
    protected function sendHttpGetRequest($url)
    {
        return wp_remote_get(
            $url,
            [
                'timeout' => 30,
                'sslverify' => false
            ]
        );
    }

    /**
     * @return string
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    protected function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @param mixed $site
     */
    protected function setSite($site): void
    {
        $this->site = $site;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    protected function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getRestApiUrl()
    {
        return $this->rest_api_url;
    }

    /**
     * @param mixed $rest_api_url
     */
    protected function setRestApiUrl($rest_api_url): void
    {
        $this->rest_api_url = $rest_api_url;
    }

    /**
     * @return mixed
     */
    public function getLocal()
    {
        return $this->local;
    }

    /**
     * @param mixed $local
     */
    protected function setLocal($local): void
    {
        $this->local = $local;
    }

    /**
     * @return bool
     */
    protected function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * @param bool $initialized
     */
    protected function setInitialized(bool $initialized): void
    {
        $this->initialized = $initialized;
    }
}
// End of file RestApiDetector.php
// Location: mnelson4/RestApiDetector.php
