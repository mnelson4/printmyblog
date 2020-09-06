<?php
namespace Twine\forms\strategies\layout;
/**
 * Template Layout strategy class for the EE Forms System that applies no layout.
 *
 * @package               Event Espresso
 * @subpackage            core
 * @author                Mike Nelson
 * @since                 4.6.0
 */
class NoLayout extends DivPerSectionLayout
{


    /**
     * This is a flag indicating whether to use '<br>' tags after each input in the layout
     * strategy.
     *
     * @var bool
     */
    protected $_use_break_tags = true;



    /**
     * NoLayout constructor.
     *
     * @param array $options  Currently if this has a 'use_break_tags' key that is used to set the _use_break_tags
     *                        property on the class.
     */
    public function __construct($options = array())
    {
        $this->_use_break_tags = is_array($options) && isset($options['use_break_tags'])
            ? filter_var($options['use_break_tags'], FILTER_VALIDATE_BOOLEAN)
            : $this->_use_break_tags;
        parent::__construct();
    }



    /**
     * Add line break at beginning of form
     *
     * @return string
     */
    public function layout_form_begin()
    {
        return EEH_HTML::nl(1);
    }



    /**
     * Lays out the row for the input, including label and errors
     *
     * @param FormInputBase $input
     * @return string
     * @throws \Error
     */
    public function layout_input($input)
    {
        $html = '';
        if ($input instanceof Hidden_Input) {
            $html .= EEH_HTML::nl() . $input->get_html_for_input();
        } elseif ($input instanceof Submit_Input) {
            $html .= $this->br();
            $html .= $input->get_html_for_input();
        } elseif ($input instanceof Select_Input) {
            $html .= $this->br();
            $html .= EEH_HTML::nl(1) . $input->get_html_for_label();
            $html .= EEH_HTML::nl() . $input->get_html_for_errors();
            $html .= EEH_HTML::nl() . $input->get_html_for_input();
            $html .= EEH_HTML::nl() . $input->get_html_for_help();
            $html .= $this->br();
        } elseif ($input instanceof FormInputWithOptionsBase) {
            $html .= $this->br();
            $html .= EEH_HTML::nl() . $input->get_html_for_errors();
            $html .= EEH_HTML::nl() . $input->get_html_for_input();
            $html .= EEH_HTML::nl() . $input->get_html_for_help();
        } else {
            $html .= $this->br();
            $html .= EEH_HTML::nl(1) . $input->get_html_for_label();
            $html .= EEH_HTML::nl() . $input->get_html_for_errors();
            $html .= EEH_HTML::nl() . $input->get_html_for_input();
            $html .= EEH_HTML::nl() . $input->get_html_for_help();
        }
        $html .= EEH_HTML::nl(-1);
        return $html;
    }



    /**
     * Lays out a row for the subsection
     *
     * @param FormSectionProper $form_section
     * @return string
     */
    public function layout_subsection($form_section)
    {
        //      d( $form_section );
        return EEH_HTML::nl(1) . $form_section->get_html() . EEH_HTML::nl(-1);
    }



    /**
     * Add line break at end of form.
     *
     * @return string
     */
    public function layout_form_end()
    {
        return EEH_HTML::nl(-1);
    }



    /**
     * This returns a break tag or an empty string depending on the value of the `_use_break_tags` property.
     *
     * @return string
     */
    protected function br()
    {
        return $this->_use_break_tags ? EEH_HTML::br() : '';
    }
}
