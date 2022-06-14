<?php

namespace PrintMyBlog\db\migrations;

use PrintMyBlog\system\CustomPostTypes;
use Twine\db\migrations\MigrationBase;

// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class Migration3_2_3 extends MigrationBase
{

    public function perform()
    {
        global $wpdb;
        // Only do this once on a singe request.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->update(
            $wpdb->posts,
            [
                'post_status' => 'private',
            ],
            [
                'post_type' => CustomPostTypes::CONTENT,
                'post_status' => 'publish',
            ]
        );
    }
}
