<?php

namespace PrintMyBlog\controllers;

use Exception;
use PrintMyBlog\controllers\helpers\ProjectsListTable;
use PrintMyBlog\db\PostFetcher;
use PrintMyBlog\db\TableManager;
use PrintMyBlog\domain\DefaultPersistentNotices;
use PrintMyBlog\domain\FrontendPrintSettings;
use PrintMyBlog\domain\PrintOptions;
use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\entities\FileFormat;
use PrintMyBlog\entities\ProjectProgress;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\orm\managers\DesignManager;
use PrintMyBlog\orm\managers\ProjectManager;
use PrintMyBlog\orm\managers\ProjectSectionManager;
use PrintMyBlog\services\FileFormatRegistry;
use PrintMyBlog\services\SvgDoer;
use PrintMyBlog\system\CustomPostTypes;
use Twine\entities\notifications\OneTimeNotification;
use Twine\forms\base\FormSectionHtmlFromTemplate;
use Twine\forms\base\FormSection;
use Twine\forms\helpers\InputOption;
use Twine\forms\inputs\RadioButtonInput;
use Twine\forms\inputs\TextInput;
use Twine\forms\strategies\layout\TemplateLayout;
use Twine\services\display\FormInputs;
use Twine\controllers\BaseController;
use Twine\services\notifications\OneTimeNotificationManager;
use WP_Query;
use WP_User_Query;

use WPTRT\AdminNotices\Notices;
use const http\Client\Curl\PROXY_HTTP;

/**
 * Class PmbAdmin
 *
 * Hooks needed to add our stuff to the admin.
 * Mostly it's just an admin page.
 *
 * @package     Print My Blog
 * @author         Mike Nelson
 * @since         1.0.0
 *
 */
class Admin extends BaseController
{
    const SLUG_ACTION_ADD_NEW = 'new';
    const SLUG_ACTION_EDIT_PROJECT = 'edit';
    const SLUG_SUBACTION_PROJECT_SETUP = 'setup';
    const SLUG_SUBACTION_PROJECT_CUSTOMIZE_DESIGN = 'customize_design';
    const SLUG_SUBACTION_PROJECT_CHANGE_DESIGN = 'choose_design';
    const SLUG_SUBACTION_PROJECT_CONTENT = 'content';
    const SLUG_SUBACTION_PROJECT_META = 'metadata';
    const SLUG_SUBACTION_PROJECT_GENERATE = 'generate';

    /**
     * @var PostFetcher
     */
    protected $post_fetcher;

    /**
     * @var ProjectSectionManager
     */
    protected $section_manager;

    /**
     * @var ProjectManager
     */
    protected $project_manager;

    /**
     * @var FileFormatRegistry
     */
    protected $file_format_registry;

    /**
     * @var DesignManager
     */
    protected $design_manager;

    /**
     * @var FormSection
     */
    protected $invalid_form;
    /**
     * @var TableManager
     */
    protected $table_manager;

    /**
     * @var SvgDoer
     */
    protected $svg_doer;
    /**
     * @var OneTimeNotificationManager
     */
    protected $notification_manager;

    /**
     * @param PostFetcher $post_fetcher
     * @param ProjectSectionManager $section_manager
     * @param ProjectManager $project_manager
     * @param FileFormatRegistry $file_format_registry
     *
     * @param DesignManager $design_manager
     * @param TableManager $table_manager
     *
     * @since $VID:$
     */
    public function inject(
        PostFetcher $post_fetcher,
        ProjectSectionManager $section_manager,
        ProjectManager $project_manager,
        FileFormatRegistry $file_format_registry,
        DesignManager $design_manager,
        TableManager $table_manager,
        SvgDoer $svg_doer,
        OneTimeNotificationManager $notification_manager
    ) {
        $this->post_fetcher    = $post_fetcher;
        $this->section_manager = $section_manager;
        $this->project_manager = $project_manager;
        $this->file_format_registry = $file_format_registry;
        $this->design_manager = $design_manager;
        $this->table_manager = $table_manager;
        $this->svg_doer = $svg_doer;
        $this->notification_manager = $notification_manager;
    }
    /**
     * name of the option that just indicates we successfully saved the setttings
     */
    const SETTINGS_SAVED_OPTION = 'pmb-settings-saved';
    /**
     * Sets hooks that we'll use in the admin.
     * @since 1.0.0
     */
    public function setHooks()
    {
        add_action('admin_menu', array($this, 'addToMenu'));
        add_filter('plugin_action_links_' . PMB_BASENAME, array($this, 'pluginPageLinks'));
        add_action('admin_enqueue_scripts', [$this,'enqueueScripts']);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            add_action('admin_init', [$this, 'checkFormSubmission']);
        }
        if (isset($_GET['action']) && $_GET['action'] === 'uninstall') {
            $this->uninstall();
            if (current_user_can('activate_plugins')) {
                if (! function_exists('deactivate_plugins')) {
                    require_once ABSPATH . 'wp-admin/includes/plugin.php';
                }
                deactivate_plugins(PMB_BASENAME, true);
            }
            wp_safe_redirect(admin_url('plugins.php'));
        }
        $this->makePrintContentsSaySaved();
        $this->notification_manager->showOneTimeNotifications();
    }

    /**
     * Adds our menu page.
     * @since 1.0.0
     */
    public function addToMenu()
    {
        add_menu_page(
            esc_html__('Print My Blog', 'print-my-blog'),
            esc_html__('Print My Blog', 'print-my-blog'),
            PMB_ADMIN_CAP,
            PMB_ADMIN_PROJECTS_PAGE_SLUG,
            array(
                $this,
                'renderProjects'
            ),
            $this->svg_doer->getSvgDataAsColor(PMB_DIR . 'assets/images/menu-icon.svg', 'white')
        );

        add_submenu_page(
            PMB_ADMIN_PROJECTS_PAGE_SLUG,
            esc_html__('Pro Print', 'print-my-blog'),
            esc_html__('Pro Print', 'print-my-blog'),
            PMB_ADMIN_CAP,
            PMB_ADMIN_PROJECTS_PAGE_SLUG,
            array($this, 'renderProjects')
        );
        $this->hackSubmenuContentIntoRightSpot();
        add_submenu_page(
            PMB_ADMIN_PROJECTS_PAGE_SLUG,
            esc_html__('Print My Blog – Quick Print', 'print-my-blog'),
            esc_html__('Free Quick Print', 'print-my-blog'),
            PMB_ADMIN_CAP,
            PMB_ADMIN_PAGE_SLUG,
            array($this,'renderAdminPage')
        );
        add_submenu_page(
            PMB_ADMIN_PROJECTS_PAGE_SLUG,
            esc_html__('Print My Blog Settings', 'print-my-blog'),
            esc_html__('Settings', 'print-my-blog'),
            'manage_options',
            PMB_ADMIN_SETTINGS_PAGE_SLUG,
            array($this,'settingsPage')
        );
    }

    /**
     * Hacks WP menu so the links to the PMB contents CPT appear underneath the Print My Blog top-level menu.
     */
    protected function hackSubmenuContentIntoRightSpot()
    {
        global $submenu;

        if (array_key_exists(PMB_ADMIN_PROJECTS_PAGE_SLUG, $submenu)) {
            foreach ($submenu[PMB_ADMIN_PROJECTS_PAGE_SLUG] as $key => $value) {
                $k = array_search('edit.php?post_type=pmb_content', $value);
                if ($k) {
                    unset($submenu[ PMB_ADMIN_PROJECTS_PAGE_SLUG ][ $key ]);
                    $submenu[ PMB_ADMIN_PROJECTS_PAGE_SLUG ][] = $value;
                }
            }
        }
    }

    public function settingsPage()
    {
        $settings = new FrontendPrintSettings(new PrintOptions());
        $settings->load();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('pmb-settings');
            // Ok save those settings!
            if (isset($_POST['pmb-reset'])) {
                $settings = new FrontendPrintSettings(new PrintOptions());
            } else {
                $settings->setShowButtons(isset($_POST['show_buttons']));
                $settings->setShowButtonsPages(isset($_POST['show_buttons_pages']));
                $settings->setPlaceAbove($_POST['place_above']);
                foreach ($settings->formatSlugs() as $slug) {
                    if (isset($_POST['format'][$slug])) {
                        $active = true;
                    } else {
                        $active = false;
                    }
                    $settings->setFormatActive($slug, $active);
                    if (isset($_POST['frontend_labels'][$slug])) {
                        $settings->setFormatFrontendLabel($slug, $_POST['frontend_labels'][$slug]);
                    }
                    if (isset($_POST['print_options'][$slug])) {
                        $settings->setPrintOptions($slug, $_POST['print_options'][$slug]);
                    }
                }
            }
            $settings->save();
            update_option(self::SETTINGS_SAVED_OPTION, true, false);
            wp_redirect('');
        }
        $saved = get_option(self::SETTINGS_SAVED_OPTION, false);
        if ($saved) {
            delete_option(self::SETTINGS_SAVED_OPTION);
            $posts = get_posts(array ( 'orderby' => 'desc', 'posts_per_page' => '1' ));
            $text = esc_html__('Settings Saved!', 'print-my-blog');
            if ($posts) {
                $a_post = reset($posts);
                $permalink = get_permalink($a_post);
                $text .= ' ' . sprintf(
                    esc_html__('You should see the changes on your %1$slatest post%2$s.', 'print-my-blog'),
                    '<a href="' . $permalink . '" target="_blank">',
                    '</a>'
                );
            }
            echo '<div class="notice notice-success is-dismissible"><p>' . $text .  '</p></div>';
        }
        $print_options = new PrintOptions();
        $displayer = new FormInputs();
        include(PMB_TEMPLATES_DIR . 'settings_page.php');
    }


    /**
     * Shows the setup page.
     * @since 1.0.0
     */
    public function renderAdminPage()
    {

        if (isset($_GET['welcome'])) {
            include(PMB_TEMPLATES_DIR . 'welcome.php');
        } else {
            $print_options = new PrintOptions();
            $displayer = new FormInputs();
            include(PMB_TEMPLATES_DIR . 'setup_page.php');
        }
    }

    /**
     * Adds links to PMB stuff on the plugins page.
     * @since 1.0.0
     * @param array $links
     */
    public function pluginPageLinks($links)
    {
        $links = array_merge(
            array(
                '<a href="'
            . admin_url(PMB_ADMIN_PAGE_PATH)
            . '">'
            . esc_html__('Print Now', 'print-my-blog')
            . '</a>',

            '<a href="'
            . admin_url(PMB_ADMIN_SETTINGS_PAGE_PATH)
            . '">'
            . esc_html__('Settings', 'print-my-blog')
            . '</a>'
            ),
            $links,
            [
                '<a href="'
                . add_query_arg(
                    [
                        'action' => 'uninstall'
                    ],
                    admin_url(PMB_ADMIN_PAGE_PATH)
                )
                . '" id="pmb-uninstall" class="pmb-uninstall">'
                . esc_html__('Delete All Data', 'print-my-blog')
                . '</a>'
            ]
        );

        return $links;
    }

    public function enqueueScripts($hook)
    {
        wp_enqueue_style(
            'pmb_admin',
            PMB_STYLES_URL . 'pmb-admin.css',
            [],
            filemtime(PMB_STYLES_DIR . 'pmb-admin.css')
        );
        if (isset($_GET['welcome'])) {
            wp_enqueue_style(
                'pmb_welcome',
                PMB_ASSETS_URL . 'styles/welcome.css',
                array(),
                filemtime(PMB_ASSETS_DIR . 'styles/welcome.css')
            );
        } elseif ($hook === 'print-my-blog_page_print-my-blog-now') {
            wp_enqueue_script('pmb-setup-page');
            wp_enqueue_style('pmb-setup-page');
        } elseif (
            $hook === 'toplevel_page_print-my-blog-projects'
                 && isset($_GET['action'])
                && $_GET['action'] === self::SLUG_ACTION_EDIT_PROJECT
        ) {
            switch (isset($_GET['subaction']) ? $_GET['subaction'] : null) {
                case self::SLUG_SUBACTION_PROJECT_CONTENT:
                    wp_register_script(
                        'sortablejs',
                        PMB_SCRIPTS_URL . 'libs/Sortable.min.js',
                        array(),
                        '1.10.2'
                    );

                    wp_enqueue_script(
                        'pmb_project_edit_content',
                        PMB_SCRIPTS_URL . 'project-edit-content.js',
                        array('sortablejs','jquery-ui-datepicker', 'jquery-ui-dialog'),
                        filemtime(PMB_SCRIPTS_DIR . 'project-edit-content.js')
                    );
                    wp_enqueue_style('jquery-ui');
                    /**
                     * @var $project Project
                     */
                    $project = $this->project_manager->getById($_GET['ID']);
                    wp_localize_script(
                        'pmb_project_edit_content',
                        'pmb_project_edit_content_data',
                        [
                            'levels' => $project->getLevelsAllowed()
                        ]
                    );
                    break;
                case self::SLUG_SUBACTION_PROJECT_CHANGE_DESIGN:
                    wp_enqueue_script(
                        'pmb-choose-design',       // handle
                        PMB_SCRIPTS_URL . 'pmb-design-choose.js',       // source
                        array('jquery-ui-dialog'),
                        filemtime(PMB_SCRIPTS_DIR . 'pmb-design-choose.js')
                    );
                    // A style available in WP
                    wp_enqueue_style('wp-jquery-ui-dialog');
                    wp_enqueue_style(
                        'pmb-choose-design',
                        PMB_STYLES_URL . 'design-choose.css',
                        [],
                        filemtime(PMB_STYLES_DIR . 'design-choose.css')
                    );
            }

            // everybody uses the style, right?
            wp_enqueue_style(
                'pmb_project_edit',
                PMB_STYLES_URL . 'project-edit.css',
                array(),
                filemtime(PMB_STYLES_DIR . 'project-edit.css')
            );
        } elseif ($hook === 'plugins.php') {
            wp_enqueue_script(
                'pmb-plugins-page',
                PMB_SCRIPTS_URL . 'pmb-plugins-page.js',
                [],
                filemtime(PMB_SCRIPTS_DIR . 'pmb-plugins-page.js')
            );
        }
    }

    public function renderProjects()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : null;
        if ($action === self::SLUG_ACTION_ADD_NEW) {
            $this->editSetup();
        } elseif ($action === self::SLUG_ACTION_EDIT_PROJECT) {
            $subaction = isset($_GET['subaction']) ? $_GET['subaction'] : null;
            $project = $this->project_manager->getById($_GET['ID']);
            switch ($subaction) {
                case self::SLUG_SUBACTION_PROJECT_CHANGE_DESIGN:
                    $this->editChooseDesign($project);
                    break;
                case self::SLUG_SUBACTION_PROJECT_CUSTOMIZE_DESIGN:
                    $this->editCustomizeDesign($project);
                    break;
                case self::SLUG_SUBACTION_PROJECT_CONTENT:
                    $this->editContent($project);
                    break;
                case self::SLUG_SUBACTION_PROJECT_META:
                    $this->editMetadata($project);
                    break;
                case self::SLUG_SUBACTION_PROJECT_GENERATE:
                    $this->editGenerate($project);
                    break;
                case self::SLUG_SUBACTION_PROJECT_SETUP:
                default:
                    $this->editSetup($project);
            }
        } else {
            if (!class_exists('WP_List_Table')) {
                require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
            }
            $table = new ProjectsListTable();
            $add_new_url = add_query_arg(
                [
                    'action' => self::SLUG_ACTION_ADD_NEW,
                ],
                admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
            );
            include(PMB_TEMPLATES_DIR . 'projects_list_table.php');
        }
    }

    protected function editChooseDesign(Project $project)
    {
        // determine the format
        $format = $this->file_format_registry->getFormat($_GET['format']);
        // get all the designs for this format
        // including which format is actually in-use
        $wp_query_args = [
            'meta_query' => [
                [
                    'key' => Design::META_PREFIX . 'format',
                    'value' => $format->slug()
                ]
            ]
        ];
        $designs = $this->design_manager->getAll(new WP_Query($wp_query_args));
        $chosen_design = $project->getDesignFor($format->slug());
        // show them in a template
        $this->renderProjectTemplate(
            'design_choose.php',
            [
                'project' => $project,
                'format' => $format,
                'designs' => $designs,
                'chosen_design' => $chosen_design
            ]
        );
    }

    /**
     * @param $action
     */
    protected function editSetup(Project $project = null)
    {
        if ($this->invalid_form instanceof FormSection) {
            $form = $this->invalid_form;
        } else {
            $form = $this->getSetupForm($project);
        }
        $this->renderProjectTemplate(
            'project_edit_setup.php',
            [
                'form' => $form,
                'project' => $project
            ]
        );
    }

    protected function editCustomizeDesign(Project $project)
    {
        $format_slug = $_GET['format'];
        $design = $project->getDesignFor($format_slug);
        if (! $design instanceof Design) {
            throw new Exception(sprintf(
                'Could not determine the design for project "%s" for format "%s"',
                $project->getWpPost()->ID,
                $format_slug
            ));
        }
        // If there was an invalid form submission, show it.
        if ($this->invalid_form instanceof FormSection) {
            $form = $this->invalid_form;
        } else {
            $form = $design->getDesignForm();
        }

        $form_url = add_query_arg(
            [
                'action' => self::SLUG_ACTION_EDIT_PROJECT,
                'subaction' => self::SLUG_SUBACTION_PROJECT_CUSTOMIZE_DESIGN,
                '_nonce' => wp_create_nonce('pmb-project-edit'),
                'ID' => $project->getWpPost()->ID,
                'format' => $format_slug
            ],
            admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
        );
        $this->renderProjectTemplate(
            'design_customize.php',
            [
            'form_url' => $form_url,
            'form' => $form,
            'design' => $design,
            'format_slug' => $format_slug,
            'project' => $project
            ]
        );
    }

    protected function editContent(Project $project)
    {
        $project_support_front_matter = $project->supportsDivision(DesignTemplate::IMPLIED_DIVISION_FRONT_MATTER);
        if ($project_support_front_matter) {
            $front_matter_sections = $project->getSections(
                1000,
                0,
                true,
                DesignTemplate::IMPLIED_DIVISION_FRONT_MATTER
            );
        } else {
            $front_matter_sections = null;
        }
        $sections = $project->getSections(
            1000,
            0,
            true,
            DesignTemplate::IMPLIED_DIVISION_MAIN_MATTER
        );
        $project_support_back_matter = $project->supportsDivision(DesignTemplate::IMPLIED_DIVISION_BACK_MATTER);
        if ($project_support_back_matter) {
            $back_matter_sections = $project->getSections(
                1000,
                0,
                true,
                DesignTemplate::IMPLIED_DIVISION_BACK_MATTER
            );
        } else {
            $back_matter_sections = null;
        }

        $form_url = add_query_arg(
            [
                'ID' => $project->getWpPost()->ID,
                'action' => self::SLUG_ACTION_EDIT_PROJECT,
                'subaction' => self::SLUG_SUBACTION_PROJECT_CONTENT
            ],
            admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
        );
        $this->renderProjectTemplate(
            'project_edit_content.php',
            [
            'form_url' => $form_url,
            'back_matter_sections' => $back_matter_sections,
            'sections' => $sections,
            'front_matter_sections' => $front_matter_sections,
            'project' => $project,
            'project_support_front_matter' => $project_support_front_matter,
            'project_support_back_matter' => $project_support_back_matter,
            'post_types' => $this->post_fetcher->getProjectPostTypes('objects'),
            'authors' => get_users(['number' => 10, 'who' => 'authors']),
            ]
        );
    }

    protected function editMetadata(Project $project)
    {

        if ($this->invalid_form instanceof FormSection) {
            $form = $this->invalid_form;
        } else {
            $form = $project->getMetaForm();
            $defaults = [];
            foreach ($form->inputsInSubsections() as $input) {
                $saved_value = $project->getSetting($input->name());
                if ($saved_value) {
                    $defaults[$input->name()] = $saved_value;
                }
            }
            $form->populateDefaults($defaults);
        }
        $form_url = add_query_arg(
            [
                'ID' => $project->getWpPost()->ID,
                'action' => self::SLUG_ACTION_EDIT_PROJECT,
                'subaction' => self::SLUG_SUBACTION_PROJECT_META
            ],
            admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
        );
        $this->renderProjectTemplate(
            'project_edit_metadata.php',
            [
            'form_url' => $form_url,
            'form' => $form,
            'project' => $project
            ]
        );
    }

    protected function editGenerate(Project $project)
    {
        $generations = $project->getAllGenerations();
        $this->renderProjectTemplate(
            'project_edit_generate.php',
            [
            'project' => $project,
            'generations' => $generations
            ]
        );
    }

    protected function renderProjectTemplate($template_name, $args)
    {

        if ($args['project'] instanceof Project) {
            $args['steps_to_urls'] = $this->mapStepToUrls($args['project']);
            $args['current_step'] = $args['project']->getProgress()->mapSubactionToStep(
                isset($_GET['subaction']) ? $_GET['subaction'] : null,
                isset($_GET['format']) ? $_GET['format'] : null
            );
        } else {
            $args['steps_to_urls'] = [];
            $args['current_step'] = ProjectProgress::SETUP_STEP;
        }
        echo pmb_render_template($template_name, $args);
    }

    /**
     * @param Project $project
     *
     * @return array keys are steps for that project, and values are their URLs.
     */
    protected function mapStepToUrls(Project $project)
    {
        $base_url_args = [
            'ID' => $project->getWpPost()->ID,
            'action' => Admin::SLUG_ACTION_EDIT_PROJECT,
        ];
        $mapping = [];
        foreach ($project->getProgress()->getSteps() as $step => $label) {
            $args = $project->getProgress()->mapStepToSubactionArgs($step);
            $mappings[$step] = add_query_arg(
                array_merge($base_url_args, $args),
                admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
            );
        }
        return $mappings;
    }



    /**
     * Checks if a form was submitted, in which case we'd want to redirect.
     * @since 3.0
     *
     */
    public function checkFormSubmission()
    {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
        if ($action === self::SLUG_ACTION_ADD_NEW) {
            $this->saveNewProject();
            exit;
        }
        if ($action === self::SLUG_ACTION_EDIT_PROJECT) {
            $subaction = isset($_GET['subaction']) ? $_GET['subaction'] : null;
            $project = $this->project_manager->getById($_GET['ID']);
            switch ($subaction) {
                case self::SLUG_SUBACTION_PROJECT_SETUP:
                    $this->saveNewProject($project);
                    break;
                case self::SLUG_SUBACTION_PROJECT_CHANGE_DESIGN:
                    $this->saveProjectChooseDesign($project);
                    break;
                case self::SLUG_SUBACTION_PROJECT_CUSTOMIZE_DESIGN:
                    $this->saveProjectCustomizeDesign($project);
                    break;
                case self::SLUG_SUBACTION_PROJECT_CONTENT:
                    $this->saveProjectContent($project);
                    break;
                case self::SLUG_SUBACTION_PROJECT_META:
                    $this->saveProjectMetadata($project);
                    break;
                case self::SLUG_SUBACTION_PROJECT_GENERATE:
                    $this->saveProjectGenerate($project);
                    break;
            }
        } elseif ($action === 'delete') {
            $this->deleteProjects();
            $redirect = admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH);
            wp_safe_redirect($redirect);
            exit;
        }
    }

    /**
     * @param Project|null $project
     *
     * @return FormSection
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    protected function getSetupForm(Project $project = null)
    {
        $formats = $this->file_format_registry->getFormats();
        $format_options = [];
        foreach ($formats as $format) {
            $format_options[$format->slug()] = new InputOption(
                $format->title(),
                $format->desc()
            );
        }
        $format_options['all'] = new InputOption(
            __('Both', 'print-my-blog'),
            // phpcs:disable Generic.Files.LineLength.TooLong
            __('A project can be prepared to use both a Digital PDF and a Print-Ready PDF, but it will be more complex. (Eg the design for one format might support different features than the design for the other format.)', 'print-my-blog')
            // phpcs:enable Generic.Files.LineLength.TooLong
        );
        $default_format = null;
        if ($project instanceof Project) {
            $formats_preselected = $project->getFormatsSelected();
            if (count($formats_preselected) === 1) {
                $format_preselected = reset($formats_preselected);
                $default_format = $format_preselected->slug();
            } elseif (count($format_options) > 1) {
                $default_format = 'all';
            }
        }
        return new FormSection([
            'name' => 'pmb-project',
            'subsections' => [
                'title' => new TextInput([
                    'html_label_text' => __('Project Title', 'print-my-blog'),
                    'required' => true,
                    'default' => $project instanceof Project ? $project->getWpPost()->post_title : '',
                ]),
                'formats' => new RadioButtonInput(
                    $format_options,
                    [
                        'html_label_text' => __('Format', 'print-my-blog'),
                        'required' => true,
                        'default' => $default_format
                    ]
                )
            ]
        ]);
    }
    protected function saveNewProject(Project $project = null)
    {
        $form = $this->getSetupForm($project);
        $form->receiveFormSubmission($_REQUEST);
        if (! $form->isValid()) {
            $this->invalid_form = $form;
            return;
        }
        $initialize_steps = false;

        if (! $project instanceof Project) {
            $project_id = wp_insert_post(
                [
                    'post_content' => '',
                    'post_type' => CustomPostTypes::PROJECT,
                    'post_status' => 'publish'
                ],
                true
            );
            if (is_wp_error($project_id)) {
                wp_die($project_id->get_error_message());
            }
            $project = $this->project_manager->getById($project_id);
            $project->setCode();
            $title_page = get_page_by_path('pmb-title-page', OBJECT, CustomPostTypes::CONTENT);
            $toc_page = get_page_by_path('pmb-toc', OBJECT, CustomPostTypes::CONTENT);
            $this->section_manager->setSectionsFor(
                $project_id,
                [
                    [
                        $title_page->ID,
                        'just_content',
                        0,
                        1,
                        []
                    ],
                    [
                        $toc_page->ID,
                        '',
                        0,
                        1,
                        []
                    ]
                ],
                DesignTemplate::IMPLIED_DIVISION_FRONT_MATTER
            );
            $initialize_steps = true;
        }
        $project->setTitle($form->getInputValue('title'));
        $formats_to_save = [];
        if ($form->getInputValue('formats') === 'all') {
            $formats_to_save = array_map(
                function (FileFormat $format) {
                    return $format->slug();
                },
                $this->file_format_registry->getFormats()
            );
        } else {
            $formats_to_save[] = $form->getInputValue('formats');
        }
        $old_formats = $project->getFormatSlugsSelected();
        $project->setFormatsSelected($formats_to_save);
        $this->notification_manager->addTextNotificationForCurrentUser(
                OneTimeNotification::TYPE_SUCCESS,
                sprintf(
                        __('Successfully setup the project "%s".', 'print-my-blog'),
                    $project->getWpPost()->post_title
                )
        );
        if ($initialize_steps) {
            $project->getProgress()->initialize();
        } else {
            $new_formats = array_diff($formats_to_save, $old_formats);
            foreach ($new_formats as $new_format) {
                $project->getProgress()->markChooseDesignStepComplete($new_format, false);
                $project->getProgress()->markCustomizeDesignStepComplete($new_format, false);
                $this->notification_manager->addTextNotificationForCurrentUser(
                    OneTimeNotification::TYPE_INFO,
                    sprintf(
                        __('You need to choose and customize the design for your %s.', 'print-my-blog'),
                        $this->file_format_registry->getFormat($new_format)->title()
                    )
                );
            }
        }
        $project->getProgress()->markStepComplete(ProjectProgress::SETUP_STEP);
        $this->redirectToNextStep($project);
    }

    /**
     *
     * @param Project $project
     */
    protected function redirectToNextStep(Project $project)
    {
        $args = [
            'ID' => $project->getWpPost()->ID,
            'action' => self::SLUG_ACTION_EDIT_PROJECT
        ];
        $next_step = $project->getProgress()->getNextStep();
        $args = array_merge($args, $project->getProgress()->mapStepToSubactionArgs($next_step));
        // Redirect to it
        wp_redirect(
            add_query_arg(
                $args,
                admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
            )
        );
        exit;
    }

    /**
     * Saves the project's name and parts etc.
     * @return int project ID
     */
    protected function saveProjectContent(Project $project)
    {
        check_admin_referer('pmb-project-edit');
        foreach ($project->getAllGenerations() as $project_generation) {
            $project_generation->addDirtyReason(
                'content_update',
                __('The content in your project has changed', 'print-my-blog')
            );
        }
        $project->setProjectDepth(intval($_POST['pmb-project-depth']));

        $this->section_manager->clearSectionsFor($project->getWpPost()->ID);
        $order = 1;
        $this->setSectionFromRequest(
            $project,
            'pmb-project-front-matter-data',
            DesignTemplate::IMPLIED_DIVISION_FRONT_MATTER,
            $order
        );
        $this->setSectionFromRequest(
            $project,
            'pmb-project-main-matter-data',
            DesignTemplate::IMPLIED_DIVISION_MAIN_MATTER,
            $order
        );
        $this->setSectionFromRequest(
            $project,
            'pmb-project-back-matter-data',
            DesignTemplate::IMPLIED_DIVISION_BACK_MATTER,
            $order
        );
        $project->getProgress()->markStepComplete(ProjectProgress::EDIT_CONTENT_STEP);
        $this->notification_manager->addTextNotificationForCurrentUser(
                OneTimeNotification::TYPE_SUCCESS,
                __('Updated project content.', 'print-my-blog')
        );
        $this->redirectToNextStep($project);
    }

    protected function setSectionFromRequest(Project $project, $request_data, $placement, &$order = 1)
    {
        $section_data = stripslashes($_POST[$request_data]);
        $sections = json_decode($section_data);
        if ($section_data) {
            $this->section_manager->setSectionsFor(
                $project->getWpPost()->ID,
                $sections,
                $placement,
                $order
            );
        }
    }

    protected function saveProjectCustomizeDesign(Project $project)
    {
        $design = $project->getDesignFor($_GET['format']);
        $design_form = $design->getDesignTemplate()->getDesignFormTemplate();
        $design_form->receiveFormSubmission($_REQUEST);
        if (! $design_form->isValid()) {
            $this->invalid_form = $design_form;
        }
        foreach ($design_form->inputValues(true, true) as $setting_name => $normalized_value) {
            $design->setSetting($setting_name, $normalized_value);
        }
        $project_generation = $project->getGenerationFor($_GET['format']);
        $project_generation->addDirtyReason(
            'design_change',
            __('You have customized this design', 'print-my-blog')
        );
        $project->getProgress()->markCustomizeDesignStepComplete($design->getDesignTemplate()->getFormatSlug());
        $this->notification_manager->addTextNotificationForCurrentUser(
                OneTimeNotification::TYPE_SUCCESS,
                sprintf(
                        __('The design "%s" has been customized, and its changes will be reflected in all projects that use it.', 'print-my-blog'),
                    $design->getWpPost()->post_title
                )
        );
        $this->redirectToNextStep($project);
    }

    protected function saveProjectChooseDesign(Project $project)
    {
        $design = $this->design_manager->getById((int)$_REQUEST['design']);
        $format = $this->file_format_registry->getFormat($_GET['format']);
        if (! $design instanceof Design || ! $format instanceof FileFormat) {
            throw new Exception(
                sprintf(
                    __('An invalid design (%s) or format provided(%s)', 'print-my-blog'),
                    $_GET['design'],
                    $_GET['format']
                )
            );
        }
        $project->setDesignFor($format, $design);
        $project_generation = $project->getGenerationFor($format);
        $project_generation->addDirtyReason(
            'design_change',
            __('You changed the design', 'print-my-blog')
        );
        $project->getProgress()->markCustomizeDesignStepComplete($format->slug(), false);
        $project->getProgress()->markChooseDesignStepComplete($format->slug());
        $this->notification_manager->addTextNotificationForCurrentUser(
                OneTimeNotification::TYPE_SUCCESS,
                sprintf(
                        __('Successful chose the design "%1$s" for the %2$s for your project.', 'print-my-blog'),
                    $design->getWpPost()->post_title,
                    $format->title()
                )
        );
        $this->notification_manager->addTextNotificationForCurrentUser(
                OneTimeNotification::TYPE_INFO,
                __('You may want to customize the design.', 'print-my-blog')
        );
        $this->redirectToNextStep($project);
    }

    /**
     * Gets the project form, which is a combination of the project forms for all the designs in use.
     * @param Project $project
     *
     * @return FormSection
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    protected function saveProjectMetadata(Project $project)
    {
        $form = $project->getMetaForm();
        $form->receiveFormSubmission($_REQUEST);
        if (! $form->isValid()) {
            $this->invalid_form = $form;
            return;
        }
        foreach ($form->inputValues(true, true) as $setting_name => $normalized_value) {
            $project->setSetting($setting_name, $normalized_value);
        }
        $project_generations = $project->getAllGenerations();
        foreach ($project_generations as $generation) {
            $generation->addDirtyReason(
                'metadata',
                __('You changed projected metadata', 'print-my-blog')
            );
        }
        $project->getProgress()->markStepComplete(ProjectProgress::EDIT_METADATA_STEP);
        $this->notification_manager->addTextNotificationForCurrentUser(
                OneTimeNotification::TYPE_SUCCESS,
                __('Project metadata updated.', 'print-my-blog')
        );
        $this->redirectToNextStep($project);
    }

    /**
     * Currently unused, but probably will be once we support skipping re-generating etc.
     * @param Project $project
     *
     * @throws Exception
     */
    protected function saveProjectGenerate(Project $project)
    {
        $format = $this->file_format_registry->getFormat($_GET['format']);
        if (! $format instanceof FileFormat) {
            throw new Exception(__('There is no file format with the slug "%s"', 'print-my-blog'), $_GET['format']);
        }
        $project_generation = $project->getGenerationFor($format);
        $project_generation->deleteGeneratedFiles();
        $project_generation->clearDirty();
        $project->getProgress()->markStepComplete(ProjectProgress::GENERATE_STEP);
        $url = add_query_arg(
            [
                PMB_PRINTPAGE_SLUG => 3,
                'project' => $project->getWpPost()->ID,
                'format' => $format->slug()
            ],
            site_url()
        );
        $project->getProgress()->markStepComplete(ProjectProgress::GENERATE_STEP);
        wp_safe_redirect($url);
        exit;
    }

    /**
     * Makes it so the "publish" button on PMB Print Materials pages instead say "Save".
     * Works for both classic editor and Gutenberg
     */
    protected function makePrintContentsSaySaved()
    {
        global $pagenow;
        if (
            isset($pagenow) && $pagenow == 'post-new.php'
            && isset($_GET['post_type']) && $_GET['post_type'] === CustomPostTypes::CONTENT
        ) {
            add_action('admin_print_footer_scripts', [$this,'makePrintContentsSaySavedGutenberg']);
            add_filter(
                'gettext',
                function ($translated, $text_domain, $original) {
                    if ($translated === 'Publish') {
                        return __('Save', 'print-my-blog');
                    }
                    return $translated;
                },
                10,
                3
            );
        }
    }

    /**
     * On the PMB Print Materials CPT Gutenberg new page, change "Publish" button to just be "Save" because the post
     * type isn't publicly visible.
     */
    public function makePrintContentsSaySavedGutenberg()
    {
        // we've already checked we're on the right page
        if (wp_script_is('wp-i18n')) {
            ?>
        <script>
            // Note: Make sure that `wp.i18n` has already been defined by the time you call `wp.i18n.setLocaleData()`.
            wp.i18n.setLocaleData({
                'Publish': [
                    'Save',
                    'print-my-blog'
                ]
            });
        </script>
            <?php
        }
    }

    protected function deleteProjects()
    {
        // In our file that handles the request, verify the nonce.
        $nonce = esc_attr($_REQUEST['_wpnonce']);
        if (!wp_verify_nonce($nonce, 'bulk-projects')) {
            die('The request has expired. Please refresh the previous page and try again.');
        } else {
            $this->project_manager->deleteProjects($_POST['ID']);
        }
    }

    protected function uninstall()
    {
        // clear custom table
        $this->table_manager->dropTables();

        // clear CPTs
        $deleted = $this->post_fetcher->deleteCustomPostTypes();

        // clear options
        global $wpdb;
        $wpdb->query('DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "pmb_%"');
    }
}
