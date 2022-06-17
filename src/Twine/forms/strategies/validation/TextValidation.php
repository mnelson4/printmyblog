<?php

namespace Twine\forms\strategies\validation;

use Twine\forms\helpers\ValidationError;

/**
 * TextValidation
 * Optionally, a regular expression can be provided that will be used for validation.
 *
 * @package         Event Espresso
 * @subpackage  Expression package is undefined on line 19, column 19 in Templates/Scripting/PHPClass.php.
 * @author              Mike Nelson
 */
class TextValidation extends ValidationBase
{
    /**
     * @var string|null
     */
    protected $regex = null;

    /**
     *
     * @param string $validation_error_message
     * @param string $regex a PHP regex; the javascript regex will be derived from this
     */
    public function __construct($validation_error_message = null, $regex = null)
    {
        $this->regex = $regex;
        parent::__construct($validation_error_message);
    }

    /**
     * @param string $normalized_value
     * @throws ValidationError
     */
    public function validate($normalized_value)
    {
        $string_normalized_value = (string) $normalized_value;
        if ($this->regex && $string_normalized_value) {
            if (! preg_match($this->regex, $string_normalized_value)) {
                throw new ValidationError($this->getValidationErrorMessage(), 'regex');
            }
        }
    }

    /**
     * @return array
     */
    public function getJqueryValidationRuleArray()
    {
        if ($this->regex !== null) {
            return array(
                'regex' => $this->regexJs(),
                'messages' => array(
                    'regex' => $this->getValidationErrorMessage(),
                ),
            );
        } else {
            return array();
        }
    }

/**
 * Translates a PHP regex into a javscript regex (eg, PHP needs separate delimieters, whereas
 * javscript does not
 * @return string
 */
    public function regexJs()
    {
        // first character must be the delimiter
        $delimeter = $this->regex[0];
        $last_occurence_of_delimieter = strrpos($this->regex, $delimeter);
        return substr($this->regex, 1, $last_occurence_of_delimieter - 1);
    }
}
