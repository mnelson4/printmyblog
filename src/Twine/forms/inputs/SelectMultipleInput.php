<?php

namespace Twine\forms\inputs;

use Twine\forms\helpers\InputOption;
use Twine\forms\strategies\display\SelectMultipleDisplay;
use Twine\forms\strategies\validation\EnumValidation;
use Twine\forms\strategies\validation\ManyValuedValidation;

/**
 * Select_Multiple_Input
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 */
class SelectMultipleInput extends FormInputWithOptionsBase
{

    /**
     * @param InputOption[] $answer_options
     * @param array $input_settings
     */
    public function __construct($answer_options, $input_settings = array())
    {
        $this->setDisplayStrategy(new SelectMultipleDisplay());
        $this->addValidationStrategy(
            new ManyValuedValidation(
                array(
                    new EnumValidation(
                        isset($input_settings['validation_error_message'])
                        ? $input_settings['validation_error_message']
                        : null
                    )
                )
            )
        );
        $this->multiple_selections = true;
        parent::__construct($answer_options, $input_settings);
    }
}
