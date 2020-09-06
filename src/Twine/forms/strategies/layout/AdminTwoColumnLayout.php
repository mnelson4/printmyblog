<?php
namespace Twine\forms\strategies\layout;
/**
 * Like the standard two-column form section layout, but this one adds css classes
 * specific to the WP Admin
 */
class AdminTwoColumnLayout extends TwoColumnLayout
{

    /**
     * Overriding the parent table layout to include <tbody> tags
     *
     * @param array $additional_args
     * @return string
     */
    public function layout_form_begin($additional_args = array())
    {
        $this->_form_section->set_html_class($this->_form_section->html_class() . ' form-table');
        return parent::layout_form_begin($additional_args);
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
        if ($input instanceof Text_Area_Input) {
            $input->set_rows(4);
            $input->set_cols(60);
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
                EEH_HTML::th(
                    $input->get_html_for_label(),
                    '',
                    '',
                    '',
                    'scope="row"'
                ) . EEH_HTML::td($input_html)
            );
        }
        return $html;
    }
}
