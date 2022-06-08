<?php

namespace PrintMyBlog\compatibility\plugins;

use Twine\compatibility\CompatibilityBase;

/**
 * Class EasyFootnotes
 *
 * For the plugin located at https://wordpress.org/plugins/wp-vr-view/
 * 360 VR images obviously dont work when reading, so replace them with just the preview image or some text.
 *
 * @package     Print My Blog
 * @author         Mike Nelson
 * @since         2.1.4
 *
 */
class WpVrView extends CompatibilityBase
{

    public function setHooks()
    {
    }

    public function setRenderingHooks()
    {
        // Their shortcode becomes a simple iFrame that has JS that doesn't work in Prince XML.
        // So replace their shortcode with our own thing.
        remove_shortcode('vrview');
        add_shortcode('vrview', [$this, 'shortcode']);
    }

    /**
     * We just want to set some hooks; we don't want to actually change any results.
     * @since $VID:$
     * @param $normal_result
     * @return mixed
     */
    public function shortcode($atts)
    {
        // code mostly copy-and-pasted from wp-vr-view's vr_creation()
        $a = shortcode_atts(
            array(
                'img' => '',
                'video' => '',
                'pimg' => '',
                'stereo' => 'false',
                'width' => '640',
                'height' => '360',
            ),
            $atts
        );

        $img_url = 'image=' . $a['img'];
        $stereo = '&is_stereo=' . $a['stereo']; // generate s_stereo parameter -  defaul is FALSE

        /* if has video then add it to URL */
        $video_url = '';
        if ($a['video']) {
            $video_url = 'video=' . $a['video'];
            if ($a['img']) {
                $img_url = '&image=' . $a['img'];
            } else {
                $img_url = '';
            }
        }
        /* if has preview then add it to URL */
        $pimg_url = '';
        if ($a['pimg']) {
            $pimg_url = '&preview=' . $a['pimg'];
        }

        // ==================================================================
        // MODIFIED FROM ORIGINAL because original just used plugin_dir_url(__FILE__) from WP VR View's main file
        $iframe_url = plugin_dir_url(
            dirname(dirname(PMB_MAIN_FILE))
            . '/wp-vr-view/wp-vrview.php'
        )
            . 'asset/index.html?'
            . $video_url
            . $img_url
            . $stereo
            . $pimg_url;

        // turn it into a link instead of an iframe here
        $html = '<div class="pmb-wp-vr-view-wrapper"><a href="' . $iframe_url . '">';

        if (isset($a['pimg']) && $a['pimg']) {
            $html .= '<img class="wp-vr-view pmb-video-preview" style="max-width:'
                . $a['width']
                . ';max-height:'
                . $a['height']
                . '" src="'
                . esc_url($a['pimg']) . '">';
        } else {
            $html .= '<p>' . __('360 Image Available', 'print-my-blog') . '</p>';
        }

        $html .= '</a></p></div>';
        return $html;
    }
}
// End of file EasyFootnotes.php
// Location: PrintMyBlog\compatibility\plugins/EasyFootnotes.php
