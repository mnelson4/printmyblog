<?php
namespace Twine\forms\strategies\validation;
use Twine\forms\helpers\ValidationError;

/**
 * MinLengthValidation
 *
 * Validates that the normalized value is at least the specified length
 *
 * @package         Event Espresso
 * @subpackage  Expression package is undefined on line 19, column 19 in Templates/Scripting/PHPClass.php.
 * @author              Mike Nelson
 */
class MinLengthValidation extends ValidationBase
{

    protected $_min_length;

    public function __construct($validation_error_message = null, $min_length = 0)
    {
        $this->_min_length = $min_length;
        parent::__construct($validation_error_message);
    }

    /**
     * @param $normalized_value
     */
    public function validate($normalized_value)
    {
        if ($this->_min_length > 0 &&
                $normalized_value &&
                is_string($normalized_value) &&
                strlen($normalized_value) < $this->_min_length) {
            throw new ValidationError($this->get_validation_error_message(), 'minlength');
        }
    }

    /**
     * @return array
     */
    public function get_jquery_validation_rule_array()
    {
        return array( 'minlength'=> $this->_min_length, 'messages' => array( 'minlength' => $this->get_validation_error_message() ) );
    }
}
