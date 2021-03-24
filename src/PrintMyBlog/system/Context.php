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
    protected function setDependencies()
    {
        $this->deps = [
            'PrintMyBlog\system\Activation' => [
                'Twine\system\RequestType' => self::REUSE,
                'PrintMyBlog\db\TableManager' => self::REUSE,
                'PrintMyBlog\system\Capabilities' => self::REUSE,
                'PrintMyBlog\services\DesignRegistry' => self::REUSE,
                'PrintMyBlog\domain\DefaultProjectContents' => self::REUSE
            ],
            'Twine\system\RequestType'      => [
                'Twine\system\VersionHistory' => self::REUSE,
                'pmb_activation'
            ],
            'Twine\system\VersionHistory'   => [
                PMB_VERSION,
                'pmb_previous_version',
                'pmb_version_history'
            ],
            'PrintMyBlog\controllers\Admin' => [
                'PrintMyBlog\db\PostFetcher'              => self::REUSE,
                'PrintMyBlog\orm\managers\ProjectSectionManager'              => self::REUSE,
                'PrintMyBlog\orm\managers\ProjectManager' => self::REUSE,
                'PrintMyBlog\services\FileFormatRegistry'   => self::REUSE,
                'PrintMyBlog\orm\managers\DesignManager' => self::REUSE,
                'PrintMyBlog\db\TableManager' => self::REUSE,
                'PrintMyBlog\services\SvgDoer' => self::REUSE,
                'Twine\services\notifications\OneTimeNotificationManager' => self::REUSE,
                'PrintMyBlog\services\DebugInfo' => self::REUSE,
                'PrintMyBlog\services\PmbCentral' => self::REUSE
            ],
            'PrintMyBlog\services\PersistentNotices' => [
                'WPTRT\AdminNotices\Notices' => self::REUSE,
                'PrintMyBlog\domain\DefaultPersistentNotices' => self::REUSE
            ],
            'PrintMyBlog\controllers\Ajax'  => [
                'PrintMyBlog\orm\managers\ProjectManager' => self::REUSE,
                'PrintMyBlog\services\FileFormatRegistry' => self::REUSE,
                'PrintMyBlog\db\PostFetcher' => self::REUSE,
                'PrintMyBlog\services\PmbCentral' => self::REUSE,
            ],
            'PrintMyBlog\orm\entities\Project'          => [
                'PrintMyBlog\orm\managers\ProjectSectionManager'             => self::REUSE,
                'PrintMyBlog\services\FileFormatRegistry'  => self::REUSE,
                'PrintMyBlog\orm\managers\DesignManager' => self::REUSE,
                'PrintMyBlog\services\config\Config' => self::REUSE,
                'PrintMyBlog\factories\ProjectGenerationFactory' => self::REUSE,
            ],
            'PrintMyBlog\services\DesignRegistry' => [
                'PrintMyBlog\orm\managers\DesignManager' => self::REUSE,
                'PrintMyBlog\services\DesignTemplateRegistry' => self::REUSE
            ],
            'PrintMyBlog\orm\entities\Design' => [
                'PrintMyBlog\services\DesignTemplateRegistry' => self::REUSE
            ],
            'PrintMyBlog\entities\DesignTemplate' => [
                'PrintMyBlog\services\FileFormatRegistry' => self::REUSE,
                'PrintMyBlog\orm\managers\DesignManager' => self::REUSE,
                'PrintMyBlog\services\SectionTemplateRegistry' => self::REUSE,
            ],
            'PrintMyBlog\services\config\Config' => [
                'PrintMyBlog\services\FileFormatRegistry' => self::REUSE,
                'PrintMyBLog\orm\managers\DesignManager' => self::REUSE
            ],
            'PrintMyBlog\entities\ProjectGeneration' => [
                'PrintMyBlog\orm\managers\ProjectSectionManager' => self::REUSE,
                'PrintMyBlog\factories\ProjectFileGeneratorFactory' => self::REUSE
            ],
            'PrintMyBlog\services\generators\PdfGenerator' => [
                'PrintMyBlog\db\PostFetcher' => self::REUSE,
                'PrintMyBlog\compatibility\DetectAndActivate' => self::REUSE
            ],
            'PrintMyBlog\services\FileFormatRegistry' => [
                'PrintMyBlog\factories\FileFormatFactory' => self::REUSE,
            ],
            'PrintMyBlog\entities\FileFormat' => [
                'PrintMyBlog\services\DesignTemplateRegistry' => self::REUSE,
            ],
            'PrintMyBlog\db\PostFetcher' => [
                'PrintMyBlog\system\CustomPostTypes' => self::REUSE
            ],
            'PrintMyBlog\system\CustomPostTypes' => [
                'PrintMyBlog\services\SvgDoer' => self::REUSE
            ],
            'PrintMyBlog\services\DebugInfo' => [
                'PrintMyBlog\orm\managers\ProjectManager' => self::REUSE,
                'PrintMyBlog\orm\managers\DesignManager' => self::REUSE,
            ]
        ];
    }
}
