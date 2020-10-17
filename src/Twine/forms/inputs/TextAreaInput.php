<?php
namespace Twine\forms\inputs;
use Twine\forms\strategies\display\TextAreaDisplay;
use Twine\forms\strategies\normalization\TextNormalization;
use Twine\forms\strategies\validation\PlaintextValidation;

/**
 * Text_Area
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 *
 * This input has a default validation strategy of plaintext (which can be removed after construction)
 */
class TextAreaInput extends FormInputBase
{


    protected $_rows = 2;
    protected $_cols = 20;

    /**
     * sets the rows property on this input
     * @param int $rows
     */
    public function set_rows($rows)
    {
        $this->_rows = $rows;
    }
    /**
     * sets the cols html property on this input
     * @param int $cols
     */
    public function set_cols($cols)
    {
        $this->_cols = $cols;
    }
    /**
     *
     * @return int
     */
    public function get_rows()
    {
        return $this->_rows;
    }
    /**
     *
     * @return int
     */
    public function get_cols()
    {
        return $this->_cols;
    }



    /**
     * @param array $options_array
     */
    public function __construct($options_array = array())
    {
        $this->_set_display_strategy(new TextAreaDisplay());
        $this->_set_normalization_strategy(new TextNormalization());
        
        
        parent::__construct($options_array);
        
        // if the input hasn't specifically mentioned a more lenient validation strategy,
        // apply plaintext validation strategy
        if (! $this->has_validation_strategy(
            array(
                    'FullHtmlValidation',
                    'SimpleHtmlValidation'
                )
        )
        ) {
            // by default we use the plaintext validation. If you want something else,
            // just remove it after the input is constructed :P using FormInputBase::remove_validation_strategy()
            $this->_add_validation_strategy(new PlaintextValidation());
        }
    }
}
