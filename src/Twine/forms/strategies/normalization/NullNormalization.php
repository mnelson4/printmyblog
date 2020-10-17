<?php
namespace Twine\forms\strategies\normalization;
/**
 * NullNormalization
 * Replaces input with null. This is for inputs whose value should be totally ignored server-side
 *
 * @package               Event Espresso
 * @subpackage
 * @author                Mike Nelson
 */
class NullNormalization extends NormalizationBase
{

    /**
     * @param string $value_to_normalize
     * @return null
     */
    public function normalize($value_to_normalize)
    {
        return null;
    }



    /**
     * In the form input we need some string, so use a blank one.
     *
     * @param string $normalized_value
     * @return string
     */
    public function unnormalize($normalized_value)
    {
        return '';
    }
}
