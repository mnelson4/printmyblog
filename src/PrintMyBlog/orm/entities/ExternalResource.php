<?php

namespace PrintMyBlog\orm\entities;

use stdClass;

/**
 * Class ExternalResource
 * @package PrintMyBlog\orm\entities
 */
class ExternalResource
{
    /**
     * @var string|int
     */
    protected $ID;

    /**
     * @var string
     */
    protected $external_url;

    /**
     * @var string
     */
    protected $copy_filename;

    /**
     * ExternalResource constructor.
     * @param stdClass|array $db_row
     */
    public function __construct($db_row)
    {
        $db_row = (object)$db_row;
        $this->ID = $db_row->ID;
        $this->external_url = $db_row->external_url;
        $this->copy_filename = $db_row->copy_filename;
    }

    /**
     * @return array
     */
    public function properties()
    {
        return [
            'ID' => $this->getID(),
            'external_url' => $this->getExternalUrl(),
            'copy_filename' => $this->getCopyFilename(),
        ];
    }

    /**
     * @return string[]
     */
    public function wpdbPropertyFormats()
    {
        return [
            '%d',
            '%s',
            '%s',
        ];
    }

    /**
     * @return mixed
     */
    public function getID()
    {
        return $this->ID;
    }

    /**
     * @return mixed
     */
    public function getExternalUrl()
    {
        return $this->external_url;
    }

    /**
     * @return mixed
     */
    public function getCopyFilename()
    {
        return $this->copy_filename;
    }
}
