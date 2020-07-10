<?php

namespace PrintMyBlog\system;

use Twine\system\Context as BaseContext;

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
                'Twine\system\VersionHistory' => self::REUSE,
                'Twine\system\RequestType' => self::REUSE,
                'PrintMyBlog\system\CustomPostTypes' => self::REUSE
            ],
            'PrintMyBlog\system\Activation' => [
                'Twine\system\RequestType' => self::REUSE,
                'PrintMyBlog\db\TableManager' => self::REUSE,
                'PrintMyBlog\system\Capabilities' => self::REUSE
            ],
            'Twine\system\RequestType' => [
                'Twine\system\VersionHistory' => self::REUSE,
                'pmb_activation'
            ],
            'Twine\system\VersionHistory' => [
                PMB_VERSION,
                'pmb_previous_version',
                'pmb_version_history'
            ],
            'PrintMyBlog\controllers\PmbAdmin' => [
                'PrintMyBlog\db\PostFetcher' => self::REUSE,
                'PrintMyBlog\db\PartFetcher' => self::REUSE,
            ]
        ];
    }


}