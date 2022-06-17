<?php

namespace Twine\forms\strategies\display;

/**
 * Class DatepickerDisplay
 * @package Twine\forms\strategies\display
 */
class DatepickerDisplay extends TextInputDisplay
{
    /**
     * DatepickerDisplay constructor.
     * @param string $type
     */
    public function __construct($type = 'text')
    {
        parent::__construct('datepicker');
    }
}
