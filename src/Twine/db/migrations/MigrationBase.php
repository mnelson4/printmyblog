<?php

namespace Twine\db\migrations;

abstract class MigrationBase
{
    /**
     * Performs the migration
     * @return bool
     */
    abstract public function perform();
}
