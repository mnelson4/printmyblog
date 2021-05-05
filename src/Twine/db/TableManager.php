<?php

namespace Twine\db;

abstract class TableManager
{

    /**
     *
     * @return void
     */
    abstract public function installTables();
    abstract public function dropTables();
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
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function dropTable($table_name)
    {
        global $wpdb;
        return $wpdb->query('DROP TABLE ' . $wpdb->prefix . $table_name);
    }
}
