<?php

namespace PrintMyBlog\controllers;

use Twine\controllers\BaseController;

/**
 * Class ProPrintPage
 *
 * Description
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
class ProLoadingPage extends BaseController
{

    /**
     * Sets hooks needed for this controller to execute its logic.
     *
     * @since 1.0.0
     */
    public function setHooks()
    {
        add_filter(
            'template_include',
            array($this, 'templateRedirect'),
            /* after Elementor at priority 12,
            Enfold theme at the ridiculous priority 20,000...
            Someday, perhaps we should have a regular page dedicated to Print My Blog.
            If you're reading this code and agree, feel free to work on a pull request! */
            20001
        );
    }

    /**
     * Determines if the request is for our page generator page, and if so, uses our template for it.
     * @since 1.0.0
     */
    public function templateRedirect($template)
    {
        if (isset($_GET[ PMB_PRINTPAGE_SLUG ]) && $_GET[ PMB_PRINTPAGE_SLUG ] == 3) {
            return PMB_TEMPLATES_DIR . 'pro_loading_page.template.php';
        }
        return $template;
    }
}