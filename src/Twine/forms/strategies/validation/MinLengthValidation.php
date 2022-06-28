<?php

namespace Twine\forms\strategies\validation;

use Twine\forms\helpers\ValidationError;

/**
 * MinLengthValidation
 *
 * Validates that the normalized value is at least the specified length
 *
 * @package         Event Espresso
 * @subpackage  Expression package is undefined on line 19, column 19 in Templates/Scripting/PHPClass.php.
 * @author              Mike Nelson
 */
class MinLengthValidation extends ValidationBase
{
    /**
     * @var int
     */
    protected $min_length;

    /**
     * MinLengthValidation constructor.
     * @param null $validation_error_message
     * @param int $min_length
     */
    public function __construct($validation_error_message = null, $min_length = 0)
    {
        $this->min_length = $min_length;
        parent::__construct($validation_error_message);
    }

    /**
     * Validates string length requirement met.
     * @param string $normalized_value
     * @throws ValidationError
     */
    public function validate($normalized_value)
    {
        if (
            $this->min_length > 0 &&
                $normalized_value &&
                is_string($normalized_value) &&
                strlen($normalized_value) < $this->min_length
        ) {
            throw new ValidationError($this->getValidationErrorMessage(), 'minlength');
        }
    }

    /**
     * @return array
     */
    public function getJqueryValidationRuleArray()
    {
        return array(
            'minlength' => $this->min_length,
            'messages' => array(
                'minlength' => $this->getValidationErrorMessage(),
            ),
        );
    }
}
