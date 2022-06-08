<?php

namespace Twine\forms\strategies\validation;

use Twine\forms\helpers\ImproperUsageException;
use Twine\forms\helpers\ValidationError;
use Twine\forms\inputs\FormInputWithOptionsBase;

/**
 * Class EnumValidation
 *
 * @package             Event Espresso
 * @subpackage  core
 * @author              Mike Nelson
 * @since               4.6
 *
 */
class EnumValidation extends ValidationBase
{

    /**
     * Check that the value is in the allowed list
     * @param string $normalized_value
     * @throws ImproperUsageException
     * @throws ValidationError
     * @return boolean
     */
    public function validate($normalized_value)
    {
        parent::validate($normalized_value);
        if (! $this->input instanceof FormInputWithOptionsBase) {
            throw new ImproperUsageException(
                __('Cannot use Enum Validation Strategy with an input that doesn\'t have options', 'print-my-blog')
            );
        }
        $enum_options = $this->input->flatOptions();
        if ($normalized_value === true) {
            $normalized_value = 1;
        } elseif ($normalized_value === false) {
            $normalized_value = 0;
        }
        if ($normalized_value !== null && ! array_key_exists($normalized_value, $enum_options)) {
            throw new ValidationError(
                $this->getValidationErrorMessage(),
                'invalid_enum_value'
            );
        } else {
            return true;
        }
    }

    /**
     * If we are using the default validation error message, make it dynamic based
     * on the allowed options.
     * @return string
     */
    public function getValidationErrorMessage()
    {
        $parent_validation_error_message = parent::getValidationErrorMessage();
        if (! $parent_validation_error_message) {
            $enum_options = $this->input instanceof FormInputWithOptionsBase ? $this->input->flatOptions() : '';
            return sprintf(
                // translators: 1: list of options.
                __('This is not allowed option. Allowed options are %s.', 'print-my-blog'),
                implode(', ', $enum_options)
            );
        } else {
            return $parent_validation_error_message;
        }
    }
}
