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
     * Just checks the field isn't blank
     *
     * @param string $normalized_value
     * @throws ValidationError
     */
    public function validate($normalized_value)
    {
        if ($normalized_value) {
            if (esc_url_raw($normalized_value) !== $normalized_value) {
                throw new ValidationError($this->getValidationErrorMessage(), 'invalid_url');
            }
        }
    }



    /**
     * @return array
     */
    public function getJqueryValidationRuleArray()
    {
        return array(
            'validUrl' => true,
            'messages' => array(
                'validUrl' => $this->getValidationErrorMessage(),
            ),
        );
    }
}
