<?php

namespace PrintMyBlog\compatibility\plugins;

use Twine\compatibility\CompatibilityBase;

/**
 * Class EasyFootnotes
 *
 * For the plugin located at https://wordpress.org/plugins/easy-footnotes/.
 * On REST API requests, tell WP_Query is_singular so footnotes get rendered.
 * See https://wordpress.org/support/topic/showing-footnotes-in-rest-api/ where I suggested a fix in
 * their plugin, but so far it hasn't been implemented.
 *
 * @package     Print My Blog
 * @author         Mike Nelson
 * @since         2.1.4
 *
 */
class EasyFootnotes extends CompatibilityBase
{

    /**
     * @var $old_wp_query_is_singular_value boolean used to store WP_Query->is_singular's original value
     *      which we temporarily reassign.
     */
    protected $old_wp_query_is_singular_value;
    public function setHooks()
    {
        // There's no actions between when we know it's a REST request ('parse_request' is when "REST_REQUEST" gets defined)
        // and the posts are fetched for the REST API response, except this one (and maybe another).
        add_filter('rest_pre_dispatch', [$this,'checkIfRestRequest'], 11);
    }

    /**
     * We just want to set some hooks; we don't want to actually change any results.
     * @since $VID:$
     * @param $normal_result
     * @return mixed
     */
    public function checkIfRestRequest($normal_result)
    {

        add_filter('the_content', [$this, 'tellEasyFootnotesItsASingularRequest'], 19);
        add_filter('the_content', [$this,'okNoMoreNeedForTheDisguise'], 21);
        return $normal_result;
    }

    /**
     * Just tell Easy Footnoes its a singular request so it places the footnotes on the page.
     * @since $VID:$
     * @param $content
     * @return mixed
     */
    public function tellEasyFootnotesItsASingularRequest($content)
    {
        global $wp_query;
        $this->old_wp_query_is_singular_value = $wp_query->is_singular;
        $wp_query->is_singular = true;
        return $content;
    }

    /**
     * Easy Footnotes should have added the footnotes, so we can restore the true value of WP_Query->is_singular.
     * @since $VID:$
     * @param $content
     * @return mixed
     */
    public function okNoMoreNeedForTheDisguise($content)
    {
        global $wp_query;
        $wp_query->is_singular = $this->old_wp_query_is_singular_value;
        return $content;
    }
}
// End of file EasyFootnotes.php
// Location: PrintMyBlog\compatibility\plugins/EasyFootnotes.php
