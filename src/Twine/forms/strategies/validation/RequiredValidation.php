<?php

namespace Twine\forms\strategies\validation;

use Twine\forms\helpers\ImproperUsageException;
use Twine\forms\helpers\ValidationError;

/**
 * Class RequiredValidation
 *
 * @package             Event Espresso
 * @subpackage  core
 * @author              Mike Nelson
 * @since               4.6
 *
 */
class RequiredValidation extends ValidationBase
{



    /**
     * @param string|null $validation_error_message
     */
    public function __construct($validation_error_message = null)
    {
        if (! $validation_error_message) {
            $validation_error_message = __('This field is required.', 'print-my-blog');
        }
        parent::__construct($validation_error_message);
    }



    /**
     * Just checks the field isn't blank, provided the requirement conditions
     * indicate this input is still required
     *
     * @param string $normalized_value
     * @return bool
     * @throws ValidationError
     */
    public function validate($normalized_value)
    {
        if (
            $normalized_value === ''
            || $normalized_value === null
            || $normalized_value === array()
        ) {
            throw new ValidationError($this->getValidationErrorMessage(), 'required');
        } else {
            return true;
        }
    }



    /**
     * @return array
     * @throws \Error
     */
    public function getJqueryValidationRuleArray()
    {
        return array(
            'required' => true,
            'messages' => array(
                'required' => $this->getValidationErrorMessage(),
            ),
        );
    }
}
