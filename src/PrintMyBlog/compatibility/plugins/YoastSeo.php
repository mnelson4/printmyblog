<?php


namespace PrintMyBlog\compatibility\plugins;


use PrintMyBlog\system\CustomPostTypes;
use Twine\compatibility\CompatibilityBase;

class YoastSeo extends CompatibilityBase
{
    /**
     * Set hooks for compatibility with PMB for any request.
     */
    public function setHooks()
    {
        // remove pmb content from sitemap
        add_filter('wpseo_sitemap_index_links',[$this,'removePmbContentFromSitemap']);
    }

    /**
     * Filters the sitemap to remove pmb_content. See https://wordpress.org/support/topic/pmb_content-sitemap-xml/
     * @param array $links
     * @return array
     */
    public function removePmbContentFromSitemap($links){
        foreach($links as $key => $link_data){
            if(strstr($link_data['loc'],CustomPostTypes::CONTENT) !== false ){
                unset($links[$key]);
            }
        }
        return $links;
    }
}