<?php

namespace Twine\forms\inputs;

use Twine\forms\strategies\display\TextInputDisplay;
use Twine\forms\strategies\normalization\TextNormalization;
use Twine\forms\strategies\validation\PlaintextValidation;

/**
 * Class ColorInput
 * @package Twine\forms\inputs
 */
class ColorInput extends FormInputBase
{
    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->setDisplayStrategy(new TextInputDisplay('color'));
        $this->setNormalizationStrategy(new TextNormalization());
        parent::__construct($options);
        $this->addValidationStrategy(new PlaintextValidation());
    }
}
