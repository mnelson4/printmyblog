<?php

namespace mnelson4\rest_api_detector;

use Exception;
use WP_Error;

/**
 * Class RestApiDetectorError
 *
 * An error while trying to detect REST API information about a site.
 *
 * @package     Print My Blog
 * @author         Mike Nelson
 *
 */
class RestApiDetectorError extends Exception
{
    /**
     * @var string
     */
    protected $string_code = 'not_set';

    /**
     * @var WP_Error
     */
    protected $wp_error;

    /**
     * RestApiDetectorError constructor.
     * @param WP_Error $wp_error
     */
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

    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    /**
     * @return WP_Error
     */
    public function wp_error()
    {
        //phpcs:enable
        return $this->wp_error;
    }
}
// End of file RestApiDetectorError.php
// Location: mnelson4/RestApiDetectorError.php
