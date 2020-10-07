<?php


namespace PrintMyBlog\exceptions;


use Exception;

class TemplateDoesNotExist extends Exception{
	public function __construct($template_file){
		parent::__construct(
			sprintf(
				__('Template file "%s" should exist but doesn\'t.', 'print-my-blog'),
				$template_file
			)
		);
	}
}