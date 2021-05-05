<?php

namespace PrintMyBlog\db;

/**
 * Class InstallTables
 *
 * Installs the database tables needed by Print My Blog
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 *
 */
class TableManager extends \Twine\db\TableManager
{
    const SECTIONS_TABLE = 'pmb_project_sections';

    /**
     * Ensures PMB's tables exist.
     */
    public function installTables()
    {
        $this->installTable(
            self::SECTIONS_TABLE,
            'ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                project_id bigint(20) UNSIGNED NOT NULL,
                post_id bigint(20) UNSIGNED NOT NULL DEFAULT \'0\',
                parent_id bigint(20) UNSIGNED NULL DEFAULT \'0\',
                section_order int(11) NOT NULL DEFAULT \'0\',
                template varchar(50) NOT NULL DEFAULT \'\',
                placement varchar(15) NOT NULL DEFAULT \'main\',
                height smallint NOT NULL DEFAULT \'0\',
                depth smallint NOT NULL DEFAULT \'0\',
                PRIMARY KEY  (ID),
                KEY sorted (project_id,placement,section_order)'
        );
    }

    public function dropTables()
    {
        $this->dropTable(self::SECTIONS_TABLE);
    }
}
