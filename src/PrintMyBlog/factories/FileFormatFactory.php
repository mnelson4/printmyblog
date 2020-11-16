<?php

namespace PrintMyBlog\factories;

use PrintMyBlog\system\Context;

class FileFormatFactory
{
    public function create($args)
    {
        return Context::instance()->useNew(
            'PrintMyBlog\entities\FileFormat',
            [$args]
        );
    }
}
