<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

namespace Twine\forms\base;

use Exception;
use Twine\forms\helpers\ImproperUsageException;

if (!defined('TWINE_SCRIPTS_URL')) {
    if (! defined('TWINE_MAIN_FILE')) {
        throw new Exception(
            __(
                'In order to use Twine forms, you need to define TWINE_MAIN_FILE to be the main file of your plugin, then put Twine folder inside wp-content/plugins/yourplugin/src/Twine',
                'twine'
            )
        );
    }
    $plugin_base_path = dirname(TWINE_MAIN_FILE);
    $plugin_url = plugin_dir_url(TWINE_MAIN_FILE);
    // Twine constants
    define('TWINE_SCRIPTS_URL', $plugin_url . 'src/Twine/assets/scripts/');
    define('TWINE_STYLES_URL', $plugin_url . 'src/Twine/assets/styles/');

    define('TWINE_SCRIPTS_DIR', $plugin_base_path . '/src/Twine/assets/scripts/');
    define('TWINE_STYLES_DIR', $plugin_base_path . '/src/Twine/assets/styles/');
}
// phpcs:enable PSR1.Files.SideEffects.FoundWithSymbols

/**
 * FormSectionBase
 * For shared functionality between form sections that are for display-only, and
 * sections for receiving form input etc.
 *
 * @package               Event Espresso
 * @subpackage
 * @author                Mike Nelson
 */
abstract class FormSectionBase
{
    /**
     * html_id and html_name are derived from this by default
     *
     * @var string
     */
    protected $name;

    /**
     * $_html_id
     * @var string
     */
    protected $html_id;

    /**
     * $_html_class
     * @var string
     */
    protected $html_class;

    /**
     * $_html_style
     * @var string
     */
    protected $html_style;

    /**
     * $_other_html_attributes keys are attribute names, values are their values.
     * @var array
     */
    protected $other_html_attributes = array();

    /**
     * The form section of which this form section is a part
     *
     * @var FormSection
     */
    protected $parent_section;

    /**
     * flag indicating that _construct_finalize has been called.
     * If it has not been called and we try to use functions which require it, we call it
     * with no parameters. But normally, _construct_finalize should be called by the instantiating class
     *
     * @var boolean
     */
    protected $construction_finalized;

    /**
     * Callable which gets called when scripts and styles are enqueued
     * @var null|callable
     */
    protected $enqueue_scripts_callback = null;

    /**
     * @param array $options_array {
     * @type        $name          string the name for this form section, if you want to explicitly define it
     * @type        $other_html_attributes array of other HTML attributes (keys can either be the name of attributes, or numeric)
     *                             }
     * You can also set any property of this class using the $options_array.
     * Eg, there is a property enqueue_scripts_callback, which is a callback for enqueueing scripts. To use it,
     * pass an $options_array like this:
     * [
     *     'enequeue_scripts_callback' => function(){
     *          // enqueue scripts needed by this form
     *          ...
     *     }
     * ]
     */
    public function __construct($options_array = array())
    {
        // used by display strategies
        // assign incoming values to properties
        foreach ($options_array as $key => $value) {
            if (property_exists($this, $key) && empty($this->{$key})) {
                $this->{$key} = $value;
                if ($key === 'subsections' && ! is_array($value)) {
                    throw new Exception('Subsections was not an array');
                }
            }
        }
    }



    /**
     * @param $parent_form_section
     * @param $name
     * @throws \Error
     */
    protected function constructFinalize($parent_form_section, $name)
    {
        $this->construction_finalized = true;
        $this->parent_section = $parent_form_section;
        if ($name !== null) {
            $this->name = $name;
        }
    }



    /**
     * make sure construction finalized was called, otherwise children might not be ready
     *
     * @return void
     * @throws \Error
     */
    public function ensureConstructFinalizedCalled()
    {
        if (! $this->construction_finalized) {
            $this->constructFinalize($this->parent_section, $this->name);
        }
    }

    /**
     * Sets the html_id to its default value, if none was specified in the constructor.
     * Calculation involves using the name and the parent's html id
     * return void
     *
     * @throws \Error
     */
    protected function setDefaultHtmlIdIfEmpty()
    {
        if (! $this->html_id) {
            if ($this->parent_section && $this->parent_section instanceof FormSection) {
                $this->html_id = $this->parent_section->htmlId()
                                  . '-'
                                  . $this->prepNameForHtmlId($this->name());
            } else {
                $this->html_id = $this->prepNameForHtmlId($this->name());
            }
        }
    }



    /**
     * _prep_name_for_html_id
     *
     * @param $name
     * @return string
     */
    private function prepNameForHtmlId($name)
    {
        return sanitize_key(str_replace(array('&nbsp;', ' ', '_'), '-', $name));
    }



    /**
     * Returns the HTML, JS, and CSS necessary to display this form section on a page.
     * Note however, it's recommended that you instead call enqueue_js on the "wp_enqueue_scripts" action,
     * and call get_html when you want to output the html. Calling getHtmlAndJs after
     * "wp_enqueue_scripts" has already fired seems to work for now, but is contrary
     * to the instructions on https://developer.wordpress.org/reference/functions/wp_enqueue_script/
     * and so might stop working anytime.
     *
     * @return string
     */
    public function getHtmlAndJs()
    {
        return $this->getHtml();
    }



    /**
     * Gets the HTML for displaying this form section
     *
     * @return string
     */
    abstract public function getHtml();


    /**
     * @param bool $add_pound_sign
     * @return string
     * @throws ImproperUsageException
     */
    public function htmlId($add_pound_sign = false)
    {
        $this->setDefaultHtmlIdIfEmpty();
        return $add_pound_sign ? '#' . $this->html_id : $this->html_id;
    }



    /**
     * @return string
     */
    public function htmlClass()
    {
        return $this->html_class;
    }



    /**
     * @return string
     */
    public function htmlStyle()
    {
        return $this->html_style;
    }



    /**
     * @param mixed $html_class
     */
    public function setHtmlClass($html_class)
    {
        $this->html_class = $html_class;
    }



    /**
     * @param mixed $html_id
     */
    public function setHtmlId($html_id)
    {
        $this->html_id = $html_id;
    }



    /**
     * @param mixed $html_style
     */
    public function setHtmlStyle($html_style)
    {
        $this->html_style = $html_style;
    }



    /**
     * @param array $other_html_attributes
     */
    public function setOtherHtmlAttributes($other_html_attributes)
    {
        if (! is_array($other_html_attributes)) {
            throw new ImproperUsageException(
                get_class($this) . '::set_other_html_attribues should be passed in an array, not a string'
            );
        }
        $this->other_html_attributes = (array)$other_html_attributes;
    }

    /**
     * @param $name
     * @param $value optional. Leave blank for standalone attributes like "checked"
     */
    public function addOtherHtmlAttribute($name, $value = null)
    {
        if ($value === null) {
            $this->other_html_attributes[] = $name;
        } else {
            $this->other_html_attributes[$name] = $value;
        }
    }

    /**
     * @param $name
     */
    public function removeOtherHtmlAttribute($name)
    {
        unset($this->other_html_attributes[$name]);
    }



    /**
     * @return array keys are attribute names, values are their values
     */
    public function otherHtmlAttributes()
    {
        return $this->other_html_attributes;
    }

    /**
     * Gets a string of html attributes
     * @return string
     */
    public function otherHtmlAttributesString()
    {
        $keyvaluepairs = [];
        foreach ($this->otherHtmlAttributes() as $key => $value) {
            if (is_numeric($key)) {
                $keyvaluepairs[] = esc_attr($value);
            } else {
                $keyvaluepairs[] = $key . '="' . esc_attr($value) . '"';
            }
        }
        return ' ' . implode(' ', $keyvaluepairs);
    }


    /**
     * Gets the name of the form section. This is not the same as the HTML name.
     *
     * @throws ImproperUsageException
     * @return string
     */
    public function name()
    {
        if (! $this->construction_finalized) {
            throw new ImproperUsageException(sprintf(__(
                'You cannot use the form section\s name until constructFinalize has been called on it (when we set the name). It was called on a form section of type \'s\'',
                'print-my-blog'
            ), get_class($this)));
        }
        return $this->name;
    }



    /**
     * Gets the parent section
     *
     * @return FormSection
     */
    public function parentSection()
    {
        return $this->parent_section;
    }



    /**
     * enqueues JS (and CSS) for the form (ie immediately call wp_enqueue_script and
     * wp_enqueue_style; the scripts could have optionally been registered earlier)
     * Default does nothing, but child classes can override
     *
     * @return void
     */
    public function enqueueJs()
    {
        // defaults to enqueue NO js or css
        if (is_callable($this->enqueue_scripts_callback)) {
            call_user_func($this->enqueue_scripts_callback);
        }
    }



    /**
     * Adds any extra data needed by js. Eventually we'll call wp_localize_script
     * with it, and it will be on each form section's 'other_data' property.
     * By default nothing is added, but child classes can extend this method to add something.
     * Eg, if you have an input that will cause a modal dialog to appear,
     * here you could add an entry like 'modal_dialog_inputs' to this array
     * to map between the input's html ID and the modal dialogue's ID, so that
     * your JS code will know where to find the modal dialog when the input is pressed.
     * Eg $form_other_js_data['modal_dialog_inputs']['some-input-id']='modal-dialog-id';
     *
     * @param array $form_other_js_data
     * @return array
     */
    public function getOtherJsData($form_other_js_data = array())
    {
        return $form_other_js_data;
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
     *
     * @return FormSectionBase
     */
    public function findSectionFromPath($form_section_path)
    {
        if (strpos($form_section_path, '/') === 0) {
            $form_section_path = substr($form_section_path, strlen('/'));
        }
        if (empty($form_section_path)) {
            return $this;
        }
        if (strpos($form_section_path, '../') === 0) {
            $parent = $this->parentSection();
            $form_section_path = substr($form_section_path, strlen('../'));
            if ($parent instanceof FormSectionBase) {
                return $parent->findSectionFromPath($form_section_path);
            }
            if (empty($form_section_path)) {
                return $this;
            }
        }
        // couldn't find it using simple parent following
        return null;
    }
}
// End of file FormSectionBase.php
