<?php

use PrintMyBlog\services\DesignTemplateRegistry;
use PrintMyBlog\system\Context;

/**
 * @param $file_format_slug
 * @param $args passed into \PrintMyBlog\entities\FileFormat::__construct
 */
function pmb_register_file_format($file_format_slug, $args){
	/**
	 * @var $file_format_registry \PrintMyBlog\services\FileFormatRegistry
	 */
	$file_format_registry = Context::instance()->reuse(
		'PrintMyBlog\services\FileFormatRegistry'
	);
	$file_format_registry->registerFormat(
		$file_format_slug,
		$args
	);
}
/**
 * @param string $slug
 * @param callable $design_template_args_callback that returns the arguments to pass into the new DesignTemplate
 */
function pmb_register_design_template($slug, $design_template_args_callback){
	/**
	 * @var $design_template_registry DesignTemplateRegistry
	 */
	$design_template_registry = Context::instance()->reuse(
		'PrintMyBlog\services\DesignTemplateRegistry'
	);
	$design_template_registry->registerDesignTemplateCallback($slug, $design_template_args_callback);
}

/**
 * Use on action 'pmb_register_designs'. The configuration callback will only be called on requests where necessary.
 * @param string $design_template_slug
 * @param string $design_slug
 * @param callable $design_args_callback returns an array to be passed into PrintMyBlog\services\DesignRegistry::createNewDesign()
 */
function pmb_register_design($design_template_slug, $design_slug, $design_args_callback){
	/**
	 * @var $design_registry \PrintMyBlog\services\DesignRegistry
	 */
	$design_registry = Context::instance()->reuse(
		'PrintMyBlog\services\DesignRegistry'
	);
	$design_registry->registerDesignCallback(
		$design_template_slug,
		$design_slug,
		$design_args_callback
	);
}