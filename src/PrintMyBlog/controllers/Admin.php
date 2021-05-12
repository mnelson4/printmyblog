<?php

namespace PrintMyBlog\controllers;

use Dompdf\Renderer\Text;
use Exception;
use FS_Plugin_License;
use FS_Site;
use PrintMyBlog\controllers\helpers\ProjectsListTable;
use PrintMyBlog\db\PostFetcher;
use PrintMyBlog\db\TableManager;
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
use PrintMyBlog\services\DebugInfo;
use PrintMyBlog\services\FileFormatRegistry;
use PrintMyBlog\services\PmbCentral;
use PrintMyBlog\services\SvgDoer;
use PrintMyBlog\system\CustomPostTypes;
use Twine\entities\notifications\OneTimeNotification;
use Twine\forms\base\FormSection;
use Twine\forms\base\FormSectionHtml;
use Twine\forms\helpers\InputOption;
use Twine\forms\inputs\HiddenInput;
use Twine\forms\inputs\RadioButtonInput;
use Twine\forms\inputs\TextAreaInput;
use Twine\forms\inputs\TextInput;
use Twine\forms\inputs\YesNoInput;
use Twine\helpers\Array2;
use Twine\services\display\FormInputs;
use Twine\controllers\BaseController;
use Twine\services\notifications\OneTimeNotificationManager;
use WP_Error;
use WP_Query;

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
    const SLUG_ACTION_REVIEW = 'review';
    const SLUG_SUBACTION_PROJECT_SETUP = 'setup';
    const SLUG_SUBACTION_PROJECT_CUSTOMIZE_DESIGN = 'customize_design';
    const SLUG_SUBACTION_PROJECT_CHANGE_DESIGN = 'choose_design';
    const SLUG_SUBACTION_PROJECT_CONTENT = 'content';
    const SLUG_SUBACTION_PROJECT_META = 'metadata';
    const SLUG_SUBACTION_PROJECT_GENERATE = 'generate';
    const REVIEW_OPTION_NAME = 'pmb_review';
    const SLUG_ACTION_UNINSTALL = 'uninstall';


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
     * Somewhere to put the WP_Error emitted by wp_mail in an action (but not returned)
     * @var WP_Error
     */
    protected $wp_error;
    /**
     * @var DebugInfo
     */
    protected $debug_info;

    /**
     * @var PmbCentral
     */
    protected $pmb_central;

    /**
     * The project referenced on this request, if any.
     * @var Project|null
     */
    protected $project;

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
        OneTimeNotificationManager $notification_manager,
        DebugInfo $debug_info,
        PmbCentral $pmb_central
    ) {
        $this->post_fetcher    = $post_fetcher;
        $this->section_manager = $section_manager;
        $this->project_manager = $project_manager;
        $this->file_format_registry = $file_format_registry;
        $this->design_manager = $design_manager;
        $this->table_manager = $table_manager;
        $this->svg_doer = $svg_doer;
        $this->notification_manager = $notification_manager;
        $this->debug_info = $debug_info;
        $this->pmb_central = $pmb_central;
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

        $this->makePrintContentsSaySaved();
        $this->notification_manager->showOneTimeNotifications();
        $this->maybeRefreshCreditCache();
        $this->earlyResponseHandling();
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
            esc_html__('Quick Print', 'print-my-blog'),
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
        add_submenu_page(
            PMB_ADMIN_PROJECTS_PAGE_SLUG,
            __('Help Me Print My Blog', 'print-my-blog'),
            __('Help', 'print-my-blog'),
            PMB_ADMIN_CAP,
            PMB_ADMIN_HELP_PAGE_SLUG,
            [$this,'helpPage']
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
        if (Array2::setOr($_SERVER, 'REQUEST_METHOD', '') === 'POST') {
            check_admin_referer('pmb-settings');
            // Ok save those settings!
            if (isset($_POST['pmb-reset'])) {
                $settings = new FrontendPrintSettings(new PrintOptions());
            } else {
                $settings->setShowButtons(isset($_POST['show_buttons']));
                $settings->setShowButtonsPages(isset($_POST['show_buttons_pages']));
                $settings->setPlaceAbove(Array2::setOr($_POST, 'place_above', 1));
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

    public function helpPage()
    {

        if ($this->invalid_form instanceof FormSection) {
            $form = $this->invalid_form;
            $form_url = '';
            $method = 'GET';
            $button_text = '';
        } else {
            if (pmb_fs()->is_plan__premium_only('founding_members')) {
                $form = $this->getEmailHelpForm();
                $form_url = admin_url(PMB_ADMIN_HELP_PAGE_PATH);
                $method = 'POST';
                $button_text = esc_html__('Email Print My Blog Support', 'print-my-blog');
            } else {
                $form = $this->getGithubHelpForm();
                $form_url = 'https://github.com/mnelson4/printmyblog/issues/new';
                $method = 'GET';
                $button_text = esc_html__('Report Issue on GitHub', 'print-my-blog');
            }
        }
        pmb_render_template(
            'help.php',
            [
                'form' => $form,
                'form_url' => $form_url,
                'form_method' => $method,
                'button_text' => $button_text
            ]
        );
    }

    public function sendHelp()
    {
        global $current_user;
        $form = $this->getEmailHelpForm();
        $form->receiveFormSubmission($_REQUEST);
        if (! $form->isValid()) {
            $this->invalid_form = $form;
            return;
        }
        // don't translate these strings. They're sent to the dev who speaks English.
        add_action(
            'wp_mail_failed',
            [$this,'sendHelpError'],
            10
        );

        $headers = array(
            'Reply-To: ' . $current_user->display_name . ' <' . $current_user->user_email . '>',
        );
        $subject = sprintf('Help for %s', site_url());
        $message = sprintf(
            'Message:%1$s
            <br>
            Consent:%2$s,
            Data:%3$s',
            $form->getInputValue('reason'),
            $form->getInputValue('consent') ? 'yes' : 'no',
            $form->getInputValue('debug_info')
        );
        $success = wp_mail(
            'please@printmy.blog',
            $subject,
            $message,
            $headers
        );

        if ($success) {
            $this->notification_manager->addTextNotificationForCurrentUser(
                OneTimeNotification::TYPE_SUCCESS,
                __('Email successfully sent. Expect a reply in the next 1-2 business days.', 'print-my-blog')
            );
        } else {
            $error = $this->wp_error;
            $this->notification_manager->addTextNotificationForCurrentUser(
                OneTimeNotification::TYPE_ERROR,
                sprintf(
                    __('There was an error sending an email from your website (it was "%1$s"). Please manually send an email to %2$s, with the subject "%3$s", with the content:', 'print-my-blog'),
                    $error->get_error_message(),
                    PMB_SUPPORT_EMAIL,
                    $subject
                )
                    . '<pre>' . $message . '</pre>'
            );
        }
        wp_safe_redirect(
            admin_url(PMB_ADMIN_HELP_PAGE_PATH)
        );
    }

    public function sendHelpError(WP_Error $error)
    {
        $this->wp_error = $error;
    }

    protected function getEmailHelpForm()
    {
        global $current_user;
        return new FormSection([
            'subsections' => [
                    'reason' => new TextAreaInput([
                        'html_label_text' => __('Please explain what you did, what you expected, and what went wrong', 'print-my-blog'),
                        'required' => true,
                        'html_help_text' => __('Including links to screenshots is appreciated', 'print-my-blog')
                    ]),
                'name' => new TextInput([
                    'html_label_text' => __('Your Name', 'print-my-blog'),
                    'default' => $current_user->user_firstname
                        ? $current_user->user_firstname . ' ' . $current_user->user_lastname
                        :  $current_user->display_name,
                    ]),
                'consent' => new YesNoInput([
                    'html_label_text' => __('Are you ok with us viewing your most recent generated documents?', 'print-my-blog'),
                    'default' => true,
                    'html_help_text' => __('Viewing your most recent generated documents saves a lot of time figuring out what is going wrong. We won’t share your content with anyone else.', 'print-my-blog')
                    ]),
                'debug_info' => new TextAreaInput([
                    'html_label_text' => __('This debug info will also be sent.', 'print-my-blog'),
                    'disabled' => true,
                    'default' => $this->debug_info->getDebugInfoString(),
                    'html_help_text' => __('This is mostly system information, list of active plugins, active theme, and some Print My Blog Pro info like your most recent projects.', 'print-my-blog')
                    ]),
            ]
        ]);
    }

    protected function getGithubHelpForm()
    {
        return new FormSection([
                'subsections' => [
                        'explanatory_text' => new FormSectionHtml(
                            '<h2>' . __('Support for your plan is offered on GitHub', 'print-my-blog') . '</h2>' .
                            '<p>' . __('GitHub is a public forum to share your issues with the developer and other users.', 'print-my-blog') . '</p>' .
                            '<p>' . sprintf(
                                __('You will need a GitHub account. If prefer to use email support please %1$spurchase a license that offers email support.%2$s', 'print-my-blog'),
                                '<a href="' . esc_url(pmb_fs()->get_upgrade_url()) . '">',
                                '</a>'
                            )
                            . '</p>'
                        ),
                        'body' => new HiddenInput([
                                'default' => '** Please describe what you were doing, what you expected to happen, and what the problem was. **
                                 

```
' . substr($this->debug_info->getDebugInfoString(false), 0, 5000) . '
```',
                                'html_name' => 'body'
                        ])
                ]
        ]);
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
            $links,
            [
                '<a href="'
                . add_query_arg(
                    [
                        'action' => self::SLUG_ACTION_UNINSTALL
                    ],
                    admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
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
        wp_enqueue_script('pmb_general');
        wp_enqueue_style(
            'pmb_admin',
            PMB_STYLES_URL . 'pmb-admin.css',
            [],
            filemtime(PMB_STYLES_DIR . 'pmb-admin.css')
        );
        if (apply_filters('pmb_pro_only__is_premium', pmb_fs()->is_premium())) {
            // Paid users don't need to be reminded what's pro and what's not
            wp_add_inline_style(
                'pmb_admin',
                '.pmb-pro-only, .pmb-pro-best{display:none;}'
            );
        }
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
                        array('sortablejs','jquery-ui-datepicker', 'jquery-ui-dialog','pmb-select2','wp-api',),
                        filemtime(PMB_SCRIPTS_DIR . 'project-edit-content.js')
                    );
                    wp_enqueue_style('jquery-ui');
                    wp_enqueue_style('pmb-select2');
                    wp_localize_script(
                        'pmb_project_edit_content',
                        'pmb_project_edit_content_data',
                        [
                            'levels' => $this->project->getLevelsAllowed(),
                            'default_rest_url' => function_exists('rest_url') ? rest_url('/wp/v2') : '',
                        ]
                    );
                    break;
                case self::SLUG_SUBACTION_PROJECT_CHANGE_DESIGN:
                    wp_enqueue_script(
                        'pmb-choose-design',       // handle
                        PMB_SCRIPTS_URL . 'pmb-design-choose.js',       // source
                        array('pmb-modal'),
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
                    break;
                case self::SLUG_SUBACTION_PROJECT_GENERATE:
                    wp_enqueue_script(
                        'pmb-generate',
                        PMB_SCRIPTS_URL . 'pmb-generate.js',
                        ['pmb-modal', 'docraptor'],
                        filemtime(PMB_SCRIPTS_DIR . 'pmb-generate.js')
                    );
                    wp_enqueue_style(
                        'pmb-generate',
                        PMB_STYLES_URL . 'pmb-generate.css',
                        [
                            'wp-jquery-ui-dialog'
                            ],
                        filemtime(PMB_STYLES_DIR . 'pmb-generate.css')
                    );
                    $license = pmb_fs()->_get_license();
                    $site = pmb_fs()->get_site();
                    wp_localize_script(
                        'pmb-generate',
                        'pmb_generate',
                        [
                            'site_url' => site_url(),
                            'use_pmb_central_for_previews' => pmb_fs()->is_plan__premium_only('business') ? 1 : 0,
                            'license_data' => [
                                'endpoint' => $this->pmb_central->getCentralUrl(),
                                'license_id' => $license instanceof FS_Plugin_License ? $license->id : '',
                                'install_id' => $site instanceof FS_Site ? $site->id : '',
                                'authorization_header' =>  $site instanceof FS_Site ? $this->pmb_central->getSiteAuthorizationHeader() : '',
                            ],

                            'doc_attrs' => apply_filters(
                                '\PrintMyBlog\controllers\Admin::enqueueScripts doc_attrs',
                                [
                                        'test' => defined('PMB_TEST_LIVE') && PMB_TEST_LIVE ? true : false,
                                        'type' => 'pdf',
                                        'javascript' => true, // Javascript by DocRaptor
                                        'name' => $this->project->getPublishedTitle(),
                                        'ignore_console_messages' => true,
                                        'ignore_resource_errors' => true,
                                        'pipeline' => 9,
                                        'prince_options' => [
                                            'base_url' => site_url(),
                                            'media' => 'print',                                       // use screen
                                            'http_timeout' => 60,
                                            'http_insecure' => true,
                                // styles
                                // instead of print styles
                                            // javascript: true, // use Prince's JS, which is more error tolerant
                                        ]
                                    ]
                            ),
                            'translations' => [
                                'error_generating' => __('There was an error preparing your content. Please visit the Print My Blog Help page.', 'print-my-blog'),
                                'socket_error' => __('Your project could not be accessed in order to generate the file. Maybe your website is not public? Please visit the Print My Blog Help page.', 'print-my-blog')
                                ]
                        ]
                    );
                    break;
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
            switch ($subaction) {
                case self::SLUG_SUBACTION_PROJECT_CHANGE_DESIGN:
                    $this->editChooseDesign();
                    break;
                case self::SLUG_SUBACTION_PROJECT_CUSTOMIZE_DESIGN:
                    $this->editCustomizeDesign();
                    break;
                case self::SLUG_SUBACTION_PROJECT_CONTENT:
                    $this->editContent();
                    break;
                case self::SLUG_SUBACTION_PROJECT_META:
                    $this->editMetadata();
                    break;
                case self::SLUG_SUBACTION_PROJECT_GENERATE:
                    $this->editGenerate();
                    break;
                case self::SLUG_SUBACTION_PROJECT_SETUP:
                default:
                    $this->editSetup();
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

    protected function editChooseDesign()
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
        $chosen_design = $this->project->getDesignFor($format->slug());
        // show them in a template
        $this->renderProjectTemplate(
            'design_choose.php',
            [
                'project' => $this->project,
                'format' => $format,
                'designs' => $designs,
                'chosen_design' => $chosen_design
            ]
        );
    }

    /**
     * @param $action
     */
    protected function editSetup()
    {
        if ($this->invalid_form instanceof FormSection) {
            $form = $this->invalid_form;
        } else {
            $form = $this->getSetupForm();
        }
        $this->renderProjectTemplate(
            'project_edit_setup.php',
            [
                'form' => $form,
                'project' => $this->project
            ]
        );
    }

    protected function editCustomizeDesign()
    {
        $format_slug = Array2::setOr($_GET, 'format', '');
        $design = $this->project->getDesignFor($format_slug);
        if (! $design instanceof Design) {
            throw new Exception(sprintf(
                'Could not determine the design for project "%s" for format "%s"',
                $this->project->getWpPost()->ID,
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
                'ID' => $this->project->getWpPost()->ID,
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
            'project' => $this->project
            ]
        );
    }

    protected function editContent()
    {
        $project_support_front_matter = $this->project->supportsDivision(DesignTemplate::IMPLIED_DIVISION_FRONT_MATTER);
        if ($project_support_front_matter) {
            $front_matter_sections = $this->project->getSections(
                1000,
                0,
                true,
                DesignTemplate::IMPLIED_DIVISION_FRONT_MATTER
            );
        } else {
            $front_matter_sections = null;
        }
        $sections = $this->project->getSections(
            1000,
            0,
            true,
            DesignTemplate::IMPLIED_DIVISION_MAIN_MATTER
        );
        $project_support_back_matter = $this->project->supportsDivision(DesignTemplate::IMPLIED_DIVISION_BACK_MATTER);
        if ($project_support_back_matter) {
            $back_matter_sections = $this->project->getSections(
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
                'ID' => $this->project->getWpPost()->ID,
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
            'project' => $this->project,
            'project_support_front_matter' => $project_support_front_matter,
            'project_support_back_matter' => $project_support_back_matter,
            'post_types' => $this->post_fetcher->getProjectPostTypes('objects'),
            'authors' => get_users(['number' => 10, 'who' => 'authors']),
            ]
        );
    }

    protected function editMetadata()
    {

        if ($this->invalid_form instanceof FormSection) {
            $form = $this->invalid_form;
        } else {
            $form = $this->project->getMetaForm();
            $defaults = [];
            foreach ($form->inputsInSubsections() as $input) {
                $saved_value = $this->project->getSetting($input->name());
                if ($saved_value) {
                    $defaults[$input->name()] = $saved_value;
                }
            }
            $form->populateDefaults($defaults);
        }
        $form_url = add_query_arg(
            [
                'ID' => $this->project->getWpPost()->ID,
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
            'project' => $this->project
            ]
        );
    }

    protected function editGenerate()
    {
        $generations = $this->project->getAllGenerations();
        $license_info = null;
        if (pmb_fs()->is__premium_only()) {
            $license = pmb_fs()->_get_license();
            if ($license instanceof FS_Plugin_License) {
                try {
                    $license_info = $this->pmb_central->getCreditsInfo();
                } catch (Exception $e) {
                    $this->notification_manager->addTextNotificationForCurrentUser(
                        'warning',
                        sprintf(
                            __('There was an error communicating with Print My Blog Central. It was %s', 'print-my-blog'),
                            $e->getMessage()
                        )
                    );
                    $this->notification_manager->showOneTimeNotifications();
                }
            }
            $upgrade_url = pmb_fs()->get_upgrade_url();
        } else {
            $upgrade_url = pmb_fs()->get_upgrade_url();
        }
        $this->renderProjectTemplate(
            'project_edit_generate.php',
            [
                'project' => $this->project,
                'generations' => $generations,
                'license_info' => $license_info,
                'upgrade_url' => $upgrade_url,
                'review_url' => add_query_arg(
                    [
                        'action' => self::SLUG_ACTION_REVIEW,
                    ],
                    admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
                ),
                'suggest_review' => ! get_option(self::REVIEW_OPTION_NAME, false)
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
        // Don't bother checking for form submission if the "page" parameter isn't even set.
        if (! isset($_GET['page'])) {
            return;
        }
        if ($_GET['page'] === PMB_ADMIN_HELP_PAGE_SLUG) {
            $this->sendHelp();
            exit;
        }
        if ($_GET['page'] === PMB_ADMIN_PROJECTS_PAGE_SLUG) {
            $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
            if ($action === self::SLUG_ACTION_ADD_NEW) {
                $this->saveNewProject();
                exit;
            }
            if ($action === self::SLUG_ACTION_EDIT_PROJECT) {
                $subaction = isset($_GET['subaction']) ? $_GET['subaction'] : null;
                switch ($subaction) {
                    case self::SLUG_SUBACTION_PROJECT_SETUP:
                        $this->saveNewProject();
                        break;
                    case self::SLUG_SUBACTION_PROJECT_CHANGE_DESIGN:
                        $this->saveProjectChooseDesign();
                        break;
                    case self::SLUG_SUBACTION_PROJECT_CUSTOMIZE_DESIGN:
                        $this->saveProjectCustomizeDesign();
                        break;
                    case self::SLUG_SUBACTION_PROJECT_CONTENT:
                        $this->saveProjectContent();
                        break;
                    case self::SLUG_SUBACTION_PROJECT_META:
                        $this->saveProjectMetadata();
                        break;
                    case self::SLUG_SUBACTION_PROJECT_GENERATE:
                        $this->saveProjectGenerate();
                        break;
                }
            } elseif ($action === 'delete') {
                $this->deleteProjects();
                $redirect = admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH);
                wp_safe_redirect($redirect);
                exit;
            }
        }
    }

    /**
     * @param Project|null $project
     *
     * @return FormSection
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    protected function getSetupForm()
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
            __('A project can be prepared to use both a Digital PDF and a Print-Ready PDF, but it will be more complex. (Eg the design for one format might support different features than the design for the other format.)', 'print-my-blog')
        );
        $default_format = null;
        if ($this->project instanceof Project) {
            $formats_preselected = $this->project->getFormatsSelected();
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
                    'default' => $this->project instanceof Project ? $this->project->getWpPost()->post_title : '',
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
    protected function saveNewProject()
    {
        $form = $this->getSetupForm();
        $form->receiveFormSubmission($_REQUEST);
        if (! $form->isValid()) {
            $this->invalid_form = $form;
            return;
        }
        $initialize_steps = false;

        if (! $this->project instanceof Project) {
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
            $this->project = $this->project_manager->getById($project_id);
            $this->project->setCode();
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
        $this->project->setTitle($form->getInputValue('title'));
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
        $old_formats = $this->project->getFormatSlugsSelected();
        $this->project->setFormatsSelected($formats_to_save);
        $this->notification_manager->addTextNotificationForCurrentUser(
            OneTimeNotification::TYPE_SUCCESS,
            sprintf(
                __('Successfully setup the project "%s".', 'print-my-blog'),
                $this->project->getWpPost()->post_title
            )
        );
        if ($initialize_steps) {
            $this->project->getProgress()->initialize();
        } else {
            $new_formats = array_diff($formats_to_save, $old_formats);
            foreach ($new_formats as $new_format) {
                $this->project->getProgress()->markChooseDesignStepComplete($new_format, false);
                $this->project->getProgress()->markCustomizeDesignStepComplete($new_format, false);
                $this->notification_manager->addTextNotificationForCurrentUser(
                    OneTimeNotification::TYPE_INFO,
                    sprintf(
                        __('You need to choose and customize the design for your %s.', 'print-my-blog'),
                        $this->file_format_registry->getFormat($new_format)->title()
                    )
                );
            }
        }
        $this->project->getProgress()->markStepComplete(ProjectProgress::SETUP_STEP);
        $this->redirectToNextStep($this->project);
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
    protected function saveProjectContent()
    {
        check_admin_referer('pmb-project-edit');

        $this->updateProjectModifiedDate();
        foreach ($this->project->getAllGenerations() as $project_generation) {
            $project_generation->addDirtyReason(
                'content_update',
                __('The content in your project has changed', 'print-my-blog')
            );
        }
        $this->project->setProjectDepth(intval(Array2::setOr($_POST, 'pmb-project-depth', 0)));

        $this->section_manager->clearSectionsFor($this->project->getWpPost()->ID);
        $order = 1;
        $this->setSectionFromRequest(
            $this->project,
            'pmb-project-front-matter-data',
            DesignTemplate::IMPLIED_DIVISION_FRONT_MATTER,
            $order
        );
        $this->setSectionFromRequest(
            $this->project,
            'pmb-project-main-matter-data',
            DesignTemplate::IMPLIED_DIVISION_MAIN_MATTER,
            $order
        );
        $this->setSectionFromRequest(
            $this->project,
            'pmb-project-back-matter-data',
            DesignTemplate::IMPLIED_DIVISION_BACK_MATTER,
            $order
        );
        $this->project->getProgress()->markStepComplete(ProjectProgress::EDIT_CONTENT_STEP);
        $this->notification_manager->addTextNotificationForCurrentUser(
            OneTimeNotification::TYPE_SUCCESS,
            __('Updated project content.', 'print-my-blog')
        );
        $this->redirectToNextStep($this->project);
    }

    /**
     * It's nice to know which project the user worked on last, but many steps don't actually affect the project post
     * directly (only meta or other related data). This can help to make sure the project post's modified_date still
     * gets updated. Not needed if already calling wp_update_post() with other data.
     */
    protected function updateProjectModifiedDate()
    {
        // update the post's modified date!
        wp_update_post([
            'ID' => $this->project->getWpPost()->ID
        ]);
    }

    protected function setSectionFromRequest(Project $project, $request_data, $placement, &$order = 1)
    {
        $section_data = stripslashes(Array2::setOr($_POST, $request_data, ''));
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

    protected function saveProjectCustomizeDesign()
    {
        $this->updateProjectModifiedDate();
        $design = $this->project->getDesignFor(Array2::setOr($_GET, 'format', ''));
        $design_form = $design->getDesignTemplate()->getDesignFormTemplate();
        $design_form->receiveFormSubmission($_REQUEST);
        if (! $design_form->isValid()) {
            $this->invalid_form = $design_form;
        }
        foreach ($design_form->inputValues(true, true) as $setting_name => $normalized_value) {
            $design->setSetting($setting_name, $normalized_value);
        }
        $project_generation = $this->project->getGenerationFor(Array2::setOr($_GET, 'format', ''));
        $project_generation->addDirtyReason(
            'design_change',
            __('You have customized this design', 'print-my-blog')
        );
        $this->project->getProgress()->markCustomizeDesignStepComplete($design->getDesignTemplate()->getFormatSlug());
        $this->notification_manager->addTextNotificationForCurrentUser(
            OneTimeNotification::TYPE_SUCCESS,
            sprintf(
                __('The design "%s" has been customized, and its changes will be reflected in all projects that use it.', 'print-my-blog'),
                $design->getWpPost()->post_title
            )
        );
        $this->redirectToNextStep($this->project);
    }

    protected function saveProjectChooseDesign()
    {
        $this->updateProjectModifiedDate();
        $design = $this->design_manager->getById((int)Array2::setOr($_REQUEST, 'design', ''));
        $format = $this->file_format_registry->getFormat(Array2::setOr($_GET, 'format', ''));
        if (! $design instanceof Design || ! $format instanceof FileFormat) {
            throw new Exception(
                sprintf(
                    __('An invalid design (%s) or format provided(%s)', 'print-my-blog'),
                    Array2::setOr($_GET, 'design', ''),
                    Array2::setOr($_GET, 'format', '')
                )
            );
        }
        $this->project->setDesignFor($format, $design);
        $project_generation = $this->project->getGenerationFor($format);
        $project_generation->addDirtyReason(
            'design_change',
            __('You changed the design', 'print-my-blog')
        );
        $this->notification_manager->addTextNotificationForCurrentUser(
            OneTimeNotification::TYPE_SUCCESS,
            sprintf(
                __('You chose the design "%1$s" for the %2$s of your project.', 'print-my-blog'),
                $design->getWpPost()->post_title,
                $format->title()
            )
        );
        $this->project->getProgress()->markChooseDesignStepComplete($format->slug());
        // If they've changed the design, ask them if they want to skip it.
        if ($this->project->getProgress()->isStepComplete(ProjectProgress::CHOOSE_DESIGN_STEP_PREFIX . $format->slug())) {
            $this->project->getProgress()->markCustomizeDesignStepComplete($format->slug(), false);
            $this->notification_manager->addTextNotificationForCurrentUser(
                OneTimeNotification::TYPE_INFO,
                __('You may want to customize the design. If not, feel free to jump ahead the next step.', 'print-my-blog')
            );
        }




        $this->redirectToNextStep($this->project);
    }

    /**
     * Gets the project form, which is a combination of the project forms for all the designs in use.
     * @param Project $project
     *
     * @return FormSection
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    protected function saveProjectMetadata()
    {
        $this->updateProjectModifiedDate();
        $form = $this->project->getMetaForm();
        $form->receiveFormSubmission($_REQUEST);
        if (! $form->isValid()) {
            $this->invalid_form = $form;
            return;
        }
        foreach ($form->inputValues(true, true) as $setting_name => $normalized_value) {
            $this->project->setSetting($setting_name, $normalized_value);
        }
        $project_generations = $this->project->getAllGenerations();
        foreach ($project_generations as $generation) {
            $generation->addDirtyReason(
                'metadata',
                __('You changed projected metadata', 'print-my-blog')
            );
        }
        $this->project->getProgress()->markStepComplete(ProjectProgress::EDIT_METADATA_STEP);
        $this->notification_manager->addTextNotificationForCurrentUser(
            OneTimeNotification::TYPE_SUCCESS,
            __('Project metadata updated.', 'print-my-blog')
        );
        $this->redirectToNextStep($this->project);
    }

    /**
     * Currently unused, but probably will be once we support skipping re-generating etc.
     * @param Project $project
     *
     * @throws Exception
     */
    protected function saveProjectGenerate()
    {
        $this->updateProjectModifiedDate();
        $format = $this->file_format_registry->getFormat(Array2::setOr($_GET, 'format', ''));
        if (! $format instanceof FileFormat) {
            throw new Exception(__('There is no file format with the slug "%s"', 'print-my-blog'), Array2::setOr($_GET, 'format', ''));
        }
        $project_generation = $this->project->getGenerationFor($format);
        $project_generation->deleteGeneratedFiles();
        $project_generation->clearDirty();
        $this->project->getProgress()->markStepComplete(ProjectProgress::GENERATE_STEP);
        $url = add_query_arg(
            [
                PMB_PRINTPAGE_SLUG => 3,
                'project' => $this->project->getWpPost()->ID,
                'format' => $format->slug()
            ],
            site_url()
        );
        $this->project->getProgress()->markStepComplete(ProjectProgress::GENERATE_STEP);
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
        $nonce = esc_attr(Array2::setOr($_REQUEST, '_wpnonce', ''));
        if (!wp_verify_nonce($nonce, 'bulk-projects')) {
            die('The request has expired. Please refresh the previous page and try again.');
        } else {
            $this->project_manager->deleteProjects(Array2::setOr($_POST, 'ID', ''));
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

    /**
     * We avoid asking PMB central for the credits alloted to a license as much as possible. But on the account page
     * we make sure to refresh it. This is the page the user arrives at after upgrading or making a purchase, so
     * the cache often needs to be refreshed here.
     */
    public function maybeRefreshCreditCache()
    {
        if (isset($_GET['page']) && $_GET['page'] === 'print-my-blog-projects-account') {
            if (pmb_fs()->_get_license() instanceof FS_Plugin_License) {
                try {
                    $this->pmb_central->getCreditsInfo(true);
                } catch (Exception $e) {
                    $this->notification_manager->addTextNotificationForCurrentUser(
                        'warning',
                        sprintf(
                            __('There was an error communicating with Print My Blog Central. It was %s', 'print-my-blog'),
                            $e->getMessage()
                        )
                    );
                    $this->notification_manager->showOneTimeNotifications();
                }
            }
        }
    }

    /**
     * Handles responses for PMB requests early on
     */
    private function earlyResponseHandling()
    {
        $this->checkProjectEditPage();
        if (Array2::setOr($_SERVER, 'REQUEST_METHOD', '') == 'POST') {
            add_action('admin_init', [$this, 'checkFormSubmission']);
        } elseif (Array2::setOr($_SERVER, 'REQUEST_METHOD', '') === 'GET') {
            add_action('admin_init', [$this,'checkSpecialLinks']);
        }
    }

    /**
     * Take special action on GET requests
     */
    public function checkSpecialLinks()
    {
        if (! isset($_GET['page'])) {
            return;
        }
        if ($_GET['page'] === PMB_ADMIN_PROJECTS_PAGE_SLUG) {
            $action = isset($_GET['action']) ? $_GET['action'] : null;
            if ($action === self::SLUG_ACTION_UNINSTALL) {
                $this->uninstall();
                if (current_user_can('activate_plugins')) {
                    if (! function_exists('deactivate_plugins')) {
                        require_once ABSPATH . 'wp-admin/includes/plugin.php';
                    }
                    deactivate_plugins(PMB_BASENAME, true);
                }
                wp_safe_redirect(admin_url('plugins.php'));
            } elseif ($action === self::SLUG_ACTION_REVIEW) {
                update_option(self::REVIEW_OPTION_NAME, true);
                wp_redirect(
                    'https://wordpress.org/support/plugin/print-my-blog/reviews/#new-post'
                );
                exit;
            }
        }
    }
    public function checkProjectEditPage()
    {
        if (!isset($_GET['page'])) {
            return;
        }
        if (
            $_GET['page'] === PMB_ADMIN_PROJECTS_PAGE_SLUG &&
            isset($_GET['action']) &&
            $_GET['action'] === self::SLUG_ACTION_EDIT_PROJECT
        ) {
            $project = $this->project_manager->getById($_GET['ID']);
            if (!$project) {
                wp_safe_redirect(admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH));
                exit;
            }
            $this->project = $project;
        }
    }
}