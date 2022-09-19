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

    /**
     * @var int
     */
    protected $rows = 2;

    /**
     * @var int
     */
    protected $cols = 20;

    /**
     * Sets the rows property on this input
     * @param int $rows
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    /**
     * Sets the cols html property on this input
     * @param int $cols
     */
    public function setCols($cols)
    {
        $this->cols = $cols;
    }
    /**
     *
     * @return int
     */
    public function getRows()
    {
        return $this->rows;
    }
    /**
     *
     * @return int
     */
    public function getCols()
    {
        return $this->cols;
    }



    /**
     * @param array $options_array
     */
    public function __construct($options_array = array())
    {
        $this->setDisplayStrategy(new TextAreaDisplay());
        $this->setNormalizationStrategy(new TextNormalization());

        parent::__construct($options_array);

        // if the input hasn't specifically mentioned a more lenient validation strategy,
        // apply plaintext validation strategy
        if (
            ! $this->hasValidationStrategy(
                array(
                    'Twine\forms\strategies\validation\FullHtmlValidation',
                    'Twine\forms\strategies\validation\SimpleHtmlValidation',
                )
            )
        ) {
            // by default we use the plaintext validation. If you want something else,
            // just remove it after the input is constructed :P using FormInputBase::remove_validation_strategy()
            $this->addValidationStrategy(new PlaintextValidation());
        }
    }
}
