<?php

namespace Twine\forms\strategies\layout;

use Exception;
use Twine\forms\base\FormSection;
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
    public function layoutFormBegin()
    {
        $html_generator = Html::instance();
        return $html_generator->div(
            '',
            $this->Form_section->htmlId(),
            $this->Form_section->htmlClass(),
            $this->Form_section->htmlStyle()
        );
    }



    /**
     * Lays out the row for the input, including label and errors
     *
     * @param FormInputBase $input
     * @return string
     * @throws Exception
     */
    public function layoutInput($input)
    {
        $html_generator = Html::instance();
        $html = '';
        // set something unique for the id
        $html_id = (string) $input->htmlId() !== ''
            ? (string) $input->htmlId()
            : spl_object_hash($input);
        // and add a generic input type class
        $html_class = sanitize_key(str_replace('_', '-', get_class($input))) . '-dv';
        if ($input instanceof HiddenInput) {
            $html .= $html_generator->nl() . $input->getHtmlForInput();
        } elseif ($input instanceof SubmitInput) {
            $html .= $html_generator->div(
                $input->getHtmlForInput(),
                $html_id . '-submit-dv',
                "{$input->htmlClass()}-submit-dv {$html_class}"
            );
        } elseif ($input instanceof SelectInput) {
            $html .= $html_generator->div(
                $html_generator->nl(1) . $input->getHtmlForLabel() .
                $html_generator->nl() . $input->getHtmlForErrors() .
                $html_generator->nl() . $input->getHtmlForInput() .
                $html_generator->nl() . $input->getHtmlForHelp(),
                $html_id . '-input-dv',
                "{$input->htmlClass()}-input-dv {$html_class}"
            );
        } elseif ($input instanceof FormInputWithOptionsBase) {
            $html .= $html_generator->div(
                $html_generator->nl() . $this->displayLabelForOptionTypeQuestion($input) .
                $html_generator->nl() . $input->getHtmlForErrors() .
                $html_generator->nl() . $input->getHtmlForInput() .
                $html_generator->nl() . $input->getHtmlForHelp(),
                $html_id . '-input-dv',
                "{$input->htmlClass()}-input-dv {$html_class}"
            );
        } else {
            $html .= $html_generator->div(
                $html_generator->nl(1) . $input->getHtmlForLabel() .
                $html_generator->nl() . $input->getHtmlForErrors() .
                $html_generator->nl() . $input->getHtmlForInput() .
                $html_generator->nl() . $input->getHtmlForHelp(),
                $html_id . '-input-dv',
                "{$input->htmlClass()}-input-dv {$html_class}"
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
    protected function displayLabelForOptionTypeQuestion(FormInputWithOptionsBase $input)
    {
        $html_generator = Html::instance();
        if ($input->displayHtmlLabelText()) {
            $html_label_text = $input->htmlLabelText();
            $label_html = $html_generator->div(
                $input->required()
                    ? $html_label_text . $html_generator->span('*', '', 'twine-asterisk')
                    : $html_label_text,
                $input->htmlLabelId(),
                $input->required()
                    ? 'twine-required-label ' . $input->htmlLabelClass()
                    : $input->htmlLabelClass(),
                $input->htmlLabelStyle(),
                $input->otherHtmlAttributesString()
            );
            // if no content was provided to $html_generator->div() above (ie: an empty label),
            // then we need to close the div manually
            if (empty($html_label_text)) {
                $label_html .= $html_generator->divx($input->htmlLabelId(), $input->htmlLabelClass());
            }
            return $label_html;
        }
        return '';
    }



    /**
     * Lays out a row for the subsection
     *
     * @param FormSection $form_section
     * @return string
     */
    public function layoutSubsection($form_section)
    {
        $html_generator = Html::instance();
        return $html_generator->nl(1) . $form_section->getHtml() . $html_generator->nl(-1);
    }



    /**
     * closing div tag for a form
     *
     * @return string
     */
    public function layoutFormEnd()
    {
        $html_generator = Html::instance();
        return $html_generator->divx($this->Form_section->htmlId(), $this->Form_section->htmlClass());
    }
}
