<?php


namespace PrintMyBlog\db\migrations;


use Twine\db\migrations\MigrationManagerBase;

class MigrationManager extends MigrationManagerBase
{

    /**
     * @return string[]
     */
    public function getMigrationInfos()
    {
        return [
            '3.2.3' => 'PrintMyBlog\db\migrations\Migration3_2_3'
        ];
    }
}