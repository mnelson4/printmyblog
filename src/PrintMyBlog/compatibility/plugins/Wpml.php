<?php

namespace PrintMyBlog\compatibility\plugins;

use PrintMyBlog\entities\ProjectGeneration;
use PrintMyBlog\orm\entities\Design;
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
use WP_Post;
use WP_Query;
use wpml_get_active_languages;
use Twine\compatibility\CompatibilityBase;
use WPML_Post_Status_Display;

/**
 * Class Wpml
 * @package PrintMyBlog\compatibility\plugins
 */
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

    /**
     * @param ProjectManager $project_manager
     * @param DesignManager $design_manager
     * @param CustomPostTypes $post_types
     */
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
        add_action('PrintMyBlog\system\Activation->install done', [$this, 'fixPmbContentTranslations']);
        // add a filter for language on the content editing page
        add_action('pmb__project_edit_content__filters_top', [$this, 'addLanguageFilter'], 1);

        // change the WP_Query to only include the selected language on Ajax requests
        add_filter('\PrintMyBlog\controllers\Ajax->handlePostSearch $query_params', [$this, 'setupWpQueryWithWpml']);

        // change the print page's language according to the project
        add_filter(
            '\PrintMyBlog\controllers\Admin->enqueueScripts generate generate_ajax_data',
            [$this, 'setPrintPageLanguage'],
            10,
            2
        );

        // add translation options directly to project editing page
        add_action('pmb_content_items__project-item-title end', [$this, 'showTranslationsOnProjectItems'], 10, 6);

        // translate posts when generating a project
        add_filter('\PrintMyBlog\services\generators\ProjectFileGeneratorBase->sortPostsAndAttachSections $sections', [$this, 'sortTranslatedPosts'], 10, 2);
        add_action('project_edit_generate__under_header', [$this, 'addTranslationOptions'], 10, 2);
        add_action('\PrintMyBlog\services\generators\ProjectFileGeneratorBase->getHtmlFrom before_ob_start', [$this, 'setTranslatedProject']);
        add_action('\PrintMyBlog\services\generators\ProjectFileGeneratorBase->getHtmlFrom after_get_clean', [$this, 'unsetTranslatedProject']);
        add_action('wp_ajax_pmb_update_project_lang', [$this, 'handleAjaxUpdateProjectLanguage']);

        add_action('admin_enqueue_scripts', [$this, 'enqueueWpmlCompatAssets']);
        add_action('PrintMyBlog\controllers\Admin->saveProjectCustomizeDesign done', [$this, 'updateTranslatedDesignsToo'], 10, 4);
        add_action('PrintMyBlog\controllers\Admin->saveProjectMetadata done', [$this, 'updateTranslatedProjectsToo'], 10, 3);
    }

    /**
     * @param string $hook
     */
    public function enqueueWpmlCompatAssets($hook)
    {
        // PMB project pages are all treated as if they're in the site's primary language
        if ($hook === 'toplevel_page_print-my-blog-projects') {
            global $sitepress;
            $sitepress->switch_lang(wpml_get_default_language());
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
     * Fixes PMB content (no initial translation record, or its in the wrong language)
     */
    public function fixPmbContentTranslations()
    {
        global $wpdb, $sitepress;
        // find all PMB content needing a translation entry
        $post_types_sql = implode(
            ', ',
            array_map(
                function ($item) {
                    global $wpdb;
                    return $wpdb->prepare('%s', $item);
                },
                $this->post_types->getPostTypes()
            )
        );
        $default_lang = wpml_get_default_language();

        // find PMB content with no initial translation record (WPML assumes that always exists)
        // or its a project or design that is not for the primary language (their original entry must always be in the primary language)
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $pmb_stuff_to_fix = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->posts} posts
            LEFT JOIN {$wpdb->prefix}icl_translations translations ON translations.element_type=CONCAT('post_', posts.post_type) AND posts.ID=translations.element_id
            WHERE 
                (
                    posts.post_type IN ({$post_types_sql}) 
                    AND translations.translation_id IS NULL
                )
                OR 
                (
                    posts.post_type IN (%s, %s)
                    AND translations.language_code!=%s 
                    AND translations.source_language_code IS NULL
                )",
                CustomPostTypes::PROJECT,
                CustomPostTypes::DESIGN,
                $default_lang
            ),
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        foreach ($pmb_stuff_to_fix as $post_needing_update) {
            $sitepress->set_element_language_details(
                $post_needing_update['ID'],
                'post_' . $post_needing_update['post_type'],
                null,
                $default_lang,
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
            // This is an error condition so express it as such.
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('PMB WPML integration was trying to run but the functions wpml_get_active_languages and wpml_get_default_language were not defined.');
            return;
        }
        $languages = wpml_get_active_languages();
        $project_language = wpml_get_default_language()
        ?>
        <tr>
            <th><label for="pmb-project-choices-language"><?php esc_html_e('Language', 'sitepress'); ?></label></th>
            <td>
                <select id="pmb-project-choices-language" name="pmb-post-language" form="pmb-filter-form">
                    <option value=""><?php esc_html_e('All Languages', 'sitepress'); ?></option>
                    <?php
                    foreach ($languages as $code => $language_data) {
                        $selected_attr = $project_language === $code ? ' selected ' : '';
                        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
                        ?>
                        <option value="<?php echo esc_attr($code); ?>" <?php echo $selected_attr; ?>><?php echo $language_data['display_name']; ?></option>
                        <?php
                        // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }

    /**
     * Tell WP_Query to use filters, and add some so we only select posts of the requested language on Ajax requests searching for posts.
     * @param WP_Query $wp_query
     * @return mixed
     */
    public function setupWpQueryWithWpml($wp_query)
    {
        // remove WPML's default WP_Query filtering from WPML_Query_Filter
        // which assumes we only want items of the same language as the current post
        global $wpml_query_filter, $sitepress;
        remove_filter('posts_join', array($wpml_query_filter, 'posts_join_filter'), 10);
        remove_filter('posts_where', array($wpml_query_filter, 'posts_where_filter'), 10);
        // and don't let WPML parse the query, they turn the IDs of translated posts into their un-translated
        // equivalents, which we don't want when excluding posts.
        remove_action('parse_query', array($sitepress, 'parse_query'));

        // setup our filters
        $wp_query['suppress_filters'] = false;
        add_filter('posts_join', [$this, 'joinToWpmlLanguagesTable']);
        add_filter('posts_where', [$this, 'whereWpmlCondition']);
        add_filter('posts_request', [$this, 'postsRequest']);

        // and remember to re-add WPML's filters where we're done
        add_filter('\PrintMyBlog\controllers\Ajax->handlePostSearch $posts', [$this, 'doneWpQuery']);
        return $wp_query;
    }

    /**
     * Filters the JOIN statement, so we join to the WPML translations table
     * @param string $join_sql
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
     * @param string $where_sql
     * @return string
     */
    public function whereWpmlCondition($where_sql)
    {
        global $wpdb;
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- no form was submitted, we're just looking at the URL.
        if (empty($_GET['pmb-post-language'])) {
            return $where_sql;
        }
        $language_code = sanitize_key($_GET['pmb-post-language']);

        $where_sql .= $wpdb->prepare(' AND t.language_code=%s', $language_code);
        return $where_sql;
    }

    /**
     * Just useful for debugging sometimes, to see exactly what query we're using.
     * @param string $sql
     * @return mixed
     */
    public function postsRequest($sql)
    {
        return $sql;
    }

    /**
     * Put WPML's filters back in place in case they're needed
     *
     * @param WP_Post[] $posts
     * @return mixed
     */
    public function doneWpQuery($posts)
    {
        global $wpml_query_filter, $sitepress;
        add_filter('posts_join', array($wpml_query_filter, 'posts_join_filter'), 10, 2);
        add_filter('posts_where', array($wpml_query_filter, 'posts_where_filter'), 10, 2);
        add_action('parse_query', array($sitepress, 'parse_query'));
        return $posts;
    }

    /**
     * @param array $data
     * @param Project $project
     * @return mixed
     */
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
     * @param ProjectFileGeneratorBase $project_file_generator
     * @return ProjectSection[] with the post IDs updated to their translations
     */
    public function sortTranslatedPosts($sections, ProjectFileGeneratorBase $project_file_generator)
    {
        if (! function_exists('wpml_object_id_filter')) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- it's an error condition so record it.
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
     * Gets the translated project and design objects.
     */
    public function setTranslatedProject()
    {
        global $pmb_project, $pmb_wpml_original_project, $pmb_design, $pmb_wpml_original_design;
        $pmb_wpml_original_project = $pmb_project;
        $pmb_wpml_original_design = $pmb_design;
        $pmb_project = $this->project_manager->getById(wpml_object_id_filter($pmb_project->getWpPost()->ID, 'post', true));
        $pmb_design = $this->design_manager->getById(wpml_object_id_filter($pmb_design->getWpPost()->ID, 'post', true));
    }

    /**
     * Restore to the original project and design objects.
     */
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
     * @param array $generations
     */
    public function addTranslationOptions(Project $project, $generations)
    {
        global $sitepress;
        if (! function_exists('wpml_get_active_languages') || ! $sitepress instanceof SitePress || ! class_exists('\WPML_Post_Status_Display')) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- record error.
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
                // translators: %s: language name
                    esc_html__('Default language (currently %s)', 'sitepress'),
                    $default_language_details['display_name']
                )
            ),
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
                    // translators: %s: design name
                        __('Translate %s Design', 'print-my-blog'),
                        esc_html($design->getWpPost()->post_title)
                    )
                    . $post_status_display->get_status_html($design->getWpPost()->ID, $language_code)
                );
            }
            $form_sections[$language_code] = new FormSection(
                [
                    'subsections' => [
                        'html' => new FormSectionHtml(
                            $html_helper->h2(
                                sprintf(
                                // translators: %s: language name.
                                    __('%s Translations', 'print-my-blog'),
                                    $language_data['display_name']
                                )
                            )
                            . $html_helper->div(
                                __('Translate Project Metadata', 'print-my-blog')
                                . $post_status_display->get_status_html($project->getWpPost()->ID, $language_code)
                            )
                            . $design_translations_html
                        ),
                    ],
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
                ),
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
                },
            ]
        );
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intentionally outputting HTML.
        echo $form->getHtmlAndJs();
    }

    /**
     * Records the last-requested language for the project.
     */
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
     * @param int $post_id
     * @param string $title
     * @param string $post_type
     * @param string $template
     * @param mixed $subs
     * @param int $depth
     */
    public function showTranslationsOnProjectItems($post_id, $title, $post_type, $template, $subs, $depth)
    {
        global $sitepress;
        if (! function_exists('wpml_get_active_languages') || ! $sitepress instanceof SitePress || ! class_exists('\WPML_Post_Status_Display')) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- it's an error so let's record it but not die outright.
            error_log('PMB WPML integration was trying to run but the function wpml_get_active_languages was not defined, the $sitepress global was not set, or the class WPML_Post_Status_Display was not defined.');
            return;
        }
        $languages_data = wpml_get_active_languages();
        $post_status_display = new WPML_Post_Status_Display($languages_data);

        foreach ($languages_data as $language_code => $language_data) {
            list($text, $link, $trid, $css_class, $status) = $post_status_display->get_status_data($post_id, $language_code);
            if ($status >= ICL_TM_TRANSLATION_READY_TO_DOWNLOAD) {
                $flag_url = $sitepress->get_flag_url($language_code);
                if ($flag_url) {
                    ?>
                    <img src="<?php echo esc_url($flag_url); ?>" title="
                    <?php
                    echo esc_attr(
                        sprintf(
                        // translators: 1: post title, 2: language
                            __('"%1$s" is fully translated into %2$s', 'print-my-blog'),
                            $title,
                            $language_data['display_name']
                        )
                    );
                    ?>
                    " width="18" height="12">
                    <?php
                } else {
                    ?>
                    <span style="margin-right:5px; padding-left: 5px; padding-right:5px; padding-bottom:3px; color:white; background-color:green; border-radius:4px;"><?php echo esc_html($language_code); ?></span>
                    <?php
                }
            }
            ?>
            <?php
        }
    }

    /**
     * Make sure to update the translations of the design too when the design is customized.
     * See https://wpml.org/wpml-hook/wpml_sync_all_custom_fields/
     * @param Project $project
     * @param ProjectGEneration $project_generation
     * @param Design $design
     * @param FormSection $design_form
     */
    public function updateTranslatedDesignsToo($project, $project_generation, $design, $design_form)
    {
        if (! $design instanceof Design) {
            return;
        }
        do_action('wpml_sync_all_custom_fields', $design->getWpPost()->ID);
    }

    /**
     * Make sure to update all the translations of a project's metadata when it gets saved.
     * @param Project $project
     * @param ProjectGeneration[] $project_generations
     * @param FormSection $form
     */
    public function updateTranslatedProjectsToo($project, $project_generations, $form)
    {
        if (! $project instanceof Project) {
            return;
        }
        do_action('wpml_sync_all_custom_fields', $project->getWpPost()->ID);
    }
    // otgs-ico-in-progress
    // otgs-ico-add
}
