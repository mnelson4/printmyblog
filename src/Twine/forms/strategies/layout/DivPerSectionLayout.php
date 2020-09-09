<?php
namespace Twine\forms\strategies\layout;


use Exception;
use Twine\forms\base\FormSectionProper;
use Twine\forms\inputs\FormInputBase;
use Twine\forms\inputs\FormInputWithOptionsBase;
use Twine\forms\inputs\HiddenInput;
use Twine\forms\inputs\SelectInput;
use Twine\forms\inputs\SubmitInput;
use Twine\helpers\Html;

/**
 * Class DivPerSectionLayout
 * Description
 *
 * @package               Event Espresso
 * @subpackage            core
 * @author                Mike Nelson
 * @since                 4.6.0
 */
class DivPerSectionLayout extends FormSectionLayoutBase
{

    /**
     * opening div tag for a form
     *
     * @return string
     */
    public function layout_form_begin()
    {
	    $html_generator = Html::instance();
        return $html_generator->div(
            '',
            $this->_form_section->html_id(),
            $this->_form_section->html_class(),
            $this->_form_section->html_style()
        );
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
        $html = '';
        // set something unique for the id
        $html_id = (string) $input->html_id() !== ''
            ? (string) $input->html_id()
            : spl_object_hash($input);
        // and add a generic input type class
        $html_class = sanitize_key(str_replace('_', '-', get_class($input))) . '-dv';
        if ($input instanceof HiddenInput) {
            $html .= $html_generator->nl() . $input->get_html_for_input();
        } elseif ($input instanceof SubmitInput) {
            $html .= $html_generator->div(
                $input->get_html_for_input(),
                $html_id . '-submit-dv',
                "{$input->html_class()}-submit-dv {$html_class}"
            );
        } elseif ($input instanceof SelectInput) {
            $html .= $html_generator->div(
                $html_generator->nl(1) . $input->get_html_for_label() .
                $html_generator->nl() . $input->get_html_for_errors() .
                $html_generator->nl() . $input->get_html_for_input() .
                $html_generator->nl() . $input->get_html_for_help(),
                $html_id . '-input-dv',
                "{$input->html_class()}-input-dv {$html_class}"
            );
        } elseif ($input instanceof FormInputWithOptionsBase) {
            $html .= $html_generator->div(
                $html_generator->nl() . $this->_display_label_for_option_type_question($input) .
                $html_generator->nl() . $input->get_html_for_errors() .
                $html_generator->nl() . $input->get_html_for_input() .
                $html_generator->nl() . $input->get_html_for_help(),
                $html_id . '-input-dv',
                "{$input->html_class()}-input-dv {$html_class}"
            );
        } else {
            $html .= $html_generator->div(
                $html_generator->nl(1) . $input->get_html_for_label() .
                $html_generator->nl() . $input->get_html_for_errors() .
                $html_generator->nl() . $input->get_html_for_input() .
                $html_generator->nl() . $input->get_html_for_help(),
                $html_id . '-input-dv',
                "{$input->html_class()}-input-dv {$html_class}"
            );
        }
        return $html;
    }



    /**
     *
     * _display_label_for_option_type_question
     * Gets the HTML for the 'label', which is just text for this (because labels
     * should be for each input)
     *
     * @param FormInputWithOptionsBase $input
     * @return string
     */
    protected function _display_label_for_option_type_question(FormInputWithOptionsBase $input)
    {
	    $html_generator = Html::instance();
        if ($input->display_html_label_text()) {
            $html_label_text = $input->html_label_text();
            $label_html = $html_generator->div(
                $input->required()
                    ? $html_label_text . $html_generator->span('*', '', 'ee-asterisk')
                    : $html_label_text,
                $input->html_label_id(),
                $input->required()
                    ? 'ee-required-label ' . $input->html_label_class()
                    : $input->html_label_class(),
                $input->html_label_style(),
                $input->other_html_attributes()
            );
            // if no content was provided to $html_generator->div() above (ie: an empty label),
            // then we need to close the div manually
            if (empty($html_label_text)) {
                $label_html .= $html_generator->divx($input->html_label_id(), $input->html_label_class());
            }
            return $label_html;
        }
        return '';
    }



    /**
     * Lays out a row for the subsection
     *
     * @param FormSectionProper $form_section
     * @return string
     */
    public function layout_subsection($form_section)
    {
	    $html_generator = Html::instance();
        return $html_generator->nl(1) . $form_section->get_html() . $html_generator->nl(-1);
    }



    /**
     * closing div tag for a form
     *
     * @return string
     */
    public function layout_form_end()
    {
	    $html_generator = Html::instance();
        return $html_generator->divx($this->_form_section->html_id(), $this->_form_section->html_class());
    }
}
