<?php
namespace PrintMyBlog\controllers;
use Twine\controllers\BaseController;
class PmgFrontend extends BaseController
{
    public function setHooks()
    {
        add_filter('template_include',array($this,'templateRedirect'));
    }

    /**
     * Determines if the request is for our page generator page, and if so, uses our template for it.
     * @since $VID:$
     */
    public function templateRedirect($template)
    {
        if(isset($_GET[PMG_PRINTPAGE_SLUG])) {
            wp_enqueue_script(
                'pmg_print_page',
                PMG_ASSETS_URL . 'scripts/print_page.js',
                array('jquery','wp-api'),
                filemtime(PMG_DIR . 'assets/scripts/print_page.js')
            );
            wp_enqueue_style(
                'pmg_print_page',
                PMG_ASSETS_URL . 'styles/print_page.css',
                array(),
                filemtime(PMG_DIR . 'assets/styles/print_page.css')
            );
            return PMG_TEMPLATES_DIR . 'print_page.template.php';
        }
        return $template;
    }
}