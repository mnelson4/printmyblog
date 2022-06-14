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
     * @param string $query_param_name
     * @param mixed $default
     * @return mixed
     */
    protected function getFromRequest($query_param_name, $default)
    {
        // Nonce verification must be done before calling this. Sanitization on these inputs will occur later in forms code.
        // phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        if (isset($_GET[$query_param_name])) {
            return isset($_GET[$query_param_name]) ? $_GET[$query_param_name] : $default;
        } else {
            $query_param_name = str_replace('-', '_', $query_param_name);
            return isset($_GET[$query_param_name]) ? $_GET[$query_param_name] : $default;
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
    }
}
// End of file BaseController.php
// Location: Twine\controllers/BaseController.php
