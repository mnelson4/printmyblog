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
        wp_enqueue_script('pmb-media-uploader', PMB_SCRIPTS_URL .'media-uploader.js');
        parent::enqueue_js();
    }



    /**
     *
     * @return string of html to display the field
     */

    public function display()
    {
        // the actual input
        $input = '<input type="text" size="34" ';
        $input .= 'name="' . $this->_input->html_name() . '" ';
        $input .= $this->_input->html_class() != '' ? 'class="large-text ee_media_url ' . $this->_input->html_class() . '" ' : 'class="large-text ee_media_url" ';
        $input .= 'value="' . $this->_input->raw_value_in_form() . '" ';
        $input .= $this->_input->otherHtmlAttributesString() . '>';
        // image uploader
	    $html = Html::instance();
        $uploader = $html->link('#', '<img src="' . admin_url('images/media-button-image.gif') . '" >', __('click to add an image', 'event_espresso'), '', 'ee_media_upload');
        // only attempt to show the image if it at least exists
	    if($this->src_exists($this->_input->raw_value())){
		    $image = '<br><br>' . $html->br() . $html->br() . $html->img($this->_input->raw_value(), '', '', "twine_media_image");
	    } else {
	    	$image = '';
	    }

        // html string
        return $html->div($input . $html->nbsp() . $uploader . $image, '', 'twine_media_uploader_area');
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
