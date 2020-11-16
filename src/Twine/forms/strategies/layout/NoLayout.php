<?php

namespace Twine\forms\strategies\layout;

use Twine\forms\base\FormSection;
use Twine\forms\inputs\FormInputBase;
use Twine\forms\inputs\FormInputWithOptionsBase;
use Twine\forms\inputs\HiddenInput;
use Twine\forms\inputs\SelectInput;
use Twine\forms\inputs\SubmitInput;
use Twine\helpers\Html;

/**
 * Template Layout strategy class for the EE Forms System that applies no layout.
 *
 * @package               Event Espresso
 * @subpackage            core
 * @author                Mike Nelson
 * @since                 4.6.0
 */
class NoLayout extends DivPerSectionLayout
{


    /**
     * This is a flag indicating whether to use '<br>' tags after each input in the layout
     * strategy.
     *
     * @var bool
     */
    protected $use_break_tags = true;



    /**
     * NoLayout constructor.
     *
     * @param array $options  Currently if this has a 'use_break_tags' key that is used to set the _use_break_tags
     *                        property on the class.
     */
    public function __construct($options = array())
    {
        $this->use_break_tags = is_array($options) && isset($options['use_break_tags'])
            ? filter_var($options['use_break_tags'], FILTER_VALIDATE_BOOLEAN)
            : $this->use_break_tags;
        parent::__construct();
    }



    /**
     * Add line break at beginning of form
     *
     * @return string
     */
    public function layoutFormBegin()
    {
        $html_generator = Html::instance();
        return $html_generator->nl(1);
    }



    /**
     * Lays out the row for the input, including label and errors
     *
     * @param FormInputBase $input
     * @return string
     * @throws \Error
     */
    public function layoutInput($input)
    {
        $html_generator = Html::instance();
        $html = '';
        if ($input instanceof HiddenInput) {
            $html .= $html_generator->nl() . $input->getHtmlForInput();
        } elseif ($input instanceof SubmitInput) {
            $html .= $this->br();
            $html .= $input->getHtmlForInput();
        } elseif ($input instanceof SelectInput) {
            $html .= $this->br();
            $html .= $html_generator->nl(1) . $input->getHtmlForLabel();
            $html .= $html_generator->nl() . $input->getHtmlForErrors();
            $html .= $html_generator->nl() . $input->getHtmlForInput();
            $html .= $html_generator->nl() . $input->getHtmlForHelp();
            $html .= $this->br();
        } elseif ($input instanceof FormInputWithOptionsBase) {
            $html .= $this->br();
            $html .= $html_generator->nl() . $input->getHtmlForErrors();
            $html .= $html_generator->nl() . $input->getHtmlForInput();
            $html .= $html_generator->nl() . $input->getHtmlForHelp();
        } else {
            $html .= $this->br();
            $html .= $html_generator->nl(1) . $input->getHtmlForLabel();
            $html .= $html_generator->nl() . $input->getHtmlForErrors();
            $html .= $html_generator->nl() . $input->getHtmlForInput();
            $html .= $html_generator->nl() . $input->getHtmlForHelp();
        }
        $html .= $html_generator->nl(-1);
        return $html;
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
     * Add line break at end of form.
     *
     * @return string
     */
    public function layoutFormEnd()
    {
        $html_generator = Html::instance();
        return $html_generator->nl(-1);
    }



    /**
     * This returns a break tag or an empty string depending on the value of the `_use_break_tags` property.
     *
     * @return string
     */
    protected function br()
    {
        $html_generator = Html::instance();
        return $this->use_break_tags ? $html_generator->br() : '';
    }
}
