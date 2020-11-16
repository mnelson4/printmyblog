<?php

namespace Twine\forms\strategies\validation;

use Twine\forms\helpers\ValidationError;

/**
 * MaxLengthValidation
 *
 * Validates that the normalized value is smaller than max length
 *
 * @package         Event Espresso
 * @subpackage  Expression package is undefined on line 19, column 19 in Templates/Scripting/PHPClass.php.
 * @author              Mike Nelson
 */
class MaxLengthValidation extends ValidationBase
{

    protected $max_length;

    public function __construct($validation_error_message = null, $max_length = INF)
    {
        $this->max_length = $max_length;
        if ($validation_error_message === null) {
            $validation_error_message = sprintf(
                __('Input is too long. Maximum number of characters is %1$s', 'print-my-blog'),
                $max_length
            );
        }
        parent::__construct($validation_error_message);
    }

    /**
     * @param $normalized_value
     */
    public function validate($normalized_value)
    {
        if (
            $this->max_length !== INF &&
                $normalized_value &&
                is_string($normalized_value) &&
                 strlen($normalized_value) > $this->max_length
        ) {
            throw new ValidationError($this->getValidationErrorMessage(), 'maxlength');
        }
    }

    /**
     * @return array
     */
    public function getJqueryValidationRuleArray()
    {
        if ($this->max_length !== INF) {
            return array(
                'maxlength' => $this->max_length,
                'messages' => array(
                    'maxlength' => $this->getValidationErrorMessage()
                )
            );
        } else {
            return array();
        }
    }
}
