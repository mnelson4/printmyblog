<?php

namespace PrintMyBlog\controllers;

use PrintMyBlog\domain\PrintOptions;
use Twine\controllers\BaseController;
use PrintMyBlog\domain\PrintNowSettings;

/**
 * Class PmbAdmin
 *
 * Hooks needed to add our stuff to the admin.
 * Mostly it's just an admin page.
 *
 * @package     Event Espresso
 * @author         Mike Nelson
 * @since         1.0.0
 *
 */
class PmbAdmin extends BaseController
{
    /**
     * Sets hooks that we'll use in the admin.
     * @since 1.0.0
     */
    public function setHooks()
    {
        add_action('admin_menu', array($this, 'addToMenu'));
        add_filter('plugin_action_links_' . PMB_BASENAME, array($this, 'pluginPageLinks'));
        add_action( 'admin_enqueue_scripts', [$this,'enqueueScripts'] );
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
            esc_html__('Print My Blog Settings', 'print-my-blog'),
            esc_html__('Settings', 'print-my-blog'),
            'manage_options',
            'print-my-blog-settings',
            array($this,'settingsPage')

        );
    }

    public function settingsPage(){
        $pmb_print_now_formats = new PrintNowSettings();
        $pmb_print_now_formats->load();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Ok save those settings!
            if(isset($_POST['pmb-reset'])){
                $pmb_print_now_formats = new PrintNowSettings();
            } else {
                foreach($pmb_print_now_formats->formatSlugs() as $slug){
                    if(isset($_POST['format'][$slug])){
                        $active = true;
                    } else {
                        $active = false;
                    }
                    $pmb_print_now_formats->setFormatActive($slug,$active);
                    if(isset($_POST['frontend_labels'][$slug])){
                        $pmb_print_now_formats->setFormatFrontendLabel($slug,$_POST['frontend_labels'][$slug]);
                    }

                }
            }
            $pmb_print_now_formats->save();
            wp_redirect('');
        }

        include(PMB_TEMPLATES_DIR . 'settings_page.template.php');
    }


    /**
     * Shows the setup page.
     * @since 1.0.0
     */
    public function renderAdminPage()
    {
        $print_options = new PrintOptions();
        include(PMB_TEMPLATES_DIR . 'setup_page.template.php');
    }

    /**
     * Adds links to PMB stuff on the plugins page.
     * @since 1.0.0
     * @param array $links
     */
    public function pluginPageLinks($links)
    {
        $links[] = '<a href="'
            . admin_url(PMB_ADMIN_PAGE_PATH)
            . '">'
            . esc_html__('Print Setup Page', 'print-my-blog')
            . '</a>';
        return $links;
    }

    function enqueueScripts($hook) {
        if ( 'toplevel_page_print-my-blog' !== $hook ) {
            return;
        }
        wp_enqueue_script('pmb-setup-page');
        wp_enqueue_style('pmb-setup-page');
    }
}