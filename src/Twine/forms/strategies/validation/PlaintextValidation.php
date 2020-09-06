<?php
namespace Twine\forms\strategies\validation;
use Twine\forms\helpers\ValidationError;

/**
 * Class PlaintextValidation
 *
 * Makes sure there are no tags in the submission.
 *
 * @package             Event Espresso
 * @subpackage  core
 * @author              Mike Nelson
 * @since               4.6
 *
 */
class PlaintextValidation extends ValidationBase
{

    /**
     * @param null $validation_error_message
     */
    public function __construct($validation_error_message = null)
    {
        if (! $validation_error_message) {
            $validation_error_message = __("HTML tags are not permitted in this field", "event_espresso");
        }
        parent::__construct($validation_error_message);
    }

    /**
     * @param $normalized_value
     * @throws ValidationError
     */
    public function validate($normalized_value)
    {
        $no_tags = wp_strip_all_tags($normalized_value);
        if (strlen($no_tags) < strlen(trim($normalized_value))) {
            throw new ValidationError($this->get_validation_error_message(), 'no_html_tags');
        }
        parent::validate($normalized_value);
    }
}
