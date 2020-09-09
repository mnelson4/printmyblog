<?php
namespace Twine\forms\strategies\layout;

use Exception;
use Twine\forms\base\FormSectionHtml;
use Twine\forms\base\FormSectionProper;
use Twine\forms\inputs\FormInputBase;
use Twine\forms\inputs\HiddenInput;
use Twine\forms\strategies\display\AdminFileUploaderDisplay;
use Twine\forms\strategies\display\TextAreaDisplay;
use Twine\forms\strategies\display\TextInputDisplay;
use Twine\helpers\Html;

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
    	$html_generator = Html::instance();
        return $html_generator->table(
            '',
            $this->_form_section->html_id(),
            $this->_form_section->html_class() . ' form-table',
            $this->_form_section->html_style()
        ) . $html_generator->tbody();
    }


    /**
     * Ends the form section
     *
     * @param array $additional_args
     * @return string
     */
    public function layout_form_end($additional_args = array())
    {
	    $html_generator = Html::instance();
        return $html_generator->tbodyx() . $html_generator->tablex($this->_form_section->html_id());
    }


    /**
     * Lays out the row for the input, including label and errors
     *
     * @param FormInputBase $input
     * @return string
     * @throws Exception
     */
    public function layout_input($input)
    {
	    $html_generator = Html::instance();
        if ($input->get_display_strategy() instanceof TextAreaDisplay
            || $input->get_display_strategy() instanceof TextInputDisplay
            || $input->get_display_strategy() instanceof AdminFileUploaderDisplay
        ) {
            $input->set_html_class($input->html_class() . ' large-text');
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
                $html_generator->td(
                    $input->get_html_for_label()
                    . $html_generator->nl()
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
	    $html_generator = Html::instance();
        if ( $form_section instanceof FormSectionProper
            || $form_section instanceof FormSectionHtml
        ) {
            return $html_generator->no_row($form_section->get_html());
        }
        return '';
    }
}
