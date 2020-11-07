<?php
namespace Twine\forms\inputs;
use Twine\forms\helpers\InputOption;
use Twine\forms\strategies\display\SelectDisplay;
use Twine\forms\strategies\validation\EnumValidation;

/**
 * Class SelectInput
 *
 * Generates an HTML <select> form input
 *
 * @package             Event Espresso
 * @subpackage  core
 * @author              Mike Nelson
 * @since               4.6
 *
 */
class SelectInput extends FormInputWithOptionsBase
{

    /**
     * @param InputOption[] $answer_options
     * @param array $input_settings
     */
    public function __construct($answer_options, $input_settings = array())
    {
        $this->_set_display_strategy(new SelectDisplay());
        $this->_add_validation_strategy(
            new EnumValidation(
                isset($input_settings['validation_error_message'])
                    ? $input_settings['validation_error_message']
                    : null
            )
        );
        parent::__construct($answer_options, $input_settings);
    }
}
