<?php

namespace PrintMyBlog\compatibility\plugins;

use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\orm\entities\ProjectSection;
use PrintMyBlog\orm\managers\DesignManager;
use PrintMyBlog\orm\managers\ProjectManager;
use PrintMyBlog\services\generators\ProjectFileGeneratorBase;
use PrintMyBlog\system\CustomPostTypes;
use SitePress;
use Twine\forms\base\FormSection;
use Twine\forms\base\FormSectionHtml;
use Twine\forms\helpers\InputOption;
use Twine\forms\inputs\SelectInput;
use Twine\forms\inputs\SelectRevealInput;
use Twine\helpers\Array2;
use Twine\helpers\Html;
use wpml_get_active_languages;
use Twine\compatibility\CompatibilityBase;
use WPML_Post_Status_Display;

class Wpml extends CompatibilityBase
{
    /**
     * @var ProjectManager
     */
    private $project_manager;
    /**
     * @var DesignManager
     */
    private $design_manager;
    /**
     * @var CustomPostTypes
     */
    private $post_types;

    public function inject(ProjectManager $project_manager, DesignManager $design_manager, CustomPostTypes $post_types)
    {
        $this->project_manager = $project_manager;
        $this->design_manager = $design_manager;
        $this->post_types = $post_types;
    }
    /**
     * Set hooks for compatibility with PMB for any request.
     */
    public function setHooks()
    {
        // when activating make sure we create the needed translations of PMB default content
        add_action('PrintMyBlog\system\Activation->install done', [$this, 'verifyPmbContentsTranslated']);
        // add a filter for language on the content editing page
        add_action('pmb__project_edit_content__filters_top', [$this, 'addLanguageFilter'], 1);

        // change the WP_Query to only include the selected language on Ajax requests
        add_filter('\PrintMyBlog\controllers\Ajax->handlePostSearch $query_params', [$this,'setupWpQueryWithWpml']);

        // change the print page's language according to the project
        add_filter(
            '\PrintMyBlog\controllers\Admin->enqueueScripts generate generate_ajax_data',
            [$this, 'setPrintPageLanguage'],
            10,
            2
        );

        // add translation options directly to project editing page
        add_action('pmb_content_items__project-item-title end', [$this,'showTranslationsOnProjectItems'], 10, 6);

        // translate posts when generating a project
        add_filter('\PrintMyBlog\services\generators\ProjectFileGeneratorBase->sortPostsAndAttachSections $sections', [$this, 'sortTranslatedPosts'], 10, 2);
        add_action('project_edit_generate__under_header', [$this,'addTranslationOptions'], 10, 2);
        add_action('\PrintMyBlog\services\generators\ProjectFileGeneratorBase->getHtmlFrom before_ob_start', [$this,'setTranslatedProject']);
        add_action('\PrintMyBlog\services\generators\ProjectFileGeneratorBase->getHtmlFrom after_get_clean', [$this,'unsetTranslatedProject']);
        add_action('wp_ajax_pmb_update_project_lang', [$this,'handleAjaxUpdateProjectLanguage']);

        add_action('admin_enqueue_scripts',[$this,'enqueueWpmlCompatAssets']);
    }

    public function enqueueWpmlCompatAssets($hook){
        // PMB project pages are all treated as if they're in the site's primary language
        if ($hook === 'toplevel_page_print-my-blog-projects') {
            global $sitepress;
            $sitepress->switch_lang('all');
            wp_add_inline_style(
                'pmb_common',
                '/* Hide WPML language switcher on PMB project pages as it doesn\'t make sense there. All projects are in the main languager then translated*/
            #wp-admin-bar-WPML_ALS{
                display:none;
            }'
            );
        }
    }

    /**
     * Finds a ton of PMB content that has no translation entry and adds it
     */
    public function verifyPmbContentsTranslated(){
        global $wpdb, $sitepress;
        // find all PMB content needing a translation entry
        $post_types_sql = implode(
            ', ',
            array_map(
                function($item){
                    global $wpdb;
                    return $wpdb->prepare('%s', $item);
                },
                $this->post_types->getPostTypes()
            )
        );
        $posts_needing_update = $wpdb->get_results(
            "SELECT * FROM {$wpdb->posts} posts
            LEFT JOIN {$wpdb->prefix}icl_translations translations ON translations.element_type=CONCAT('post_', posts.post_type) AND posts.ID=translations.element_id
            WHERE posts.post_type IN ({$post_types_sql}) AND translations.translation_id IS NULL",
                ARRAY_A
        );
        foreach($posts_needing_update as $post_needing_update){
            $sitepress->set_element_language_details(
                $post_needing_update['ID'],
                'post_' . $post_needing_update['post_type'],
                null,
                wpml_get_default_language(),
                null,
                true
            );
        }

    }

    /**
     * @param Project|null $project
     */
    protected function getProjectLanguage($project)
    {
        return $project instanceof Project ? $project->getPmbMeta('lang') : '';
    }

    /**
     * @param Project|null $project
     * Outputs the HTML for the language picker. Uses directly HTML because this form needed to be very custom-made.
     *
     */
    public function addLanguageFilter(Project $project = null)
    {
        if (! function_exists('wpml_get_active_languages') || ! function_exists('wpml_get_default_language')) {
            error_log('PMB WPML integration was trying to run but the functions wpml_get_active_languages and wpml_get_default_language were not defined.');
            return;
        }
        $languages = wpml_get_active_languages();
        $project_language = wpml_get_default_language()
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
     * Tell WP_Query to use filters, and add some so we only select posts of the requested language on Ajax requests searching for posts.
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
    public function sortTranslatedPosts($sections, ProjectFileGeneratorBase $project_file_generator)
    {
        if (! function_exists('wpml_object_id_filter')) {
            error_log('PMB WPML integration was trying to run but the function wpml_object_id_filter was not defined.');
            return $sections;
        }
        $project = $project_file_generator->getProject();

        // if we're using the site's default language for the project, use the posts in whatever language they selected
        // on the content editing step. This is primarily for backward compatibility, and maybe for projects with
        // multiple languages in the future too.
        $project_language = $project->getPmbMeta('lang');
        if ($project_language === '') {
            return $sections;
        }
        foreach ($sections as $section) {
            $translated_post_id = wpml_object_id_filter($section->getPostId());
            // if we couldn't find the translated version, use the original language
            if ($translated_post_id) {
                $section->setPostId($translated_post_id);
            }
        }
        return $sections;
    }

    /**
     * @param $project_id
     * @return int|null
     */
    public function setTranslatedProject()
    {
        global $pmb_project, $pmb_wpml_original_project, $pmb_design, $pmb_wpml_original_design;
        $pmb_wpml_original_project = $pmb_project;
        $pmb_wpml_original_design = $pmb_design;
        $pmb_project = $this->project_manager->getById(wpml_object_id_filter($pmb_project->getWpPost()->ID));
        $pmb_design = $this->design_manager->getById(wpml_object_id_filter($pmb_design->getWpPost()->ID));
    }

    public function unsetTranslatedProject()
    {
        global $pmb_project, $pmb_wpml_original_project, $pmb_design, $pmb_wpml_original_design;
        $pmb_project = $pmb_wpml_original_project;
        $pmb_design = $pmb_wpml_original_design;
    }

    /**
     * Add a language switcher and a div for each language.
     * When the language is switched, unless it's the default language, provide translation options for it and its design
     * @param Project $project
     * @param $generations
     */
    public function addTranslationOptions(Project $project, $generations)
    {
        global $sitepress;
        if (! function_exists('wpml_get_active_languages') || ! $sitepress instanceof SitePress || ! class_exists('\WPML_Post_Status_Display')) {
            error_log('PMB WPML integration was trying to run but the function wpml_get_active_languages was not defined, the $sitepress global was not set, or the class WPML_Post_Status_Display was not defined.');
            return;
        }
        $languages_data = wpml_get_active_languages();
        $post_status_display = new WPML_Post_Status_Display($languages_data);
        $default_language_code = $sitepress->get_default_language();
        $default_language_details = $sitepress->get_language_details($default_language_code);

        $language_options = [
            $default_language_code => new InputOption(
                sprintf(
                    esc_html__('Default language (currently %s)', 'sitepress'),
                    $default_language_details['display_name']
                )
            )
        ];

        $form_sections = [];
        foreach ($languages_data as $language_code => $language_data) {
            if ($language_code === $default_language_code) {
                continue;
            }
            $html_helper = Html::instance();
            $design_translations_html = '';
            foreach ($project->getDesignsSelected() as $design) {
                $design_translations_html .= $html_helper->div(
                    sprintf(
                        __('Translate %s Design', 'print-my-blog'),
                        $design->getWpPost()->post_title
                    )
                        . $post_status_display->get_status_html($design->getWpPost()->ID, $language_code)
                );
            }
            $form_sections[$language_code] = new FormSection(
                [
                    'subsections' => [
                        'html' => new FormSectionHtml(
                            $html_helper->h2(sprintf(__('%s Translations', 'print-my-blog'), $language_data['display_name']))
                            . $html_helper->div(
                                __('Translate Project Metadata', 'print-my-blog')
                                    . $post_status_display->get_status_html($project->getWpPost()->ID, $language_code)
                            )
                            . $design_translations_html
                        )
                    ]
                ]
            );

            $language_options[$language_code] = new InputOption($language_data['display_name']);
        }
        $form_sections = array_merge(
            [
                'choose_language' => new SelectRevealInput(
                    $language_options,
                    [
                        'html_label_text' => __('Language', 'sitepress'),
                        'default' => $this->getProjectLanguage($project),
                    ]
                )
            ],
            $form_sections
        );

        $form = new FormSection(
            [
                    'name' => 'pmb-language-chooser',
                    'subsections' => $form_sections,
                    'enqueue_scripts_callback' => function () {
                        wp_add_inline_script(
                            'twine_form_section_validation',
                            "
                        // when the language is changed, change the parameter for generating the project.
                        jQuery(document).ready(function(){
                            jQuery('#pmb-language-chooser-choose-language').change(function(event){
                                var new_lang = jQuery('#pmb-language-chooser-choose-language').val();
                                pmb_generate.generate_ajax_data.lang = new_lang;
                                var data = {
                                    'action':'pmb_update_project_lang',
                                    '_nonce': pmb_generate.generate_ajax_data._nonce,
                                    'project_id':pmb_generate.generate_ajax_data.ID,
                                    'new_lang': new_lang
                                };
                                jQuery.ajax({
                                    url:ajaxurl,
                                    method:'POST',
                                    data:data,
                                    success:function(){
                                        // alert('success');
                                    }
                                });
                            });
                        });"
                        );
                    }
            ]
        );
        echo $form->getHtmlAndJs();
    }

    public function handleAjaxUpdateProjectLanguage()
    {
        if (! check_ajax_referer('pmb-project-edit', '_nonce')) {
            wp_send_json_error('please refresh the page');
        }
        $project_id = (int)Array2::setOr($_REQUEST, 'project_id', null);
        $language_code = Array2::setOr($_REQUEST, 'new_lang', null);
        if (! $project_id) {
            wp_send_json_error('oups no project id');
        }
        if (! current_user_can('edit_pmb_project', $project_id)) {
            wp_send_json_error('Oups you don\'t have permission to edit a project');
        }
        $project = $this->project_manager->getById($project_id);
        if (! $project instanceof Project) {
            wp_send_json_error('oups no such project');
        }
        $project->setPmbMeta('lang', $language_code);
        wp_send_json_success();
        exit;
    }

    /**
     * @param $post_id
     * @param $title
     * @param $post_type
     * @param $template
     * @param $subs
     * @param $depth
     */
    public function showTranslationsOnProjectItems($post_id, $title, $post_type, $template, $subs, $depth)
    {
        global $sitepress;
        if (! function_exists('wpml_get_active_languages') || ! $sitepress instanceof SitePress || ! class_exists('\WPML_Post_Status_Display')) {
            error_log('PMB WPML integration was trying to run but the function wpml_get_active_languages was not defined, the $sitepress global was not set, or the class WPML_Post_Status_Display was not defined.');
            return;
        }
        $languages_data = wpml_get_active_languages();
        $post_status_display = new WPML_Post_Status_Display($languages_data);

        foreach ($languages_data as $language_code => $language_data) {
            list( $text, $link, $trid, $css_class, $status ) = $post_status_display->get_status_data($post_id, $language_code);
            if ($status === 10) {
                ?>
                <img src="<?php echo esc_url($sitepress->get_flag_url($language_code));?>"
                     title="<?php echo esc_attr(sprintf(__('"%s" is fully translated into %s', 'print-my-blog'), $title, $language_data['display_name']));?>" width="18" height="12">
                <?php
            }
            ?>
            <?php
        }
    }
}
