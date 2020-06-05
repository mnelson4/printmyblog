<?php

namespace Twine\controllers;

/**
 * Class BaseController
 *
 * Classes that
 *
 * @package     Twine
 * @author         Mike Nelson
 * @since         1.0.0
 *
 */
abstract class BaseController
{
    /**
     * Sets hooks needed for this controller to execute its logic.
     * @since 1.0.0
     */
    abstract public function setHooks();

    /**
     * Helper for getting a value from the request, or setting a default.
     * @since 2.2.3
     * @param $query_param_name
     * @param $default
     * @return mixed
     */
    protected function getFromRequest($query_param_name, $default)
    {
        if (isset($_GET[$query_param_name])) {
            return isset($_GET[$query_param_name]) ? $_GET[$query_param_name] : $default;
        } else {
            $query_param_name = str_replace('-', '_', $query_param_name);
            return isset($_GET[$query_param_name]) ? $_GET[$query_param_name] : $default;
        }
    }
}
// End of file BaseController.php
// Location: Twine\controllers/BaseController.php
