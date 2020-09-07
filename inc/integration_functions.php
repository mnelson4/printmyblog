<?php

use PrintMyBlog\services\DesignTemplateRegistry;
use PrintMyBlog\system\Context;

/**
 * @param string $slug
 * @param callable $design_template_args_callback that returns the arguments to pass into the new DesignTemplate
 */
function pmb_register_design_template($slug, $design_template_args_callback){
	/**
	 * @var $template_manager DesignTemplateRegistry
	 */
	$template_manager = Context::instance()->reuse(
		'PrintMyBlog\services\DesignTemplateRegistry'
	);
	$template_manager->registerDesignTemplateCallback($slug, $design_template_args_callback);
}

/**
 * Use on action 'pmb_register_designs'. The configuration callback will only be called on requests where necessary.
 * @param string $design_template_slug
 * @param string $design_slug
 * @param callable $design_args_callback returns an array to be passed into PrintMyBlog\services\DesignRegistry::createNewDesign()
 */
function pmb_register_design($design_template_slug, $design_slug, $design_args_callback){
	/**
	 * @var $design_manager \PrintMyBlog\services\DesignRegistry
	 */
	$design_manager = Context::instance()->reuse(
		'PrintMyBlog\services\DesignRegistry'
	);
	$design_manager->registerDesignCallback(
		$design_template_slug,
		$design_slug,
		$design_args_callback
	);
}