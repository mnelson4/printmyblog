<?php

namespace PrintMyBlog\factories;

use PrintMyBlog\entities\FileFormat;
use PrintMyBlog\system\Context;

/**
 * Class FileFormatFactory
 * @package PrintMyBlog\factories
 */
class FileFormatFactory
{
    /**
     * @param array $args
     * @return FileFormat
     */
    public function create($args)
    {
        return Context::instance()->useNew(
            'PrintMyBlog\entities\FileFormat',
            [$args]
        );
    }
}
