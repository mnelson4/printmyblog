<?php

namespace Twine\forms\base;

use InvalidArgumentException;
use Twine\forms\helpers\ImproperUsageException;
use Twine\forms\helpers\ValidationError;
use Twine\forms\inputs\FormInputBase;
use Twine\forms\strategies\display\HiddenDisplay;
use Twine\forms\strategies\layout\AdminTwoColumnLayout;
use Twine\forms\strategies\layout\FormSectionLayoutBase;
use Twine\forms\strategies\layout\TwoColumnLayout;
use Twine\helpers\Array2;

/**
 * For containing info about a non-field form section, which contains other form sections/fields.
 * Relies heavily on the script form_section_validation.js for client-side validation, mostly
 * the php code just provides form_section_validation.js with teh variables to use.
 * Important: in order for the JS to be loaded properly, you must construct a form section
 * before the hook wp_enqueue_scripts is called (so that the form section can enqueue its needed scripts).
 * However, you may output the form (usually by calling get_html) anywhere you like.
 */
class FormSection extends FormSectionValidatable
{

    const SUBMITTED_FORM_DATA_SSN_KEY = 'submitted_form_data';

    /**
     * Subsections
     *
     * @var FormSectionValidatable[]
     */
    protected $subsections = array();

    /**
     * Strategy for laying out the form
     *
     * @var FormSectionLayoutBase
     */
    protected $layout_strategy;

    /**
     * Whether or not this form has received and validated a form submission yet
     *
     * @var boolean
     */
    protected $received_submission = false;

    /**
     * message displayed to users upon successful form submission
     *
     * @var string
     */
    protected $form_submission_success_message = '';

    /**
     * message displayed to users upon unsuccessful form submission
     *
     * @var string
     */
    protected $form_submission_error_message = '';

    /**
     * @var array like $_REQUEST
     */
    protected $cached_request_data;

    /**
     * Stores whether this form (and its sub-sections) were found to be valid or not.
     * Starts off as null, but once the form is validated, it set to either true or false
     * @var boolean|null
     */
    protected $is_valid;

    /**
     * Stores all the data that will localized for form validation
     *
     * @var array
     */
    protected static $js_localization = array();

    /**
     * whether or not the form's localized validation JS vars have been set
     *
     * @type boolean
     */
    protected static $scripts_localized = false;

    /**
     * @var bool
     */
    protected $use_nonce = true;


    /**
     * when constructing a proper form section, calls _construct_finalize on children
     * so that they know who their parent is, and what name they've been given.
     *
     * @param array[] $options_array   {
     * @type          $subsections     FormSectionValidatable[] where keys are the section's name
     * @type          $include         string[] numerically-indexed where values are section names to be included,
     *                                 and in that order. This is handy if you want
     *                                 the subsections to be ordered differently than the default, and if you override
     *                                 which fields are shown
     * @type          $exclude         string[] values are subsections to be excluded. This is handy if you want
     *                                 to remove certain default subsections (note: if you specify BOTH 'include' AND
     *                                 'exclude', the inclusions will be applied first, and the exclusions will exclude
     *                                 items from that list of inclusions)
     * @type          $layout_strategy FormSectionLayoutBase strategy for laying out the form
     *                                 } @see FormSectionValidatable::__construct()
     * @type          $use_nonce whether or not to use a nonce on this form. Defaults to true.
     * @throws ImproperUsageException
     */
    public function __construct($options_array = array())
    {
        $options_array = (array) apply_filters(
            'FH_FormSectionProper___construct__options_array',
            $options_array,
            $this
        );
        // call parent first, as it may be setting the name
        parent::__construct($options_array);
        // if they've included subsections in the constructor, add them now
        if (isset($options_array['include'])) {
            // we are going to make sure we ONLY have those subsections to include
            // AND we are going to make sure they're in that specified order
            $reordered_subsections = array();
            foreach ($options_array['include'] as $input_name) {
                if (isset($this->subsections[ $input_name ])) {
                    $reordered_subsections[ $input_name ] = $this->subsections[ $input_name ];
                }
            }
            $this->subsections = $reordered_subsections;
        }
        if (isset($options_array['exclude'])) {
            $exclude            = $options_array['exclude'];
            $this->subsections = array_diff_key($this->subsections, array_flip($exclude));
        }
        if (isset($options_array['layout_strategy'])) {
            $this->layout_strategy = $options_array['layout_strategy'];
        }
        if (! $this->layout_strategy) {
            $this->layout_strategy = is_admin() ? new AdminTwoColumnLayout() : new TwoColumnLayout();
        }
        $this->layout_strategy->constructFinalize($this);
        /**
         * Gives other plugins a chance to hook in before construct finalize is called.
         * The form probably doesn't yet have a parent form section.
         * Since 4.9.32, when this action was introduced, this is the best place to add a subsection onto a form,
         * assuming you don't care what the form section's name, HTML ID, or HTML name etc are.
         * Also see AH_FormSectionProper___construct_finalize__end
         *
         * @param FormSection $this before __construct is done, but all of its logic,
         *                                              except maybe calling _construct_finalize has been done
         * @param array                  $options_array options passed into the constructor
         *
         *@since 4.9.32
         */
        do_action(
            'AH_FormInputBase___construct__before_construct_finalize_called',
            $this,
            $options_array
        );
        if (isset($options_array['name'])) {
            $this->constructFinalize(null, $options_array['name']);
        }
    }

    /**
     * @return string
     */
    public function htmlClass()
    {
        return $this->html_class . ' twine-form';
    }

    /**
     * Merges this form with the other one. Recursively calls merge on conflicting proper subsections.
     * @param FormSection $other_form
     *
     * @return FormSection returns this updated form section
     * @throws ImproperUsageException
     */
    public function merge(FormSection $other_form)
    {
        foreach ($other_form->subsections(false) as $name => $subsection) {
            if ($this->subsectionExists($name)) {
                if ($subsection instanceof FormSection) {
                    $this->getSubsection($name, false)->merge($subsection);
                }
                // else it's a conflicting input. Leave the original.
            } else {
                $this->addSubsection($name, $subsection);
            }
        }
        return $this;
    }


    /**
     * Finishes construction given the parent form section and this form section's name
     *
     * @param FormSection $parent_form_section
     * @param string                 $name
     *
     * @throws ImproperUsageException
     */
    public function constructFinalize($parent_form_section, $name)
    {
        parent::constructFinalize($parent_form_section, $name);
        $this->setDefaultNameIfEmpty();
        $this->setDefaultHtmlIdIfEmpty();
        foreach ($this->subsections as $subsection_name => $subsection) {
            if ($subsection instanceof FormSectionBase) {
                $subsection->constructFinalize($this, $subsection_name);
            } else {
                throw new ImproperUsageException(
                    sprintf(
                        esc_html__(
                            'Subsection "%s" is not an instanceof FormSectionBase on form "%s". It is a "%s"',
                            'print-my-blog'
                        ),
                        $subsection_name,
                        get_class($this),
                        $subsection ? get_class($subsection) : esc_html__('NULL', 'print-my-blog')
                    )
                );
            }
        }
        /**
         * Action performed just after form has been given a name (and HTML ID etc) and is fully constructed.
         * If you have code that should modify the form and needs it and its subsections to have a name, HTML ID
         * (or other attributes derived from the name like the HTML label id, etc), this is where it should be done.
         * This might only happen just before displaying the form, or just before it receives form submission data.
         * If you need to modify the form or its subsections before _construct_finalize is called on it (and we've
         * ensured it has a name, HTML IDs, etc
         *
         * @param FormSection      $this
         * @param FormSection|null $parent_form_section
         * @param string                      $name
         */
        do_action(
            'AH_FormSectionProper___construct_finalize__end',
            $this,
            $parent_form_section,
            $name
        );
    }


    /**
     * Gets the layout strategy for this form section
     *
     * @return FormSectionLayoutBase
     */
    public function getLayoutStrategy()
    {
        return $this->layout_strategy;
    }


    /**
     * Gets the HTML for a single input for this form section according
     * to the layout strategy
     *
     * @param FormInputBase $input
     * @return string
     */
    public function getHtmlForInput($input)
    {
        return $this->layout_strategy->layoutInput($input);
    }


    /**
     * was_submitted - checks if form inputs are present in request data
     * Basically an alias for form_data_present_in() (which is used by both
     * proper form sections and form inputs)
     *
     * @param null $form_data
     * @return boolean
     */
    public function wasSubmitted($form_data = null)
    {
        return $this->formDataPresentIn($form_data);
    }

    /**
     * Gets the cached request data; but if there is none, or $req_data was set with
     * something different, refresh the cache, and then return it
     * @param null $req_data
     * @return array
     */
    protected function getCachedRequest($req_data = null)
    {
        if (
            $this->cached_request_data === null
            || (
                $req_data !== null
                && $req_data !== $this->cached_request_data
            )
        ) {
            $req_data = apply_filters(
                'FH_FormSectionProper__receive_form_submission__req_data',
                $req_data,
                $this
            );
            if ($req_data === null) {
                $req_data = array_merge($_GET, $_POST);
            }
            $req_data = apply_filters(
                'FH_FormSectionProper__receive_form_submission__request_data',
                $req_data,
                $this
            );
            $this->cached_request_data = (array) $req_data;
        }
        return $this->cached_request_data;
    }


    /**
     * After the form section is initially created, call this to sanitize the data in the submission
     * which relates to this form section, validate it, and set it as properties on the form.
     *
     * @param array|null $req_data should usually be $_POST (the default).
     *                             However, you CAN supply a different array.
     *                             Consider using set_defaults() instead however.
     *                             (If you rendered the form in the page using echo $form_x->get_html()
     *                             the inputs will have the correct name in the request data for this function
     *                             to find them and populate the form with them.
     *                             If you have a flat form (with only input subsections),
     *                             you can supply a flat array where keys
     *                             are the form input names and values are their values)
     * @param boolean    $validate whether or not to perform validation on this data. Default is,
     *                             of course, to validate that data, and set errors on the invalid values.
     *                             But if the data has already been validated
     *                             (eg you validated the data then stored it in the DB)
     *                             you may want to skip this step.
     * @throws InvalidArgumentException
     * @throws ImproperUsageException
     */
    public function receiveFormSubmission($req_data = null, $validate = true)
    {
        $req_data = $this->getCachedRequest($req_data);
        $this->normalize($req_data);
        if ($validate) {
            $this->validate();
        }
        if ($this->submissionErrorMessage() === '' && ! $this->isValid()) {
            $this->setSubmissionErrorMessage();
        }
        do_action(
            'AH_FormSectionProper__receive_form_submission__end',
            $req_data,
            $this,
            $validate
        );
    }

    /**
     * Populates the default data for the form, given an array where keys are
     * the input names, and values are their values (preferably normalized to be their
     * proper PHP types, not all strings... although that should be ok too).
     * Proper subsections are sub-arrays, the key being the subsection's name, and
     * the value being an array formatted in teh same way
     *
     * @param array $default_data
     * @throws ImproperUsageException
     */
    public function populateDefaults($default_data)
    {
        foreach ($this->subsections(false) as $subsection_name => $subsection) {
            if (isset($default_data[ $subsection_name ])) {
                if ($subsection instanceof FormInputBase) {
                    $subsection->setDefault($default_data[ $subsection_name ]);
                } elseif ($subsection instanceof FormSection) {
                    $subsection->populateDefaults($default_data[ $subsection_name ]);
                }
            } else {
                // maybe the defaults are for form inputs only? try that
                if ($subsection instanceof FormSection) {
                    $subsection->populateDefaults($default_data);
                }
            }
        }
    }

    /**
     * Recursively searches through the form for an input with the specified name.
     * @param $input_name
     *
     * @return FormSectionValidatable|null
     */
    public function findSection($input_name)
    {
        foreach ($this->subsections() as $subsection_name => $subsection_obj) {
            if ($subsection_name === $input_name) {
                return $subsection_obj;
            }
            if ($subsection_obj instanceof FormSection) {
                return $subsection_obj->findSection($input_name);
            }
        }
        return null;
    }


    /**
     * returns true if subsection exists
     *
     * @param string $name
     * @return boolean
     */
    public function subsectionExists($name)
    {
        return isset($this->subsections[ $name ]) ? true : false;
    }


    /**
     * Gets the subsection specified by its name
     *
     * @param string  $name
     * @param boolean $require_construction_to_be_finalized most client code should leave this as TRUE
     *                                                      so that the inputs will be properly configured.
     *                                                      However, some client code may be ok
     *                                                      with construction finalize being called later
     *                                                      (realizing that the subsections' html names
     *                                                      might not be set yet, etc.)
     * @return FormSectionBase|null
     * @throws ImproperUsageException
     */
    public function getSubsection($name, $require_construction_to_be_finalized = true)
    {
        if ($require_construction_to_be_finalized) {
            $this->ensureConstructFinalizedCalled();
        }
        return $this->subsectionExists($name) ? $this->subsections[ $name ] : null;
    }


    /**
     * Gets all the validatable subsections of this form section
     *
     * @return FormSectionValidatable[]
     * @throws ImproperUsageException
     */
    public function getValidatableSubsections()
    {
        $validatable_subsections = array();
        foreach ($this->subsections() as $name => $obj) {
            if ($obj instanceof FormSectionValidatable) {
                $validatable_subsections[ $name ] = $obj;
            }
        }
        return $validatable_subsections;
    }


    /**
     * Gets an input by the given name. If not found, or if its not an FOrm_Input_Base child,
     * throw an Error.
     *
     * @param string  $name
     * @param boolean $require_construction_to_be_finalized most client code should
     *                                                      leave this as TRUE so that the inputs will be properly
     *                                                      configured. However, some client code may be ok with
     *                                                      construction finalize being called later
     *                                                      (realizing that the subsections' html names might not be
     *                                                      set yet, etc.)
     * @return FormInputBase
     * @throws ImproperUsageException
     */
    public function getInput($name, $require_construction_to_be_finalized = true)
    {
        $subsection = $this->getSubsection(
            $name,
            $require_construction_to_be_finalized
        );
        if (! $subsection instanceof FormInputBase) {
            throw new ImproperUsageException(
                sprintf(
                    esc_html__(
                        "Subsection '%s' is not an instanceof FormInputBase on form '%s'. It is a '%s'",
                        'print-my-blog'
                    ),
                    $name,
                    get_class($this),
                    $subsection ? get_class($subsection) : esc_html__('NULL', 'print-my-blog')
                )
            );
        }
        return $subsection;
    }


    /**
     * Like get_input(), gets the proper subsection of the form given the name,
     * otherwise throws an Error
     *
     * @param string  $name
     * @param boolean $require_construction_to_be_finalized most client code should
     *                                                      leave this as TRUE so that the inputs will be properly
     *                                                      configured. However, some client code may be ok with
     *                                                      construction finalize being called later
     *                                                      (realizing that the subsections' html names might not be
     *                                                      set yet, etc.)
     *
     * @return FormSection
     * @throws ImproperUsageException
     */
    public function getProperSubsection($name, $require_construction_to_be_finalized = true)
    {
        $subsection = $this->getSubsection(
            $name,
            $require_construction_to_be_finalized
        );
        if (! $subsection instanceof FormSection) {
            throw new ImproperUsageException(
                sprintf(
                    esc_html__(
                        "Subsection '%'s is not an instanceof FormSectionProper on form '%s'",
                        'print-my-blog'
                    ),
                    $name,
                    get_class($this)
                )
            );
        }
        return $subsection;
    }


    /**
     * Gets the value of the specified input. Should be called after receive_form_submission()
     * or populate_defaults() on the form, where the normalized value on the input is set.
     *
     * @param string $name
     * @return mixed depending on the input's type and its normalization strategy
     * @throws ImproperUsageException
     */
    public function getInputValue($name)
    {
        $input = $this->getInput($name);
        return $input->normalizedValue();
    }


    /**
     * Checks if this form section itself is valid, and then checks its subsections
     *
     * @throws ImproperUsageException
     * @return boolean
     */
    public function isValid()
    {
        if ($this->is_valid === null) {
            if (! $this->hasHeceivedSubmission()) {
                throw new ImproperUsageException(
                    sprintf(
                        esc_html__(
                            'You cannot check if a form is valid before receiving the form submission using receive_form_submission',
                            'print-my-blog'
                        )
                    )
                );
            }
            if (! parent::isValid()) {
                $this->is_valid = false;
            } else {
                // ok so no general errors to this entire form section.
                // so let's check the subsections, but only set errors if that hasn't been done yet
                $this->is_valid = true;
                foreach ($this->getValidatableSubsections() as $subsection) {
                    if (! $subsection->isValid()) {
                        $this->is_valid = false;
                    }
                }
            }
        }
        return $this->is_valid;
    }


    /**
     * gets the default name of this form section if none is specified
     *
     * @return void
     */
    protected function setDefaultNameIfEmpty()
    {
        if (! $this->name) {
            $classname    = get_class($this);
            $default_name = str_replace('', '', $classname);
            $this->name  = $default_name;
        }
    }


    /**
     * Returns the HTML for the form, except for the form opening and closing tags
     * (as the form section doesn't know where you necessarily want to send the information to),
     * and except for a submit button. Enqueues JS and CSS; if called early enough we will
     * try to enqueue them in the header, otherwise they'll be enqueued in the footer.
     * Not doing_it_wrong because theoretically this CAN be used properly,
     * provided its used during "wp_enqueue_scripts", or it doesn't need to enqueue
     * any CSS.
     *
     * @throws InvalidArgumentException
     * @throws ImproperUsageException
     */
    public function getHtmlAndJs()
    {
        $this->enqueueJs();
        return $this->getHtml();
    }


    /**
     * returns HTML for displaying this form section. recursively calls display_section() on all subsections
     *
     * @param bool $display_previously_submitted_data
     * @return string
     * @throws InvalidArgumentException
     * @throws ImproperUsageException
     */
    public function getHtml()
    {
        $this->ensureConstructFinalizedCalled();
        return $this->layout_strategy->layoutForm();
    }


    /**
     * enqueues JS and CSS for the form.
     * It is preferred to call this before wp_enqueue_scripts so the
     * scripts and styles can be put in the header, but if called later
     * they will be put in the footer (which is OK for JS, but in HTML4 CSS should
     * only be in the header; but in HTML5 its ok in the body.
     * See http://stackoverflow.com/questions/4957446/load-external-css-file-in-body-tag.
     * So if your form enqueues CSS, it's preferred to call this before wp_enqueue_scripts.)
     *
     * @return void
     * @throws ImproperUsageException
     */
    public function enqueueJs()
    {
        // ok so we are definitely going to want the forms JS,
        // so enqueue it or remember to enqueue it during wp_enqueue_scripts
        if (did_action('wp_enqueue_scripts') || did_action('admin_enqueue_scripts')) {
            // ok so they've constructed this object after when they should have.
            // just enqueue the generic form scripts and initialize the form immediately in the JS
            FormSection::wpEnqueueScripts(true);
        } else {
            add_action('wp_enqueue_scripts', array('Twine\forms\base\FormSection', 'wpEnqueueScripts'));
            add_action('admin_enqueue_scripts', array('Twine\forms\base\FormSection', 'wpEnqueueScripts'));
        }
        add_action('wp_footer', array($this, 'ensureScriptsLocalized'), 1);
        $this->enqueueAndLocalizeFormJs();
        foreach ($this->subsections() as $subsection) {
            $subsection->enqueueJs();
        }
    }


    /**
     * adds a filter so that jquery validate gets enqueued in System::wp_enqueue_scripts().
     * This must be done BEFORE wp_enqueue_scripts() gets called, which is on
     * the wp_enqueue_scripts hook.
     * However, registering the form js and localizing it can happen when we
     * actually output the form (which is preferred, seeing how teh form's fields
     * could change until it's actually outputted)
     *
     * @param boolean $init_form_validation_automatically whether or not we want the form validation
     *                                                    to be triggered automatically or not
     * @return void
     */
    public static function wpEnqueueScripts($init_form_validation_automatically = true)
    {
        wp_register_script(
            'twine-jquery-validate',
            TWINE_SCRIPTS_URL . '/jquery.validate.min.js',
            array('jquery'),
            filemtime(TWINE_SCRIPTS_DIR . 'jquery.validate.min.js')
        );
        wp_register_script(
            'twine-jquery-validate-additional-methods',
            TWINE_SCRIPTS_URL . '/jquery.validate.additional-methods.min.js',
            array('jquery'),
            filemtime(TWINE_SCRIPTS_DIR . 'jquery.validate.additional-methods.min.js')
        );
        wp_enqueue_script(
            'twine_form_section_validation',
            TWINE_SCRIPTS_URL . '/form_section_validation.js',
            array('twine-jquery-validate', 'jquery-ui-datepicker', 'twine-jquery-validate-additional-methods'),
            filemtime(TWINE_SCRIPTS_DIR . 'form_section_validation.js'),
            true
        );
        wp_localize_script(
            'twine_form_section_validation',
            'twine_form_section_validation_init',
            array('init' => $init_form_validation_automatically ? '1' : '0')
        );
        wp_enqueue_style(
            'twine-jquery-ui',
            TWINE_STYLES_URL . '/jquery-ui-1.10.3.custom.css',
            [],
            filemtime(TWINE_STYLES_DIR . 'jquery-ui-1.10.3.custom.css')
        );
    }


    /**
     * gets the variables used by form_section_validation.js.
     * This needs to be called AFTER we've called $this->_enqueue_jquery_validate_script,
     * but before the wordpress hook wp_loaded
     */
    public function enqueueAndLocalizeFormJs()
    {
        $this->ensureConstructFinalizedCalled();
        wp_enqueue_style(
            'twine-forms',
            TWINE_STYLES_URL . 'forms.css',
            [],
            filemtime(TWINE_STYLES_DIR . 'forms.css')
        );
        // actually, we don't want to localize just yet. There may be other forms on the page.
        // so we need to add our form section data to a static variable accessible by all form sections
        // and localize it just before the footer
        $this->localizeValidationRules();
        add_action('wp_footer', array('Twine\forms\base\FormSection', 'localizeScriptForAllForms'), 2);
        add_action('admin_footer', array('Twine\forms\base\FormSection', 'localizeScriptForAllForms'));
    }


    /**
     * add our form section data to a static variable accessible by all form sections
     *
     * @param bool $return_for_subsection
     * @return void
     */
    public function localizeValidationRules($return_for_subsection = false)
    {
        // we only want to localize vars ONCE for the entire form,
        // so if the form section doesn't have a parent, then it must be the top dog
        if ($return_for_subsection || ! $this->parentSection()) {
            FormSection::$js_localization['form_data'][ $this->htmlId() ] = array(
                'form_section_id'  => $this->htmlId(true),
                'validation_rules' => $this->getJqueryValdationRules(),
                'other_data'       => $this->getOtherJsData(),
                'errors'           => $this->subsectionValidationErrorsByHtmlName(),
            );
            FormSection::$scripts_localized                                = true;
        }
    }


    /**
     * Gets an array of extra data that will be useful for client-side javascript.
     * This is primarily data added by inputs and forms in addition to any
     * scripts they might enqueue
     *
     * @param array $form_other_js_data
     * @return array
     */
    public function getOtherJsData($form_other_js_data = array())
    {
        foreach ($this->subsections() as $subsection) {
            $form_other_js_data = $subsection->getOtherJsData($form_other_js_data);
        }
        return $form_other_js_data;
    }


    /**
     * Gets a flat array of inputs for this form section and its subsections.
     * Keys are their form input names, and values are the inputs themselves
     *
     * @return FormInputBase[]
     */
    public function inputsInSubsections()
    {
        $inputs = array();
        foreach ($this->subsections() as $subsection) {
            if ($subsection instanceof FormInputBase) {
                $inputs[ $subsection->htmlName() ] = $subsection;
            } elseif ($subsection instanceof FormSection) {
                $inputs += $subsection->inputsInSubsections();
            }
        }
        return $inputs;
    }


    /**
     * Gets a flat array of all the validation errors.
     * Keys are html names (because those should be unique)
     * and values are a string of all their validation errors
     *
     * @return string[]
     */
    public function subsectionValidationErrorsByHtmlName()
    {
        $inputs = $this->inputs();
        $errors = array();
        foreach ($inputs as $form_input) {
            if ($form_input instanceof FormInputBase && $form_input->getValidationErrors()) {
                $errors[ $form_input->htmlName() ] = $form_input->getValidationErrorString();
            }
        }
        return $errors;
    }


    /**
     * passes all the form data required by the JS to the JS, and enqueues the few required JS files.
     * Should be setup by each form during the _enqueues_and_localize_form_js
     *
     * @throws InvalidArgumentException
     */
    public static function localizeScriptForAllForms()
    {
        // allow inputs and stuff to hook in their JS and stuff here
        do_action('AH_FormSectionProper__localize_script_for_all_forms__begin');
        FormSection::$js_localization['localized_error_messages'] = FormSection::getLocalizedErrorMessages();
        $email_validation_level                                          = 'wp_default';
        FormSection::$js_localization['email_validation_level']   = $email_validation_level;
        wp_enqueue_script('twine_form_section_validation');
        wp_localize_script(
            'twine_form_section_validation',
            'twine_form_section_vars',
            FormSection::$js_localization
        );
    }


    /**
     * ensure_scripts_localized
     */
    public function ensureScriptsLocalized()
    {
        if (! FormSection::$scripts_localized) {
            $this->enqueueAndLocalizeFormJs();
        }
    }


    /**
     * Gets the hard-coded validation error messages to be used in the JS. The convention
     * is that the key here should be the same as the custom validation rule put in the JS file
     *
     * @return array keys are custom validation rules, and values are internationalized strings
     */
    private static function getLocalizedErrorMessages()
    {
        return array(
            'validUrl' => esc_html__(
                'This is not a valid absolute URL. Eg, http://domain.com/monkey.jpg',
                'print-my-blog'
            ),
            'regex'    => esc_html__('Please check your input', 'print-my-blog'),
        );
    }


    /**
     * @return array
     */
    public static function jsLocalization()
    {
        return self::$js_localization;
    }


    /**
     * @return void
     */
    public static function resetJsLocalization()
    {
        self::$js_localization = array();
    }


    /**
     * Gets the JS to put inside the jquery validation rules for subsection of this form section.
     * See parent function for more...
     *
     * @return array
     */
    public function getJqueryValdationRules()
    {
        $jquery_validation_rules = array();
        foreach ($this->getValidatableSubsections() as $subsection) {
            $jquery_validation_rules = array_merge(
                $jquery_validation_rules,
                $subsection->getJqueryValdationRules()
            );
        }
        return $jquery_validation_rules;
    }


    /**
     * Sanitizes all the data and sets the sanitized value of each field
     *
     * @param array $req_data like $_POST
     * @return void
     */
    protected function normalize($req_data)
    {
        if($this->useNonce()) {
            check_admin_referer($this->name(), $this->name() . '_nonce');
        }
        $this->received_submission = true;
        $this->validation_errors   = array();
        foreach ($this->getValidatableSubsections() as $subsection) {
            try {
                $subsection->normalize($req_data);
            } catch (ValidationError $e) {
                $subsection->addValidationError($e);
            }
        }
    }


    /**
     * Performs validation on this form section and its subsections.
     * For each subsection,
     * calls _validate_{subsection_name} on THIS form (if the function exists)
     * and passes it the subsection, then calls _validate on that subsection.
     * If you need to perform validation on the form as a whole (considering multiple)
     * you would be best to override this _validate method,
     * calling parent::_validate() first.
     */
    protected function validate()
    {
        // reset the cache of whether this form is valid or not- we're re-validating it now
        $this->is_valid = null;
        foreach ($this->getValidatableSubsections() as $subsection_name => $subsection) {
            if (method_exists($this, '_validate_' . $subsection_name)) {
                call_user_func_array(array($this, '_validate_' . $subsection_name), array($subsection));
            }
            $subsection->validate();
        }
    }


    /**
     * Gets all the validated inputs for the form section
     *
     * @return array
     * @throws ImproperUsageException
     */
    public function validData()
    {
        $inputs = array();
        foreach ($this->subsections() as $subsection_name => $subsection) {
            if ($subsection instanceof FormSection) {
                $inputs[ $subsection_name ] = $subsection->validData();
            } elseif ($subsection instanceof FormInputBase) {
                $inputs[ $subsection_name ] = $subsection->normalizedValue();
            }
        }
        return $inputs;
    }


    /**
     * Gets all the inputs on this form section
     *
     * @return FormInputBase[]
     * @throws ImproperUsageException
     */
    public function inputs()
    {
        $inputs = array();
        foreach ($this->subsections() as $subsection_name => $subsection) {
            if ($subsection instanceof FormInputBase) {
                $inputs[ $subsection_name ] = $subsection;
            }
        }
        return $inputs;
    }


    /**
     * Gets all the subsections which are a proper form
     *
     * @return FormSection[]
     * @throws @throws ImproperUsageException
     */
    public function subforms()
    {
        $form_sections = array();
        foreach ($this->subsections() as $name => $obj) {
            if ($obj instanceof FormSection) {
                $form_sections[ $name ] = $obj;
            }
        }
        return $form_sections;
    }


    /**
     * Gets all the subsections (inputs, proper subsections, or html-only sections).
     * Consider using inputs() or subforms()
     * if you only want form inputs or proper form sections.
     *
     * @param boolean $require_construction_to_be_finalized most client code should
     *                                                      leave this as TRUE so that the inputs will be properly
     *                                                      configured. However, some client code may be ok with
     *                                                      construction finalize being called later
     *                                                      (realizing that the subsections' html names might not be
     *                                                      set yet, etc.)
     *
     * @return FormSection[]
     * @throws @throws ImproperUsageException
     */
    public function subsections($require_construction_to_be_finalized = true)
    {
        if ($require_construction_to_be_finalized) {
            $this->ensureConstructFinalizedCalled();
        }
        return $this->subsections;
    }


    /**
     * Returns whether this form has any subforms or inputs
     * @return bool
     */
    public function hasSubsections()
    {
        return ! empty($this->subsections);
    }


    /**
     * Returns a simple array where keys are input names, and values are their normalized
     * values. (Similar to calling get_input_value on inputs)
     *
     * @param boolean $include_subform_inputs Whether to include inputs from subforms,
     *                                        or just this forms' direct children inputs
     * @param boolean $flatten                Whether to force the results into 1-dimensional array,
     *                                        or allow multidimensional array
     * @return array if $flatten is TRUE it will always be a 1-dimensional array
     *                                        with array keys being input names
     *                                        (regardless of whether they are from a subsection or not),
     *                                        and if $flatten is FALSE it can be a multidimensional array
     *                                        where keys are always subsection names and values are either
     *                                        the input's normalized value, or an array like the top-level array
     * @throws @throws ImproperUsageException
     */
    public function inputValues($include_subform_inputs = false, $flatten = false)
    {
        return $this->inputValuesList(false, $include_subform_inputs, $flatten);
    }


    /**
     * Similar to FormSectionProper::input_values(), except this returns the 'display_value'
     * of each input. On some inputs (especially radio boxes or checkboxes), the value stored
     * is not necessarily the value we want to display to users. This creates an array
     * where keys are the input names, and values are their display values
     *
     * @param boolean $include_subform_inputs Whether to include inputs from subforms,
     *                                        or just this forms' direct children inputs
     * @param boolean $flatten                Whether to force the results into 1-dimensional array,
     *                                        or allow multidimensional array
     * @return array if $flatten is TRUE it will always be a 1-dimensional array
     *                                        with array keys being input names
     *                                        (regardless of whether they are from a subsection or not),
     *                                        and if $flatten is FALSE it can be a multidimensional array
     *                                        where keys are always subsection names and values are either
     *                                        the input's normalized value, or an array like the top-level array
     * @throws @throws ImproperUsageException
     */
    public function inputPrettyValues($include_subform_inputs = false, $flatten = false)
    {
        return $this->inputValuesList(true, $include_subform_inputs, $flatten);
    }


    /**
     * Gets the input values from the form
     *
     * @param boolean $pretty                 Whether to retrieve the pretty value,
     *                                        or just the normalized value
     * @param boolean $include_subform_inputs Whether to include inputs from subforms,
     *                                        or just this forms' direct children inputs
     * @param boolean $flatten                Whether to force the results into 1-dimensional array,
     *                                        or allow multidimensional array
     * @return array if $flatten is TRUE it will always be a 1-dimensional array with array keys being
     *                                        input names (regardless of whether they are from a subsection or not),
     *                                        and if $flatten is FALSE it can be a multidimensional array
     *                                        where keys are always subsection names and values are either
     *                                        the input's normalized value, or an array like the top-level array
     * @throws ImproperUsageException
     */
    public function inputValuesList($pretty = false, $include_subform_inputs = false, $flatten = false)
    {
        $input_values = array();
        foreach ($this->subsections() as $subsection_name => $subsection) {
            if ($subsection instanceof FormInputBase) {
                $input_values[ $subsection_name ] = $pretty
                    ? $subsection->prettyValue()
                    : $subsection->normalizedValue();
            } elseif ($subsection instanceof FormSection && $include_subform_inputs) {
                $subform_input_values = $subsection->inputValuesList(
                    $pretty,
                    $include_subform_inputs,
                    $flatten
                );
                if ($flatten) {
                    $input_values = array_merge($input_values, $subform_input_values);
                } else {
                    $input_values[ $subsection_name ] = $subform_input_values;
                }
            }
        }
        return $input_values;
    }


    /**
     * Gets the originally submitted input values from the form
     *
     * @param boolean $include_subforms  Whether to include inputs from subforms,
     *                                   or just this forms' direct children inputs
     * @return array                     if $flatten is TRUE it will always be a 1-dimensional array
     *                                   with array keys being input names
     *                                   (regardless of whether they are from a subsection or not),
     *                                   and if $flatten is FALSE it can be a multidimensional array
     *                                   where keys are always subsection names and values are either
     *                                   the input's normalized value, or an array like the top-level array
     * @throws ImproperUsageException
     */
    public function submittedValues($include_subforms = false)
    {
        $submitted_values = array();
        foreach ($this->subsections() as $subsection) {
            if ($subsection instanceof FormInputBase) {
                // is this input part of an array of inputs?
                if (strpos($subsection->htmlName(), '[') !== false) {
                    $full_input_name  = Array2::convertArrayValuesToKeys(
                        explode(
                            '[',
                            str_replace(']', '', $subsection->htmlName())
                        ),
                        $subsection->rawValue()
                    );
                    $submitted_values = array_replace_recursive($submitted_values, $full_input_name);
                } else {
                    $submitted_values[ $subsection->htmlName() ] = $subsection->rawValue();
                }
            } elseif ($subsection instanceof FormSection && $include_subforms) {
                $subform_input_values = $subsection->submittedValues($include_subforms);
                $submitted_values     = array_replace_recursive($submitted_values, $subform_input_values);
            }
        }
        return $submitted_values;
    }


    /**
     * Indicates whether or not this form has received a submission yet
     * (ie, had receive_form_submission called on it yet)
     *
     * @return boolean
     * @throws ImproperUsageException
     */
    public function hasHeceivedSubmission()
    {
        $this->ensureConstructFinalizedCalled();
        return $this->received_submission;
    }


    /**
     * Equivalent to passing 'exclude' in the constructor's options array.
     * Removes the listed inputs from the form
     *
     * @param array $inputs_to_exclude values are the input names
     * @return void
     */
    public function exclude(array $inputs_to_exclude = array())
    {
        foreach ($inputs_to_exclude as $input_to_exclude_name) {
            unset($this->subsections[ $input_to_exclude_name ]);
        }
    }


    /**
     * Changes these inputs' display strategy to be HiddenDisplay.
     * @param array $inputs_to_hide
     * @throws \Exception
     */
    public function hide(array $inputs_to_hide = array())
    {
        foreach ($inputs_to_hide as $input_to_hide) {
            $input = $this->getInput($input_to_hide);
            $input->overwriteDisplayStrategy(new HiddenDisplay());
        }
    }


    /**
     * add_subsections
     * Adds the listed subsections to the form section.
     * If $subsection_name_to_target is provided,
     * then new subsections are added before or after that subsection,
     * otherwise to the start or end of the entire subsections array.
     *
     * @param FormSectionBase[] $new_subsections           array of new form subsections
     *                                                          where keys are their names
     * @param string                 $subsection_name_to_target an existing for section that $new_subsections
     *                                                          should be added before or after
     *                                                          IF $subsection_name_to_target is null,
     *                                                          then $new_subsections will be added to
     *                                                          the beginning or end of the entire subsections array
     * @param boolean                $add_before                whether to add $new_subsections, before or after
     *                                                          $subsection_name_to_target,
     *                                                          or if $subsection_name_to_target is null,
     *                                                          before or after entire subsections array
     * @return void
     * @throws ImproperUsageException
     */
    public function addSubsections($new_subsections, $subsection_name_to_target = null, $add_before = true)
    {
        foreach ($new_subsections as $subsection_name => $subsection) {
            if (! $subsection instanceof FormSectionBase) {
                throw new ImproperUsageException(
                    sprintf(
                        esc_html__(
                            "Trying to add a %s as a subsection (it was named '%s') to the form section '%s'. It was removed.",
                            'print-my-blog'
                        ),
                        get_class($subsection),
                        $subsection_name,
                        $this->name()
                    )
                );
                unset($new_subsections[ $subsection_name ]);
            }
        }
        $this->subsections = Array2::insertIntoArray(
            $this->subsections,
            $new_subsections,
            $subsection_name_to_target,
            $add_before
        );
        if ($this->construction_finalized) {
            foreach ($this->subsections as $name => $subsection) {
                $subsection->constructFinalize($this, $name);
            }
        }
    }

    /**
     * Adds the specified section
     * @param string $name
     * @param FormSectionBase $form_section
     */
    public function addSubsection($name, FormSectionBase $form_section)
    {
        $this->subsections[$name] = $form_section;
        if ($this->construction_finalized) {
            $form_section->constructFinalize($this, $name);
        }
    }


    /**
     * @param string $subsection_name
     * @param bool   $recursive
     * @return bool
     */
    public function hasSubsection($subsection_name, $recursive = false)
    {
        foreach ($this->subsections as $name => $subsection) {
            if (
                $name === $subsection_name
                || (
                    $recursive
                    && $subsection instanceof FormSection
                    && $subsection->hasSubsection($subsection_name, $recursive)
                )
            ) {
                return true;
            }
        }
        return false;
    }



    /**
     * Just gets all validatable subsections to clean their sensitive data
     */
    public function cleanSensitiveData()
    {
        foreach ($this->getValidatableSubsections() as $subsection) {
            $subsection->cleanSensitiveData();
        }
    }


    /**
     * Sets the submission error message (aka validation error message for this form section and all sub-sections)
     * @param string                           $form_submission_error_message
     * @param FormSectionValidatable $form_section unused
     */
    public function setSubmissionErrorMessage(
        $form_submission_error_message = ''
    ) {
        $this->form_submission_error_message = ! empty($form_submission_error_message)
            ? $form_submission_error_message
            : $this->getAllValidationErrorsString();
    }


    /**
     * Returns the cached error message. A default value is set for this during _validate(),
     * (called during receive_form_submission) but it can be explicitly set using
     * set_submission_error_message
     *
     * @return string
     */
    public function submissionErrorMessage()
    {
        return $this->form_submission_error_message;
    }


    /**
     * Sets a message to display if the data submitted to the form was valid.
     * @param string $form_submission_success_message
     */
    public function setSubmissionSuccessMessage($form_submission_success_message = '')
    {
        $this->form_submission_success_message = ! empty($form_submission_success_message)
            ? $form_submission_success_message
            : esc_html__('Form submitted successfully', 'print-my-blog');
    }


    /**
     * Gets a message appropriate for display when the form is correctly submitted
     * @return string
     */
    public function submissionSuccessMessage()
    {
        return $this->form_submission_success_message;
    }


    /**
     * Returns the prefix that should be used on child of this form section for
     * their html names. If this form section itself has a parent, prepends ITS
     * prefix onto this form section's prefix. Used primarily by
     * FormInputBase::_set_default_html_name_if_empty
     *
     * @return string
     */
    public function htmlNamePrefix()
    {
        if ($this->parentSection() instanceof FormSection) {
            return $this->parentSection()->htmlNamePrefix() . '[' . $this->name() . ']';
        }
        return $this->name();
    }


    /**
     * Gets the name, but first checks _construct_finalize has been called. If not,
     * calls it (assumes there is no parent and that we want the name to be whatever
     * was set, which is probably nothing, or the classname)
     *
     * @return string
     * @throws ImproperUsageException
     */
    public function name()
    {
        $this->ensureConstructFinalizedCalled();
        return parent::name();
    }


    /**
     * @return FormSection
     * @throws ImproperUsageException
     */
    public function parentSection()
    {
        $this->ensureConstructFinalizedCalled();
        return parent::parentSection();
    }


    /**
     * make sure construction finalized was called, otherwise children might not be ready
     *
     * @return void
     * @throws ImproperUsageException
     */
    public function ensureConstructFinalizedCalled()
    {
        if (! $this->construction_finalized) {
            $this->constructFinalize($this->parent_section, $this->name);
        }
    }


    /**
     * Checks if any of this form section's inputs, or any of its children's inputs,
     * are in teh form data. If any are found, returns true. Else false
     *
     * @param array $req_data
     * @return boolean
     * @throws ImproperUsageException
     */
    public function formDataPresentIn($req_data = null)
    {
        $req_data = $this->getCachedRequest($req_data);
        foreach ($this->subsections() as $subsection) {
            if ($subsection instanceof FormInputBase) {
                if ($subsection->formDataPresentIn($req_data)) {
                    return true;
                }
            } elseif ($subsection instanceof FormSection) {
                if ($subsection->formDataPresentIn($req_data)) {
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * Gets validation errors for this form section and subsections
     * Similar to FormSectionValidatable::get_validation_errors() except this
     * gets the validation errors for ALL subsection
     *
     * @return ValidationError[]
     * @throws ImproperUsageException
     */
    public function getValidationErrorsAccumulated()
    {
        $validation_errors = $this->getValidationErrors();
        foreach ($this->getValidatableSubsections() as $subsection) {
            if ($subsection instanceof FormSection) {
                $validation_errors_on_this_subsection = $subsection->getValidationErrorsAccumulated();
            } else {
                $validation_errors_on_this_subsection = $subsection->getValidationErrors();
            }
            if ($validation_errors_on_this_subsection) {
                $validation_errors = array_merge($validation_errors, $validation_errors_on_this_subsection);
            }
        }
        return $validation_errors;
    }

    /**
     * Fetch validation errors from children and grandchildren and puts them in a single string.
     * This traverses the form section tree to generate this, but you probably want to instead use
     * get_form_submission_error_message() which is usually this message cached (or a custom validation error message)
     *
     * @return string
     * @since 4.9.59.p
     */
    protected function getAllValidationErrorsString()
    {
        $submission_error_messages = array();
        // bad, bad, bad registrant
        foreach ($this->getValidationErrorsAccumulated() as $validation_error) {
            if ($validation_error instanceof ValidationError) {
                $form_section = $validation_error->getFormSection();
                if ($form_section instanceof FormInputBase) {
                    $label = $validation_error->getFormSection()->htmlLabelText();
                } elseif ($form_section instanceof FormSectionValidatable) {
                    $label = $validation_error->getFormSection()->name();
                } else {
                    $label = esc_html__('Unknown', 'print-my-blog');
                }
                $submission_error_messages[] = sprintf(
                    __('%s : %s', 'print-my-blog'),
                    $label,
                    $validation_error->getMessage()
                );
            }
        }
        return implode('<br', $submission_error_messages);
    }


    /**
     * This isn't just the name of an input, it's a path pointing to an input. The
     * path is similar to a folder path: slash (/) means to descend into a subsection,
     * dot-dot-slash (../) means to ascend into the parent section.
     * After a series of slashes and dot-dot-slashes, there should be the name of an input,
     * which will be returned.
     * Eg, if you want the related input to be conditional on a sibling input name 'foobar'
     * just use 'foobar'. If you want it to be conditional on an aunt/uncle input name
     * 'baz', use '../baz'. If you want it to be conditional on a cousin input,
     * the child of 'baz_section' named 'baz_child', use '../baz_section/baz_child'.
     * Etc
     *
     * @param string|false $form_section_path we accept false also because substr( '../', '../' ) = false
     * @return FormSectionBase
     * @throws ImproperUsageException
     */
    public function findSectionFromPath($form_section_path)
    {
        // check if we can find the input from purely going straight up the tree
        $input = parent::findSectionFromPath($form_section_path);
        if ($input instanceof FormSectionBase) {
            return $input;
        }
        $next_slash_pos = strpos($form_section_path, '/');
        if ($next_slash_pos !== false) {
            $child_section_name = substr($form_section_path, 0, $next_slash_pos);
            $subpath            = substr($form_section_path, $next_slash_pos + 1);
        } else {
            $child_section_name = $form_section_path;
            $subpath            = '';
        }
        $child_section = $this->getSubsection($child_section_name);
        if ($child_section instanceof FormSectionBase) {
            return $child_section->findSectionFromPath($subpath);
        }
        return null;
    }

    /**
     * Declares whether or not to use a nonce on this form.
     * Note each sub-form has an independent value for this.
     * @return bool
     */
    public function useNonce(){
        return $this->use_nonce;
    }
}
