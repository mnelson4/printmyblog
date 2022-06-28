<?php

namespace Twine\forms\inputs;

use Twine\forms\helpers\InputOption;
use Twine\forms\strategies\display\CheckboxDisplay;
use Twine\forms\strategies\validation\EnumValidation;
use Twine\forms\strategies\validation\ManyValuedValidation;

/**
 *
 * Class Checkbox_Multi_Input
 *
 * Configures a set of checkbox inputs
 *
 * @package             Event Espresso
 * @subpackage  core
 * @author              Mike Nelson
 *
 *
 */
class CheckboxMultiInput extends FormInputWithOptionsBase
{

    /**
     * @param array $answer_options
     * @param InputOption $input_settings
     */
    public function __construct($answer_options, $input_settings = array())
    {
        $this->setDisplayStrategy(new CheckboxDisplay());
        $this->addValidationStrategy(
            new ManyValuedValidation(
                array(
                    new EnumValidation(
                        isset($input_settings['validation_error_message'])
                            ? $input_settings['validation_error_message']
                            : null
                    ),
                )
            )
        );
        $this->multiple_selections = true;
        parent::__construct($answer_options, $input_settings);
    }
}
