<?php

namespace PrintMyBlog\controllers;

use Exception;
use PrintMyBlog\controllers\helpers\ProjectsListTable;
use PrintMyBlog\db\PostFetcher;
use PrintMyBlog\db\TableManager;
use PrintMyBlog\domain\FrontendPrintSettings;
use PrintMyBlog\domain\PrintOptions;
use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\entities\FileFormat;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\orm\managers\DesignManager;
use PrintMyBlog\orm\managers\ProjectManager;
use PrintMyBlog\orm\managers\ProjectSectionManager;
use PrintMyBlog\services\FileFormatRegistry;
use PrintMyBlog\system\CustomPostTypes;
use Twine\forms\base\FormSectionProper;
use Twine\services\display\FormInputs;
use Twine\controllers\BaseController;
use WP_Query;

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

	const SLUG_ACTION_EDIT_PROJECT = 'edit';
	const SLUG_SUBACTION_PROJECT_MAIN = 'main';
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
	 * @var FormSectionProper
	 */
    protected $invalid_form;
	/**
	 * @var TableManager
	 */
	protected $table_manager;

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
		TableManager $table_manager
    ){
        $this->post_fetcher    = $post_fetcher;
        $this->section_manager = $section_manager;
        $this->project_manager = $project_manager;
        $this->file_format_registry = $file_format_registry;
        $this->design_manager = $design_manager;
        $this->table_manager = $table_manager;
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
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            add_action('admin_init', [$this, 'checkFormSubmission']);
        }
        if(isset($_GET['action']) && $_GET['action'] === 'uninstall'){
	        $this->uninstall();
	        if (current_user_can('activate_plugins')) {
		        if (! function_exists('deactivate_plugins')) {
			        require_once ABSPATH . 'wp-admin/includes/plugin.php';
		        }
		        deactivate_plugins(PMB_BASENAME, true);
	        }
	        wp_safe_redirect(admin_url('plugins.php'));
        }
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
            'data:image/svg+xml;base64,' . base64_encode(file_get_contents(PMB_DIR . 'assets/images/menu-icon.svg'))
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
    protected function hackSubmenuContentIntoRightSpot(){
	    global $submenu;

	    if(array_key_exists(PMB_ADMIN_PROJECTS_PAGE_SLUG, $submenu)){

		    foreach($submenu[PMB_ADMIN_PROJECTS_PAGE_SLUG] as $key => $value){
			    $k = array_search('edit.php?post_type=pmb_content', $value);
			    if($k) {
				    unset( $submenu[ PMB_ADMIN_PROJECTS_PAGE_SLUG ][ $key ] );
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
        } elseif($hook === 'print-my-blog_page_print-my-blog-now'){
	        wp_enqueue_script('pmb-setup-page');
	        wp_enqueue_style('pmb-setup-page');
        }elseif ($hook === 'toplevel_page_print-my-blog-projects'
                 && isset($_GET['action'])
	            && $_GET['action'] === self::SLUG_ACTION_EDIT_PROJECT
            ) {
        	switch(isset($_GET['subaction']) ? $_GET['subaction'] : null){
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
				        array('sortablejs',),
				        filemtime(PMB_SCRIPTS_DIR . 'project-edit-content.js')
			        );
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
		        case self::SLUG_SUBACTION_PROJECT_MAIN:
		        default:
		        	wp_enqueue_script('pmb_project_edit_main',
			        PMB_SCRIPTS_URL . 'project-edit-main.js',
			        array('jquery', 'jquery-debounce'),
			        filemtime(PMB_SCRIPTS_DIR . 'project-edit-main.js')
			        );
		        	wp_localize_script(
		        		'pmb_project_edit_main',
				        'pmb_project_edit',
				        [
				        	'translations' => [
				        		'saved' => __('Saved', 'print-my-blog'),
						        'error' => __('Error saving. Check your internet connection and try modifying again.','print-my-blog')
					        ]
				        ]
			        );
	        }

	        // everybody uses the style, right?
            wp_enqueue_style(
                'pmb_project_edit',
                PMB_STYLES_URL . 'project-edit.css',
                array(),
                filemtime(PMB_STYLES_DIR . 'project-edit.css')
            );
        } elseif($hook === 'plugins.php'){
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
        if($action === self::SLUG_ACTION_EDIT_PROJECT) {
        	$subaction = isset($_GET['subaction']) ? $_GET['subaction'] : null;
	        $project = $this->project_manager->getById($_GET['ID']);
        	switch($subaction) {
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
		        case self::SLUG_SUBACTION_PROJECT_MAIN:
		        default:
			        $this->editMain($project);
	        }

        } else {
	        $table = new ProjectsListTable();
	        $add_new_url = add_query_arg(
		        [
			        'action' => 'new',
		        ],
		        admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
	        );
	        include(PMB_TEMPLATES_DIR . 'projects_list_table.php');
        }

    }

    protected function editChooseDesign(Project $project){
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
	    include(PMB_TEMPLATES_DIR . 'design_choose.php');
    }

	/**
	 * @param $action
	 */
    protected function editMain(Project $project){
		$form_url = add_query_arg(
			[
				'action' => 'pmb_save_project_main',
				'_nonce' => wp_create_nonce( 'pmb-project-edit' ),
				'ID' => $project->getWpPost()->ID
			],
	        admin_url('admin-ajax.php')
		);
	    $project_content_url = add_query_arg(
		    [
			    'ID' => $project->getWpPost()->ID,
			    'action' => self::SLUG_ACTION_EDIT_PROJECT,
			    'subaction' => self::SLUG_SUBACTION_PROJECT_CONTENT
		    ],
		    admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
	    );
	    $project_metadata_url = add_query_arg(
		    [
			    'ID' => $project->getWpPost()->ID,
			    'action' => self::SLUG_ACTION_EDIT_PROJECT,
			    'subaction' => self::SLUG_SUBACTION_PROJECT_META
		    ],
		    admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
	    );
	    $project_generate_url = add_query_arg(
		    [
			    'ID' => $project->getWpPost()->ID,
			    'action' => self::SLUG_ACTION_EDIT_PROJECT,
			    'subaction' => self::SLUG_SUBACTION_PROJECT_GENERATE
		    ],
		    admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
	    );
		$formats = $this->file_format_registry->getFormats();
		include(PMB_TEMPLATES_DIR . 'project_edit_main.php');
    }

    protected function editCustomizeDesign(Project $project){
    	$format_slug = $_GET['format'];
    	$design = $project->getDesignFor($format_slug);
    	if(! $design instanceof Design){
    		throw new Exception(sprintf(
    			'Could not determine the design for project "%s" for format "%s"',
		        $project->getWpPost()->ID,
		        $format_slug
		    ));
	    }
    	// If there was an invalid form submission, show it.
    	if( $this->invalid_form instanceof FormSectionProper){
    		$form = $this->invalid_form;
	    } else {
		    $form = $design->getDesignForm();
	    }

    	$form_url = add_query_arg(
		    [
			    'action' => self::SLUG_ACTION_EDIT_PROJECT,
			    'subaction' => self::SLUG_SUBACTION_PROJECT_CUSTOMIZE_DESIGN,
			    '_nonce' => wp_create_nonce( 'pmb-project-edit' ),
			    'ID' => $project->getWpPost()->ID,
			    'format' => $format_slug
		    ],
		    admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
	    );
    	include(PMB_TEMPLATES_DIR . 'design_customize.php');
    }

    protected function editContent(Project $project)
    {
	    $post_options = $this->post_fetcher->fetchPostOptionssForProject();
	    $project_support_front_matter = $project->supportsDivision(DesignTemplate::IMPLIED_DIVISION_FRONT_MATTER);
	    if($project_support_front_matter){
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
	    if($project_support_back_matter){
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
	    echo pmb_render_template(
	    	'project_edit_content.php',
	    [
		    'form_url' => $form_url,
		    'back_matter_sections' => $back_matter_sections,
		    'sections' => $sections,
		    'front_matter_sections' => $front_matter_sections,
		    'post_options' => $post_options,
		    'project' => $project,
		    'project_support_front_matter' => $project_support_front_matter,
	        'project_support_back_matter' => $project_support_back_matter
	    ]);
    }

    protected function editMetadata(Project $project){

	    if( $this->invalid_form instanceof FormSectionProper){
		    $form = $this->invalid_form;
	    } else {
		    $form = $project->getMetaForm();
		    $defaults = [];
		    foreach($form->inputs_in_subsections() as $input){
			    $saved_value = $project->getSetting($input->name());
			    if($saved_value){
				    $defaults[$input->name()] = $saved_value;
			    }
		    }
		    $form->populate_defaults($defaults);
	    }
    	$form_url = add_query_arg(
		    [
			    'ID' => $project->getWpPost()->ID,
			    'action' => self::SLUG_ACTION_EDIT_PROJECT,
			    'subaction' => self::SLUG_SUBACTION_PROJECT_META
		    ],
		    admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
	    );
	    echo pmb_render_template(
	    	'project_edit_metadata.php',
	    [
	    	'form_url' => $form_url,
		    'form' => $form,
		    'project' => $project
	    ]);
    }

    protected function editGenerate(Project $project){
    	$generations = $project->getAllGenerations();
    	include(PMB_TEMPLATES_DIR . 'project_edit_generate.php');
    }

    /**
     * Checks if a form was submitted, in which case we'd want to redirect.
     * @since 3.0
     *
     */
    public function checkFormSubmission()
    {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
        if($action === 'new'){
        	$this->saveNewProject();
	        exit;
        }
        if( $action === self::SLUG_ACTION_EDIT_PROJECT){
        	$subaction = isset($_GET['subaction']) ? $_GET['subaction'] : null;
	        $project = $this->project_manager->getById($_GET['ID']);
        	switch($subaction){
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
	        if($this->invalid_form instanceof FormSectionProper){
	        	// did we have validation issues? Don't redirect, just show the invalid form again then.
		        return;
	        }
	        $project_id = isset($_GET['ID']) ? $_GET['ID'] : null;

            // Just redirect back to the editing page.
            wp_redirect(
                add_query_arg(
	                [
		                'ID' => $project_id,
		                'action' => self::SLUG_ACTION_EDIT_PROJECT,
		                'subaction' => 'main'
	                ],
	                admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
                )
            );
            exit;
        } elseif($action === 'delete'){
        	$this->deleteProjects();
	        $redirect = admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH);
	        wp_safe_redirect($redirect);
	        exit;
        }
    }

    protected function saveNewProject()
    {
	    // Create a draft project

	    $project_id = wp_insert_post(
		    [
			    'post_content' => '',
			    'post_type' => CustomPostTypes::PROJECT,
			    'post_status' => 'publish'
		    ],
		    true
	    );
	    if(is_wp_error($project_id)){
		    wp_die($project_id->get_error_message());
	    }
	    $project_obj = $this->project_manager->getById($project_id);
	    $project_obj->setCode();
	    $project_obj->setFormatsSelected(
	    	array_map(
	    		function(FileFormat $format){
		            return $format->slug();
			    },
			    $this->file_format_registry->getFormats()
		    )
	    );
	    // add default sections
	    // ...after we figure out what they should be.
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
	    // Redirect to it
	    wp_redirect(
		    add_query_arg(
			    [
				    'ID' => $project_id,
				    'action' => self::SLUG_ACTION_EDIT_PROJECT,
				    'subaction' => self::SLUG_SUBACTION_PROJECT_MAIN
			    ],
			    admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
		    )
	    );
    }

	/**
	 * Saves the project's name and parts etc.
	 * @return int project ID
	 */
	protected function saveProjectContent(Project $project)
	{
		check_admin_referer('pmb-project-edit');
		foreach($project->getAllGenerations() as $project_generation){
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
	}

	protected function setSectionFromRequest(Project $project, $request_data, $placement, &$order = 1){
		$section_data = stripslashes($_POST[$request_data]);
		$sections = json_decode($section_data);
		if($section_data) {
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
    	$design_form->receive_form_submission($_REQUEST);
    	if(! $design_form->is_valid()) {
		    $this->invalid_form = $design_form;
	    }
        foreach($design_form->input_values(true,true) as $setting_name => $normalized_value){
            $design->setSetting($setting_name, $normalized_value);
	    }
        $project_generation = $project->getGenerationFor($_GET['format']);
        $project_generation->addDirtyReason(
        	'design_change',
	        __('You have customized this design', 'print-my-blog')
        );
        wp_safe_redirect(
		    add_query_arg(
			    [
				    'ID' => $project->getWpPost()->ID,
				    'action' => self::SLUG_ACTION_EDIT_PROJECT,
				    'subaction' => 'main'
			    ],
			    admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
		    )
	    );
    }

	protected function saveProjectChooseDesign(Project $project){
		$design = $this->design_manager->getById((int)$_GET['design']);
		$format = $this->file_format_registry->getFormat($_GET['format']);
		if(! $design instanceof Design || ! $format instanceof FileFormat){
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
		// send back to main
		wp_safe_redirect(
			add_query_arg(
				[
					'ID' => $project->getWpPost()->ID,
					'action' => self::SLUG_ACTION_EDIT_PROJECT,
					'subaction' => 'main'
				],
				admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
			)
		);
	}

	/**
	 * Gets the project form, which is a combination of the project forms for all the designs in use.
	 * @param Project $project
	 *
	 * @return FormSectionProper
	 * @throws \Twine\forms\helpers\ImproperUsageException
	 */
	protected function saveProjectMetadata(Project $project){
		$form = $project->getMetaForm();
		$form->receive_form_submission($_REQUEST);
		if(! $form->is_valid()) {
			$this->invalid_form = $form;
			return;
		}
		foreach($form->input_values(true,true) as $setting_name => $normalized_value){
			$project->setSetting($setting_name, $normalized_value);
		}
		$project_generations = $project->getAllGenerations();
		foreach($project_generations as $generation){
			$generation->addDirtyReason(
				'metadata',
				__('You changed projected metadata', 'print-my-blog')
			);
		}
		wp_safe_redirect(
			add_query_arg(
				[
					'ID' => $project->getWpPost()->ID,
					'action' => self::SLUG_ACTION_EDIT_PROJECT,
					'subaction' => 'main'
				],
				admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
			)
		);
	}

	/**
	 * Currently unused, but probably will be once we support skipping re-generating etc.
	 * @param Project $project
	 *
	 * @throws Exception
	 */
    protected function saveProjectGenerate(Project $project){
		$format = $this->file_format_registry->getFormat($_GET['format']);
		if(! $format instanceof FileFormat){
			throw new Exception(__('There is no file format with the slug "%s"', 'print-my-blog'),$_GET['format']);
		}
		$project_generation = $project->getGenerationFor($format);
		$project_generation->deleteGeneratedFiles();
		$project_generation->clearDirty();
	    $url = add_query_arg(
		    [
			    PMB_PRINTPAGE_SLUG => 3,
			    'project' => $project->getWpPost()->ID,
			    'format' => $format->slug()
		    ],
		    site_url()
	    );
	    wp_safe_redirect($url);
	    exit;
    }

	protected function deleteProjects()
	{
		// In our file that handles the request, verify the nonce.
		$nonce = esc_attr($_REQUEST['_wpnonce']);
		if (!wp_verify_nonce($nonce, 'bulk-projects')) {
			die('The request has expired. Please refresh the previous page and try again.');
		}
		else {
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
