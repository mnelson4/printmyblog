<?php

namespace Twine\forms\base;

use Twine\forms\strategies\layout\AdminTwoColumnLayout;
use Twine\forms\strategies\layout\DetailsSummaryLayout;

/**
 * Class FormSectionDetails
 * @package Twine\forms\base
 */
class FormSectionDetails extends FormSection
{
    /**
     * @var mixed|string|void
     */
    protected $html_summary;

    /**
     * FormSectionDetails constructor.
     * @param array $options_array
     * @throws \Twine\forms\helpers\ImproperUsageException
     */
    public function __construct($options_array = array())
    {
        if (isset($options_array['html_summary'])) {
            $this->html_summary = (string)$options_array['html_summary'];
        } else {
            $this->html_summary = __('Details', 'twine');
        }
        if (! isset($options_array['layout_strategy'])) {
            $options_array['layout_strategy'] = new AdminTwoColumnLayout();
        }
        // wrap the layout strategy in the details-summary one.
        $options_array['layout_strategy'] = new DetailsSummaryLayout(
            $options_array['layout_strategy']
        );
        parent::__construct($options_array);
    }


    /**
     * Gets the summary text to show
     * @return string
     */
    public function getSummary()
    {
        return $this->html_summary;
    }
}
