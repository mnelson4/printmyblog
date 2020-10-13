<?php
/**
 * Functions used internally by Print My Blog that other devs probably won't need.
 */

/**
 * Returns a string that says this feature only works with Print My Blog Pro.
 * @return string
 */
function pmb_pro_only(){
	return ' ' . __('*Pro Only*', 'print-my-blog');
}

/**
 * Returns a string that says this feature works best with Print My Blog Pro.
 * @return string
 */
function pmb_pro_best(){
	return ' ' . __('*Best with Pro*', 'print-my-blog');
}

/**
 * Whether or not this is the pro version.
 * @todo BETA replace with Freemius magic
 * @return bool
 */
function pmb_pro(){
	return defined('PMB_PRO');
}

/**
 * Maps
 * @param \PrintMyBlog\orm\entities\ProjectSection $section
 *
 * @return string
 */
function pmb_map_section_to_division(\PrintMyBlog\orm\entities\ProjectSection $section){
	return apply_filters(
		'pmb_map_section_to_division',
		pmb_map_section_height_to_division($section->getHeight()),
		$section
	);
}

function pmb_map_section_height_to_division($height){
	switch($height){
		case 1:
			$division_name = 'part';
			break;
		case 2:
			$division_name = 'volume';
			break;
		case 3:
			$division_name = 'anthology';
			break;
		case 0:
		default:
			$division_name = 'article';
	}
	return $division_name;
}

/**
 * @param $template_name
 * @param array $context
 */
function pmb_render_template($template_name, $context=[]){
	extract($context);
	require(PMB_TEMPLATES_DIR . $template_name);
}