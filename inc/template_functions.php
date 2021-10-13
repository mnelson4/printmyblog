<?php
/**
 * This function is only included when rendering Print My Blog
 */

use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;

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
 * @global Project $pmb_project
 * @global \PrintMyBlog\entities\ProjectGeneration $pmb_project_generation
 */
function pmb_include_design_template($relative_filepath){
	/**
	 * @var $pmb_design Design
	 */
	global $pmb_project, $pmb_design, $pmb_project_generation;
	require($pmb_design->getDesignTemplate()->getTemplatePathToDivision($relative_filepath));
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
		$pmb_classes = 'pmb-' . pmb_map_section_to_division($section) . '-wrapper pmb-section-wrapper';
		if($section->getSectionOrder() === 1){
			$pmb_classes .= ' pmb-first-section-wrapper';
		}
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
		$pmb_classes = ' pmb-section pmb-' . pmb_map_section_to_division($section) . ' pmb-height-' . $section->getHeight() . ' pmb-depth-' . $section->getDepth();
		if($section->getSectionOrder() == 1){
			$pmb_classes .= ' pmb-first-section';
		}
	}
	post_class($pmb_classes . ' ' . $class);
	echo 'data-height="' . esc_attr($section->getHeight()) . '" data-depth="' . esc_attr($section->getDepth()) . '"';
}

function pmb_section_wrapper_id(){
	global $post;
	echo 'id="' . esc_attr($post->post_name) . '-wrapper"';
}
/**
 * Echoes out the ID attribute to use on the section.
 */
function pmb_section_id(){
	echo 'id="' . esc_attr(str_replace('%','-',get_the_permalink())) . '"';
}

/**
 * Echoes out the section's title and makes sure to add the CSS class PMB expects (especially important for finding the table of contents.)
 */
function pmb_the_title(){
    $post = get_post();
    if($post instanceof WP_Post){
        $title_from_meta = get_post_meta($post->ID, 'pmb_title',true);
        if($title_from_meta){
            $title =  $title_from_meta;
        } else {
            $title = get_the_title($post);
        }
    }
	return '<h1 class="pmb-title">' . $title . '</h1>';
}

function pmb_design_uses($post_content_thing, $default){
	global $pmb_design;
	$post_content = $pmb_design->getSetting('post_content');
	if(! $post_content){
		return $default;
	}
	return in_array($post_content_thing, $post_content);
}



/**
 * Gets the template options select input HTML
 * @param $selected_template
 * @param Project $project
 *
 * @return string
 */
function pmb_section_template_selector($selected_template, Project $project){
	$options = $project->getSectionTemplateOptions();
	$html = '<select class="pmb-template">';
	foreach($options as $value => $display_text){
		$html .= '<option value="' . esc_attr($value) . '" ' . selected($value, $selected_template, false) . '>' . $display_text . '</option>';
	}
	$html .= '</select>';
	return $html;
}

/**
 * @param WP_Post_Type $post_type
 *
 * @return string HTML for the post type's icon
 */
function pmb_post_type_icon_html(WP_Post_Type $post_type){
    $icon = $post_type->menu_icon;
	if ( empty( $icon ) ) {
	    $icon = 'dashicons-media-default';
    }
    $img = '<img src="' . $icon . '" alt="" />';
	$img_style = '';
	$img_class = '';
    if ( 'none' === $icon || 'div' === $icon ) {
        $img = '<br />';
    } elseif ( 0 === strpos( $icon, 'data:image/svg+xml;base64,' ) ) {
        $img       = '<br />';
        $img_style = ' style=\'background-image:url("' . esc_attr( $icon ) . '") !important;\'';
        $img_class = 'pmb-svg-icon svg';
    } elseif ( 0 === strpos( $icon, 'dashicons-' ) ) {
        $img       = '<br />';
        $img_class = ' dashicons ' . sanitize_html_class( $icon );
	}
    return "<div class='{$img_class}'{$img_style}>{$img}</div>";
}