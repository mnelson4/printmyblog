<?php
/**
 * @param \PrintMyBlog\orm\entities\Design $design
 * @return string CSS to include in the style
 */
function pmb_design_styles(\PrintMyBlog\orm\entities\Design $design){
	$css = $design->getSetting('custom_css');

	// image placement CSS
	$selector = '.pmb-image, .wp-block-gallery';
	switch($design->getPmbMeta('image_placement')){
		case 'snap':
			$css .= $selector . '{float:prince-snap;}';
			break;
		case 'snap-unless-fit':
			$css .= $selector . '{float:prince-snap unless-fit;}';
			break;
		case 'default':
		default:
			// leave alone
	}

	// page reference CSS
    $css .= '.pmb-posts a.pmb-page-ref[href]::after{
        content: "' . sprintf($design->getSetting('page_reference_text'),'" target-counter(attr(href), page) "') . '";
    }';
	return $css;
}