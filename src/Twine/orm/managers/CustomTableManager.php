<?php

namespace Twine\orm\managers;

use ReflectionClass;
use Twine\orm\entities\CustomTableRow;

abstract class CustomTableManager
{
    /**
     * format to use for DateTime functions when we want the format used by MySQL DateTimes
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
    public function __construct()
    {
    }

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
            $success = $wpdb->update(
                $this->getFullTableName(),
                $entity->fieldsExceptId(),
                [
                    'id' => $entity->getId()
                ],
                array_map(
                    function ($item) {
                        return '%s';
                    },
                    $entity->fieldsExceptId()
                ),
                [
                    '%d'
                ]
            );
            return $success;
        } else {
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
