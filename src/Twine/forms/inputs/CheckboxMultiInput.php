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
 * configures a set of checkbox inputs
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
     * @param InputOption $input_settings
     * @param array $answer_options
     */
    public function __construct($answer_options, $input_settings = array())
    {
        $this->_set_display_strategy(new CheckboxDisplay());
        $this->_add_validation_strategy(new ManyValuedValidation(array( new EnumValidation(isset($input_settings['validation_error_message']) ? $input_settings['validation_error_message'] : null) )));
        $this->_multiple_selections = true;
        parent::__construct($answer_options, $input_settings);
    }
}
