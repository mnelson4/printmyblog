<?php
namespace Twine\forms\inputs;
use Twine\forms\strategies\display\ButtonDisplay;
use Twine\forms\strategies\normalization\TextNormalization;
use Twine\forms\strategies\validation\PlaintextValidation;

/**
 * Button_Input
 * Similar to Submit_Input, but renders a button element, and its text being displayed
 * can differ from the value, and it can contain HTML.
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 *
 */
class ButtonInput extends FormInputBase
{

    /**
     * @var string of HTML to put between the button tags
     */
    protected $_button_content;
    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        if (empty($options['button_content'])) {
            $options['button_content'] = esc_html__('Button', 'event_espresso');
        }
        $this->_set_display_strategy(new ButtonDisplay());
        $this->_set_normalization_strategy(new TextNormalization());
        $this->_add_validation_strategy(new PlaintextValidation());
        parent::__construct($options);
    }



    /**
     * Sets the button content
     * @see Button_Input::$_button_content
     * @param string $new_content
     */
    public function set_button_content($new_content)
    {
        $this->_button_content = $new_content;
    }
    
    /**
     * Gets the button content
     * @return string
     */
    public function button_content()
    {
        return $this->_button_content;
    }
}
