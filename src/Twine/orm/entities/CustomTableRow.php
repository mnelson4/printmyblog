<?php

namespace Twine\orm\entities;

/**
 * Class CustomTableRow
 * @package Twine\orm\entities
 */
abstract class CustomTableRow
{
    /**
     * @var array keys are field names, values are their values
     */
    protected $fields;

    /**
     * CustomTableRow constructor.
     * @param array|object $wpdb_row
     */
    public function __construct($wpdb_row = null)
    {
        if ($wpdb_row) {
            $this->fields = (array) $wpdb_row;
        } else {
            $this->fields = [];
        }
    }

    /**
     * Returns the id field's value.
     * @return mixed
     */
    public function getId()
    {
        if (! isset($this->fields['id'])) {
            return null;
        }
        return $this->fields['id'];
    }

    /**
     * Geta field's value.
     * @param string $field_name
     * @return mixed
     */
    public function get($field_name)
    {
        return $this->fields[$field_name];
    }

    /**
     * Set a field's value.
     * @param string $field_name
     * @param mixed $new_value
     */
    public function set($field_name, $new_value)
    {
        $this->fields[$field_name] = $new_value;
    }

    /**
     * @return array keys are field names, values are their values
     */
    public function fields()
    {
        return $this->fields;
    }

    /**
     * @return array same as fields(), except removes the 'id' field
     */
    public function fieldsExceptId()
    {
        $fields = $this->fields();
        unset($fields['id']);
        return $fields;
    }
}
