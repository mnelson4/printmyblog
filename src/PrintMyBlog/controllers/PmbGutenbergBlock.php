<?php

namespace PrintMyBlog\controllers;

use PrintMyBlog\domain\PrintOptions;
use Twine\services\display\FormInputs;
use Twine\controllers\BaseController;

class PmbGutenbergBlock extends BaseController
{


    /**
     * Sets hooks needed for this controller to execute its logic.
     * @since 1.0.0
     */
    public function setHooks()
    {
        $this->registerGutenbergBlock();
        add_filter('the_content', array($this, 'enqueueFrontendScript'));
    }

    public function registerGutenbergBlock()
    {
        wp_register_script(
            'pmb-block',
            PMB_ASSETS_URL . 'scripts/pmb-block.js',
            array('wp-blocks', 'wp-element', 'wp-components', 'pmb-setup-page')
        );
        if (function_exists('register_block_type')) {
            register_block_type('printmyblog/setupform', array(
                'editor_script' => 'pmb-block',
                'style' => 'pmb-setup-page',
                'render_callback' => [$this, 'block_dynamic_render_cb'],
            ));
        }
    }

    /**
     * Enqueues the script on the frontend only if the block is in use.
     * See https://wordpress.stackexchange.com/questions/328536/load-css-javascript-in-frontend-conditionally-if-block-is-used/328553#328553
     * @since 2.3.6
     * @param string $content
     * @return string
     */
    public function enqueueFrontendScript($content)
    {
        if (function_exists('has_block') && has_block('printmyblog/setupform')) {
            wp_enqueue_script('pmb-setup-page', '', '', '', true);
        }
        return $content;
    }

    /**
     * CALLBACK
     *
     * Render callback for the dynamic block.
     *
     * Instead of rendering from the block's save(), this callback will render the front-end
     *
     * @since    1.0.0
     * @param $att Attributes from the JS block
     * @return string Rendered HTML
     */
    //phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function block_dynamic_render_cb($att)
    {
        //phpcs:enable
        // Coming from RichText, each line is an array's element
        ob_start();
        $print_options = new PrintOptions();
        $displayer = new FormInputs();
        include(PMB_TEMPLATES_DIR . 'setup_page.template.php');
        return ob_get_clean();
    }
}
