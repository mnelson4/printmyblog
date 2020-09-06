<?php
namespace Twine\forms\strategies\validation;
use Twine\forms\helpers\ValidationError;

/**
 * MaxLengthValidation
 *
 * Validates that the normalized value is smaller than max length
 *
 * @package         Event Espresso
 * @subpackage  Expression package is undefined on line 19, column 19 in Templates/Scripting/PHPClass.php.
 * @author              Mike Nelson
 */
class MaxLengthValidation extends ValidationBase
{

    protected $_max_length;

    public function __construct($validation_error_message = null, $max_length = INF)
    {
        $this->_max_length = $max_length;
        if ($validation_error_message === null) {
            $validation_error_message = sprintf(__('Input is too long. Maximum number of characters is %1$s', 'event_espresso'), $max_length);
        }
        parent::__construct($validation_error_message);
    }

    /**
     * @param $normalized_value
     */
    public function validate($normalized_value)
    {
        if ($this->_max_length !== INF &&
                $normalized_value &&
                is_string($normalized_value) &&
                 strlen($normalized_value) > $this->_max_length) {
            throw new ValidationError($this->get_validation_error_message(), 'maxlength');
        }
    }

    /**
     * @return array
     */
    public function get_jquery_validation_rule_array()
    {
        if ($this->_max_length !== INF) {
            return array( 'maxlength'=> $this->_max_length, 'messages' => array( 'maxlength' => $this->get_validation_error_message() ) );
        } else {
            return array();
        }
    }
}
