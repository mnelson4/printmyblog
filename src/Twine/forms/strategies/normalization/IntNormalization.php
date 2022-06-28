<?php

namespace Twine\forms\strategies\normalization;

use Twine\forms\helpers\ValidationError;
use Twine\forms\strategies\validation\IntValidation;

/**
 * IntNormalization
 * Casts the string to an int. If the user inputs anything but numbers, we growl at them
 *
 * @package               Event Espresso
 * @subpackage
 * @author                Mike Nelson
 */
class IntNormalization extends NormalizationBase
{

    /**
     * Regex pattern that matches for the following:
     *      * optional negative sign
     *      * one or more digits
     */
    const REGEX = '/^(-?)(\d+)(?:\.0+)?$/';



    /**
     * @param string $value_to_normalize
     * @return int|mixed|string
     * @throws ValidationError
     */
    public function normalize($value_to_normalize)
    {
        if ($value_to_normalize === null) {
            return null;
        }
        if (is_int($value_to_normalize) || is_float($value_to_normalize)) {
            return (int) $value_to_normalize;
        }
        if (! is_string($value_to_normalize)) {
            throw new ValidationError(
                sprintf(
                    // translators: 1: value to normalize, 2: type of submitted value
                    __('The value "%1$s" must be a string submitted for normalization, it was %2$s', 'print-my-blog'),
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
                    print_r($value_to_normalize, true),
                    gettype($value_to_normalize)
                )
            );
        }
        $value_to_normalize = filter_var(
            $value_to_normalize,
            FILTER_SANITIZE_NUMBER_FLOAT,
            FILTER_FLAG_ALLOW_FRACTION
        );
        if ($value_to_normalize === '') {
            return null;
        }
        $matches = array();
        if (preg_match(self::REGEX, $value_to_normalize, $matches)) {
            if (count($matches) === 3) {
                // if first match is the negative sign,
                // then the number needs to be multiplied by -1 to remain negative
                return $matches[1] === '-'
                    ? (int) $matches[2] * -1
                    : (int) $matches[2];
            }
        }
        // find if this input has a int validation strategy
        // in which case, use its message
        $validation_error_message = null;
        foreach ($this->input->getValidationStrategies() as $validation_strategy) {
            if ($validation_strategy instanceof IntValidation) {
                $validation_error_message = $validation_strategy->getValidationErrorMessage();
            }
        }
        // this really shouldn't ever happen because fields with a int normalization strategy
        // should also have a int validation strategy, but in case it doesn't use the default
        if (! $validation_error_message) {
            $default_validation_strategy = new IntValidation();
            $validation_error_message = $default_validation_strategy->getValidationErrorMessage();
        }
        throw new ValidationError($validation_error_message, 'numeric_only');
    }



    /**
     * Converts the int into a string for use in teh html form
     *
     * @param int $normalized_value
     * @return string
     */
    public function unnormalize($normalized_value)
    {
        if ($normalized_value === null || $normalized_value === '') {
            return '';
        }
        if (empty($normalized_value)) {
            return '0';
        }
        return "$normalized_value";
    }
}
