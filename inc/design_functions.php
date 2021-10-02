<?php
/**
 * @param \PrintMyBlog\orm\entities\Design $design
 * @return string CSS to include in the style
 */
function pmb_design_styles(\PrintMyBlog\orm\entities\Design $design){
	$css = '/* PMB design styles for ' . $design->getWpPost()->post_title. '*/' . $design->getSetting('custom_css');

	// image placement CSS
    //
    $selectors_to_snap = [
        // Gutenberg
        '.wp-block-image', // Gutenberg image block. With or without caption
        '.wp-block-gallery', // Gutenberg gallery
        '.wp-block-table', /// Gutenberg table

        // Classic Editor
        'img', // Classic Editor image
        'figure.wp-caption', // Classic Editor image with caption
        '.gallery', // Classic Editor gallery
    ];
	foreach($selectors_to_snap as $key => $selector){
	    $selectors_to_snap[$key] = $selector . ':not(.pmb-dont-snap, .emoji, div.tiled-gallery img, img.fg-image)';
    }
	$combined_selector = implode(', ', $selectors_to_snap);
	switch($design->getPmbMeta('image_placement')){
		case 'snap':
			$css .= $combined_selector . '{float:prince-snap;}';
			break;
		case 'snap-unless-fit':
			$css .= $combined_selector . '{float:prince-snap unless-fit;}';
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
    $show_powered_by = true;
    if(pmb_fs()->is_plan__premium_only('hobby')){
        $show_powered_by = false;
    }
    if($design->getSetting('powered_by') || $show_powered_by){
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