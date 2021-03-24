<?php

namespace Twine\forms\inputs;

use Twine\forms\strategies\display\SubmitInputDisplay;
use Twine\forms\strategies\normalization\TextNormalization;
use Twine\forms\strategies\validation\PlaintextValidation;

/**
 * SubmitInput
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 *
 * This input has a default validation strategy of plaintext (which can be removed after construction)
 */
class SubmitInput extends FormInputBase
{

    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        if (empty($options['default'])) {
            $options['default'] = esc_html__('Submit', 'print-my-blog');
        }
        $this->setDisplayStrategy(new SubmitInputDisplay());
        $this->setNormalizationStrategy(new TextNormalization());
        $this->addValidationStrategy(new PlaintextValidation());
        parent::__construct($options);
    }
}
