<?php

namespace PrintMyBlog\orm\managers;

use PrintMyBlog\db\TableManager;
use PrintMyBlog\orm\entities\ExternalResource;
use stdClass;

/**
 * Class ExternalResourceManager
 * @package PrintMyBlog\orm\managers
 */
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
            // todo: cache
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
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
            // todo: cache
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
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
        // todo: cache
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return (bool)$wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*) FROM ' . $wpdb->prefix . TableManager::EXTERNAL_RESOURCE_TABLE . ' WHERE external_url=%s LIMIT 1',
                $external_url
            )
        );
    }

    /**
     * Map external URLS ro local one.
     * @param string $external_url
     * @param string $filename
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
     * @param ExternalResource $external_resource
     * @return int the ID of the saved row
     */
    public function save(ExternalResource $external_resource)
    {
        global $wpdb;
        if ($external_resource->getID()) {
            // Custom table needs direct query.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->update(
                $wpdb->prefix . TableManager::EXTERNAL_RESOURCE_TABLE,
                $external_resource->properties(),
                $external_resource->wpdbPropertyFormats(),
                [
                    'ID' => $external_resource->getID(),
                ],
                [
                    '%d',
                ]
            );
            return $external_resource->getID();
        } else {
            // Custom table needs direct query.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            return (int)$wpdb->insert(
                $wpdb->prefix . TableManager::EXTERNAL_RESOURCE_TABLE,
                $external_resource->properties(),
                $external_resource->wpdbPropertyFormats()
            );
        }
    }

    /**
     * Truncates the table storing all the information about cached items.
     */
    public function clear()
    {
        global $wpdb;
        // Custom table needs direct query.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . TableManager::EXTERNAL_RESOURCE_TABLE);
    }
}
