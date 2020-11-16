<?php

namespace Twine\forms\base;

use Exception;
use Twine\forms\helpers\ValidationError;

/**
 * FormSectionValidatable
 * Class for cross-cutting job of handling forms.
 * In the presentation layer Form Sections handle the display of form inputs on the page.
 * In both the presentation and controller layer, Form Sections handle validation (by js and php)
 * Used from within a controller, Form Sections handle input sanitization.
 * And the Model_Form_Section takes care of taking a model object and producing a generic form section,
 * and takes a filled form section, and can save the model object to the database.
 * Note there are actually two children of FormSectionValidatable: FormSectionProper and FormInputBase.
 * The former is what you probably expected FormSectionValidatable to be, whereas the latter is the parent class
 * for all fields within a form section. So this means that a Form Input is considered a subsection of form section in
 * its own right.
 *
 * @package               Event Espresso
 * @subpackage
 * @author                Mike Nelson
 *                        ------------------------------------------------------------------------
 */
abstract class FormSectionValidatable extends FormSectionBase
{

    /**
     * Array of validation errors in this section. Does not contain validation errors in subsections, however.
     * Those are stored individually on each subsection.
     *
     * @var ValidationError[]
     */
    protected $validation_errors = array();



    /**
     * Errors on this form section. Note: FormSectionProper
     * has another function for getting all errors in this form section and subsections
     * called get_validation_errors_accumulated
     *
     * @return ValidationError[]
     */
    public function getValidationErrors()
    {
        return $this->validation_errors;
    }



    /**
     * returns a comma-separated list of all the validation errors in it.
     * If we want this to be customizable, we may decide to create a strategy for displaying it
     *
     * @return string
     */
    public function getValidationErrorString()
    {
        $validation_error_messages = array();
        if ($this->getValidationErrors()) {
            foreach ($this->getValidationErrors() as $validation_error) {
                if ($validation_error instanceof ValidationError) {
                    $validation_error_messages[] = $validation_error->getMessage();
                }
            }
        }
        return implode(", ", $validation_error_messages);
    }



    /**
     * Performs validation on this form section (and subsections). Should be called after _normalize()
     *
     * @return boolean of whether or not the form section is valid
     */
    abstract protected function validate();



    /**
     * Checks if this field has any validation errors
     *
     * @return boolean
     */
    public function isValid()
    {
        if (count($this->validation_errors)) {
            return false;
        } else {
            return true;
        }
    }



    /**
     * Sanitizes input for this form section
     *
     * @param array $req_data is the full request data like $_POST
     * @return boolean of whether a normalization error occurred
     */
    abstract protected function normalize($req_data);



    /**
     * Creates a validation error from the arguments provided, and adds it to the form section's list.
     * If such an ValidationError object is passed in as the first arg, simply sets this as its form section, and
     * adds it to the list of validation errors of errors
     *
     * @param mixed     $message_or_object  internationalized string describing the validation error; or it could be a
     *                                      proper ValidationError object
     * @param string    $error_code         a short key which can be used to uniquely identify the error
     * @param Exception $previous_exception if there was an exception that caused the error, that exception
     * @return void
     */
    public function addValidationError($message_or_object, $error_code = null, $previous_exception = null)
    {
        if ($message_or_object instanceof ValidationError) {
            $validation_error = $message_or_object;
            $validation_error->setFormSection($this);
        } else {
            $validation_error = new ValidationError($message_or_object, $error_code, $this, $previous_exception);
        }
        $this->validation_errors[] = $validation_error;
    }



    /**
     * When generating the JS for the jquery validation rules like<br>
     * <code>$( "#myform" ).validate({
     * rules: {
     * password: "required",
     * password_again: {
     * equalTo: "#password"
     * }
     * }
     * });</code>
     * gets the sections like
     * <br><code>password: "required",
     * password_again: {
     * equalTo: "#password"
     * }</code>
     * except we leave it as a PHP object, and leave wp_localize_script to
     * turn it into a JSON object which can be used by the js
     *
     * @return array
     */
    abstract public function getJqueryValdationRules();



    /**
     * Checks if this form section's data is present in the req data specified
     *
     * @param array $req_data usually $_POST, if null that's what's used
     * @return boolean
     */
    abstract public function formDataPresentIn($req_data = null);
}
