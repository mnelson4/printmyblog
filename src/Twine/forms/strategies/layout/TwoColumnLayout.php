<?php

namespace Twine\forms\strategies\layout;

use Twine\forms\base\FormSectionHtml;
use Twine\forms\base\FormSection;
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
    public function layoutFormBegin($additional_args = array())
    {
        $html_generator = Html::instance();
        return $this->displayFormWideErrors()
        . $html_generator->table(
            '',
            $this->Form_section->htmlId(),
            $this->Form_section->htmlClass(),
            $this->Form_section->htmlStyle()
        ) . $html_generator->tbody();
    }



    /**
     * Should be used to end the form section (eg a /table tag, or a /div tag, etc)
     *
     * @param array $additional_args
     * @return string
     */
    public function layoutFormEnd($additional_args = array())
    {
        $html_generator = Html::instance();
        return $html_generator->tbodyx() . $html_generator->tablex($this->Form_section->htmlId());
    }



    /**
     * Lays out the row for the input, including label and errors
     *
     * @param FormInputBase $input
     * @return string
     */
    public function layoutInput($input)
    {
        $html = '';
        $html_generator = Html::instance();
        if ($input instanceof HiddenInput) {
            $html .= $input->getHtmlForInput();
        } else {
            $html_for_input = $input->getHtmlForInput();
            $html_for_input .= $input->getHtmlForErrors() != ''
                ? $html_generator->nl() . $input->getHtmlForErrors()
                : '';
            $html_for_input .= $input->getHtmlForHelp() != '' ? $html_generator->nl() . $input->getHtmlForHelp() : '';
            $html .= $html_generator->tr(
                $html_generator->th($input->getHtmlForLabel()) .
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
     * @param FormSection $form_section
     *
     * @return string
    */
    public function layoutSubsection($form_section)
    {
        if (
            $form_section instanceof FormSection
            || $form_section instanceof FormSectionHtml
        ) {
            $html_generator = Html::instance();
            return $html_generator->noRow($form_section->getHtml());
        }
        return '';
    }
}
