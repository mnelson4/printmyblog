<?php

namespace PrintMyBlog\db\migrations;

use PrintMyBlog\system\CustomPostTypes;
use Twine\db\migrations\MigrationBase;

// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
class Migration3_2_3 extends MigrationBase
{
    // phpcs:enable Generic.Files.LineLength.TooLong

    public function perform()
    {
        global $wpdb;
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
