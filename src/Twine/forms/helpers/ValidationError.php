<?php
namespace Twine\forms\helpers;
use Exception;
use Twine\forms\base\FormSectionValidatable;

class ValidationError extends Exception
{
    /**
     * Form Section from which this error originated.
     * @var FormSectionValidatable
     */
    protected $_form_section;
    /**
     * a short string for uniquely identifying the error, which isn't internationalized and
     * machines can use to identify the error
     * @var string
     */
    protected $_string_code;

    /**
     * When creating a validation error, we need to know which field the error relates to.
     * @param string $message message you want to display about this error
     * @param string $string_code a code for uniquely identifying the exception
     * @param FormSectionValidatable $form_section
     * @param Exception $previous if there was an exception that caused this exception
     */
    public function __construct($message = null, $string_code = null, $form_section = null, $previous = null)
    {
        $this->_form_section = $form_section;
        $this->_string_code = $string_code;
        parent::__construct($message, 500, $previous);
    }

    /**
     * returns teh form section which caused the error.
     * @return FormSectionValidatable
     */
    public function get_form_section()
    {
        return $this->_form_section;
    }
    /**
     * Sets teh form seciton of the error, in case it wasnt set previously
     * @param FormSectionValidatable $form_section
     * @return void
     */
    public function set_form_section($form_section)
    {
        $this->_form_section = $form_section;
    }
}
