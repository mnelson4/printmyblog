<?php

namespace Twine\forms\inputs;

/**
 * Fixed_HiddenInput
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Brent Christensen
 */
class FixedHiddenInput extends HiddenInput
{


    /**
     * Fixed Inputs are inputs that do NOT accept user input
     * therefore they will ALWAYS return the default value that was set upon their creation
     * and NO normalization or sanitization will occur because the $_REQUEST value is being ignored
     *
     * @param array $req_data like $_POST
     */
    protected function normalize($req_data)
    {
    }
}
