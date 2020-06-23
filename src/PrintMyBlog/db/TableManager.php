<?php

namespace PrintMyBlog\db;

/**
 * Class InstallTables
 *
 * Installs the database tables needed by Print My Blog
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 *
 */
class TableManager
{

    /**
     * Ensures PMB's tables exist.
     */
    public function installTables()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta(
          'CREATE TABLE ' . $wpdb->prefix. 'pmb_doc_divisions (
                doc_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                post_id bigint(20) UNSIGNED NOT NULL DEFAULT \'0\',
                parent_id bigint(20) UNSIGNED NULL DEFAULT \'0\',
                doc_order int(11) NOT NULL DEFAULT \'0\',
                type varchar(50) NOT NULL DEFAULT \'0\',
                PRIMARY KEY  (doc_id),
                KEY sorted (doc_id,doc_order)
            ) ' . $charset_collate . '
        ;'
        );
    }
}