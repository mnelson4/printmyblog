<?php

namespace Twine\forms\strategies\layout;

use Twine\forms\base\FormSectionBase;
use Twine\forms\base\FormSectionDetails;
use Twine\forms\base\FormSection;
use Twine\forms\inputs\FormInputBase;
use Twine\helpers\Html;

/**
 * Class DetailsSummaryLayout
 * @package Twine\forms\strategies\layout
 */
class DetailsSummaryLayout extends FormSectionLayoutBase
{
    /**
     * @var FormSectionBase
     */
    protected $inner_layout;

    /**
     * DetailsSummaryLayout constructor.
     *
     * @param FormSectionLayoutBase $inner_layout
     */
    public function __construct(FormSectionLayoutBase $inner_layout)
    {
        $this->inner_layout = $inner_layout;
        parent::__construct();
    }

    /**
     * @param FormSection $form
     */
    public function constructFinalize(FormSection $form)
    {
        $this->inner_layout->constructFinalize($form);
        parent::constructFinalize($form);
    }

    /**
     * @return string
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    public function layoutFormBegin()
    {
        $html_generator = Html::instance();
        if ($this->form_section instanceof FormSectionDetails) {
            $summary = $this->form_section->getSummary();
        } else {
            $summary = __('Show Options', 'print-my-blog');
        }

        return $this->displayFormWideErrors()
            . $html_generator->openTag(
                'details',
                $this->form_section->htmlId(),
                $this->form_section->htmlClass() . ' twine-details',
                $this->form_section->htmlStyle()
            ) . $html_generator->tag(
                'summary',
                $summary,
                $this->form_section->htmlId() . '-summary',
                'twine-summary'
            ) . $this->inner_layout->layoutFormBegin() . $this->inner_layout->layoutFormLoop();
    }

    /**
     * @return string
     */
    public function layoutFormEnd()
    {
        $html_generator = Html::instance();
        return $this->inner_layout->layoutFormEnd() . $html_generator->closeTag('details');
    }

    /**
     * @param FormInputBase $input
     * @return string|void
     */
    public function layoutInput($input)
    {
        $this->inner_layout->layoutInput($input);
    }

    /**
     * @param FormSectionBase $subsection
     * @return string|void
     */
    public function layoutSubsection($subsection)
    {
        $this->inner_layout->layoutSubsection($subsection);
    }
}
