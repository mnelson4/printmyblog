<?php

namespace Twine\forms\strategies\validation;

/**
 * Class FloatValidation
 *
 * @package             Event Espresso
 * @subpackage  core
 * @author              Mike Nelson
 * @since               4.6
 *
 */
class FloatValidation extends ValidationBase
{

    /**
     * @param null $validation_error_message
     */
    public function __construct($validation_error_message = null)
    {
        if (! $validation_error_message) {
            $validation_error_message = __(
                "Only numeric characters, commas, periods, and spaces, please!",
                "print-my-blog"
            );
        }
        parent::__construct($validation_error_message);
    }



    /**
     *
     * @param $normalized_value
     */
    public function validate($normalized_value)
    {
        // errors should have been detected by the normalization strategy
    }



    /**
     * @return array
     */
    public function getJqueryValidationRuleArray()
    {
        return array('number' => true, 'messages' => array( 'number' => $this->getValidationErrorMessage() ) );
    }
}
