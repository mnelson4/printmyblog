<?php

/**
 * @param string $slug
 * @param callable $configuration_callback
 */
function pmb_register_design_template($slug, $configuration_callback){
	// slug
	// title
	// format
	// directory
	// options
	// metadata
}

/**
 * Use on action 'pmb_register_designs'. The configuration callback will only be called on requests where necessary.
 * @param string $design_slug
 * @param string $template_slug
 * @param callable $configuration_callback
 */
function pmb_register_design($design_slug, $template_slug, $configuration_callback){
	// slug
	// pretty name
	// preview image
	// design_template_slug
	// description
	// options and metadata
}