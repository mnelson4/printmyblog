<?php

namespace PrintMyBlog\controllers;

use Exception;
use PrintMyBlog\controllers\helpers\ProjectsListTable;
use PrintMyBlog\db\PartFetcher;
use PrintMyBlog\db\PostFetcher;
use PrintMyBlog\domain\FrontendPrintSettings;
use PrintMyBlog\domain\PrintOptions;
use PrintMyBlog\domain\DefaultFileFormats;
use PrintMyBlog\entities\FileFormat;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\orm\managers\DesignManager;
use PrintMyBlog\orm\managers\ProjectManager;
use PrintMyBlog\services\FileFormatRegistry;
use PrintMyBlog\system\Context;
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
class PmbAdmin extends BaseController
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
     * @var PartFetcher
     */
    protected $part_fetcher;

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
	 * @param PostFetcher $post_fetcher
	 * @param PartFetcher $part_fetcher
	 * @param ProjectManager $project_manager
	 * @param FileFormatRegistry $project_format_manager
	 *
	 * @since $VID:$
	 */
    public function inject(
    	PostFetcher $post_fetcher,
	    PartFetcher $part_fetcher,
	    ProjectManager $project_manager,
		FileFormatRegistry $project_format_manager,
		DesignManager $design_manager
    ){
        $this->post_fetcher = $post_fetcher;
        $this->part_fetcher = $part_fetcher;
        $this->project_manager = $project_manager;
        $this->file_format_registry = $project_format_manager;
        $this->design_manager = $design_manager;
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
            PMB_ADMIN_PAGE_SLUG,
            array(
                $this,
                'renderAdminPage'
            ),
            'data:image/svg+xml;base64,' . base64_encode(file_get_contents(PMB_DIR . 'assets/images/menu-icon.svg'))
        );
        add_submenu_page(
            PMB_ADMIN_PAGE_SLUG,
            esc_html__('Print My Blog Now', 'print-my-blog'),
            esc_html__('Print Now', 'print-my-blog'),
            PMB_ADMIN_CAP,
            PMB_ADMIN_PAGE_SLUG,
            array($this,'renderAdminPage')
        );
        add_submenu_page(
            PMB_ADMIN_PAGE_SLUG,
            esc_html__('Projects', 'print-my-blog'),
            esc_html__('Projects', 'print-my-blog'),
            PMB_ADMIN_CAP,
            PMB_ADMIN_PROJECTS_PAGE_SLUG,
            array($this, 'renderProjects')
        );
        add_submenu_page(
            PMB_ADMIN_PAGE_SLUG,
            esc_html__('Print My Blog Settings', 'print-my-blog'),
            esc_html__('Settings', 'print-my-blog'),
            'manage_options',
            PMB_ADMIN_SETTINGS_PAGE_SLUG,
            array($this,'settingsPage')
        );

        // Add the legacy button, just so folks aren't confused.
        add_submenu_page(
            'tools.php',
            esc_html__('Print My Blog', 'print-my-blog'),
            esc_html__('Print My Blog', 'print-my-blog'),
            PMB_ADMIN_CAP,
            'print-my-blog',
            array(
                $this,
                'renderLegacyAdminPage'
            )
        );
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
        include(PMB_TEMPLATES_DIR . 'settings_page.template.php');
    }


    /**
     * Shows the setup page.
     * @since 1.0.0
     */
    public function renderAdminPage()
    {

        if (isset($_GET['welcome'])) {
            include(PMB_TEMPLATES_DIR . 'welcome.template.php');
        } else {
            $print_options = new PrintOptions();
            $displayer = new FormInputs();
            include(PMB_TEMPLATES_DIR . 'setup_page.template.php');
        }
    }

    public function renderLegacyAdminPage()
    {
        $print_options = new PrintOptions();
        $displayer = new FormInputs();
        $legacy_page = true;
        include(PMB_TEMPLATES_DIR . 'setup_page.template.php');
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
            $links
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
        } elseif(
            in_array(
                $hook,
                array(
                    'tools_page_print-my-blog',
                    'toplevel_page_print-my-blog-now'
                )
            )) {
            wp_enqueue_script('pmb-setup-page');
            wp_enqueue_style('pmb-setup-page');
        } elseif($hook === 'print-my-blog_page_print-my-blog-projects'
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
	        include(PMB_TEMPLATES_DIR . 'projects_list_table.template.php');
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
	    include(PMB_TEMPLATES_DIR . 'design_choose.template.php');
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
		$formats = $this->file_format_registry->getFormats();
		include(PMB_TEMPLATES_DIR . 'project_edit_main.template.php');
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
		    $form = $design->getDesignTemplate()->getDesignForm();
		    $defaults = [];
		    foreach($form->inputs_in_subsections() as $input){
		    	$defaults[$input->name()] = $design->getSetting($input->name());
		    }
		    $form->populate_defaults($defaults);
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
    	include(PMB_TEMPLATES_DIR . 'design_customize.template.php');
    }

    protected function editContent(Project $project)
    {
	    $post_options = $this->post_fetcher->fetchPostOptionssForProject();
	    $parts = $this->part_fetcher->fetchPartsFor($_GET['ID']);
	    $form_url = add_query_arg(
		    [
			    'ID' => $project->ID,
			    'action' => self::SLUG_ACTION_EDIT_PROJECT,
			    'subaction' => self::SLUG_SUBACTION_PROJECT_CONTENT
		    ],
		    admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
	    );
	    include(PMB_TEMPLATES_DIR . 'project_edit_content.template.php');
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
        	// Create a draft project
	        $project_id = wp_insert_post(
		        [
			        'post_content' => '',
			        'post_type' => CustomPostTypes::PROJECT,
			        'post_status' => 'draft'
		        ],
		        true
	        );
	        if(is_wp_error($project_id)){
		        wp_die($project_id->get_error_message());
	        }
	        $project_obj = new Project($project_id);
	        $project_obj->setCode();
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
	        exit;
        }
        if( $action === self::SLUG_ACTION_EDIT_PROJECT){
        	$subaction = isset($_GET['subaction']) ? $_GET['subaction'] : null;
        	switch($subaction){
		        case self::SLUG_SUBACTION_PROJECT_CHANGE_DESIGN:
			        $this->saveProjectChooseDesign();
			        break;
		        case self::SLUG_SUBACTION_PROJECT_CUSTOMIZE_DESIGN:
			        $this->saveProjectCustomizeDesign();
			        break;
		        case self::SLUG_SUBACTION_PROJECT_CONTENT:
			        $this->saveProjectContent();
		        case self::SLUG_SUBACTION_PROJECT_META:
			        $this->saveProjectMetadata();
			        break;
		        case self::SLUG_SUBACTION_PROJECT_GENERATE:
		        	$this->saveProjectGenerate();
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

	/**
	 * Saves the project's name and parts etc.
	 * @return int project ID
	 */
	protected function saveProjectContent()
	{
		check_admin_referer('pmb-project-edit');
		$project_id = $_GET['ID'];
		$project_obj = new Project($project_id);
		$parts_string = stripslashes($_POST['pmb-project-sections-data']);
		$parts = json_decode($parts_string);
		$project_obj->clearGeneratedFiles();
		$this->part_fetcher->clearPartsFor($project_id);
		$this->part_fetcher->setPartsFor($project_id, $parts);
		return $project_id;
	}

    protected function saveProjectCustomizeDesign()
    {
	    /**
	     * @var $project Project
	     */
    	$project = $this->project_manager->getById($_GET['ID']);

    	$design = $project->getDesignFor($_GET['format']);

    	$design_form = $design->getDesignTemplate()->getDesignForm();
    	$design_form->receive_form_submission($_REQUEST);
    	if($design_form->is_valid()){
    		foreach($design_form->input_values(true,true) as $setting_name => $normalized_value){
    			$design->setSetting($setting_name, $normalized_value);
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
    	$this->invalid_form = $design_form;
    }

	protected function saveProjectChooseDesign(){
		/**
		 * @var $project Project
		 */
		$project = $this->project_manager->getById($_GET['ID']);

		$design = $project->getDesignFor($_GET['format']);
		$project->setDesignFor($_GET['format'],)
	}

    protected function saveProjectGenerate(){
		    $url = add_query_arg(
			    [
				    PMB_PRINTPAGE_SLUG => 3,
				    'project' => $_GET['ID']
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
			/**
			 * @var $manager ProjectManager
			 */
			$manager = Context::instance()->reuse('PrintMyBlog\orm\managers\ProjectManager');
			$manager->deleteProjects($_POST['ID']);
		}
	}
}
