<?php

namespace PrintMyBlog\controllers;

use Dompdf\Renderer\Text;
use Exception;
use FS_Plugin_License;
use FS_Site;
use PrintMyBlog\controllers\helpers\ProjectsListTable;
use PrintMyBlog\db\PostFetcher;
use PrintMyBlog\db\TableManager;
use PrintMyBlog\domain\DefaultFileFormats;
use PrintMyBlog\domain\FrontendPrintSettings;
use PrintMyBlog\domain\PrintOptions;
use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\entities\FileFormat;
use PrintMyBlog\entities\ProjectGeneration;
use PrintMyBlog\entities\ProjectProgress;
use PrintMyBlog\exceptions\DesignTemplateDoesNotExist;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\orm\managers\DesignManager;
use PrintMyBlog\orm\managers\ProjectManager;
use PrintMyBlog\orm\managers\ProjectSectionManager;
use PrintMyBlog\services\DebugInfo;
use PrintMyBlog\services\ExternalResourceCache;
use PrintMyBlog\services\FileFormatRegistry;
use PrintMyBlog\services\PmbCentral;
use PrintMyBlog\services\SvgDoer;
use PrintMyBlog\system\Context;
use PrintMyBlog\system\CustomPostTypes;
use Twine\entities\notifications\OneTimeNotification;
use Twine\forms\base\FormSection;
use Twine\forms\base\FormSectionBase;
use Twine\forms\base\FormSectionHtml;
use Twine\forms\helpers\InputOption;
use Twine\forms\inputs\CheckboxMultiInput;
use Twine\forms\inputs\HiddenInput;
use Twine\forms\inputs\RadioButtonInput;
use Twine\forms\inputs\TextAreaInput;
use Twine\forms\inputs\TextInput;
use Twine\forms\inputs\YesNoInput;
use Twine\helpers\Array2;
use Twine\orm\managers\PostWrapperManager;
use Twine\services\display\FormInputs;
use Twine\controllers\BaseController;
use Twine\services\filesystem\Folder;
use Twine\services\notifications\OneTimeNotificationManager;
use WP_Error;
use WP_Post;
use WP_Post_Type;
use WP_Query;

use const http\Client\Curl\PROXY_HTTP;

/**
 * Class Admin
 *
 * Hooks needed to add our stuff to the admin.
 * Mostly it's just a few admin pages.
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
    const SLUG_ACTION_DUPLICATE_PRINT_MATERIAL = 'duplicate_print_material';
    const SLUG_SUBACTION_PROJECT_SETUP = 'setup';
    const SLUG_SUBACTION_PROJECT_CUSTOMIZE_DESIGN = 'customize_design';
    const SLUG_SUBACTION_PROJECT_CHANGE_DESIGN = 'choose_design';
    const SLUG_SUBACTION_PROJECT_CONTENT = 'content';
    const SLUG_SUBACTION_PROJECT_META = 'metadata';
    const SLUG_SUBACTION_PROJECT_GENERATE = 'generate';
    const SLUG_SUBACTION_PROJECT_DUPLICATE = 'duplicate';
    const SLUG_SUBACTION_PROJECT_CLEAR_CACHE = 'clear_cache';
    const REVIEW_OPTION_NAME = 'pmb_review';
    const SLUG_ACTION_UNINSTALL = 'uninstall';

    /**
     * Name of the option that just indicates we successfully saved the setttings.
     */
    const SETTINGS_SAVED_OPTION = 'pmb-settings-saved';


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
     * @var PostWrapperManager
     */
    protected $post_manager;

    /**
     * @var ExternalResourceCache
     */
    protected $external_resource_cache;

    /**
     * @param PostFetcher $post_fetcher
     * @param ProjectSectionManager $section_manager
     * @param ProjectManager $project_manager
     * @param FileFormatRegistry $file_format_registry
     * @param DesignManager $design_manager
     * @param TableManager $table_manager
     * @param SvgDoer $svg_doer
     * @param OneTimeNotificationManager $notification_manager
     * @param DebugInfo $debug_info
     * @param PmbCentral $pmb_central
     * @param PostWrapperManager $post_manager
     * @param ExternalResourceCache $external_resouce_cache
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
        PmbCentral $pmb_central,
        PostWrapperManager $post_manager,
        ExternalResourceCache $external_resouce_cache
    ) {
        $this->post_fetcher = $post_fetcher;
        $this->section_manager = $section_manager;
        $this->project_manager = $project_manager;
        $this->file_format_registry = $file_format_registry;
        $this->design_manager = $design_manager;
        $this->table_manager = $table_manager;
        $this->svg_doer = $svg_doer;
        $this->notification_manager = $notification_manager;
        $this->debug_info = $debug_info;
        $this->pmb_central = $pmb_central;
        $this->post_manager = $post_manager;
        $this->external_resource_cache = $external_resouce_cache;
    }

    /**
     * Sets hooks that we'll use in the admin.
     * @since 1.0.0
     */
    public function setHooks()
    {
        add_action('admin_menu', array($this, 'addToMenu'));
        add_filter('plugin_action_links_' . PMB_BASENAME, array($this, 'pluginPageLinks'));
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);

        if (pmb_fs()->is_plan__premium_only('founding_members')) {
            add_filter('post_row_actions', [$this, 'postAdminRowActions'], 10, 2);
            add_filter('page_row_actions', [$this, 'postAdminRowActions'], 10, 2);
            add_action('post_submitbox_misc_actions', array($this, 'addDuplicateAsPrintMaterialToClassicEditor'));
            add_action('enqueue_block_editor_assets', array($this, 'addDuplicateAsPrintMaterialToGutenberg'));
        }

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
                'renderProjects',
            ),
            $this->svg_doer->getSvgDataAsColor(PMB_DIR . 'assets/images/menu-icon.svg', 'white')
        );

        $projects_page = add_submenu_page(
            PMB_ADMIN_PROJECTS_PAGE_SLUG,
            esc_html__('Pro Print', 'print-my-blog'),
            esc_html__('Pro Print', 'print-my-blog'),
            PMB_ADMIN_CAP,
            PMB_ADMIN_PROJECTS_PAGE_SLUG,
            array($this, 'renderProjects')
        );
        add_action('load-' . $projects_page, [$this, 'addHelpTab']);
        $this->hackSubmenuContentIntoRightSpot();
        add_submenu_page(
            PMB_ADMIN_PROJECTS_PAGE_SLUG,
            esc_html__('Print My Blog – Quick Print', 'print-my-blog'),
            esc_html__('Quick Print', 'print-my-blog'),
            PMB_ADMIN_CAP,
            PMB_ADMIN_PAGE_SLUG,
            array($this, 'renderAdminPage')
        );
        add_submenu_page(
            PMB_ADMIN_PROJECTS_PAGE_SLUG,
            esc_html__('Print My Blog Settings', 'print-my-blog'),
            esc_html__('Settings', 'print-my-blog'),
            'manage_options',
            PMB_ADMIN_SETTINGS_PAGE_SLUG,
            array($this, 'settingsPage')
        );
        add_submenu_page(
            PMB_ADMIN_PROJECTS_PAGE_SLUG,
            __('Help Me Print My Blog', 'print-my-blog'),
            __('Help', 'print-my-blog'),
            PMB_ADMIN_CAP,
            PMB_ADMIN_HELP_PAGE_SLUG,
            [$this, 'helpPage']
        );
    }

    /**
     * Adds help tab on PMB pages.
     */
    public function addHelpTab()
    {
        $screen = get_current_screen();
        // Don't worry, we're not doing anything with this request input besides checking it for key values.
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        if (
            isset($_GET['page'], $_GET['action'], $_GET['subaction'])
            && $_GET['page'] === PMB_ADMIN_PROJECTS_PAGE_SLUG
            && $_GET['action'] === self::SLUG_ACTION_EDIT_PROJECT
            && $_GET['subaction'] === self::SLUG_SUBACTION_PROJECT_CONTENT
        ) {
            //phpcs:enable WordPress.Security.NonceVerification.Recommended
            $screen->add_help_tab(
                array(
                    'id' => 'my_help_tab',
                    'title' => __('Keyboard Accessibility', 'print-my-blog'),
                    'content' => pmb_get_contents(PMB_TEMPLATES_DIR . 'project_edit_content_help_tab.php'),
                )
            );
        }
    }

    /**
     * Hacks WP menu so the links to the PMB contents CPT appear underneath the Print My Blog top-level menu.
     */
    protected function hackSubmenuContentIntoRightSpot()
    {
        global $submenu;

        if (array_key_exists(PMB_ADMIN_PROJECTS_PAGE_SLUG, $submenu)) {
            foreach ($submenu[PMB_ADMIN_PROJECTS_PAGE_SLUG] as $key => $value) {
                $k = array_search('edit.php?post_type=pmb_content', $value, true);
                if ($k) {
                    unset($submenu[PMB_ADMIN_PROJECTS_PAGE_SLUG][$key]);
                    // Sorry, this is the only way to rearrange menu items how I want them.
                    // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                    $submenu[PMB_ADMIN_PROJECTS_PAGE_SLUG][] = $value;
                }
            }
        }
    }

    /**
     * Legacy settings page.
     */
    public function settingsPage()
    {
        $settings = Context::instance()->reuse('PrintMyBlog\domain\FrontendPrintSettings');
        if (Array2::setOr($_SERVER, 'REQUEST_METHOD', '') === 'POST') {
            check_admin_referer('pmb-settings');
            // Ok save those settings!
            if (isset($_POST['pmb-reset'])) {
                $settings = Context::instance()->useNew('PrintMyBlog\domain\FrontendPrintSettings', [null, false]);
            } else {
                $settings->setShowButtons(isset($_POST['pmb_show_buttons']));
                $settings->setShowButtonsPages(isset($_POST['pmb_show_buttons_pages']));
                $settings->setPlaceAbove(Array2::setOr($_POST, 'pmb_place_above', 1));
                foreach ($settings->formatSlugs() as $slug) {
                    if (isset($_POST['pmb_format'][$slug])) {
                        $active = true;
                    } else {
                        $active = false;
                    }
                    $settings->setFormatActive($slug, $active);
                    if (isset($_POST['pmb_frontend_labels'][$slug])) {
                        // Sanitization happens inside FrontendPrintSettings::setFormatFrontendLabel()
                        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                        $settings->setFormatFrontendLabel($slug, wp_unslash($_POST['pmb_frontend_labels'][$slug]));
                    }
                    if (isset($_POST['pmb_print_options'][$slug])) {
                        // Sanitization happens inside FrontendPrintSettings::setPrintOptions(), which is pretty involved.
                        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                        $settings->setPrintOptions($slug, wp_unslash($_POST['pmb_print_options'][$slug]));
                    }
                }
            }
            $settings->save();
            update_option(self::SETTINGS_SAVED_OPTION, true, false);
            wp_safe_redirect('');
        }
        $saved = get_option(self::SETTINGS_SAVED_OPTION, false);
        if ($saved) {
            delete_option(self::SETTINGS_SAVED_OPTION);
            $posts = get_posts(
                array(
                    'orderby' => 'desc',
                    'posts_per_page' => '1',
                )
            );
            $text = esc_html__('Settings Saved!', 'print-my-blog');
            if ($posts) {
                $a_post = reset($posts);
                $permalink = get_permalink($a_post);
                $text .= ' ' . sprintf(
                        // translators: 1: opening anchor tag, 2: closing anchor tag
                    esc_html__('You should see the changes on your %1$slatest post%2$s.', 'print-my-blog'),
                    '<a href="' . $permalink . '" target="_blank">',
                    '</a>'
                );
            }
            // Output prepared just a couple lines ago, there's no user input in it.
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '<div class="notice notice-success is-dismissible"><p>' . $text . '</p></div>';
        }
        $print_options = new PrintOptions();
        $displayer = new FormInputs();
        include PMB_TEMPLATES_DIR . 'settings_page.php';
    }

    /**
     * For sending help info to the dev.
     */
    public function helpPage()
    {

        if ($this->invalid_form instanceof FormSection) {
            $form = $this->invalid_form;
            $form_url = '';
            $method = 'GET';
            $button_text = '';
        } else {
            $form = $this->getEmailHelpForm();
            $form_url = admin_url(PMB_ADMIN_HELP_PAGE_PATH);
            $method = 'POST';
            $button_text = esc_html__('Email Print My Blog Support', 'print-my-blog');
        }
        pmb_render_template(
            'help.php',
            [
                'form' => $form,
                'form_url' => $form_url,
                'form_method' => $method,
                'button_text' => $button_text,
            ]
        );
    }

    /**
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    public function sendHelp()
    {
        global $current_user;
        $form = $this->getEmailHelpForm();
        // Nonces verified by form class.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $form->receiveFormSubmission($_REQUEST);
        if (! $form->isValid()) {
            $this->invalid_form = $form;
            return;
        }
        // don't translate these strings. They're sent to the dev who speaks English.
        add_action(
            'wp_mail_failed',
            [$this, 'sendHelpError'],
            10
        );

        $headers = array(
            'Reply-To: ' . $current_user->display_name . ' <' . $current_user->user_email . '>',
        );
        $subject = sprintf('Help for %s', site_url());
        $message = sprintf(
            'Name:%1$s
            <br>
            Message:%2$s
            <br>
            Consent:%3$s,
            Data:%4$s',
            $form->getInputValue('name'),
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
                // translators: 1: error message, 2: email address, 3: subject of email, 4: content of email.
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

    /**
     * Callback for recording error when sending email.
     * @param WP_Error $error
     */
    public function sendHelpError(WP_Error $error)
    {
        $this->wp_error = $error;
    }

    /**
     * @return FormSection
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    protected function getEmailHelpForm()
    {
        global $current_user;

        // reminder: Twine forms default to always add and check nonces.
        return new FormSection(
            [
                'subsections' => [
                    'reason' => new TextAreaInput(
                        [
                            'html_label_text' => __('Please explain what you did, what you expected, and what went wrong', 'print-my-blog'),
                            'required' => true,
                            'html_help_text' => __('Including links to screenshots is appreciated', 'print-my-blog'),
                        ]
                    ),
                    'name' => new TextInput(
                        [
                            'html_label_text' => __('Your Name', 'print-my-blog'),
                            'default' => $current_user->user_firstname
                                ? $current_user->user_firstname . ' ' . $current_user->user_lastname
                                : $current_user->display_name,
                        ]
                    ),
                    'consent' => new YesNoInput(
                        [
                            'html_label_text' => __('Are you ok with us viewing your most recent generated documents?', 'print-my-blog'),
                            'default' => true,
                            'html_help_text' => __('Viewing your most recent generated documents saves a lot of time figuring out what is going wrong. We won’t share your content with anyone else.', 'print-my-blog'),
                        ]
                    ),
                    'debug_info' => new TextAreaInput(
                        [
                            'html_label_text' => __('This debug info will also be sent.', 'print-my-blog'),
                            'disabled' => true,
                            'default' => $this->debug_info->getDebugInfoString(),
                            'html_help_text' => __('This is mostly system information, list of active plugins, active theme, and some Print My Blog Pro info like your most recent projects.', 'print-my-blog'),
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Now-unused method for getting a form to submit info to GitHub. Might add it back some day.
     * @return FormSection
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    protected function getGithubHelpForm()
    {
        return new FormSection(
            [
                'subsections' => [
                    'explanatory_text' => new FormSectionHtml(
                        '<h2>' . __('Support for your plan is offered on GitHub', 'print-my-blog') . '</h2>' .
                        '<p>' . __('GitHub is a public forum to share your issues with the developer and other users.', 'print-my-blog') . '</p>' .
                        '<p>' . sprintf(
                        // translators: 1: opening anchor tag, 2: closing anchor tag, 3: opening anchor tag.
                            __('You will need to first %1$screate a GitHub account%2$s. If you prefer to use email support please %3$spurchase a license that offers email support.%2$s', 'print-my-blog'),
                            '<a target="_blank" href="https://github.com/signup">',
                            '</a>',
                            '<a href="' . esc_url(pmb_fs()->get_upgrade_url()) . '">'
                        )
                        . '</p>'
                    ),
                    'body' => new HiddenInput(
                        [
                            'default' => '** Please describe what you were doing, what you expected to happen, and what the problem was. **
                                 

```
' . substr($this->debug_info->getDebugInfoString(false), 0, 5000) . '
```',
                            'html_name' => 'body',
                        ]
                    ),
                ],
            ]
        );
    }


    /**
     * Shows the setup page.
     * @since 1.0.0
     */
    public function renderAdminPage()
    {
        // Nonce overkill on these pages, no form is being submitted.
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['welcome'])) {
            include PMB_TEMPLATES_DIR . 'welcome.php';
        } elseif (isset($_GET['upgrade_to_3'])) {
            include PMB_TEMPLATES_DIR . 'upgrade_to_3.php';
        } else {
            $print_options = new PrintOptions();
            $displayer = new FormInputs();
            include PMB_TEMPLATES_DIR . 'setup_page.php';
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended
    }

    /**
     * Adds links to PMB stuff on the plugins page.
     * @param array $links
     * @since 1.0.0
     */
    public function pluginPageLinks($links)
    {
        $links = array_merge(
            $links,
            [
                '<a href="'
                . wp_nonce_url(
                    add_query_arg(
                        [
                            'action' => self::SLUG_ACTION_UNINSTALL,
                        ],
                        admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
                    ),
                    self::SLUG_ACTION_UNINSTALL
                )
                . '" id="pmb-uninstall" class="pmb-uninstall">'
                . esc_html__('Delete All Data', 'print-my-blog')
                . '</a>',
            ]
        );

        return $links;
    }

    /**
     * Enqueus scripts for any admin pages.
     * @param string $hook
     */
    public function enqueueScripts($hook)
    {
        wp_enqueue_script('pmb_general');
        wp_enqueue_style(
            'pmb_admin',
            PMB_STYLES_URL . 'pmb-admin.css',
            [],
            filemtime(PMB_STYLES_DIR . 'pmb-admin.css')
        );
        if (pmb_fs()->is_plan__premium_only('hobbyist')) {
            // Paid users don't need to be reminded what's pro and what's not
            wp_add_inline_style(
                'pmb_admin',
                '.pmb-pro-only, .pmb-pro-best{display:none;}'
            );
        }
        // Nonce overkill for just checking which page they're on.
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['welcome']) || isset($_GET['upgrade_to_3'])) {
            wp_enqueue_style(
                'pmb_welcome',
                PMB_ASSETS_URL . 'styles/welcome.css',
                array(),
                filemtime(PMB_ASSETS_DIR . 'styles/welcome.css')
            );
            // don't let admin notices ruin the welcoming moment
            remove_all_actions('admin_notices');
        } elseif (isset($_GET['page']) && $_GET['page'] === 'print-my-blog-now') {
            wp_enqueue_script('pmb-setup-page');
            wp_enqueue_style('pmb-setup-page');
        } elseif (
            isset($_GET['page']) && $_GET['page'] === 'print-my-blog-projects'
        ) {
            if (
                isset($_GET['action'])
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
                            array('sortablejs', 'jquery-ui-datepicker', 'jquery-ui-dialog', 'pmb-select2', 'wp-api', 'jquery-debounce'),
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
                                'translations' => [
                                    'cant_add' => __('Please select items to add to the project', 'print-my-blog'),
                                    'cant_remove' => __('Please select items to remove', 'print-my-blog'),
                                    'cant_move' => __('Please select items to move', 'print-my-blog'),
                                    'insert_error' => __('Error inserting. Please use the PMB help page to get help', 'print-my-blog'),
                                    'duplicate_error' => __('Error creating new print material. Please use the PMB help page to get help.', 'print-my-blog'),
                                ],
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
                                'wp-jquery-ui-dialog',
                            ],
                            filemtime(PMB_STYLES_DIR . 'pmb-generate.css')
                        );
                        $license = pmb_fs()->_get_license();
                        $site = pmb_fs()->get_site();
                        $use_pmb_central = 0;
                        if (pmb_fs()->is_plan__premium_only('business') || (defined('PMB_USE_CENTRAL') && PMB_USE_CENTRAL)) {
                            $use_pmb_central = 1;
                        }
                        wp_localize_script(
                            'pmb-generate',
                            'pmb_generate',
                            [
                                'generate_ajax_data' => apply_filters(
                                    '\PrintMyBlog\controllers\Admin->enqueueScripts generate generate_ajax_data',
                                    [
                                        'action' => Frontend::PMB_PROJECT_STATUS_ACTION,
                                        'ID' => $this->project->getWpPost()->ID,
                                        '_nonce' => wp_create_nonce('pmb-project-edit'),
                                    ],
                                    $this->project
                                ),
                                'pmb_ajax' => add_query_arg(
                                    [
                                        Frontend::PMB_AJAX_INDICATOR => 1,
                                    ],
                                    site_url()
                                ),
                                'site_url' => site_url(),
                                'use_pmb_central_for_previews' => $use_pmb_central,
                                'license_data' => [
                                    'endpoint' => $this->pmb_central->getCentralUrl(),
                                    'license_id' => $license instanceof FS_Plugin_License ? $license->id : '',
                                    'install_id' => $site instanceof FS_Site ? $site->id : '',
                                    'authorization_header' => $site instanceof FS_Site ? $this->pmb_central->getSiteAuthorizationHeader() : '',
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
                                        ],
                                    ]
                                ),
                                'translations' => [
                                    'error_generating' => __('There was an error preparing your content. Please visit the Print My Blog Help page.', 'print-my-blog'),
                                    'socket_error' => __('Your project could not be accessed in order to generate the file. Maybe your website is not public? Please visit the Print My Blog Help page.', 'print-my-blog'),
                                ],
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
            } else {
                // projects list table
                wp_enqueue_script(
                    'pmb-projects-list',       // handle
                    PMB_SCRIPTS_URL . 'pmb-projects-list.js',       // source
                    array('jquery'),
                    filemtime(PMB_SCRIPTS_DIR . 'pmb-projects-list.js')
                );
                wp_localize_script(
                    'pmb-projects-list',
                    'pmb_data',
                    [
                        'translations' => [
                            'confirm_duplicate' => __('Are you sure you want to duplicate this project?', 'print-my-blog'),
                        ],
                    ]
                );
            }
        } elseif ($hook === 'plugins.php') {
            wp_enqueue_script(
                'pmb-plugins-page',
                PMB_SCRIPTS_URL . 'pmb-plugins-page.js',
                [],
                filemtime(PMB_SCRIPTS_DIR . 'pmb-plugins-page.js')
            );
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended
    }

    /**
     * Displays a page.
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    public function renderProjects()
    {
        // Nonce overkill for just checking which page they're on.
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : null;
        if ($action === self::SLUG_ACTION_ADD_NEW) {
            $this->editSetup();
        } elseif ($action === self::SLUG_ACTION_EDIT_PROJECT) {
            $subaction = isset($_GET['subaction']) ? sanitize_key($_GET['subaction']) : null;
            try {
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
            } catch (DesignTemplateDoesNotExist $e) {
                $this->notification_manager->addTextNotificationForCurrentUser(
                    OneTimeNotification::TYPE_ERROR,
                    $e->getMessage()
                );
                $this->notification_manager->showOneTimeNotifications();
                foreach ($this->file_format_registry->getFormats() as $format) {
                    $this->project->setDesignFor($format->slug(), null);
                }
                $this->project->getProgress()->initialize();
                $this->project->getProgress()->markStepComplete(ProjectProgress::SETUP_STEP);
                $this->editSetup();
            }
        } else {
            if (! class_exists('WP_List_Table')) {
                require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
            }
            $table = new ProjectsListTable();
            $add_new_url = add_query_arg(
                [
                    'action' => self::SLUG_ACTION_ADD_NEW,
                ],
                admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
            );
            include PMB_TEMPLATES_DIR . 'projects_list_table.php';
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended
    }

    /**
     * Shows the design-choosing step.
     */
    protected function editChooseDesign()
    {
        // determine the format
        // Nonce overkill for just checking which page they're on.
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $format = $this->file_format_registry->getFormat(isset($_GET['format']) ? sanitize_key($_GET['format']) : null);
        // get all the designs for this format
        // including which format is actually in-use
        $wp_query_args = [
            // Sorry, I'm storing the design on a metakey. (Ya maybe we could store them on a custom table too).
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
            'meta_query' => [
                [
                    'key' => Design::META_PREFIX . 'format',
                    'value' => $format->slug(),
                ],
            ],
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
                'chosen_design' => $chosen_design,
            ]
        );
    }

    /**
     * Project setup page.
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
                'project' => $this->project,
            ]
        );
    }

    /**
     * @throws Exception
     */
    protected function editCustomizeDesign()
    {
        $format_slug = Array2::setOr($_GET, 'format', '');
        $design = $this->project->getDesignFor($format_slug);
        if (! $design instanceof Design) {
            throw new Exception(
                sprintf(
                    'Could not determine the design for project "%s" for format "%s"',
                    $this->project->getWpPost()->ID,
                    $format_slug
                )
            );
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
                'format' => $format_slug,
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
                'project' => $this->project,
            ]
        );
    }

    /**
     * Page for editinga a project's content.
     */
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
                'subaction' => self::SLUG_SUBACTION_PROJECT_CONTENT,
            ],
            admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
        );
        $user_query_args = [
            'number' => 100,
        ];

        // Capability queries were only introduced in WP 5.9.
        if (version_compare($GLOBALS['wp_version'], '5.9', '<')) {
            $user_query_args['who'] = 'authors';
        } else {
            $user_query_args['capability'] = ['edit_posts'];
        }

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
                'authors' => get_users($user_query_args),
            ]
        );
    }

    /**
     * Page for editing a project's metadata.
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
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
                'subaction' => self::SLUG_SUBACTION_PROJECT_META,
            ],
            admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
        );
        $this->renderProjectTemplate(
            'project_edit_metadata.php',
            [
                'form_url' => $form_url,
                'form' => $form,
                'project' => $this->project,
            ]
        );
    }

    /**
     * Page for generating a project's files.
     */
    protected function editGenerate()
    {
        // check the design templates still exist
        foreach ($this->project->getDesigns() as $design) {
            $design->getDesignTemplate();
        }
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
                        // translators: %s error message.
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
                'review_url' => wp_nonce_url(
                    add_query_arg(
                        [
                            'action' => self::SLUG_ACTION_REVIEW,
                        ],
                        admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
                    ),
                    self::SLUG_ACTION_REVIEW
                ),
                'suggest_review' => ! get_option(self::REVIEW_OPTION_NAME, false),
            ]
        );
    }

    /**
     * @param string $template_name
     * @param array $args
     */
    protected function renderProjectTemplate($template_name, $args)
    {

        if ($args['project'] instanceof Project) {
            $args['steps_to_urls'] = $this->mapStepToUrls($args['project']);
            $args['current_step'] = $args['project']->getProgress()->mapSubactionToStep(
                isset($_GET['subaction']) ? sanitize_key(wp_unslash($_GET['subaction'])) : null,
                isset($_GET['format']) ? sanitize_key(wp_unslash($_GET['format'])) : null
            );
        } else {
            $args['steps_to_urls'] = [];
            $args['current_step'] = ProjectProgress::SETUP_STEP;
        }
        // Yes we're rendering an HTML file. Escaping happens in that file.
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
            'action' => self::SLUG_ACTION_EDIT_PROJECT,
        ];
        $mappings = [];
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
            $action = isset($_REQUEST['action']) ? sanitize_key($_REQUEST['action']) : null;
            if ($action === self::SLUG_ACTION_ADD_NEW) {
                $this->saveNewProject();
                exit;
            }
            if ($action === self::SLUG_ACTION_EDIT_PROJECT) {
                $subaction = isset($_GET['subaction']) ? sanitize_key($_GET['subaction']) : null;
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
     *
     * @return FormSection
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    protected function getSetupForm()
    {
        $formats = $this->file_format_registry->getFormats();
        $format_options = [];
        foreach ($formats as $format) {
            $option_text = $format->coloredTitleAndIcon();
            $format_options[$format->slug()] = new InputOption(
                $option_text,
                $format->desc(),
                $format->supported()
            );
        }
        $default_formats = [DefaultFileFormats::DIGITAL_PDF];
        if ($this->project instanceof Project) {
            $default_formats = array_keys($this->project->getFormatsSelected());
        }
        return apply_filters(
            '\PrintMyBlog\controllers\Admin::getSetupForm',
            new FormSection(
                [
                    'name' => 'pmb-project',
                    'subsections' => [
                        'title' => new TextInput(
                            [
                                'html_label_text' => __('Project Title', 'print-my-blog'),
                                'required' => true,
                                'default' => $this->project instanceof Project ? $this->project->getWpPost()->post_title : '',
                                'other_html_attributes' => [
                                    'autofocus',
                                ],
                            ]
                        ),
                        'formats' => new CheckboxMultiInput(
                            $format_options,
                            [
                                'html_label_text' => __('Format', 'print-my-blog'),
                                'required' => true,
                                'default' => $default_formats,
                            ]
                        ),
                    ],
                ]
            ),
            $this->project
        );
    }

    /**
     * Save's a new project after the setup step.
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
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
                    'post_status' => 'publish',
                ],
                true
            );
            if (is_wp_error($project_id)) {
                wp_die(esc_html($project_id->get_error_message()));
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
                        [],
                    ],
                    [
                        $toc_page->ID,
                        '',
                        0,
                        1,
                        [],
                    ],
                ],
                DesignTemplate::IMPLIED_DIVISION_FRONT_MATTER
            );
            $initialize_steps = true;
        }
        $this->project->setTitle($form->getInputValue('title'));
        $formats_to_save = $form->getInputValue('formats');
        $old_formats = $this->project->getFormatSlugsSelected();
        $this->project->setFormatsSelected($formats_to_save);
        $this->notification_manager->addTextNotificationForCurrentUser(
            OneTimeNotification::TYPE_SUCCESS,
            sprintf(
            // translators: %s project name.
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
                    // translators: %s format name
                        __('You need to choose and customize the design for your %s.', 'print-my-blog'),
                        $this->file_format_registry->getFormat($new_format)->title()
                    )
                );
            }
        }
        $this->project->getProgress()->markStepComplete(ProjectProgress::SETUP_STEP);
        do_action('\PrintMyBlog\controllers\Admin->saveNewProject', $this->project, $form);
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
            'action' => self::SLUG_ACTION_EDIT_PROJECT,
        ];
        $next_step = $project->getProgress()->getNextStep();
        $args = array_merge($args, $project->getProgress()->mapStepToSubactionArgs($next_step));
        // Redirect to it
        wp_safe_redirect(
            add_query_arg(
                $args,
                admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
            )
        );
        exit;
    }

    /**
     * Saves the project's content (sections, parts, etc).
     */
    protected function saveProjectContent()
    {
        if (! check_admin_referer('pmb-project-edit')) {
            wp_die('The request has expired. Please refresh the previous page and try again.');
        }

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
        wp_update_post(
            [
                'ID' => $this->project->getWpPost()->ID,
            ]
        );
    }

    /**
     * @param Project $project
     * @param array $request_data
     * @param string $placement
     * @param int $order
     */
    protected function setSectionFromRequest(Project $project, $request_data, $placement, &$order = 1)
    {
        // nonce verified before calling this method.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $section_data = stripslashes(Array2::setOr($_POST, $request_data, ''));
        $sections = json_decode($section_data);
        if (is_array($sections)) {
            $this->section_manager->setSectionsFor(
                $project->getWpPost()->ID,
                $sections,
                $placement,
                $order
            );
        }
    }

    /**
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    protected function saveProjectCustomizeDesign()
    {
        $this->updateProjectModifiedDate();
        $design = $this->project->getDesignFor(Array2::setOr($_GET, 'format', ''));
        $design_form = $design->getDesignTemplate()->getDesignFormTemplate();
        // Nonce verified by form class.
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
            // translators: %s: design name
                __('The design "%s" has been customized, and its changes will be reflected in all projects that use it.', 'print-my-blog'),
                $design->getWpPost()->post_title
            )
        );
        /**
         * Hook for doing more processing after a design is customized
         * @param Project $project
         * @param ProjectGeneration $project_generation
         * @param Design $design
         * @param FormSection $design_form
         */
        do_action('PrintMyBlog\controllers\Admin->saveProjectCustomizeDesign done', $this->project, $project_generation, $design, $design_form);
        $this->redirectToNextStep($this->project);
    }

    /**
     * @throws Exception
     */
    protected function saveProjectChooseDesign()
    {
        $this->updateProjectModifiedDate();
        $design = $this->design_manager->getById((int)Array2::setOr($_REQUEST, 'design', ''));
        $format = $this->file_format_registry->getFormat(Array2::setOr($_GET, 'format', ''));
        if (! $design instanceof Design || ! $format instanceof FileFormat) {
            throw new Exception(
                sprintf(
                // translators: 1: design slug, 2: format slug
                    __('An invalid design (%1$s) or format provided(%2$s)', 'print-my-blog'),
                    sanitize_key(Array2::setOr($_GET, 'design', '')),
                    sanitize_key(Array2::setOr($_GET, 'format', ''))
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
            // translators: 1: design name, 2: format name.
                __('You chose the design "%1$s" for the %2$s of your project.', 'print-my-blog'),
                $design->getWpPost()->post_title,
                $format->title()
            )
        );
        $this->project->getProgress()->markChooseDesignStepComplete($format->slug());
        $button_pressed = Array2::setOr($_REQUEST, 'submit-button', 'customize');
        if ($button_pressed === 'choose') {
            // skip customizing
            $this->project->getProgress()->markCustomizeDesignStepComplete($format->slug());
        } else {
            // make sure they customize the design (especially if its a new choice)
            $this->project->getProgress()->markCustomizeDesignStepComplete($format->slug(), false);
        }

        $this->redirectToNextStep($this->project);
    }

    /**
     * Gets the project form, which is a combination of the project forms for all the designs in use.
     *
     * @return void
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
        /**
         * Hook for doing more processing when project metadata is saved.
         * @param Project $project
         * @param ProjectGeneration[] $project_generations
         * @param FormSectionBase $form
         */
        do_action('PrintMyBlog\controllers\Admin->saveProjectMetadata done', $this->project, $project_generations, $form);
        $this->redirectToNextStep($this->project);
    }

    /**
     * Currently unused, but probably will be once we support skipping re-generating etc.
     *
     * @throws Exception
     */
    protected function saveProjectGenerate()
    {
        $this->updateProjectModifiedDate();
        $format = $this->file_format_registry->getFormat(Array2::setOr($_GET, 'format', ''));
        if (! $format instanceof FileFormat) {
            throw new Exception(
                sprintf(
                // translators: %s: format slug
                    __('There is no file format with the slug "%s"', 'print-my-blog'),
                    sanitize_key(Array2::setOr($_GET, 'format', ''))
                )
            );
        }
        $project_generation = $this->project->getGenerationFor($format);
        $project_generation->deleteGeneratedFiles();
        $project_generation->clearDirty();
        $this->project->getProgress()->markStepComplete(ProjectProgress::GENERATE_STEP);
        $url = add_query_arg(
            [
                PMB_PRINTPAGE_SLUG => 3,
                'project' => $this->project->getWpPost()->ID,
                'format' => $format->slug(),
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
            isset($pagenow) && $pagenow === 'post-new.php'
            && isset($_GET['post_type']) && $_GET['post_type'] === CustomPostTypes::CONTENT
        ) {
            add_action('admin_print_footer_scripts', [$this, 'makePrintContentsSaySavedGutenberg']);
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

    /**
     * Deletes selected projects from list page.
     */
    protected function deleteProjects()
    {
        // In our file that handles the request, verify the nonce.
        $nonce = esc_attr(Array2::setOr($_REQUEST, '_wpnonce', ''));
        if (! wp_verify_nonce($nonce, 'bulk-projects')) {
            wp_die('The request has expired. Please refresh the previous page and try again.');
        } else {
            $this->project_manager->deleteProjects(Array2::setOr($_POST, 'ID', ''));
        }
    }

    /**
     * Duplicates a post as a print material.
     */
    protected function duplicate()
    {
        if (! check_admin_referer(self::SLUG_ACTION_EDIT_PROJECT)) {
            wp_die('The request has expired. Please refresh the previous page and try again.');
        }
        $new_project = $this->project->duplicate();
        $this->notification_manager->addTextNotificationForCurrentUser(
            OneTimeNotification::TYPE_SUCCESS,
            sprintf(
            // translators: 1: the name of the new project.
                __('Project successfully duplicated. It is titled "%1$s".', 'print-my-blog'),
                $new_project->getWpPost()->post_title
            )
        );
    }

    /**
     * Clears the external file cache (in case those files are stale).
     */
    protected function clearCachedExternalResources()
    {
        if (! check_admin_referer(self::SLUG_ACTION_EDIT_PROJECT)) {
            wp_die('The request has expired. Please refresh the previous page and try again.');
        }
        $this->external_resource_cache->clear();
    }

    /**
     * Duplicates a post (any type) to be a print material and redirects to it.
     */
    protected function duplicatePrintMaterial()
    {
        if (! check_admin_referer(self::SLUG_ACTION_DUPLICATE_PRINT_MATERIAL)) {
            wp_die('The request has expired. Please refresh the previous page and try again.');
        }
        $post_id = Array2::setOr($_GET, 'ID', 0);
        $wrapped_post = $this->post_manager->getById($post_id);
        $new_post = $wrapped_post->duplicateAsPrintMaterial();
        wp_safe_redirect(
            get_edit_post_link($new_post->ID, 'not_display')
        );
        exit;
    }

    /**
     * Deletes plugin data. No security checks here.
     */
    protected function uninstall()
    {
        // clear custom table
        $this->table_manager->dropTables();

        // clear CPTs
        $deleted = $this->post_fetcher->deleteCustomPostTypes();

        // clear options
        global $wpdb;
        // Direct DB query way more efficient.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query('DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "pmb_%"');

        $upload_dir_info = wp_upload_dir();
        $folder = new Folder($upload_dir_info['basedir'] . '/pmb');
        $folder->delete();
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
                        // translators: 1: error message.
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
        if (Array2::setOr($_SERVER, 'REQUEST_METHOD', '') === 'POST') {
            add_action('admin_init', [$this, 'checkFormSubmission']);
        } elseif (Array2::setOr($_SERVER, 'REQUEST_METHOD', '') === 'GET') {
            add_action('admin_init', [$this, 'checkSpecialLinks']);
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
            $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : null;
            if ($action === self::SLUG_ACTION_UNINSTALL) {
                if (! check_admin_referer(self::SLUG_ACTION_UNINSTALL)) {
                    wp_die('The request has expired. Please refresh the previous page and try again.');
                }
                if (current_user_can('activate_plugins')) {
                    $this->uninstall();
                    if (! function_exists('deactivate_plugins')) {
                        require_once ABSPATH . 'wp-admin/includes/plugin.php';
                    }
                    deactivate_plugins(PMB_BASENAME, true);
                }
                wp_safe_redirect(admin_url('plugins.php'));
            } elseif ($action === self::SLUG_ACTION_REVIEW) {
                check_admin_referer(self::SLUG_ACTION_REVIEW);
                update_option(self::REVIEW_OPTION_NAME, true);
                // We're redicting to hard-coded URL. It's ok.
                // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
                wp_redirect(
                    'https://wordpress.org/support/plugin/print-my-blog/reviews/#new-post'
                );
                exit;
            } elseif ($action === self::SLUG_ACTION_EDIT_PROJECT) {
                $subsection = Array2::setOr($_GET, 'subaction', null);
                if ($subsection === self::SLUG_SUBACTION_PROJECT_DUPLICATE) {
                    $this->duplicate();
                    $redirect = admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH);
                    wp_safe_redirect($redirect);
                    exit;
                }
                if ($subsection === self::SLUG_SUBACTION_PROJECT_CLEAR_CACHE) {
                    $this->clearCachedExternalResources();
                    $this->notification_manager->addTextNotificationForCurrentUser(
                        OneTimeNotification::TYPE_SUCCESS,
                        __('Cached external resources and images were cleared.', 'print-my-blog')
                    );
                    $redirect = add_query_arg(
                        [
                            'action' => self::SLUG_ACTION_EDIT_PROJECT,
                            'subaction' => self::SLUG_SUBACTION_PROJECT_GENERATE,
                            'ID' => $this->project->getWpPost()->ID,
                        ],
                        admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
                    );
                    wp_safe_redirect($redirect);
                    exit;
                }
            } elseif ($action === self::SLUG_ACTION_DUPLICATE_PRINT_MATERIAL) {
                $this->duplicatePrintMaterial();
                exit;
            }
        }
    }

    /**
     * Checks if it's a project editing page, in which case sets the project.
     */
    public function checkProjectEditPage()
    {
        if (! isset($_GET['page'])) {
            return;
        }
        if (
            $_GET['page'] === PMB_ADMIN_PROJECTS_PAGE_SLUG &&
            isset($_GET['action']) &&
            $_GET['action'] === self::SLUG_ACTION_EDIT_PROJECT
        ) {
            $project = $this->project_manager->getById(isset($_GET['ID']) ? (int)$_GET['ID'] : null);
            if (! $project) {
                wp_safe_redirect(admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH));
                exit;
            }
            $this->project = $project;
        }
    }

    /**
     * @param array $actions
     * @param WP_Post $post
     */
    public function postAdminRowActions($actions, $post)
    {
        if (! $post instanceof WP_Post || ! current_user_can('publish_' . CustomPostTypes::CONTENTS) || ! is_array($actions)) {
            return $actions;
        }
        $html = $this->getDuplicateAsPrintMaterialHtml();
        if ($html) {
            $actions['pmb_new_print_material'] = $html;
        }

        return $actions;
    }

    /**
     * Adds a button to duplicate the post inside the publish metabox.
     */
    public function addDuplicateAsPrintMaterialToClassicEditor()
    {
        ?>
        <div class="pmb-duplicate-button-area">
            <?php
            // HTML prepared by the called method.
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $this->getDuplicateAsPrintMaterialHtml('button button-secondary');
            ?>
        </div>
        <?php
    }

    /**
     * Get the HTML for link to either duplicate as a print material, or edit the related print material
     * that was already created, or edit the original
     * @param string $css_class to add to the link
     * @return string
     */
    protected function getDuplicateAsPrintMaterialHtml($css_class = '')
    {
        global $post;
        if ($css_class) {
            $css_attr = ' class="' . esc_attr($css_class) . '" ';
        } else {
            $css_attr = '';
        }
        if ($post->post_type === CustomPostTypes::CONTENT) {
            $original_id = get_post_meta($post->ID, '_pmb_original_post', true);
            $post_type = get_post_type_object(get_post_type($original_id));
            $type_label = '';
            if ($post_type instanceof WP_Post_Type && isset($post_type->labels, $post_type->labels->singular_name)) {
                $type_label = $post_type->labels->singular_name;
            }
            if ($original_id) {
                $html = '<a href="' . esc_url(get_edit_post_link($original_id)) . '" title="'
                    // translators: 1 post type label, 2: post title
                    . esc_attr(sprintf(__('Go to the %1$s "%2$s" was copied from.', 'print-my-blog'), $type_label, $post->post_title))
                    . '"'
                    . '>' .
                    // translators: %s: type label.
                    sprintf(esc_html__('Go to Original %s', 'print-my-blog'), $type_label)
                    . '</a>';
            } else {
                $html = '';
            }
        } else {
            $print_material = null;
            $print_materials = $this->post_manager->getByPostMeta('_pmb_original_post', (string)$post->ID, 1);
            if ($print_materials) {
                $print_material = reset($print_materials);
            }
            if ($print_material) {
                $html = '<a href="' . esc_url(get_edit_post_link($print_material->getWpPost()->ID)) . '" title="'
                    // translators: 1: post title
                    . esc_attr(sprintf(__('Go to Print Material "%s" was created from.', 'print-my-blog'), $print_material->getWpPost()->post_title))
                    . '"'
                    . '>' .
                    esc_html__('Go to Print Material', 'print-my-blog')
                    . '</a>';
            } else {
                $html = '<a href="' . esc_url($this->getDuplicatePostAsPrintMaterialUrl($post)) . '" title="'
                    // translators: %s: post title
                    . esc_attr(sprintf(__('Copy "%s" to New Print Material for a Print My Blog project', 'print-my-blog'), $post->post_title))
                    . '"'
                    . $css_attr
                    . '>' .
                    esc_html__('Copy to Print Material', 'print-my-blog')
                    . '</a>';
            }
        }
        return $html;
    }

    /**
     * Gets the URL to the admin action which makes a duplicate print material of the given post.
     *
     * @param WP_Post $post
     * @return string
     */
    protected function getDuplicatePostAsPrintMaterialUrl($post)
    {
        $url = wp_nonce_url(
            add_query_arg(
                [
                    'action' => self::SLUG_ACTION_DUPLICATE_PRINT_MATERIAL,
                    'ID' => $post->ID,
                ],
                admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
            ),
            self::SLUG_ACTION_DUPLICATE_PRINT_MATERIAL
        );
        return $url;
    }

    /**
     * To add the duplicate print material button to the Gutenberg block editor, we need to add it via JS
     */
    public function addDuplicateAsPrintMaterialToGutenberg()
    {
        global $post;
        wp_enqueue_script(
            'pmb_blockeditor',
            PMB_SCRIPTS_URL . 'build/editor.js',
            array('wp-components', 'wp-edit-post', 'wp-element', 'wp-i18n', 'wp-plugins'),
            filemtime(PMB_SCRIPTS_DIR . 'build/editor.js')
        );
        wp_localize_script(
            'pmb_blockeditor',
            'pmbBlockEditor',
            [
                // add HTML entities because wp_localize_script calls html_entities_decode which messes up the
                // quotes inside the title attributes on the HTML elements
                'html' => htmlentities($this->getDuplicateAsPrintMaterialHtml('button button-secondary'), ENT_QUOTES, 'UTF-8'),
            ]
        );
    }
}
