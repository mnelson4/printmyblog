<?php


namespace PrintMyBlog\factories;


use PrintMyBlog\system\Context;

class FileFormatFactory {
	public function create($args)
	{
		return Context::instance()->use_new(
			'PrintMyBlog\entities\FileFormat',
		[$args]
		);
	}
}