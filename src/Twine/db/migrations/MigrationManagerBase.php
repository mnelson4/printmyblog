<?php

namespace Twine\db\migrations;

use Twine\system\RequestType;
use Twine\system\VersionHistory;

/**
 * Class MigrationManagerBase
 * @package Twine\db\migrations
 */
abstract class MigrationManagerBase
{
    /**
     * @var RequestType
     */
    protected $request_type;

    /**
     * @var VersionHistory
     */
    protected $version_history;

    /**
     * @var string
     */
    protected $option_prefix;

    /**
     * @var MigrationBase[];
     */
    protected $migrations;

    /**
     * @var MigrationBase[]
     */
    protected $applicable_migrations;

    /**
     * @param RequestType $request_type
     * @param VersionHistory $version_history
     * @param string $option_prefix
     */
    public function inject(RequestType $request_type, VersionHistory $version_history, $option_prefix)
    {
        $this->request_type = $request_type;
        $this->version_history = $version_history;
        $this->option_prefix = $option_prefix;
    }

    /**
     * Performs any quick migrations and remembers so they don't get ran again.
     */
    public function migrate()
    {
        $migrations = $this->getMigrationsToRun();
        foreach ($migrations as $migration) {
            $migration->perform();
        }
        $this->rememberMigrationsRan($migrations);
    }

    /**
     * Gets all the migrations that should be run.
     * @return MigrationBase[]
     */
    protected function getMigrationsToRun()
    {
        return (array)array_diff_key(
            $this->getApplicableMigrations(),
            $this->getMigrationsRan()
        );
    }

    /**
     * @param array $applicable_migrations keys must be the version migrated to.
     */
    protected function rememberMigrationsRan($applicable_migrations)
    {
        $migrations_ran = $this->getMigrationsRan();
        foreach ($applicable_migrations as $version => $migration) {
            $migrations_ran[$version] = current_time('mysql');
        }
        update_option($this->getOptionName(), $migrations_ran, false);
    }

    /**
     * @return array keys are migration versions, values are MySQL datetimes of when they were run
     */
    protected function getMigrationsRan()
    {
        return get_option($this->getOptionName(), []);
    }

    /**
     * @return string
     */
    protected function getOptionName()
    {
        return $this->option_prefix . 'migrations';
    }

    /**
     * Gets all the migrations that should run
     * @return MigrationBase[]
     */
    protected function getApplicableMigrations()
    {
        if ($this->applicable_migrations === null) {
            $this->applicable_migrations = [];
            $current_version = $this->version_history->currentVersion();
            $previous_version = $this->version_history->previousVersion();
            // If this is a brand new install, we shouldn't need to do any migrations right?
            if ($previous_version === null) {
                return [];
            }
            foreach ($this->getMigrationInfos() as $version => $migration_class) {
                if (
                    version_compare($current_version, $version, '<=')
                    && version_compare($version, $previous_version, '>')
                ) {
                    $this->applicable_migrations[$version] = new $migration_class();
                }
            }
        }
        return $this->applicable_migrations;
    }

    /**
     * @return MigrationBase[]|null
     */
    public function allMigrations()
    {
        if ($this->migrations === null) {
            $migration_infos = $this->getMigrationInfos();
            foreach ($migration_infos as $version => $classname) {
                $this->migrations[$version] = new $classname();
            }
        }
        return $this->migrations;
    }

    /**
     * Gets the versions and classes of all migrations. Does not return actua;ly MigrationBases.
     * @return array Keys are the versions, values are classnames.
     */
    abstract public function getMigrationInfos();
}
