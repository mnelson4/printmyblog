<?php
namespace Twine\forms\inputs;
/**
 * Password_Input
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 */
class PasswordInput extends FormInputBase
{


    /**
     * @param array $input_settings
     */
    public function __construct($input_settings = array())
    {
        $this->_set_display_strategy(new TextInputDisplay('password'));
        $this->_set_normalization_strategy(new TextNormalization());
        parent::__construct($input_settings);
        $this->set_html_class($this->html_class() . 'password');
    }
}
