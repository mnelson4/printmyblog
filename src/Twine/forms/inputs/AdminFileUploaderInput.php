<?php
namespace Twine\forms\inputs;

use Twine\forms\strategies\display\AdminFileUploaderDisplay;
use Twine\forms\strategies\normalization\TextNormalization;

/**
 * Class AdminFileUploaderInput
 *
 * @package            Event Espresso
 * @subpackage    core
 * @author                Mike Nelson
 * @since                4.6
 *
 */
class AdminFileUploaderInput extends FormInputBase
{

    /**
     * @param array $input_settings
     */
    public function __construct($input_settings = array())
    {
        $this->_set_display_strategy(new AdminFileUploaderDisplay());
        $this->_set_normalization_strategy(new TextNormalization());
        $this->_add_validation_strategy(new URLValidation());
        parent::__construct($input_settings);
    }
}
