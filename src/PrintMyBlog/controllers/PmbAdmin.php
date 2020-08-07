<?php

namespace PrintMyBlog\controllers;

use PrintMyBlog\controllers\helpers\ProjectsListTable;
use PrintMyBlog\db\PartFetcher;
use PrintMyBlog\db\PostFetcher;
use PrintMyBlog\domain\FrontendPrintSettings;
use PrintMyBlog\domain\PrintOptions;
use PrintMyBlog\orm\Project;
use PrintMyBlog\system\CustomPostTypes;
use Twine\services\display\FormInputs;
use Twine\controllers\BaseController;
use WP_Post;

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

    /**
     * @var PostFetcher
     */
    protected $post_fetcher;

    /**
     * @var PartFetcher
     */
    protected $part_fetcher;

    /**
     * @since $VID:$
     * @param PostFetcher $post_fetcher
     * @param PartFetcher $part_fetcher
     */
    public function inject(PostFetcher $post_fetcher, PartFetcher $part_fetcher){
        $this->post_fetcher = $post_fetcher;
        $this->part_fetcher = $part_fetcher;
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
                 && in_array($_GET['action'], ['edit','new'],true)
            ) {
            wp_register_script(
                'sortablejs',
                PMB_SCRIPTS_URL . 'libs/Sortable.min.js',
                array(),
                '1.10.2'
            );

            wp_enqueue_script(
                'pmb_project_edit',
                PMB_SCRIPTS_URL . 'project-edit.js',
                array('sortablejs',),
                filemtime(PMB_SCRIPTS_DIR . 'project-edit.js')
            );
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
        if(in_array($action,['new','edit'],true)) {
			if($action === 'new') {
				$project = new WP_Post(new \stdClass());
				$post_options = $this->post_fetcher->fetchPostOptionssForProject();
				$parts = [];
				$form_url = add_query_arg(
					[
						'action' => 'edit',
					],
					admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
				);
			} else {
				$post_options = $this->post_fetcher->fetchPostOptionssForProject();
				$project = get_post($_GET['ID']);
				$parts = $this->part_fetcher->fetchPartsFor($_GET['ID']);
				$form_url = add_query_arg(
					[
						'ID' => $project->ID,
						'action' => 'edit',
					],
					admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
				);
			}
	        include(PMB_TEMPLATES_DIR . 'project_edit.template.php');
			return;
        }
        $table = new ProjectsListTable();
        $add_new_url = add_query_arg(
	        [
		        'action' => 'new',
	        ],
	        admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
        );
        include(PMB_TEMPLATES_DIR . 'projects_list_table.template.php');
    }

    /**
     * Saves the project's name and parts etc.
     * @return int project ID
     */
    protected function saveProject()
    {
        check_admin_referer('pmb-project-edit');
        if(isset($_GET['ID'])) {
            $project_id = $_GET['ID'];
            wp_update_post(
            	[
            		'ID' => $project_id,
            	    'post_title' => $_POST['pmb-project-title']
	            ]
            );
        } else {
            $project_id = wp_insert_post(
                [
                    'post_title' => stripslashes($_POST['pmb-project-title']),
	                'post_content' => '',
	                'post_type' => CustomPostTypes::PROJECTS,
	                'post_status' => 'publish'
                ],
                true
            );
            $project_obj = new Project($project_id);
            $project_obj->setCode();
            if(is_wp_error($project_id)){
                wp_die($project_id->get_error_message());
            }
        }
        $parts_string = stripslashes($_POST['pmb-project-sections-data']);
        $parts = json_decode($parts_string);
        $this->part_fetcher->clearPartsFor($project_id);
        $this->part_fetcher->setPartsFor($project_id, $parts);
        return $project_id;
    }


    /**
     * Checks if a form was submitted, in which case we'd want to redirect.
     * @since 3.0
     *
     */
    public function checkFormSubmission()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : null;
        if(in_array($action,['new','edit'],true)){
            $project_id = $this->saveProject();
            if($_POST['pmb-save'] === 'pdf'){
                $url = add_query_arg(
                    [
                        PMB_PRINTPAGE_SLUG => 3,
                        'project' => $project_id
                    ],
                    site_url()
                );
                wp_redirect($url);
                exit;
            } else {
                wp_redirect(
	                add_query_arg(
		                [
			                'ID' => $project_id,
			                'action' => 'edit',
		                ],
		                admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
	                )
                );
            }
        }
    }
}
