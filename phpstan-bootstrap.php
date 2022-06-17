<?php
define('TWINE_MAIN_FILE', __FILE__);
define('PMB_DIR', __DIR__ . '/');
define('PMB_VENDOR_DIR', PMB_DIR . 'vendor/');
require PMB_VENDOR_DIR . 'autoload.php';

// Add WPML functions for PHPstan
if (! function_exists('wpml_get_active_languages')) {
    function wpml_get_active_languages()
    {
    }

    function wpml_get_default_language()
    {
    }

    function wpml_object_id_filter()
    {
    }

    class SitePress
    {
        function get_default_language()
        {
        }

        function get_language_details()
        {
        }
    }

    class WPML_Post_Status_Display
    {
        function __construct()
        {
        }

        function get_status_html()
        {
        }
    }
}