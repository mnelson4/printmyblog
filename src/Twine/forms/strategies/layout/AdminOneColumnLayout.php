<?php
namespace Twine\forms\strategies\layout;

class AdminOneColumnLayout extends FormSectionLayoutBase
{

    /**
     * Starts the form section
     *
     * @param array $additional_args
     * @return string
     */
    public function layout_form_begin($additional_args = array())
    {
        return EEH_HTML::table(
            '',
            $this->_form_section->html_id(),
            $this->_form_section->html_class() . ' form-table',
            $this->_form_section->html_style()
        ) . EEH_HTML::tbody();
    }


    /**
     * Ends the form section
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
     * @throws Error
     */
    public function layout_input($input)
    {
        if ($input->get_display_strategy() instanceof TextAreaDisplay
            || $input->get_display_strategy() instanceof TextInputDisplay
            || $input->get_display_strategy() instanceof AdminFileUploaderDisplay
        ) {
            $input->set_html_class($input->html_class() . ' large-text');
        }
        $input_html = $input->get_html_for_input();
        // maybe add errors and help text ?
        $input_html .= $input->get_html_for_errors() !== ''
            ? EEH_HTML::nl() . $input->get_html_for_errors()
            : '';
        $input_html .= $input->get_html_for_help() !== ''
            ? EEH_HTML::nl() . $input->get_html_for_help()
            : '';
        // overriding parent to add wp admin specific things.
        $html = '';
        if ($input instanceof Hidden_Input) {
            $html .= EEH_HTML::no_row($input->get_html_for_input());
        } else {
            $html .= EEH_HTML::tr(
                EEH_HTML::td(
                    $input->get_html_for_label()
                    . EEH_HTML::nl()
                    . $input_html
                )
            );
        }
        return $html;
    }


    /**
     * Lays out a row for the subsection
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
