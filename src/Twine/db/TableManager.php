<?php

namespace Twine\db;

/**
 * Class TableManager
 * @package Twine\db
 */
abstract class TableManager
{

    /**
     *
     * @return void
     */
    abstract public function installTables();

    /**
     * @return void
     */
    abstract public function dropTables();

    /**
     * @param string $table_name
     * @param string $columns_sql
     */
    public function installTable($table_name, $columns_sql)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        $wpdb_collate = $wpdb->collate;
        $sql =
            "CREATE TABLE {$table_name} (
	         {$columns_sql}
			)
	         COLLATE {$wpdb_collate}";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * @param string $table_name pre-sanitized or hard-coded.
     * @return bool|int|\mysqli_result|resource|null
     */
    public function dropTable($table_name)
    {
        global $wpdb;
        // Drop the table. Caching the results of this would be silly. And of course we want to alter the schema, that's what this method is for.
        // This should only be done on PMB's custom tables, of course.
        // And of course we shouldn't be passing in user input for the table name
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared
        return $wpdb->query('DROP TABLE ' . $wpdb->prefix . $table_name);
    }
}
