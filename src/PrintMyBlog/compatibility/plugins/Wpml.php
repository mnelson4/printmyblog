<?php

namespace PrintMyBlog\compatibility\plugins;

use \wpml_get_active_languages;
use Twine\compatibility\CompatibilityBase;

class Wpml extends CompatibilityBase
{
    /**
     * Set hooks for compatibility with PMB for any request.
     */
    public function setHooks()
    {


        // add a filter for language on the content editing page
        add_action('pmb__project_edit_content__filters_top', [$this, 'addLanguageFilter']);

        // change the WP_Query to only include the selected language
        add_filter('\PrintMyBlog\controllers\Ajax->handlePostSearch $query_params', [$this,'setupWpQueryWithWpml']);
    }

    public function addLanguageFilter(){
        $languages = wpml_get_active_languages();
        ?>
        <tr>
            <th><label for="pmb-project-choices-language"><?php esc_html_e('Language', 'print-my-blog');?></label></th>
            <td>
                <select id="pmb-project-choices-language" name="pmb-post-language" form="pmb-filter-form">
                    <option value=""><?php esc_html_e('All', 'print-my-blog');?></option>
                    <?php
                    foreach($languages as $code => $language_data){
                        ?><option value="<?php echo esc_attr($code);?>"><?php echo $language_data['display_name'];?></option><?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }

    /**
     * Tell WP_Query to use filters, and add some so we only select posts of the requested language.
     * @param $wp_query
     * @return mixed
     */
    public function setupWpQueryWithWpml($wp_query){
        // remove WPML's default WP_Query filtering from WPML_Query_Filter
        // which assumes we only want items of the same language as the current post
        global $wpml_query_filter;
        remove_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ), 10 );
        remove_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ), 10 );

        // setup our filters
        $wp_query['suppress_filters'] = false;
        add_filter('posts_join', [$this,'joinToWpmlLanguagesTable']);
        add_filter('posts_where', [$this,'whereWpmlCondition']);
        add_filter('posts_request', [$this, 'postsRequest']);

        // and remember to re-add WPML's filters where we're done
        add_filter('\PrintMyBlog\controllers\Ajax->handlePostSearch $posts', [$this, 'doneWpQuery']);
        return $wp_query;
    }

    /**
     * Filters the JOIN statement, so we join to the WPML translations table
     * @param $join_sql
     * @return string
     */
    public function joinToWpmlLanguagesTable($join_sql){
        global $wpdb;
        $join_sql .= 'LEFT JOIN ' . $wpdb->prefix . 'icl_translations t ON t.element_id=' . $wpdb->posts . '.ID AND t.element_type LIKE "post_%"';
        return $join_sql;
    }

    /**
     * Filters the WHERE statement, so we only include items of the right language
     * @param $where_sql
     * @return string
     */
    public function whereWpmlCondition($where_sql){
        global $wpdb;
        if (empty($_GET['pmb-post-language'])){
            return $where_sql;
        }
        $language_code = $_GET['pmb-post-language'];

        $where_sql .= $wpdb->prepare(' AND t.language_code=%s', $language_code);
        return $where_sql;
    }

    /**
     * Just useful for debugging sometimes, to see exactly what query we're using
     * @param $sql
     * @return mixed
     */
    public function postsRequest($sql){
        return $sql;
    }

    /**
     * Put WPML's filters back in place in case they're needed
     *
     * @param $posts
     * @return mixed
     */
    public function doneWpQuery($posts){
        global $wpml_query_filter;
        add_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ), 10, 2 );
        add_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ), 10, 2 );
        return $posts;
    }
}
