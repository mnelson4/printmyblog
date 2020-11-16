<?php

namespace Twine\forms\strategies\normalization;

use Twine\forms\helpers\ValidationError;
use Twine\forms\strategies\validation\FloatValidation;

/**
 * FloatNormalization
 * Casts to float, and allows spaces, commas, and periods in the inputted string
 *
 * @package               Event Espresso
 * @subpackage
 * @author                Mike Nelson
 */
class FloatNormalization extends NormalizationBase
{

    /*
     * regex pattern that matches for the following:
     *      * optional negative sign
     *      * one or more digits or decimals
     */
    const REGEX = '/^(-?)([\d.]+)$/';



    /**
     * @param string $value_to_normalize
     * @return float
     * @throws ValidationError
     */
    public function normalize($value_to_normalize)
    {
        if ($value_to_normalize === null) {
            return null;
        }
        if (is_float($value_to_normalize) || is_int($value_to_normalize)) {
            return (float) $value_to_normalize;
        }
        if (! is_string($value_to_normalize)) {
            throw new ValidationError(
                sprintf(
                    __('The value "%s" must be a string submitted for normalization, it was %s', 'print-my-blog'),
                    print_r($value_to_normalize, true),
                    gettype($value_to_normalize)
                )
            );
        }
        $normalized_value = filter_var(
            $value_to_normalize,
            FILTER_SANITIZE_NUMBER_FLOAT,
            FILTER_FLAG_ALLOW_FRACTION
        );
        if ($normalized_value === '') {
            return null;
        }
        if (preg_match(FloatNormalization::REGEX, $normalized_value, $matches)) {
            if (count($matches) === 3) {
                // if first match is the negative sign,
                // then the number needs to be multiplied by -1 to remain negative
                return $matches[1] === '-'
                    ? (float) $matches[2] * -1
                    : (float) $matches[2];
            }
        }
        // find if this input has a float validation strategy
        // in which case, use its message
        $validation_error_message = null;
        foreach ($this->input->getValidationStrategies() as $validation_strategy) {
            if ($validation_strategy instanceof FloatValidation) {
                $validation_error_message = $validation_strategy->getValidationErrorMessage();
            }
        }
        // this really shouldn't ever happen because fields with a float normalization strategy
        // should also have a float validation strategy, but in case it doesn't use the default
        if (! $validation_error_message) {
            $default_validation_strategy = new FloatValidation();
            $validation_error_message = $default_validation_strategy->getValidationErrorMessage();
        }
        throw new ValidationError($validation_error_message, 'float_only');
    }



    /**
     * Converts a float into a string
     *
     * @param float $normalized_value
     * @return string
     */
    public function unnormalize($normalized_value)
    {
        if (empty($normalized_value)) {
            return '0.00';
        }
        return "{$normalized_value}";
    }
}
