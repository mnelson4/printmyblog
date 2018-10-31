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
 * @since         $VID:$
 *
 */
class PmbAdmin extends BaseController
{
    /**
     * Sets hooks that we'll use in the admin.
     * @since $VID:$
     */
    public function setHooks()
    {
        add_action('admin_menu',array($this,'addToMenu'));
    }

    /**
     * Adds our menu page.
     * @since $VID:$
     */
    public function addToMenu()
    {
        add_submenu_page(
            'tools.php',
            esc_html__('Print My Blog', 'event_espresso'),
            esc_html__('Print My Blog', 'event_espresso'),
            PMG_ADMIN_CAP,
            'print-my-blog',
            array(
                $this,
                'renderAdminPage'
            )
        );
    }

    public function renderAdminPage()
    {
        include(PMG_TEMPLATES_DIR . 'settings.template.php');
    }
}