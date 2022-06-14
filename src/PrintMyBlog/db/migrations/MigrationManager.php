<?php

namespace PrintMyBlog\db\migrations;

use Twine\db\migrations\MigrationManagerBase;

/**
 * Class MigrationManager
 * @package PrintMyBlog\db\migrations
 */
class MigrationManager extends MigrationManagerBase
{

    /**
     * @return string[]
     */
    public function getMigrationInfos()
    {
        return [
            '3.2.3' => 'PrintMyBlog\db\migrations\Migration3_2_3',
        ];
    }
}
