<?php

namespace Twine\forms\strategies\layout;

use Twine\helpers\Html;

/**
 * Class FieldsetSectionLayout
 * Description
 *
 * @package               Event Espresso
 * @subpackage            core
 * @author                Brent Christensen
 *
 */
class FieldsetSectionLayout extends DivPerSectionLayout
{

    /**
     * legend_class
     *
     * @var string
     */
    protected $legend_class;

    /**
     * legend_text
     *
     * @var string
     */
    protected $legend_text;



    /**
     *    construct
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        foreach ($options as $key => $value) {
            $key = '_' . $key;
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }



    /**
     * opening div tag for a form
     *
     * @return string
     */
    public function layoutFormBegin()
    {
        $html_generator = Html::instance();
        $html = $html_generator->nl(1)
                . '<fieldset id="'
                . $this->Form_section->htmlId()
                . '" class="'
                . $this->Form_section->htmlClass()
                . '" style="'
                . $this->Form_section->htmlStyle()
                . '">';
        $html .= '<legend class="' . $this->legendClass() . '">' . $this->legendText() . '</legend>';
        return $html;
    }



    /**
     * closing div tag for a form
     *
     * @return string
     */
    public function layoutFormEnd()
    {
        $html_generator = Html::instance();
        return $html_generator->nl(-1) . '</fieldset>';
    }



    /**
     * @param string $legend_class
     */
    public function setLegendClass($legend_class)
    {
        $this->legend_class = $legend_class;
    }



    /**
     * @return string
     */
    public function legendClass()
    {
        return $this->legend_class;
    }



    /**
     * @param string $legend_text
     */
    public function setLegendText($legend_text)
    {
        $this->legend_text = $legend_text;
    }



    /**
     * @return string
     */
    public function legendText()
    {
        return $this->legend_text;
    }
}
