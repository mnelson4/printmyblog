<?php

namespace PrintMyBlog\controllers;

use Twine\controllers\BaseController;

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
    }

    /**
     * Adds our menu page.
     * @since 1.0.0
     */
    public function addToMenu()
    {
        add_submenu_page(
            'tools.php',
            esc_html__('Print My Blog', 'print-my-blog'),
            esc_html__('Print My Blog', 'print-my-blog'),
            PMB_ADMIN_CAP,
            PMB_ADMIN_PAGE_SLUG,
            array(
                $this,
                'renderAdminPage'
            )
        );
    }

    /**
     * Shows the setup page.
     * @since 1.0.0
     */
    public function renderAdminPage()
    {
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
}