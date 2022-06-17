<?php

namespace Twine\forms\strategies\normalization;

/**
 * SlugNormalization
 * Simply converts the string into a slug. DOes not add any errors if its bad.
 *
 * @package               Event Espresso
 * @subpackage
 * @author                Mike Nelson
 */
class SlugNormalization extends NormalizationBase
{

    /**
     * @param string $value_to_normalize
     * @return string
     */
    public function normalize($value_to_normalize)
    {
        return sanitize_title($value_to_normalize);
    }



    /**
     * It's hard to unnormalize this- let's just take a guess
     *
     * @param string $normalized_value
     * @return string
     */
    public function unnormalize($normalized_value)
    {
        return str_replace('-', ' ', $normalized_value);
    }
}
