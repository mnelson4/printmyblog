<?php

namespace PrintMyBlog\system;

use Twine\services\init\Context as BaseContext;

/**
 * Class Context
 *
 * Stores instances of the classes used by Print My Blog for dependency injection.
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
class Context extends BaseContext
{
    /**
     * Sets the dependencies in the context. Keys are classnames, values are an array
     * whose keys are classnames dependend on, and values are either self::USE_NEW or self::REUSE.
     * Classes
     */
    protected function setDependencies(){
        $this->deps = [
            'PrintMyBlog\system\Init' => [
                'PrintMyBlog\system\Activation' => self::REUSE,
                'PrintMyBlog\system\VersionHistory' => self::REUSE,
            ],
            'PrintMyBlog\system\Activation' => [
                'PrintMyBlog\system\RequestType' => self::REUSE,
            ],
            'PrintMyBlog\system\RequestType' => [
                'PrintMyBlog\system\VersionHistory' => self::REUSE
            ],
        ];
    }


}