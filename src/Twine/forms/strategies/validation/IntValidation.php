<?php

namespace Twine\forms\strategies\validation;

/**
 * IntValidation
 *
 * @package         Event Espresso
 * @subpackage  Expression package is undefined on line 19, column 19 in Templates/Scripting/PHPClass.php.
 * @author              Mike Nelson
 */
class IntValidation extends ValidationBase
{

    /**
     * @param null $validation_error_message
     */
    public function __construct($validation_error_message = null)
    {
        if (! $validation_error_message) {
            $validation_error_message = __("Only digits are allowed.", "print-my-blog");
        }
        parent::__construct($validation_error_message);
    }



    /**
     * @param $normalized_value
     */
    public function validate($normalized_value)
    {
        // this should have already been detected by the normalization strategy
    }



    /**
     * @return array
     */
    public function getJqueryValidationRuleArray()
    {
        return array(
            'number' => true,
            'step' => 1,
            'messages' => array(
                'number' => $this->getValidationErrorMessage(),
                'step' => $this->getValidationErrorMessage()
            )
        );
    }
}
