<?php

namespace PrintMyBlog\controllers;

use PrintMyBlog\domain\FrontendPrintSettings;
use PrintMyBlog\domain\PrintOptions;
use Twine\controllers\BaseController;

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
        $pmb_print_now_formats = new FrontendPrintSettings();
        $pmb_print_now_formats->load();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('pmb-settings');
            // Ok save those settings!
            if (isset($_POST['pmb-reset'])) {
                $pmb_print_now_formats = new FrontendPrintSettings();
            } else {
                $pmb_print_now_formats->setShowButtons(isset($_POST['show_buttons']));
                foreach ($pmb_print_now_formats->formatSlugs() as $slug) {
                    if (isset($_POST['format'][$slug])) {
                        $active = true;
                    } else {
                        $active = false;
                    }
                    $pmb_print_now_formats->setFormatActive($slug, $active);
                    if (isset($_POST['frontend_labels'][$slug])) {
                        $pmb_print_now_formats->setFormatFrontendLabel($slug, $_POST['frontend_labels'][$slug]);
                    }
                }
            }
            $pmb_print_now_formats->save();
            update_option(self::SETTINGS_SAVED_OPTION, true, false);
            wp_redirect('');
        }
        $saved = get_option(self::SETTINGS_SAVED_OPTION, false);
        if ($saved) {
            update_option(self::SETTINGS_SAVED_OPTION, false, false);
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
            include(PMB_TEMPLATES_DIR . 'setup_page.template.php');
        }
    }

    public function renderLegacyAdminPage()
    {
        $print_options = new PrintOptions();
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
        if (
            ! in_array(
                $hook,
                array(
                'tools_page_print-my-blog',
                'toplevel_page_print-my-blog-now'
                )
            )
        ) {
            return;
        }
        if (isset($_GET['welcome'])) {
            wp_enqueue_style(
                'pmb_welcome',
                PMB_ASSETS_URL . 'styles/welcome.css',
                array(),
                filemtime(PMB_ASSETS_DIR . 'styles/welcome.css')
            );
        } else {
            wp_enqueue_script('pmb-setup-page');
            wp_enqueue_style('pmb-setup-page');
        }
    }
}
