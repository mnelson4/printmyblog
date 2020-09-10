<?php
namespace Twine\forms\strategies\layout;
use Twine\forms\inputs\FormInputBase;
use Twine\forms\inputs\HiddenInput;
use Twine\forms\inputs\TextAreaInput;
use Twine\forms\strategies\display\AdminFileUploaderDisplay;
use Twine\forms\strategies\display\TextAreaDisplay;
use Twine\forms\strategies\display\TextInputDisplay;
use Twine\helpers\Html;

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
        $this->_form_section->set_html_class($this->_form_section->html_class() . ' form-table twine-two-column-layout');
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
	    $html_generator = Html::instance();
        if ($input->get_display_strategy() instanceof TextAreaDisplay
            || (
            	$input->get_display_strategy() instanceof TextInputDisplay
                && ! in_array($input->get_display_strategy()->get_type(), ['checkbox','radio'])
                )
            || $input->get_display_strategy() instanceof AdminFileUploaderDisplay
        ) {
            $input->set_html_class($input->html_class() . ' large-text');
        }
        if ($input instanceof TextAreaInput) {
            $input->set_rows(4);
            $input->set_cols(60);
        }
        $input_html = $input->get_html_for_input();
        // maybe add errors and help text ?
        $input_html .= $input->get_html_for_errors() !== ''
            ? $html_generator->nl() . $input->get_html_for_errors()
            : '';
        $input_html .= $input->get_html_for_help() !== ''
            ? $html_generator->nl() . $input->get_html_for_help()
            : '';
        // overriding parent to add wp admin specific things.
        $html = '';
        if ($input instanceof HiddenInput) {
            $html .= $html_generator->no_row($input->get_html_for_input());
        } else {
            $html .= $html_generator->tr(
                $html_generator->th(
                    $input->get_html_for_label(),
                    '',
                    '',
                    '',
                    'scope="row"'
                ) . $html_generator->td($input_html)
            );
        }
        return $html;
    }
}
