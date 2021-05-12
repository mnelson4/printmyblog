<?php

namespace Twine\forms\inputs;

use Twine\forms\base\FormSection;
use Twine\forms\base\FormSectionValidatable;
use Twine\forms\helpers\ImproperUsageException;
use Twine\forms\helpers\ValidationError;
use Twine\forms\strategies\display\DisplayBase;
use Twine\forms\strategies\normalization\NormalizationBase;
use Twine\forms\strategies\normalization\NullNormalization;
use Twine\forms\strategies\normalization\TextNormalization;
use Twine\forms\strategies\validation\RequiredValidation;
use Twine\forms\strategies\validation\ValidationBase;

/**
 * FormInputBase
 * For representing a single form input. Extends FormSectionBase because
 * it is a part of a form and shares a surprisingly large amount of functionality
 *
 * @package               Event Espresso
 * @subpackage
 * @author                Mike Nelson
 */
abstract class FormInputBase extends FormSectionValidatable
{

    /**
     * the input's name attribute
     *
     * @var string
     */
    protected $html_name;

    /**
     * id for the html label tag
     *
     * @var string
     */
    protected $html_label_id;

    /**
     * class for teh html label tag
     *
     * @var string
     */
    protected $html_label_class;

    /**
     * style for teh html label tag
     *
     * @var string
     */
    protected $html_label_style;

    /**
     * text to be placed in the html label
     *
     * @var string
     */
    protected $html_label_text;

    /**
     * the full html label. If used, all other html_label_* properties are invalid
     *
     * @var string
     */
    protected $html_label;

    /**
     * HTML to use for help text (normally placed below form input), in a span which normally
     * has a class of 'description'
     *
     * @var string
     */
    protected $html_help_text;

    /**
     * CSS classes for displaying the help span
     *
     * @var string
     */
    protected $html_help_class = 'description';

    /**
     * CSS to put in the style attribute on the help span
     *
     * @var string
     */
    protected $html_help_style;

    /**
     * Stores whether or not this input's response is required.
     * Because certain styling elements may also want to know that this
     * input is required etc.
     *
     * @var boolean
     */
    protected $required;

    /**
     * css class added to required inputs
     *
     * @var string
     */
    protected $required_css_class = 'twine-required';

    /**
     * The raw data submitted for this, like in the $_POST super global.
     * Generally unsafe for usage in client code
     *
     * @var mixed string or array
     */
    protected $raw_value;

    /**
     * Value normalized according to the input's normalization strategy.
     * The normalization strategy dictates whether this is a string, int, float,
     * boolean, or array of any of those.
     *
     * @var mixed
     */
    protected $normalized_value;


    /**
     * Normalized default value either initially set on the input, or provided by calling
     * set_default().
     * @var mixed
     */
    protected $default;

    /**
     * Strategy used for displaying this field.
     * Child classes must use _get_display_strategy to access it.
     *
     * @var DisplayBase
     */
    private $display_strategy;

    /**
     * Gets all the validation strategies used on this field
     *
     * @var ValidationBase[]
     */
    private $validation_strategies = array();

    /**
     * The normalization strategy for this field
     *
     * @var NormalizationBase
     */
    private $normalization_strategy;

    /**
     * Whether this input has been disabled or not.
     * If it's disabled while rendering, an extra hidden input is added that indicates it has been knowingly disabled.
     * (Client-side code that wants to dynamically disable it must also add this hidden input).
     * When the form is submitted, if the input is disabled in the PHP formsection, then input is ignored.
     * If the input is missing from the $_REQUEST data but the hidden input indicating the input is disabled, then the
     * input is again ignored.
     *
     * @var boolean
     */
    protected $disabled = false;



    /**
     * @param array                         $input_args       {
     * @type string                         $html_name        the html name for the input
     * @type string                         $html_label_id    the id attribute to give to the html label tag
     * @type string                         $html_label_class the class attribute to give to the html label tag
     * @type string                         $html_label_style the style attribute to give ot teh label tag
     * @type string                         $html_label_text  the text to put in the label tag
     * @type string                         $html_label       the full html label. If used,
     *                                                        all other html_label_* args are invalid
     * @type string                         $html_help_text   text to put in help element
     * @type string                         $html_help_style  style attribute to give to teh help element
     * @type string                         $html_help_class  class attribute to give to the help element
     * @type string                         $default          default value NORMALIZED (eg, if providing the default
     *       for a Yes_No_Input, you should provide TRUE or FALSE, not '1' or '0')
     * @type DisplayBase       $display          strategy
     * @type NormalizationBase $normalization_strategy
     * @type ValidationBase[]  $validation_strategies
     * @type boolean                        $ignore_input special argument which can be used to avoid adding any
     *                                                    validation strategies, and sets the normalization strategy to
     *                                                    the Null normalization. This is good when you want the input
     *                                                    to be totally ignored server-side (like when using React.js
     *                                                    form inputs)
     * @type boolean                        $disabled whether to disabled this input or not
     * }
     */
    public function __construct($input_args = array())
    {
        $input_args = (array) apply_filters('FH_FormInputBase___construct__input_args', $input_args, $this);
        // the following properties must be cast as arrays
        if (isset($input_args['validation_strategies'])) {
            foreach ((array) $input_args['validation_strategies'] as $validation_strategy) {
                if ($validation_strategy instanceof ValidationBase && empty($input_args['ignore_input'])) {
                    $this->validation_strategies[ get_class($validation_strategy) ] = $validation_strategy;
                }
            }
            unset($input_args['validation_strategies']);
        }
        if (isset($input_args['ignore_input'])) {
            $this->validation_strategies = array();
        }
        // loop thru incoming options
        foreach ($input_args as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        // ensure that "required" is set correctly
        $this->setRequired(
            $this->required,
            isset($input_args['required_validation_error_message'])
            ? $input_args['required_validation_error_message']
            : null
        );
        // $this->_html_name_specified = isset( $input_args['html_name'] ) ? TRUE : FALSE;
        $this->display_strategy->constructFinalize($this);
        foreach ($this->validation_strategies as $validation_strategy) {
            $validation_strategy->constructFinalize($this);
        }
        if (isset($input_args['ignore_input'])) {
            $this->normalization_strategy = new NullNormalization();
        }
        if (! $this->normalization_strategy) {
                $this->normalization_strategy = new TextNormalization();
        }
        $this->normalization_strategy->constructFinalize($this);
        // at least we can use the normalization strategy to populate the default
        if (isset($input_args['default'])) {
            $this->setDefault($input_args['default']);
            unset($input_args['default']);
        }
        if (isset($input_args['disabled']) && $input_args['disabled']) {
            $this->disable(true);
            unset($input_args['disabled']);
        }
        parent::__construct($input_args);
    }



    /**
     * Sets the html_name to its default value, if none was specified in teh constructor.
     * Calculation involves using the name and the parent's html_name
     *
     * @throws \Error
     */
    protected function setDefaultHtmlNameIfEmpty()
    {
        if (! $this->html_name) {
            $this->html_name = $this->name();
            if ($this->parent_section && $this->parent_section instanceof FormSection) {
                $this->html_name = $this->parent_section->htmlNamePrefix() . "[{$this->name()}]";
            }
        }
    }



    /**
     * @param $parent_form_section
     * @param $name
     * @throws \Error
     */
    public function constructFinalize($parent_form_section, $name)
    {
        parent::constructFinalize($parent_form_section, $name);
        if ($this->html_label === null && $this->html_label_text === null) {
            $this->html_label_text = ucwords(str_replace("_", " ", $name));
        }
        do_action('AH_FormInputBase___construct_finalize__end', $this, $parent_form_section, $name);
    }



    /**
     * Returns the strategy for displaying this form input. If none is set, throws an exception.
     *
     * @return DisplayBase
     * @throws ImproperUsageException
     */
    protected function initializeDisplayStrategy()
    {
        $this->ensureConstructFinalizedCalled();
        if (! $this->display_strategy || ! $this->display_strategy instanceof DisplayBase) {
            throw new ImproperUsageException(
                sprintf(
                    __(
                        "Cannot get display strategy for form input with name %s and id %s, because it has not been set in the constructor",
                        "print-my-blog"
                    ),
                    $this->htmlName(),
                    $this->htmlId()
                )
            );
        } else {
            return $this->display_strategy;
        }
    }



    /**
     * Sets the display strategy.
     *
     * @param DisplayBase $strategy
     */
    protected function setDisplayStrategy(DisplayBase $strategy)
    {
        $this->display_strategy = $strategy;
    }



    /**
     * Sets the sanitization strategy
     *
     * @param NormalizationBase $strategy
     */
    protected function setNormalizationStrategy(NormalizationBase $strategy)
    {
        $this->normalization_strategy = $strategy;
    }


    /**
     * Gets the display strategy for this input
     *
     * @return DisplayBase
     */
    public function getDisplayStrategy()
    {
        return $this->display_strategy;
    }



    /**
     * Overwrites the display strategy
     *
     * @param DisplayBase $display_strategy
     */
    public function overwriteDisplayStrategy($display_strategy)
    {
        $this->display_strategy = $display_strategy;
        $this->display_strategy->constructFinalize($this);
    }



    /**
     * Gets the normalization strategy set on this input
     *
     * @return NormalizationBase
     */
    public function getNormalizationStrategy()
    {
        return $this->normalization_strategy;
    }



    /**
     * Overwrites the normalization strategy
     *
     * @param NormalizationBase $normalization_strategy
     */
    public function overwriteNormalizationStrategy($normalization_strategy)
    {
        $this->normalization_strategy = $normalization_strategy;
        $this->normalization_strategy->constructFinalize($this);
    }



    /**
     * Returns all teh validation strategies which apply to this field, numerically indexed
     *
     * @return ValidationBase[]
     */
    public function getValidationStrategies()
    {
        return $this->validation_strategies;
    }



    /**
     * Adds this strategy to the field so it will be used in both JS validation and server-side validation
     *
     * @param ValidationBase $validation_strategy
     * @return void
     */
    protected function addValidationStrategy(ValidationBase $validation_strategy)
    {
        $validation_strategy->constructFinalize($this);
        $this->validation_strategies[] = $validation_strategy;
    }






    /**
     * The classname of the validation strategy to remove
     *
     * @param string $validation_strategy_classname
     */
    public function removeValidationStrategy($validation_strategy_classname)
    {
        foreach ($this->validation_strategies as $key => $validation_strategy) {
            if (
                $validation_strategy instanceof $validation_strategy_classname
                || is_subclass_of($validation_strategy, $validation_strategy_classname)
            ) {
                unset($this->validation_strategies[ $key ]);
            }
        }
    }



    /**
     * returns true if input employs any of the validation strategy defined by the supplied array of classnames
     *
     * @param array $validation_strategy_classnames
     * @return bool
     */
    public function hasValidationStrategy($validation_strategy_classnames)
    {
        $validation_strategy_classnames = is_array($validation_strategy_classnames)
            ? $validation_strategy_classnames
            : array($validation_strategy_classnames);
        foreach ($this->validation_strategies as $key => $validation_strategy) {
            if (in_array($key, $validation_strategy_classnames)) {
                return true;
            }
        }
        return false;
    }



    /**
     * Gets the HTML
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->parent_section->getHtmlForInput($this);
    }



    /**
     * Gets the HTML for the input itself (no label or errors) according to the
     * input's display strategy
     * Makes sure the JS and CSS are enqueued for it
     *
     * @return string
     * @throws \Error
     */
    public function getHtmlForInput()
    {
        return $this->initializeDisplayStrategy()->display();
    }


    /**
     * Gets the HTML for displaying the label for this form input
     * according to the form section's layout strategy
     *
     * @return string
     */
    public function getHtmlForLabel()
    {
        return $this->parent_section->getLayoutStrategy()->displayLabel($this);
    }



    /**
     * Gets the HTML for displaying the errors section for this form input
     * according to the form section's layout strategy
     *
     * @return string
     */
    public function getHtmlForErrors()
    {
        return $this->parent_section->getLayoutStrategy()->displayErrors($this);
    }



    /**
     * Gets the HTML for displaying the help text for this form input
     * according to the form section's layout strategy
     *
     * @return string
     */
    public function getHtmlForHelp()
    {
        return $this->parent_section->getLayoutStrategy()->displayHelpText($this);
    }



    /**
     * Validates the input's sanitized value (assumes _sanitize() has already been called)
     * and returns whether or not the form input's submitted value is value
     *
     * @return boolean
     */
    protected function validate()
    {
        if ($this->isDisabled()) {
            return true;
        }
        foreach ($this->validation_strategies as $validation_strategy) {
            if ($validation_strategy instanceof ValidationBase) {
                try {
                    $validation_strategy->validate($this->normalizedValue());
                } catch (ValidationError $e) {
                    $this->addValidationError($e);
                }
            }
        }
        if ($this->getValidationErrors()) {
            return false;
        } else {
            return true;
        }
    }



    /**
     * Performs basic sanitization on this value. But what sanitization can be performed anyways?
     * This value MIGHT be allowed to have tags, so we can't really remove them.
     *
     * @param string $value
     * @return null|string
     */
    protected function sanitize($value)
    {
        return $value !== null ? stripslashes(html_entity_decode(trim($value))) : null;
    }



    /**
     * Picks out the form value that relates to this form input,
     * and stores it as the sanitized value on the form input, and sets the normalized value.
     * Returns whether or not any validation errors occurred
     *
     * @param array $req_data like $_POST
     * @return boolean whether or not there was an error
     * @throws \Error
     */
    protected function normalize($req_data)
    {
        // any existing validation errors don't apply so clear them
        $this->validation_errors = array();
        // if the input is disabled, ignore whatever input was sent in
        if ($this->isDisabled()) {
            $this->setRawValue(null);
            $this->setNormalizedValue($this->getDefault());
            return false;
        }
        try {
            $raw_input = $this->findFormDataForThisSection($req_data);
            // super simple sanitization for now
            if (is_array($raw_input)) {
                $raw_value = array();
                foreach ($raw_input as $key => $value) {
                    $raw_value[ $key ] = $this->sanitize($value);
                }
                $this->setRawValue($raw_value);
            } else {
                $this->setRawValue($this->sanitize($raw_input));
            }
            // we want to mostly leave the input alone in case we need to re-display it to the user
            $this->setNormalizedValue($this->normalization_strategy->normalize($this->rawValue()));
            return false;
        } catch (ValidationError $e) {
            $this->addValidationError($e);
            return true;
        }
    }



    /**
     * @return string
     */
    public function htmlName()
    {
        $this->setDefaultHtmlNameIfEmpty();
        return $this->html_name;
    }



    /**
     * @return string
     */
    public function htmlLabelId()
    {
        return ! empty($this->html_label_id) ? $this->html_label_id : $this->htmlId() . '-lbl';
    }



    /**
     * @return string
     */
    public function htmlLabelClass()
    {
        return $this->html_label_class;
    }



    /**
     * @return string
     */
    public function htmlLabelStyle()
    {
        return $this->html_label_style;
    }



    /**
     * @return string
     */
    public function htmlLabelText()
    {
        return $this->html_label_text;
    }



    /**
     * @return string
     */
    public function htmlHelpText()
    {
        return $this->html_help_text;
    }



    /**
     * @return string
     */
    public function htmlHelpClass()
    {
        return $this->html_help_class;
    }



    /**
     * @return string
     */
    public function htmlHelpStyle()
    {
        return $this->html_style;
    }



    /**
     * returns the raw, UNSAFE, input, almost exactly as the user submitted it.
     * Please note that almost all client code should instead use the normalized_value;
     * or possibly raw_value_in_form (which prepares the string for displaying in an HTML attribute on a tag,
     * mostly by escaping quotes)
     * Note, we do not store the exact original value sent in the user's request because
     * it may have malicious content, and we MIGHT want to store the form input in a transient or something...
     * in which case, we would have stored the malicious content to our database.
     *
     * @return string
     */
    public function rawValue()
    {
        return $this->raw_value;
    }



    /**
     * Returns a string safe to usage in form inputs when displaying, because
     * it escapes all html entities
     *
     * @return string
     */
    public function rawValueInForm()
    {
        return htmlentities($this->rawValue(), ENT_QUOTES, 'UTF-8');
    }



    /**
     * returns the value after it's been sanitized, and then converted into it's proper type
     * in PHP. Eg, a string, an int, an array,
     *
     * @return mixed
     */
    public function normalizedValue()
    {
        return $this->normalized_value;
    }



    /**
     * Returns the normalized value is a presentable way. By default this is just
     * the normalized value by itself, but it can be overridden for when that's not
     * the best thing to display
     *
     * @return string
     */
    public function prettyValue()
    {
        return $this->normalized_value;
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
     * if this field had the name 'password_again', it should return
     * <br><code>password_again: {
     * equalTo: "#password"
     * }</code>
     *
     * @return array
     */
    public function getJqueryValdationRules()
    {
        $jquery_validation_js = array();
        $jquery_validation_rules = array();
        foreach ($this->getValidationStrategies() as $validation_strategy) {
            $jquery_validation_rules = array_replace_recursive(
                $jquery_validation_rules,
                $validation_strategy->getJqueryValidationRuleArray()
            );
        }
        if (! empty($jquery_validation_rules)) {
            foreach ($this->getDisplayStrategy()->getHtmlInputIds(true) as $html_id_with_pound_sign) {
                $jquery_validation_js[ $html_id_with_pound_sign ] = $jquery_validation_rules;
            }
        }
        return $jquery_validation_js;
    }



    /**
     * Sets the input's default value for use in displaying in the form. Note: value should be
     * normalized (Eg, if providing a default of ra Yes_NO_Input you would provide TRUE or FALSE, not '1' or '0')
     *
     * @param mixed $value
     * @return void
     */
    public function setDefault($value)
    {
        $this->default = $value;
        $this->setNormalizedValue($value);
        $this->setRawValue($value);
    }



    /**
     * Sets the normalized value on this input
     *
     * @param mixed $value
     */
    protected function setNormalizedValue($value)
    {
        $this->normalized_value = $value;
    }



    /**
     * Sets the raw value on this input (ie, exactly as the user submitted it)
     *
     * @param mixed $value
     */
    protected function setRawValue($value)
    {
        $this->raw_value = $this->normalization_strategy->unnormalize($value);
    }



    /**
     * Sets the HTML label text after it has already been defined
     *
     * @param string $label
     * @return void
     */
    public function setHtmlLabelText($label)
    {
        $this->html_label_text = $label;
    }



    /**
     * Sets whether or not this field is required, and adjusts the validation strategy.
     * If you want to use the ConditionallyRequiredValidation,
     * please add it as a validation strategy using addValidationStrategy as normal
     *
     * @param boolean $required boolean
     * @param string|null    $required_text
     */
    public function setRequired($required = true, $required_text = null)
    {
        $required = filter_var($required, FILTER_VALIDATE_BOOLEAN);
        // whether $required is a string or a boolean, we want to add a required validation strategy
        if ($required) {
            $this->addValidationStrategy(new RequiredValidation($required_text));
        } else {
            $this->removeValidationStrategy('RequiredValidation');
        }
        $this->required = $required;
    }



    /**
     * Returns whether or not this field is required
     *
     * @return boolean
     */
    public function required()
    {
        return $this->required;
    }



    /**
     * @param string $required_css_class
     */
    public function setRequiredCssClass($required_css_class)
    {
        $this->required_css_class = $required_css_class;
    }



    /**
     * @return string
     */
    public function requiredCssClass()
    {
        return $this->required_css_class;
    }



    /**
     * @param bool $add_required
     * @return string
     */
    public function htmlClass($add_required = false)
    {
        return $add_required && $this->required()
            ? $this->requiredCssClass() . ' ' . $this->html_class
            : $this->html_class;
    }


    /**
     * Sets the help text, in case
     *
     * @param string $text
     */
    public function setHtmlHelpText($text)
    {
        $this->html_help_text = $text;
    }



    /**
     * find_form_data_for_this_section
     * using this section's name and its parents, finds the value of the form data that corresponds to it.
     * For example, if this form section's HTML name is my_form[subform][form_input_1],
     * then it's value should be in $_REQUEST at $_REQUEST['my_form']['subform']['form_input_1'].
     * (If that doesn't exist, we also check for this subsection's name
     * at the TOP LEVEL of the request data. Eg $_REQUEST['form_input_1'].)
     * This function finds its value in the form.
     *
     * @param array $req_data
     * @return mixed whatever the raw value of this form section is in the request data
     * @throws \Error
     */
    public function findFormDataForThisSection($req_data)
    {
        $name_parts = $this->getInputNameParts();
        // now get the value for the input
        $value = $this->findRequestForSectionUsingNameParts($name_parts, $req_data);
        // check if this thing's name is at the TOP level of the request data
        if ($value === null && isset($req_data[ $this->name() ])) {
            $value = $req_data[ $this->name() ];
        }
        return $value;
    }



    /**
     * If this input's name is something like "foo[bar][baz]"
     * returns an array like `array('foo','bar',baz')`
     * @return array
     */
    protected function getInputNameParts()
    {
        // break up the html name by "[]"
        if (strpos($this->htmlName(), '[') !== false) {
            $before_any_brackets = substr($this->htmlName(), 0, strpos($this->htmlName(), '['));
        } else {
            $before_any_brackets = $this->htmlName();
        }
        // grab all of the segments
        preg_match_all('~\[([^]]*)\]~', $this->htmlName(), $matches);
        if (isset($matches[1]) && is_array($matches[1])) {
            $name_parts = $matches[1];
            array_unshift($name_parts, $before_any_brackets);
        } else {
            $name_parts = array($before_any_brackets);
        }
        return $name_parts;
    }



    /**
     * @param array $html_name_parts
     * @param array $req_data
     * @return array | NULL
     */
    public function findRequestForSectionUsingNameParts($html_name_parts, $req_data)
    {
        $first_part_to_consider = array_shift($html_name_parts);
        if (isset($req_data[ $first_part_to_consider ])) {
            if (empty($html_name_parts)) {
                return $req_data[ $first_part_to_consider ];
            } else {
                return $this->findRequestForSectionUsingNameParts(
                    $html_name_parts,
                    $req_data[ $first_part_to_consider ]
                );
            }
        } else {
            return null;
        }
    }



    /**
     * Checks if this form input's data is in the request data
     *
     * @param array $req_data like $_POST
     * @return boolean
     * @throws \Error
     */
    public function formDataPresentIn($req_data = null)
    {
        if ($req_data === null) {
            $req_data = $_POST;
        }
        $checked_value = $this->findFormDataForThisSection($req_data);
        if ($checked_value !== null) {
            return true;
        } else {
            return false;
        }
    }



    /**
     * Overrides parent to add js data from validation and display strategies
     *
     * @param array $form_other_js_data
     * @return array
     */
    public function getOtherJsData($form_other_js_data = array())
    {
        $form_other_js_data = $this->getOtherJsDataFromStrategies($form_other_js_data);
        return $form_other_js_data;
    }



    /**
     * Gets other JS data for localization from this input's strategies, like
     * the validation strategies and the display strategy
     *
     * @param array $form_other_js_data
     * @return array
     */
    public function getOtherJsDataFromStrategies($form_other_js_data = array())
    {
        $form_other_js_data = $this->getDisplayStrategy()->getOtherJsData($form_other_js_data);
        foreach ($this->getValidationStrategies() as $validation_strategy) {
            $form_other_js_data = $validation_strategy->getOtherJsData($form_other_js_data);
        }
        return $form_other_js_data;
    }



    /**
     * Override parent because we want to give our strategies an opportunity to enqueue some js and css
     *
     * @return void
     */
    public function enqueueJs()
    {
        // ask our display strategy and validation strategies if they have js to enqueue
        $this->enqueueJsFromStrategies();
    }



    /**
     * Tells strategies when its ok to enqueue their js and css
     *
     * @return void
     */
    public function enqueueJsFromStrategies()
    {
        $this->getDisplayStrategy()->enqueueJs();
        foreach ($this->getValidationStrategies() as $validation_strategy) {
            $validation_strategy->enqueueJs();
        }
    }



    /**
     * Gets the default value set on the input (not the current value, which may have been
     * changed because of a form submission). If no default was set, this us null.
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }



    /**
     * Makes this input disabled. That means it will have the HTML attribute 'disabled="disabled"',
     * and server-side if any input was received it will be ignored
     */
    public function disable($disable = true)
    {
        $this->disabled = filter_var($disable, FILTER_VALIDATE_BOOLEAN);
        if ($this->disabled) {
            $this->addOtherHtmlAttribute('disabled', 'disabled');
            $this->setNormalizedValue($this->getDefault());
        } else {
            $this->removeOtherHtmlAttribute('disabled');
        }
    }



    /**
     * Returns whether or not this input is currently disabled.
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }
}
