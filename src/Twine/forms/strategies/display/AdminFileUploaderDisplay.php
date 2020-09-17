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
    public function enqueue_js()
    {
        wp_enqueue_media();
        wp_enqueue_script('media-upload');
        wp_enqueue_script('pmb-media-uploader', TWINE_SCRIPTS_URL .'media-uploader.js');
        wp_enqueue_style(
        	'pmb-media-uploader',
	        TWINE_STYLES_URL . 'media-uploader.css'
        );
        parent::enqueue_js();
    }



    /**
     *
     * @return string of html to display the field
     */

    public function display()
    {
	    // image uploader
	    $html_generator = Html::instance();
	    $html = $html_generator->link('#', __('Choose File', 'twine'), __('click to add an image', 'event_espresso'), '', 'twine_media_upload button');
        // the actual input
        $html .= '<input type="text" size="34" ';
        $html .= 'name="' . $this->_input->html_name() . '" ';
        $html .= $this->_input->html_class() != '' ? 'class="large-text twine_media_url ' . $this->_input->html_class() . '" ' : 'class="large-text twine_media_url" ';
        $html .= 'value="' . $this->_input->raw_value_in_form() . '" ';
        $html .= 'placeholder="https://..." ';
        $html .= $this->_input->otherHtmlAttributesString() . '>';

        // only attempt to show the image if it at least exists
	    if($this->src_exists($this->_input->raw_value())){
		    $image = '<br><br>' . $html_generator->br() . $html_generator->br() . $html_generator->img($this->_input->raw_value(), '', '', "twine_media_image");
	    } else {
	    	$image = '';
	    }

        // html string
        return $html_generator->div($html . $html_generator->nbsp() . $image, '', 'twine_media_uploader_area');
    }



    /**
     * Asserts an image actually exists as quickly as possible by sending a HEAD
     * request
     * @param string $src
     * @return boolean
     */
    protected function src_exists($src)
    {
        $results = wp_remote_head($src);
        if (is_array($results) && ! $results instanceof WP_Error) {
            return strpos($results['headers']['content-type'], "image") !== false;
        } else {
            return false;
        }
    }
}
