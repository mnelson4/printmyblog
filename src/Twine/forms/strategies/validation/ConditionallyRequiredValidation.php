<?php

namespace Twine\forms\strategies\validation;

use Twine\forms\helpers\ImproperUsageException;
use Twine\forms\helpers\ValidationError;
use Twine\forms\inputs\FormInputBase;
use Twine\forms\strategies\display\SelectDisplay;

/**
 * Class ConditionallyRequiredValidation
 * For having inputs' requirement depend on the value of another input in the form
 * @package             Event Espresso
 * @subpackage  core
 * @author              Mike Nelson
 * @since               4.6
 *
 */
class ConditionallyRequiredValidation extends ValidationBase
{

    /**
     * Array describing conditions necessary to make the input required.
     * This is used to derive a jquery dependency expression (see http://jqueryvalidation.org/required-method)
     * or jquery callback; and server-side logic to determine if the field is necessary.
     * @var array
     */
    protected $requirement_conditions;



    /**
     * @param string $validation_error_message
     * @param array $requirement_conditions
     */
    public function __construct($validation_error_message = null, $requirement_conditions = array())
    {
        if (! $validation_error_message) {
            $validation_error_message = __('This field is required.', 'print-my-blog');
        }
        $this->setRequirementConditions($requirement_conditions);
        parent::__construct($validation_error_message);
    }



    /**
     * Just checks the field isn't blank, provided the requirement conditions
     * indicate this input is still required
     *
     * @param string $normalized_value
     * @return bool
     * @throws \Error
     * @throws ValidationError
     */
    public function validate($normalized_value)
    {
        if (
            (
                $normalized_value === ''
                || $normalized_value === null
                || $normalized_value === array()
            )
            && $this->inputSsRequiredServerSide()
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
            'required' => $this->getJqueryRequirementValue(),
            'messages' => array(
                'required' => $this->getValidationErrorMessage(),
            ),
        );
    }

    /**
     * Sets the "required conditions". This should be an array, its top-level key
     * is the name of a field, its value is an array. This 2nd level array has two items:
     * the first is the operator (for now only '=' is accepted), and teh 2nd argument is the
     * the value the field should be in order to make the field required.
     * Eg array( 'payment_type' => array( '=', 'credit_card' ).
     *
     * @param array $requirement_conditions
     */
    public function setRequirementConditions($requirement_conditions)
    {
        $this->requirement_conditions = (array) $requirement_conditions;
    }

    /**
     * Gets the array that describes when the related input should be required.
     * see set_requirement_conditions for a description of how it should be formatted
     * @return array
     */
    public function getRequirementConditions()
    {
        return $this->requirement_conditions;
    }



    /**
     * Gets jQuery dependency expression used for client-side validation
     * Its possible this could also return a javascript callback used for determining
     * if the input is required or not. That is not yet implemented, however.
     *
     * @return string see http://jqueryvalidation.org/required-method for format
     * @throws \Error
     */
    protected function getJqueryRequirementValue()
    {
        $requirement_value = '';
        $conditions = $this->getRequirementConditions();
        if (! is_array($conditions)) {
            throw new ImproperUsageException(
                sprintf(
                    // translators: 1: name of input.
                    __('Input requirement conditions must be an array. You provided %1$s', 'print-my-blog'),
                    $this->input->name()
                )
            );
        }
        if (count($conditions) > 1) {
            throw new ImproperUsageException(
                sprintf(
                    // translators: 1: name of input.
                    __('Required Validation Strategy does not yet support multiple conditions. You should add it! The related input is %1$s', 'print-my-blog'),
                    $this->input->name()
                )
            );
        }
        foreach ($conditions as $input_path => $op_and_value) {
            $input = $this->input->findSectionFromPath($input_path);
            if (! $input instanceof FormInputBase) {
                throw new ImproperUsageException(
                    sprintf(
                        // translators: 1: name of input, 2: path to input
                        __('Error encountered while setting requirement condition for input %1$s. The path %2$s does not correspond to a valid input', 'print-my-blog'),
                        $this->input->name(),
                        $input_path
                    )
                );
            }
            list( $op, $value ) = $this->validateOpAndValue($op_and_value);
            // ok now the jquery dependency expression depends on the input's display strategy.
            if (! $input->getDisplayStrategy() instanceof SelectDisplay) {
                throw new ImproperUsageException(
                    sprintf(
                        // translators: 1: input name, 2: classname, 3: other input name
                        __('Required Validation Strategy can only depend on another input which uses the SelectDisplay, but you specified a field "%1$s" that uses display strategy "%2$s". If you need others, please add support for it! The related input is %3$s', 'print-my-blog'),
                        $input->name(),
                        get_class($input->getDisplayStrategy()),
                        $this->input->name()
                    )
                );
            }
            $requirement_value = $input->htmlId(true) . ' option[value="' . $value . '"]:selected';
        }
        return $requirement_value;
    }



    /**
     * Returns whether or not this input is required based on the _requirement_conditions
     * (not whether or not the input passes validation. That's for the validate method
     * to decide)
     *
     * @return boolean
     * @throws ImproperUsageException
     */
    protected function inputSsRequiredServerSide()
    {
        $meets_all_requirements = true;
        $conditions = $this->getRequirementConditions();
        foreach ($conditions as $input_path => $op_and_value) {
            $input = $this->input->findSectionFromPath($input_path);
            if (! $input instanceof FormInputBase) {
                throw new ImproperUsageException(
                    sprintf(
                        // translators: 1: input name, 2: path to input
                        __('Error encountered while setting requirement condition for input %1$s. The path %2$s does not correspond to a valid input', 'print-my-blog'),
                        $this->input->name(),
                        $input_path
                    )
                );
            }
            list( $op, $value ) = $this->validateOpAndValue($op_and_value);
            switch ($op) {
                case '=':
                default:
                    $meets_all_requirements = $input->normalizedValue() === $value;
            }
            if (! $meets_all_requirements) {
                break;
            }
        }
        return $meets_all_requirements;
    }



    /**
     * Verifies this is an array with keys 0 and 1, where key 0 is a usable
     * operator (initially just '=') and key 1 is something that can be cast to a string
     *
     * @param array $op_and_value
     * @return array
     * @throws ImproperUsageException
     */
    protected function validateOpAndValue($op_and_value)
    {
        if (! isset($op_and_value[0], $op_and_value[1])) {
                throw new ImproperUsageException(
                    sprintf(
                        // translators: 1: input name
                        __('Required Validation Strategy conditions array\'s value must be an array with two elements: an operator, and a value. It didn\'t. The related input is %1$s', 'print-my-blog'),
                        $this->input->name()
                    )
                );
        }
            $operator = $op_and_value[0];
            $value = (string) $op_and_value[1];
        if ($operator !== '=') {
            throw new ImproperUsageException(
                sprintf(
                    // translators: 1: input name
                    __('Required Validation Strategy conditions can currently only use the equals operator. If you need others, please add support for it! The related input is %1$s', 'print-my-blog'),
                    $this->input->name()
                )
            );
        }
            return array( $operator, $value );
    }
}
