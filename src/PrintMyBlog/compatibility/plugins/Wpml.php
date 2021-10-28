<?php

namespace PrintMyBlog\compatibility\plugins;

use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\orm\entities\ProjectSection;
use Twine\forms\base\FormSection;
use Twine\forms\helpers\InputOption;
use Twine\forms\inputs\SelectInput;
use wpml_get_active_languages;
use Twine\compatibility\CompatibilityBase;

class Wpml extends CompatibilityBase
{
    /**
     * Set hooks for compatibility with PMB for any request.
     */
    public function setHooks()
    {
        add_filter('\PrintMyBlog\controllers\Admin::getSetupForm', [$this,'addLanguageOnSetup'], 10, 2);
        add_action('\PrintMyBlog\controllers\Admin->saveNewProject', [$this,'saveProjectLanguage'], 10, 2);

        // add a filter for language on the content editing page
        add_action('pmb__project_edit_content__filters_top', [$this, 'addLanguageFilter'], 1);

        // change the WP_Query to only include the selected language
        add_filter('\PrintMyBlog\controllers\Ajax->handlePostSearch $query_params', [$this,'setupWpQueryWithWpml']);

        // change the print page's language according to the project
        add_filter(
            '\PrintMyBlog\controllers\Admin->enqueueScripts generate generate_ajax_data',
            [$this, 'setPrintPageLanguage'],
            10,
            2
        );

        // translate posts when generating a project
        add_filter('\PrintMyBlog\services\generators\ProjectFileGeneratorBase->sortPostsAndAttachSections $sections', [$this, 'sortTranslatedPosts'], 10, 1);
    }

    /**
     * @param Project|null $project
     */
    protected function getProjectLanguage($project)
    {
        return $project instanceof Project ? $project->getPmbMeta('lang') : '';
    }

    /**
     * Adds language on the setup step
     * @param FormSection $setup_form
     * @param Project|null $project
     * @return FormSection
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    public function addLanguageOnSetup(FormSection $setup_form, Project $project = null)
    {
        global $sitepress;
        $languages_data = wpml_get_active_languages();
        $default_language_details = $sitepress->get_language_details($sitepress->get_default_language());

        $language_options = [
                '' => new InputOption(
                    sprintf(
                        esc_html__('Default language (currently %s)', 'sitepress'),
                        $default_language_details['display_name']
                    )
                )
        ];
        foreach ($languages_data as $code => $language_data) {
            $language_options[$code] = new InputOption($language_data['display_name']);
        }

        $setup_form->addSubsections(
            [
                'lang' => new SelectInput(
                    $language_options,
                    [
                        'html_label_text' => __('Language', 'sitepress'),
                        'html_help_text' => __('Used for generated content and default filters', 'print-my-blog'),
                        'default' => $this->getProjectLanguage($project),
                        ]
                )
                ],
            'title',
            false
        );
        return $setup_form;
    }

    /**
     * Remember which language was selected on the project
     * @param Project $project
     * @param FormSection $setup_form
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    public function saveProjectLanguage(Project $project, FormSection $setup_form)
    {
        $project->setPmbMeta('lang', $setup_form->getInputValue('lang'));
    }

    /**
     * @param Project|null $project
     * Outputs the HTML for the language picker. Uses directly HTML because this form needed to be very custom-made.
     *
     */
    public function addLanguageFilter(Project $project = null)
    {
        $languages = wpml_get_active_languages();
        $project_language = $this->getProjectLanguage($project);
        ?>
        <tr>
            <th><label for="pmb-project-choices-language"><?php esc_html_e('Language', 'sitepress');?></label></th>
            <td>
                <select id="pmb-project-choices-language" name="pmb-post-language" form="pmb-filter-form">
                    <option value=""><?php esc_html_e('All Languages', 'sitepress');?></option>
                    <?php
                    foreach ($languages as $code => $language_data) {
                        $selected_attr = $project_language === $code ? ' selected ' : '';
                        ?><option value="<?php echo esc_attr($code);?>" <?php echo $selected_attr;?>><?php echo $language_data['display_name'];?></option><?php
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
    public function setupWpQueryWithWpml($wp_query)
    {
        // remove WPML's default WP_Query filtering from WPML_Query_Filter
        // which assumes we only want items of the same language as the current post
        global $wpml_query_filter, $sitepress;
        remove_filter('posts_join', array( $wpml_query_filter, 'posts_join_filter' ), 10);
        remove_filter('posts_where', array( $wpml_query_filter, 'posts_where_filter' ), 10);
        // and don't let WPML parse the query, they turn the IDs of translated posts into their un-translated
        // equivalents, which we don't want when excluding posts.
        remove_action('parse_query', array( $sitepress, 'parse_query' ));

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
    public function joinToWpmlLanguagesTable($join_sql)
    {
        global $wpdb;
        $join_sql .= 'LEFT JOIN ' . $wpdb->prefix . 'icl_translations t ON t.element_id=' . $wpdb->posts . '.ID AND t.element_type LIKE "post_%"';
        return $join_sql;
    }

    /**
     * Filters the WHERE statement, so we only include items of the right language
     * @param $where_sql
     * @return string
     */
    public function whereWpmlCondition($where_sql)
    {
        global $wpdb;
        if (empty($_GET['pmb-post-language'])) {
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
    public function postsRequest($sql)
    {
        return $sql;
    }

    /**
     * Put WPML's filters back in place in case they're needed
     *
     * @param $posts
     * @return mixed
     */
    public function doneWpQuery($posts)
    {
        global $wpml_query_filter, $sitepress;
        add_filter('posts_join', array( $wpml_query_filter, 'posts_join_filter' ), 10, 2);
        add_filter('posts_where', array( $wpml_query_filter, 'posts_where_filter' ), 10, 2);
        add_action('parse_query', array( $sitepress, 'parse_query' ));
        return $posts;
    }

    public function setPrintPageLanguage($data, Project $project)
    {
        $selected_language = $this->getProjectLanguage($project);
        if ($selected_language) {
            $data['lang'] = $selected_language;
        }
        return $data;
    }

    /**
     * @param ProjectSection[] $sections
     * @return ProjectSection[] with the post IDs updated to their translations
     */
    public function sortTranslatedPosts($sections){
        foreach($sections as $section){
            $section->setPostId(icl_object_id($section->getPostId()));
        }
        return $sections;
    }
}
