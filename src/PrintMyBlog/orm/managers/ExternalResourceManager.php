<?php

namespace PrintMyBlog\orm\managers;

use PrintMyBlog\db\TableManager;
use PrintMyBlog\orm\entities\ExternalResource;
use stdClass;

class ExternalResourceManager
{

    /**
     * @param stdClass $row
     *
     * @return ExternalResource|null
     */
    public function createObjFromRow($row)
    {
        if ($row) {
            return new ExternalResource($row);
        }
        return null;
    }

    /**
     * Gets a row by the external URL
     * @return ExternalResource
     */
    public function getByExternalUrl($external_resource_url)
    {
        global $wpdb;
        return $this->createObjFromRow(
            $wpdb->get_row(
                $wpdb->prepare(
                    'SELECT * FROM ' . $wpdb->prefix . TableManager::EXTERNAL_RESOURCE_TABLE . ' WHERE external_url=%s LIMIT 1',
                    $external_resource_url
                )
            )
        );
    }

    /**
     * Gets the mapping between all external resources and cached items
     * @return \stdClass[]
     */
    public function getAllMapping()
    {
        global $wpdb;
        return $this->createObjsFromRows(
            $wpdb->get_results(
                'SELECT * FROM ' . $wpdb->prefix . TableManager::EXTERNAL_RESOURCE_TABLE
            )
        );
    }

    /**
     * @param stdClass[] $rows
     * @return ExternalResource[]
     */
    protected function createObjsFromRows($rows)
    {
        $objs = [];
        foreach ($rows as $row) {
            $objs[] = $this->createObjFromRow($row);
        }
        return $objs;
    }

    /**
     * Whether that resource is already cached or not.
     * @param $external_url
     * @return bool
     */
    public function cached($external_url)
    {
        global $wpdb;
        return (bool)$wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*) FROM ' . $wpdb->prefix . TableManager::EXTERNAL_RESOURCE_TABLE . ' WHERE external_url=%s LIMIT 1',
                $external_url
            )
        );
    }

    /**
     * @param $external_url
     * @param $filename
     * @return int
     */
    public function map($external_url, $filename)
    {
        $external_resource = $this->getByExternalUrl($external_url);
        if (! $external_resource) {
            $external_resource = new ExternalResource(
                [
                    'ID' => null,
                    'external_url' => $external_url,
                    'copy_filename' => $filename,
                ]
            );
        }
        return $this->save($external_resource);
    }

    /**
     * @param ExternalResource $externalResource
     * @return int the ID of the saved row
     */
    public function save(ExternalResource $externalResource)
    {
        global $wpdb;
        if ($externalResource->getID()) {
            $wpdb->update(
                $wpdb->prefix . TableManager::EXTERNAL_RESOURCE_TABLE,
                $externalResource->properties(),
                $externalResource->wpdbPropertyFormats(),
                [
                    'ID' => $externalResource->getID(),
                ],
                [
                    '%d',
                ]
            );
            return $externalResource->getID();
        } else {
            return (int)$wpdb->insert(
                $wpdb->prefix . TableManager::EXTERNAL_RESOURCE_TABLE,
                $externalResource->properties(),
                $externalResource->wpdbPropertyFormats()
            );
        }
    }

    /**
     * Truncates the table storing all the information about cached items.
     */
    public function clear()
    {
        global $wpdb;
        $wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . TableManager::EXTERNAL_RESOURCE_TABLE);
    }
}
