<?php

namespace Twine\forms\inputs;

use Twine\forms\strategies\display\SingleCheckboxDisplay;
use Twine\forms\strategies\normalization\BooleanNormalization;

/**
 * Yes_No_Input
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 */
class YesNoInput extends FormInputBase
{

    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->overwriteNormalizationStrategy(new BooleanNormalization());
        $this->overwriteDisplayStrategy(new SingleCheckboxDisplay());
        parent::__construct($options);
    }
}
