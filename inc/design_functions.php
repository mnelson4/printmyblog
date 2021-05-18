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
        content: " ' . sprintf($design->getSetting('page_reference_text'),'" target-counter(attr(href), page) "') . '";
    }
    .pmb-posts a[href].pmb-page-num::after{
        content: target-counter(attr(href), page);
    }';
	// instruct PMB print service to add "powered by" for free users and cheap plans
    if($design->getSetting('powered_by') || ! pmb_fs()->is_plan__premium_only('hobby')){
        $css .= '@page:first{
            @bottom{
                content:\'Powered by Print My Blog Pro & WordPress\';
                color:gray;
                font-style:italic;
            }
        }';
    }
	return $css;
}