<?php

namespace Twine\forms\strategies\display;

/**
 * Class TextInputDisplay
 * Display strategy that handles how to display form inputs that represent basic
 * "text" type inputs, including "password", "email" and any other inputs that
 * are essentially the same as "text", except they just have a different "type" attribute
 *
 * @package             Event Espresso
 * @subpackage    core
 * @author              Mike Nelson
 * @since                   4.6
 *
 */
class TextInputDisplay extends DisplayBase
{
    /**
     * The html "type" attribute value. default is "text"
     * @var string
     */
    protected $type;



    /**
     * @param string $type
     */
    public function __construct($type = 'text')
    {
        $this->type = $type;
        parent::__construct();
    }



    /**
     * Gets the html "type" attribute's value
     * @return string
     */
    public function getType()
    {
        if (
            $this->type === 'email'
            && ! apply_filters('FH_TextInputDisplay__use_html5_email', false)
        ) {
            return 'text';
        }
        return $this->type;
    }



    /**
     *
     * @return string of html to display the field
     */
    public function display()
    {
        $input = '<input type="' . $this->getType() . '"';
        $input .= ' name="' . $this->input->htmlName() . '"';
        $input .= ' id="' . $this->input->htmlId() . '"';
        $class = $this->input->required() ?
            $this->input->requiredCssClass() . ' ' . $this->input->htmlClass() :
            $this->input->htmlClass();
        $input .= ' class="twine-text-input ' . $class . '"';
        // add html5 required
        $input .= $this->input->required() ? ' required' : '';
        $input .= ' value="' . $this->input->rawValueInForm() . '"';
        $input .= ' style="' . $this->input->htmlStyle() . '"';
        $input .= $this->input->otherHtmlAttributesString();
        $input .= '/>';
        return $input;
    }
}
