<?php
namespace Twine\forms\strategies\layout;

use Twine\forms\base\FormSectionHtml;
use Twine\forms\base\FormSectionProper;
use Twine\forms\inputs\FormInputBase;
use Twine\forms\inputs\HiddenInput;
use Twine\helpers\Html;

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
    	$html_generator = Html::instance();
        return $this->display_form_wide_errors()
        . $html_generator->table(
            '',
            $this->_form_section->html_id(),
            $this->_form_section->html_class(),
            $this->_form_section->html_style()
        ) . $html_generator->tbody();
    }



    /**
     * Should be used to end the form section (eg a /table tag, or a /div tag, etc)
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
     */
    public function layout_input($input)
    {
        $html = '';
        $html_generator = Html::instance();
        if ($input instanceof HiddenInput) {
            $html .= $input->get_html_for_input();
        } else {
            $html_for_input = $input->get_html_for_input();
            $html_for_input .= $input->get_html_for_errors() != ''
                ? $html_generator->nl() . $input->get_html_for_errors()
                : '';
            $html_for_input .= $input->get_html_for_help() != '' ? $html_generator->nl() . $input->get_html_for_help() : '';
            $html .= $html_generator->tr(
	            $html_generator->th($input->get_html_for_label()) .
	            $html_generator->td($html_for_input)
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
	        $html_generator = Html::instance();
            return $html_generator->no_row($form_section->get_html());
        }
        return '';
    }
}
