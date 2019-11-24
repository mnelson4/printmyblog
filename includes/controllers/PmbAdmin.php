<?php

namespace PrintMyBlog\controllers;

use PrintMyBlog\domain\PrintOptions;
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
            'data:image/svg+xml;base64,' . base64_encode('<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
 width="128.000000pt" height="128.000000pt" viewBox="0 0 128.000000 128.000000"
 preserveAspectRatio="xMidYMid meet">
<metadata>
Created by potrace 1.15, written by Peter Selinger 2001-2017
</metadata>
<g transform="translate(0.000000,128.000000) scale(0.100000,-0.100000)"
fill="#000000" stroke="none">
<path d="M514 1123 c-77 -47 -221 -134 -321 -195 l-183 -110 0 -237 0 -238
128 -60 c70 -33 227 -109 348 -168 l222 -107 233 187 c129 104 249 202 267
219 l32 30 0 243 c0 134 -3 243 -7 243 -5 0 -124 -92 -265 -205 l-257 -205
-161 70 c-88 38 -168 74 -177 79 -11 6 20 9 93 9 70 0 130 6 169 17 33 9 88
23 123 31 34 7 85 25 112 40 47 26 142 115 151 142 3 9 13 9 43 1 46 -13 106
-4 106 15 0 8 -35 28 -78 45 -43 18 -83 41 -91 53 -11 17 -11 20 1 15 7 -3 55
-24 106 -46 92 -40 126 -50 106 -32 -5 6 -133 63 -285 129 l-276 119 -139 -84z
m254 15 c94 -40 144 -69 92 -52 -14 4 -74 8 -135 8 -88 1 -115 -2 -133 -16
-13 -9 -47 -24 -75 -34 -29 -9 -62 -23 -74 -31 -24 -16 -71 -14 -64 2 6 15
253 163 274 164 11 1 62 -18 115 -41z m159 -94 c40 -19 53 -31 53 -48 0 -12 5
-27 11 -33 33 -33 -54 -139 -156 -188 -68 -34 -156 -44 -225 -25 l-44 12 40
34 c29 25 49 33 77 34 41 0 92 -21 100 -42 4 -10 8 -10 14 -2 4 7 27 27 49 46
41 34 41 35 29 66 -14 33 -44 52 -112 72 -24 7 -43 17 -43 22 0 16 86 8 125
-12 45 -23 49 -24 39 -9 -3 6 -34 24 -68 39 -33 16 -53 29 -43 29 20 1 23 21
3 21 -8 0 -18 -4 -21 -10 -8 -13 -65 -13 -85 0 -12 8 -12 10 3 16 9 3 28 3 42
0 16 -4 25 -2 25 5 0 21 128 2 187 -27z m-364 -10 c3 -3 -10 -19 -29 -36 -19
-17 -34 -34 -34 -39 0 -14 62 20 80 43 13 16 21 17 43 9 l26 -10 -29 -30 c-29
-28 -39 -51 -22 -51 18 0 72 41 72 55 0 20 23 19 50 -3 l21 -17 -20 -27 c-22
-27 -77 -53 -85 -39 -3 4 11 15 31 25 19 9 34 19 31 21 -5 5 -139 -43 -155
-57 -18 -14 -75 -2 -101 23 -44 41 -24 81 58 118 50 23 54 24 63 15z m-273
-100 c0 -40 18 -64 47 -64 29 0 103 -63 103 -87 0 -16 40 -33 78 -34 24 0 25
-1 7 -8 -23 -10 -137 4 -166 19 -11 6 -19 21 -19 36 0 38 -17 62 -48 69 -38 8
-80 -9 -93 -39 -10 -20 -8 -31 9 -60 l21 -35 -47 19 c-84 33 -144 62 -139 66
9 9 233 143 240 144 4 0 7 -12 7 -26z m932 -76 c0 -18 0 -119 -1 -225 l-1
-191 -245 -199 c-135 -109 -250 -199 -255 -201 -6 -2 -9 89 -10 227 l0 230
252 201 c138 110 253 198 255 196 3 -2 5 -19 5 -38z m-845 -213 l320 -138 2
-234 c1 -137 -3 -233 -8 -233 -5 0 -157 71 -338 159 l-328 158 -3 218 c-3 209
-2 217 16 213 11 -3 163 -67 339 -143z"/>
</g>
</svg>')
        );
        add_submenu_page(
            PMB_ADMIN_PAGE_SLUG,
            esc_html__('Print Now', 'print-my-blog'),
            esc_html__('Print My Blog Now', 'print-my-blog'),
            PMB_ADMIN_CAP,
            PMB_ADMIN_PAGE_SLUG,
            array($this,'renderAdminPage')

        );
        add_submenu_page(
            PMB_ADMIN_PAGE_SLUG,
            esc_html__('Print My Blog Settings', 'print-my-blog'),
            esc_html__('Print My Blog Settings', 'print-my-blog'),
            'manage_options',
            'print-my-blog-settings',
            array($this,'settingsPage')

        );
    }

    public function settingsPage(){
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