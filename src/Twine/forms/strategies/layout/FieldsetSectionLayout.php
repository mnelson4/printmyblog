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
     * Legend_text
     *
     * @var string
     */
    protected $legend_text;



    /**
     *    Construct
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
        parent::__construct();
    }



    /**
     * Opening div tag for a form
     *
     * @return string
     */
    public function layoutFormBegin()
    {
        $html_generator = Html::instance();
        $html = $html_generator->nl(1)
                . '<fieldset id="'
                . $this->form_section->htmlId()
                . '" class="'
                . $this->form_section->htmlClass()
                . '" style="'
                . $this->form_section->htmlStyle()
                . '">';
        $html .= '<legend class="' . $this->legendClass() . '">' . $this->legendText() . '</legend>';
        return $html;
    }



    /**
     * Closing div tag for a form
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
