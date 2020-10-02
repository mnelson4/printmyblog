<?php
/**
 * This function is only included when rendering Print My Blog
 */

use PrintMyBlog\orm\entities\Design;

/**
 * @param $post
 *
 * @return bool|false|string|WP_Error
 */

function pmb_get_the_post_anchor($post){
	if( ! $post instanceof WP_Post){
		global $post;
	}
	return get_permalink($post);
}

function pmb_the_post_anchor(){
	global $post;
	echo pmb_get_the_post_anchor($post);
}

function pmb_convert_url_to_anchor($url){
	return esc_attr($url);
}

/**
 * @param string $relative_filepath filepath relative to the current design's templates directory
 * @global Design $pmb_design
 */
function pmb_include_design_template($relative_filepath){
	/**
	 * @var $pmb_design Design
	 */
	global $pmb_design;
	require($pmb_design->getDesignTemplate()->getDirForTemplates() . $relative_filepath);
}

/**
 * Add this to the HTML div that wraps a section and its subsections.
 * @param string $class
 */
function pmb_section_wrapper_class($class = ''){
	global $post;
	$section = $post->pmb_section;
	$pmb_classes = '';
	if($section instanceof \PrintMyBlog\orm\entities\ProjectSection){
		$pmb_classes = 'pmb-' . pmb_map_section_to_division($section) . '-wrapper';
	}
	echo 'class="' . esc_attr($pmb_classes . ' ' . $class) . '"';
}

/**
 * Add this to any article tags for a PMB section.
 * @param string $class
 * @return void echoes
 */
function pmb_section_class($class = ''){
	global $post;
	$section = $post->pmb_section;
	$pmb_classes = '';
	if($section instanceof \PrintMyBlog\orm\entities\ProjectSection){
		$pmb_classes = 'pmb-' . pmb_map_section_to_division($section) . ' pmb-height-' . $section->getHeight() . ' pmb-depth-' . $section->getDepth();
	}
	post_class($pmb_classes . $class);
}

function pmb_section_wrapper_id(){
	global $post;
	echo 'id="' . esc_attr($post->post_name) . '-wrapper"';
}
/**
 * Echoes out the ID attribute to use on the section.
 */
function pmb_section_id(){
	echo 'id="' . esc_attr(get_the_permalink()) . '"';
}