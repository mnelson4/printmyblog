<?php

namespace Twine\forms\strategies\validation;

use Twine\forms\strategies\FormInputStrategyBase;

/**
 * Class ValidationBase
 *
 * @package               Event Espresso
 * @subpackage            core
 * @author                Mike Nelson
 * @since                 4.6
 */
abstract class ValidationBase extends FormInputStrategyBase
{


    protected $validation_error_message = '';



    /**
     * @param null $validation_error_message
     */
    public function __construct($validation_error_message = null)
    {
        $this->validation_error_message = $validation_error_message === null
            ? __('Input invalid', 'print-my-blog')
            : $validation_error_message;
        parent::__construct();
    }



    /**
     * Performs validation on the request data that corresponds to this field.
     * If validation fails, should throw an ValidationError.
     * Note: most validate() functions should allow $normalized_value to be empty,
     * as its the job of the RequiredValidation to ensure that the field isn't empty.
     *
     * @param mixed $normalized_value ready for validation. May very well be NULL (which, unless
     *                                this validation strategy is the 'required' validation strategy,
     *                                most should be OK with a null, empty string, etc)
     */
    public function validate($normalized_value)
    {
        // by default, the validation strategy does no validation. this should be implemented
    }



    /**
     * Gets the JS code for use in the jQuery validation js corresponding to this field when displaying.
     * For documentation, see http://jqueryvalidation.org/
     * Eg to generate the following js for validation, <br><code>
     *  $( "#myform" ).validate({
     *      rules: {
     *          field_name: {
     *              required: true,
     *              minlength: 3,
     *              equalTo: "#password"
     *          }
     *      }
     *  });
     * </code>
     * this function should return array('required'=>true,'minlength'=>3,'equalTo'=>'"#password"' ).
     * This is done so that if we are applying multiple sanitization strategies to a field,
     * we can easily combine them.
     *
     * @return array
     */
    public function getJqueryValidationRuleArray()
    {
        return array();
    }



    /**
     * Gets the i18n validation error message for when this validation strategy finds
     * the input is invalid. Used for both frontend and backend validation.
     *
     * @return string
     */
    public function getValidationErrorMessage()
    {
        return $this->validation_error_message;
    }



    /**
     * Adds js variables for localization to the $other_js_data. These should be put
     * in each form's "other_data" javascript object.
     *
     * @param array $other_js_data
     * @return array
     */
    public function getOtherJsData($other_js_data = array())
    {
        return $other_js_data;
    }

    /**
     * Opportunity for this display strategy to call wp_enqueue_script and wp_enqueue_style.
     * This should be called during wp_enqueue_scripts
     */
    public function enqueueJs()
    {
    }
}
