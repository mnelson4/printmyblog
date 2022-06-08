<?php

namespace Twine\orm\managers;

use ReflectionClass;
use Twine\orm\entities\CustomTableRow;

/**
 * Class CustomTableManager
 * @package Twine\orm\managers
 */
abstract class CustomTableManager
{
    /**
     * Format to use for DateTime functions when we want the format used by MySQL DateTimes
     */
    const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';
    /**
     * @var string without the wpdb prefix, that gets added last-minute
     */
    protected $table_name;

    /**
     * @var string fully qualified classname of the entities representing database rows
     * (which are child instances of Twine\orm\entities\CustomTableRow)
     */
    protected $entity_classname;

    /**
     * @return string gets the classname from the property $this->entity_classname, which child classes should define.
     */
    public function getEntityClassname()
    {
        return $this->entity_classname;
    }

    /**
     * Gets the name of the table, including $wpdb->prefix
     * (which is added last moment, in case of blog switching)
     * @return string
     */
    public function getFullTableName()
    {
        global $wpdb;
        return $wpdb->prefix . $this->table_name;
    }

    /**
     * @param int $id
     * @return CustomTableRow
     */
    abstract public function getById($id);

    /**
     * @param \stdClass $db_row
     *
     * @return CustomTableRow
     * @throws \ReflectionException
     */
    public function createEntityFromRow(\stdClass $db_row)
    {
        $entity_class = $this->getEntityClassname();
        $reflection = new ReflectionClass($entity_class);
        return $reflection->newInstanceArgs([$db_row]);
    }

    /**
     * @param CustomTableRow $entity
     *
     * @return bool|int
     */
    public function save(CustomTableRow $entity)
    {
        global $wpdb;
        if ($entity->getId()) {
            // Caching the result of a save is silly; and because we're operating on custom tables,
            // direct DB queries are the only option.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
            $success = $wpdb->update(
                $this->getFullTableName(),
                $entity->fieldsExceptId(),
                [
                    'id' => $entity->getId(),
                ],
                array_map(
                    function ($item) {
                        return '%s';
                    },
                    $entity->fieldsExceptId()
                ),
                [
                    '%d',
                ]
            );
            return $success;
        } else {
            // use direct query for custom tables of course
            //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $success = $wpdb->insert(
                $this->getFullTableName(),
                $entity->fields(),
                array_map(
                    function ($item) {
                        return '%s';
                    },
                    $entity->fields()
                )
            );
            if ($success) {
                $entity->set('id', $wpdb->insert_id);
                return $entity->getId();
            }
        }

        return false;
    }
}
