<?php

namespace mnelson4\RestApiDetector;

use Exception;
use WP_Error;

/**
 * Class RestApiDetectorError
 *
 * An error while trying to detect REST API information about a site.
 *
 * @package     Print My Blog
 * @author         Mike Nelson
 * @since         $VID:$
 *
 */
class RestApiDetectorError extends Exception
{
    protected $string_code = 'not_set';
    protected $wp_error;
    public function __construct(WP_Error $wp_error)
    {
        $this->string_code = $wp_error->get_error_code();
        $this->wp_error = $wp_error;
        parent::__construct($wp_error->get_error_message());
    }

    /**
     * @since $VID:$
     * @return string
     */
    public function stringCode()
    {
        return $this->string_code;
    }

    /**
     * @since $VID:$
     * @return WP_Error
     */
    //phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function wp_error()
    {
        //phpcs:enable
        return $this->wp_error;
    }
}
// End of file RestApiDetectorError.php
// Location: mnelson4/RestApiDetectorError.php
