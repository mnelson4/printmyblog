<?php
namespace Twine\forms\base;
use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\libraries\form_sections\strategies\filter\FormHtmlFilter;
use Exception;
use Twine\forms\helpers\ImproperUsageException;

if(!defined('TWINE_SCRIPTS_URL')){
	if(! defined('TWINE_MAIN_FILE')){
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
	define('TWINE_STYLES_DIR', $plugin_base_path . '/src/Twine/assets/styles');
}
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
    protected $_name;

    /**
     * $_html_id
     * @var string
     */
    protected $_html_id;

    /**
     * $_html_class
     * @var string
     */
    protected $_html_class;

    /**
     * $_html_style
     * @var string
     */
    protected $_html_style;

    /**
     * $_other_html_attributes keys are attribute names, values are their values.
     * @var array
     */
    protected $_other_html_attributes = array();

    /**
     * The form section of which this form section is a part
     *
     * @var FormSectionProper
     */
    protected $_parent_section;

    /**
     * flag indicating that _construct_finalize has been called.
     * If it has not been called and we try to use functions which require it, we call it
     * with no parameters. But normally, _construct_finalize should be called by the instantiating class
     *
     * @var boolean
     */
    protected $_construction_finalized;


    /**
     * @param array $options_array {
     * @type        $name          string the name for this form section, if you want to explicitly define it
     *                             }
     * @throws InvalidDataTypeException
     */
    public function __construct($options_array = array())
    {
        // used by display strategies
        // assign incoming values to properties
        foreach ($options_array as $key => $value) {
            $key = '_' . $key;
            if (property_exists($this, $key) && empty($this->{$key})) {
                $this->{$key} = $value;
                if ($key === '_subsections' && ! is_array($value)) {
                    throw new InvalidDataTypeException($key, $value, 'array');
                }
            }
        }
    }



    /**
     * @param $parent_form_section
     * @param $name
     * @throws \Error
     */
    protected function _construct_finalize($parent_form_section, $name)
    {
        $this->_construction_finalized = true;
        $this->_parent_section = $parent_form_section;
        if ($name !== null) {
            $this->_name = $name;
        }
    }



    /**
     * make sure construction finalized was called, otherwise children might not be ready
     *
     * @return void
     * @throws \Error
     */
    public function ensure_construct_finalized_called()
    {
        if (! $this->_construction_finalized) {
            $this->_construct_finalize($this->_parent_section, $this->_name);
        }
    }

    /**
     * Sets the html_id to its default value, if none was specified in the constructor.
     * Calculation involves using the name and the parent's html id
     * return void
     *
     * @throws \Error
     */
    protected function _set_default_html_id_if_empty()
    {
        if (! $this->_html_id) {
            if ($this->_parent_section && $this->_parent_section instanceof FormSectionProper) {
                $this->_html_id = $this->_parent_section->html_id()
                                  . '-'
                                  . $this->_prep_name_for_html_id($this->name());
            } else {
                $this->_html_id = $this->_prep_name_for_html_id($this->name());
            }
        }
    }



    /**
     * _prep_name_for_html_id
     *
     * @param $name
     * @return string
     */
    private function _prep_name_for_html_id($name)
    {
        return sanitize_key(str_replace(array('&nbsp;', ' ', '_'), '-', $name));
    }



    /**
     * Returns the HTML, JS, and CSS necessary to display this form section on a page.
     * Note however, it's recommended that you instead call enqueue_js on the "wp_enqueue_scripts" action,
     * and call get_html when you want to output the html. Calling get_html_and_js after
     * "wp_enqueue_scripts" has already fired seems to work for now, but is contrary
     * to the instructions on https://developer.wordpress.org/reference/functions/wp_enqueue_script/
     * and so might stop working anytime.
     *
     * @return string
     */
    public function get_html_and_js()
    {
        return $this->get_html();
    }



    /**
     * Gets the HTML for displaying this form section
     *
     * @return string
     */
    abstract public function get_html();


    /**
     * @param bool $add_pound_sign
     * @return string
     * @throws ImproperUsageException
     */
    public function html_id($add_pound_sign = false)
    {
        $this->_set_default_html_id_if_empty();
        return $add_pound_sign ? '#' . $this->_html_id : $this->_html_id;
    }



    /**
     * @return string
     */
    public function html_class()
    {
        return $this->_html_class;
    }



    /**
     * @return string
     */
    public function html_style()
    {
        return $this->_html_style;
    }



    /**
     * @param mixed $html_class
     */
    public function set_html_class($html_class)
    {
        $this->_html_class = $html_class;
    }



    /**
     * @param mixed $html_id
     */
    public function set_html_id($html_id)
    {
        $this->_html_id = $html_id;
    }



    /**
     * @param mixed $html_style
     */
    public function set_html_style($html_style)
    {
        $this->_html_style = $html_style;
    }



    /**
     * @param array $other_html_attributes
     */
    public function set_other_html_attributes($other_html_attributes)
    {
    	if(! is_array($other_html_attributes)){
    		throw new ImproperUsageException(get_class($this) . '::set_other_html_attribues should be passed in an array, not a string');
	    }
        $this->_other_html_attributes = (array)$other_html_attributes;
    }

	/**
	 * @param $name
	 * @param $value optional. Leave blank for standalone attributes like "checked"
	 */
    public function addOtherHtmlAttribute($name, $value = null){
    	if($value === null){
    		$this->_other_html_attributes[] = $name;
	    } else{
		    $this->_other_html_attributes[$name] = $value;
	    }
    }

	/**
	 * @param $name
	 */
    public function removeOtherHtmlAttribute($name){
    	unset($this->_other_html_attributes[$name]);
    }



    /**
     * @return array keys are attribute names, values are their values
     */
    public function other_html_attributes()
    {
        return $this->_other_html_attributes;
    }

	/**
	 * Gets a string of html attributes
	 * @return string
	 */
    public function otherHtmlAttributesString(){
    	$keyvaluepairs = [];
    	foreach($this->other_html_attributes() as $key => $value){
    		if(is_numeric($key)){
    			$keyvaluepairs[] = esc_attr($value);
		    } else{
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
        if (! $this->_construction_finalized) {
            throw new ImproperUsageException(sprintf(__(
                'You cannot use the form section\s name until _construct_finalize has been called on it (when we set the name). It was called on a form section of type \'s\'',
                'event_espresso'
            ), get_class($this)));
        }
        return $this->_name;
    }



    /**
     * Gets the parent section
     *
     * @return FormSectionProper
     */
    public function parent_section() {
	    return $this->_parent_section;
    }



    /**
     * enqueues JS (and CSS) for the form (ie immediately call wp_enqueue_script and
     * wp_enqueue_style; the scripts could have optionally been registered earlier)
     * Default does nothing, but child classes can override
     *
     * @return void
     */
    public function enqueue_js()
    {
        // defaults to enqueue NO js or css
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
    public function get_other_js_data($form_other_js_data = array())
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
    public function find_section_from_path($form_section_path)
    {
        if (strpos($form_section_path, '/') === 0) {
            $form_section_path = substr($form_section_path, strlen('/'));
        }
        if (empty($form_section_path)) {
            return $this;
        }
        if (strpos($form_section_path, '../') === 0) {
            $parent = $this->parent_section();
            $form_section_path = substr($form_section_path, strlen('../'));
            if ( $parent instanceof FormSectionBase) {
                return $parent->find_section_from_path($form_section_path);
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