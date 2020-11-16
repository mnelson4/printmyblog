<?php

namespace Twine\forms\inputs;

use Twine\forms\strategies\display\TextInputDisplay;
use Twine\forms\strategies\normalization\TextNormalization;
use Twine\forms\strategies\validation\PlaintextValidation;

/**
 * Year_Input
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 *
 * This input has a default validation strategy of plaintext (which can be removed after construction)
 */
class TextInput extends FormInputBase
{


    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->setDisplayStrategy(new TextInputDisplay());
        $this->setNormalizationStrategy(new TextNormalization());
        parent::__construct($options);
        // if the input hasn't specifically mentioned a more lenient validation strategy,
        // apply plaintext validation strategy
        if (
            ! $this->hasValidationStrategy(
                array(
                    'FullHtmlValidation',
                    'SimpleHtmlValidation'
                )
            )
        ) {
            // by default we use the plaintext validation. If you want something else,
            // just remove it after the input is constructed :P using FormInputBase::remove_validation_strategy()
            $this->addValidationStrategy(new PlaintextValidation());
        }
    }
}
