<?php

namespace Twine\controllers;

/**
 * Class BaseController
 *
 * Description
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
}
// End of file BaseController.php
// Location: Twine\controllers/BaseController.php
