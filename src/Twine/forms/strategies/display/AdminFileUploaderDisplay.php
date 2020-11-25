<?php

namespace Twine\forms\strategies\display;

use Twine\helpers\Html;
use WP_Error;

/**
 * Class AdminFileUploaderDisplay
 *
 * @package            Event Espresso
 * @subpackage    core
 * @author                Mike Nelson
 * @since                4.6
 *
 */
class AdminFileUploaderDisplay extends DisplayBase
{
    
    /**
     * Enqueues the JS and CSS needed to display this input
     */
    public function enqueueJs()
    {
        wp_enqueue_media();
        wp_enqueue_script('media-upload');
        wp_enqueue_script('pmb-media-uploader', TWINE_SCRIPTS_URL . 'media-uploader.js');
        wp_enqueue_style(
            'pmb-media-uploader',
            TWINE_STYLES_URL . 'media-uploader.css'
        );
        parent::enqueueJs();
    }



    /**
     *
     * @return string of html to display the field
     */

    public function display()
    {
        // image uploader
        $html_generator = Html::instance();
        $html = $html_generator->link(
            '#',
            __('Choose File', 'print-my-blog'),
            __('click to add an image', 'print-my-blog'),
            '',
            'twine_media_upload button'
        );
        // the actual input
        $html .= '<input type="text" size="34" ';
        $html .= 'name="' . $this->input->htmlName() . '" ';
        $html .= $this->input->htmlClass() != ''
            ? 'class="large-text twine_media_url ' . $this->input->htmlClass() . '" '
            : 'class="large-text twine_media_url" ';
        $html .= 'value="' . $this->input->rawValueInForm() . '" ';
        $html .= 'placeholder="https://..." ';
        $html .= $this->input->otherHtmlAttributesString() . '>';

        // only attempt to show the image if it at least exists
        if ($this->srcExists($this->input->rawValue())) {
            $image = $html_generator->br()
                . $html_generator->br()
                . $html_generator->img($this->input->rawValue(), '', '', "twine_media_image");
        } else {
            $image = '';
        }

        // html string
        return $html_generator->div(
            $html
            . $html_generator->nbsp()
            . $image,
            '',
            'twine_media_uploader_area'
        );
    }



    /**
     * Asserts an image actually exists as quickly as possible by sending a HEAD
     * request
     * @param string $src
     * @return boolean
     */
    protected function srcExists($src)
    {
        $results = wp_remote_head($src);
        if (is_array($results) && ! $results instanceof WP_Error) {
            return strpos($results['headers']['content-type'], "image") !== false;
        } else {
            return false;
        }
    }
}
