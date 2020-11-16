<?php

namespace Twine\forms\strategies\layout;

use Exception;
use Twine\forms\base\FormSectionHtml;
use Twine\forms\base\FormSection;
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
    public function layoutFormBegin($additional_args = array())
    {
        $html_generator = Html::instance();
        return $html_generator->table(
            '',
            $this->Form_section->htmlId(),
            $this->Form_section->htmlClass() . ' form-table',
            $this->Form_section->htmlStyle()
        ) . $html_generator->tbody();
    }


    /**
     * Ends the form section
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
     * @throws Exception
     */
    public function layoutInput($input)
    {
        $html_generator = Html::instance();
        if (
            $input->getDisplayStrategy() instanceof TextAreaDisplay
            || $input->getDisplayStrategy() instanceof TextInputDisplay
            || $input->getDisplayStrategy() instanceof AdminFileUploaderDisplay
        ) {
            $input->setHtmlClass($input->htmlClass() . ' large-text');
        }
        $input_html = $input->getHtmlForInput();
        // maybe add errors and help text ?
        $input_html .= $input->getHtmlForErrors() !== ''
            ? $html_generator->nl() . $input->getHtmlForErrors()
            : '';
        $input_html .= $input->getHtmlForHelp() !== ''
            ? $html_generator->nl() . $input->getHtmlForHelp()
            : '';
        // overriding parent to add wp admin specific things.
        $html = '';
        if ($input instanceof HiddenInput) {
            $html .= $html_generator->noRow($input->getHtmlForInput());
        } else {
            $html .= $html_generator->tr(
                $html_generator->td(
                    $input->getHtmlForLabel()
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
     * @param FormSection $form_section
     *
     * @return string
     */
    public function layoutSubsection($form_section)
    {
        $html_generator = Html::instance();
        if (
            $form_section instanceof FormSection
            || $form_section instanceof FormSectionHtml
        ) {
            return $html_generator->noRow($form_section->getHtml());
        }
        return '';
    }
}
