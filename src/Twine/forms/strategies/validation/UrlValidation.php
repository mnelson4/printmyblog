<?php
namespace Twine\forms\strategies\validation;
use EventEspresso\core\services\validators\URLValidator;
use InvalidArgumentException;
use Twine\forms\helpers\ValidationError;

/**
 * Class UrlValidation
 *
 * @package             Event Espresso
 * @subpackage  core
 * @author              Mike Nelson
 * @since               4.6
 *
 */
class UrlValidation extends ValidationBase
{

    /**
     * @var @boolean whether we should check if the file exists or not
     */
    protected $check_file_exists;

    /**
     * @var URLValidator
     */
    protected $url_validator;

    /**
     * @param null $validation_error_message
     * @param boolean $check_file_exists
     * @param URLValidator $url_validator
     * @throws InvalidArgumentException
     * @throws \EventEspresso\core\exceptions\InvalidDataTypeException
     * @throws \EventEspresso\core\exceptions\InvalidInterfaceException
     */
    public function __construct(
        $validation_error_message = null
    ) {
        parent::__construct($validation_error_message);
    }



    /**
     * just checks the field isn't blank
     *
     * @param $normalized_value
     * @return bool
     * @throws ValidationError
     */
    public function validate($normalized_value)
    {
        if ($normalized_value) {
            if (esc_url_raw($normalized_value) !== $normalized_value) {
                throw new ValidationError($this->get_validation_error_message(), 'invalid_url');
            }
        }
    }



    /**
     * @return array
     */
    public function get_jquery_validation_rule_array()
    {
        return array( 'validUrl'=>true, 'messages' => array( 'validUrl' => $this->get_validation_error_message() ) );
    }
}
