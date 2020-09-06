<?php
namespace Twine\forms\strategies\layout;


class TwoColumnLayout extends FormSectionLayoutBase
{

    /**
     * Should be used to start teh form section (Eg a table tag, or a div tag, etc.)
     *
     * @param array $additional_args
     * @return string
     */
    public function layout_form_begin($additional_args = array())
    {
        return $this->display_form_wide_errors()
        . EEH_HTML::table(
            '',
            $this->_form_section->html_id(),
            $this->_form_section->html_class(),
            $this->_form_section->html_style()
        ) . EEH_HTML::tbody();
    }



    /**
     * Should be used to end the form section (eg a /table tag, or a /div tag, etc)
     *
     * @param array $additional_args
     * @return string
     */
    public function layout_form_end($additional_args = array())
    {
        return EEH_HTML::tbodyx() . EEH_HTML::tablex($this->_form_section->html_id());
    }



    /**
     * Lays out the row for the input, including label and errors
     *
     * @param FormInputBase $input
     * @return string
     */
    public function layout_input($input)
    {
        $html = '';
        if ($input instanceof Hidden_Input) {
            $html .= $input->get_html_for_input();
        } else {
            $html_for_input = $input->get_html_for_input();
            $html_for_input .= $input->get_html_for_errors() != ''
                ? EEH_HTML::nl() . $input->get_html_for_errors()
                : '';
            $html_for_input .= $input->get_html_for_help() != '' ? EEH_HTML::nl() . $input->get_html_for_help() : '';
            $html .= EEH_HTML::tr(
                EEH_HTML::th($input->get_html_for_label()) .
                EEH_HTML::td($html_for_input)
            );
        }
        return $html;
    }



    /**
     * Lays out a row for the subsection. Please note that if you have a subsection which you don't want wrapped in
     * a tr and td with a colspan=2, you should use a different layout strategy, like NoLayout, TemplateLayout,
     * or DivPerSectionLayout, and create subsections using TwoColumnLayout for everywhere you want the
     * two-column layout, and then other sub-sections can be outside the TwoColumnLayout table.
     *
     * @param FormSectionProper $form_section
     *
     * @return string
    */
    public function layout_subsection($form_section)
    {
        if ( $form_section instanceof FormSectionProper
            || $form_section instanceof FormSectionHtml
        ) {
            return EEH_HTML::no_row($form_section->get_html());
        }
        return '';
    }
}
