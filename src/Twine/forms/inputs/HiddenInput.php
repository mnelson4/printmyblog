<?php

namespace Twine\forms\inputs;

use Twine\forms\strategies\display\HiddenDisplay;
use Twine\forms\strategies\normalization\NormalizationBase;
use Twine\forms\strategies\normalization\TextNormalization;

/**
 * HiddenInput
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 */
class HiddenInput extends FormInputBase
{

    /**
     * @param array $input_settings
     */
    public function __construct($input_settings = array())
    {
        // require_once('strategies/display_strategies/TextInputDisplay.strategy.php');
        $this->setDisplayStrategy(new HiddenDisplay());
        if (
            isset($input_settings['normalization_strategy'])
            && $input_settings['normalization_strategy'] instanceof NormalizationBase
        ) {
            $this->setNormalizationStrategy($input_settings['normalization_strategy']);
        } else {
            $this->setNormalizationStrategy(new TextNormalization());
        }
        parent::__construct($input_settings);
    }



    /**
     * @return string
     */
    public function getHtmlForLabel()
    {
        return '';
    }
}
