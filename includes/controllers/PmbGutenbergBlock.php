<?php

namespace PrintMyBlog\controllers;

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
    }

    public function registerGutenbergBlock()
    {
        wp_register_script(
            'pmb-setupform',
            PMB_ASSETS_URL . 'scripts/pmb-block.js',
            array('wp-blocks', 'wp-element')
        );

        register_block_type('printmyblog/setupform', array(
            'editor_script' => 'pmb-setupform',
            'render_callback' => [$this, 'block_dynamic_render_cb'],
        ));
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
    public function block_dynamic_render_cb ( $att ) {
        // Coming from RichText, each line is an array's element
        ob_start();
        include(PMB_TEMPLATES_DIR . 'setup_page.template.php');
        return ob_get_clean();
    }
}