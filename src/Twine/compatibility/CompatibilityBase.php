<?php

namespace Twine\compatibility;

/**
 * Class Base
 *
 * Base class for setting hooks regarding compatibility with other plugins or themes.
 *
 * @package     Twine
 * @author         Mike Nelson
 *
 */
abstract class CompatibilityBase
{
    abstract public function setHooks();

    /**
     * Sets hooks to modify a PMB request
     */
    public function setRenderingHooks(){}
}
// End of file Base.php
// Location: Twine\compatibility/Base.php
