<?php

namespace Twine\forms\strategies\display;

use Exception;
use Twine\forms\inputs\FormInputWithOptionsBase;

/**
 *
 * Class CompoundInputDisplay
 *
 * For displaying input classes that are actually a many html inputs.
 *
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 * @since              4.9.0
 *
 */
abstract class CompoundInputDisplay extends DisplayBase
{

    /**
     * Gets the html ID for the sub-input for the specified option html value (not display text)
     *
     * @param string $option_value
     * @param bool   $add_pound_sign
     * @return string
     */
    public function getSubInputId($option_value, $add_pound_sign = false)
    {
        return $this->appendChars($this->input->htmlId($add_pound_sign), '-') . sanitize_key($option_value);
    }



    /**
     * Gets the HTML IDs of all the inputs
     *
     * @param boolean $add_pound_sign
     * @return array
     * @throws \Error
     */
    public function getHtmlInputIds($add_pound_sign = false)
    {
        $html_input_ids = array();
        foreach ($this->getInput()->options() as $value => $display) {
            $html_input_ids[] = $this->getSubInputId($value, $add_pound_sign);
        }
        return $html_input_ids;
    }



    /**
     * Overrides parent to make sure this display strategy is only used with the
     * appropriate input type
     *
     * @return FormInputWithOptionsBase
     * @throws \Error
     */
    public function getInput()
    {
        if (! $this->input instanceof FormInputWithOptionsBase) {
            throw new Exception(
                __(
                    // phpcs:disable Generic.Files.LineLength.TooLong
                    'Can not use a Compound Input Display Strategy (eg checkbox or radio) with an input that doesn\'t have options',
                    // phpcs:enable Generic.Files.LineLength.TooLong
                    'print-my-blog'
                )
            );
        }
        return parent::getInput();
    }
}
