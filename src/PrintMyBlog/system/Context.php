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
                'PrintMyBlog\system\Context' => self::REUSE
            ],
	        'PrintMyBlog\system\Activation' => [
                'Twine\system\RequestType' => self::REUSE,
                'PrintMyBlog\db\TableManager' => self::REUSE,
                'PrintMyBlog\system\Capabilities' => self::REUSE,
		        'PrintMyBlog\services\DesignRegistry' => self::REUSE
            ],
	        'Twine\system\RequestType'         => [
                'Twine\system\VersionHistory' => self::REUSE,
                'pmb_activation'
            ],
	        'Twine\system\VersionHistory'      => [
                PMB_VERSION,
                'pmb_previous_version',
                'pmb_version_history'
            ],
	        'PrintMyBlog\controllers\PmbAdmin' => [
		        'PrintMyBlog\db\PostFetcher'              => self::REUSE,
		        'PrintMyBlog\db\PartFetcher'              => self::REUSE,
		        'PrintMyBlog\orm\managers\ProjectManager' => self::REUSE,
		        'PrintMyBlog\domain\FileFormats'          => self::REUSE,
            ],
	        'PrintMyBlog\controllers\Ajax'     => [
	        	'PrintMyBlog\orm\managers\ProjectManager' => self::REUSE
	        ],
	        'PrintMyBlog\orm\entities\Project'          => [
		        'PrintMyBlog\db\PartFetcher'     => self::REUSE,
		        'PrintMyBlog\domain\FileFormats' => self::REUSE,
		        'PrintMyBlog\orm\managers\DesignManager' => self::REUSE
	        ],
	        'PrintMyBlog\services\DesignRegistry' => [
	        	'PrintMyBlog\orm\managers\DesignManager' => self::REUSE,
		        'PrintMyBlog\services\DesignTemplateRegistry' => self::REUSE
	        ],
	        'PrintMyBlog\orm\entities\Design' => [
	        	'PrintMyBlog\services\DesignTemplateRegistry' => self::REUSE
	        ]
        ];
    }


}