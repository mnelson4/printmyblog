<?php


namespace Twine\forms\inputs;


use Twine\forms\strategies\display\TextInputDisplay;
use Twine\forms\strategies\normalization\TextNormalization;
use Twine\forms\strategies\validation\PlaintextValidation;

class ColorInput extends FormInputBase{
	/**
	 * @param array $options
	 */
	public function __construct($options = array())
	{
		$this->_set_display_strategy(new TextInputDisplay('color'));
		$this->_set_normalization_strategy(new TextNormalization());
		parent::__construct($options);
		$this->_add_validation_strategy(new PlaintextValidation());
	}
}