<?php

namespace Twine\forms\inputs;

use Twine\forms\strategies\display\AdminFileUploaderDisplay;
use Twine\forms\strategies\normalization\TextNormalization;
use Twine\forms\strategies\validation\UrlValidation;

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
        $this->setDisplayStrategy(new AdminFileUploaderDisplay());
        $this->setNormalizationStrategy(new TextNormalization());
        $this->addValidationStrategy(new URLValidation());
        parent::__construct($input_settings);
    }
}
