<?php
namespace Twine\forms\strategies\display;
use Exception;
use Twine\forms\inputs\FormInputWithOptionsBase;
use Twine\helpers\Array2;
use Twine\helpers\Html;

/**
 * SelectMultipleDisplay
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 *
 * ------------------------------------------------------------------------
 */
class SelectMultipleDisplay extends SelectDisplay
{

    /**
     *
     * @throws Exception
     * @return string of html to display the field
     */
    public function display()
    {

        if (! $this->_input instanceof FormInputWithOptionsBase) {
            throw new Exception(sprintf(__('Cannot use Select Multiple Display Strategy with an input that doesn\'t have options', "event_espresso")));
        }
		$html_generator = Html::instance();
        $html = $html_generator->nl(0, 'select');
        $html .= '<select multiple';
        $html .= ' id="' . $this->_input->html_id() . '"';
        $html .= ' name="' . $this->_input->html_name() . '[]"';
        $class = $this->_input->required() ? $this->_input->required_css_class() . ' ' . $this->_input->html_class() : $this->_input->html_class();
        $html .= ' class="' . $class . '"';
        // add html5 required
        $html .= $this->_input->required() ? ' required' : '';
        $html .= ' style="' . $this->_input->html_style() . '"';
        $html .= ' ' . $this->_input->otherHtmlAttributesString();
        $html .= '>';

        $html_generator->indent(1, 'select');
        if (Array2::is_multi_dimensional_array($this->_input->options())) {
            throw new Exception(sprintf(__("Select multiple display strategy does not allow for nested arrays of options.", "event_espresso")));
        } else {
            $html.=$this->_display_options($this->_input->options());
        }

        $html.= $html_generator->nl(-1, 'select') . "</select>";
        return $html;
    }



    /**
     * Checks if that $value is one of the selected ones
     * @param string|int $value unnormalized value option (string)
     * @return boolean
     */
    protected function _check_if_option_selected($value)
    {
        $selected_options = $this->_input->raw_value();
        if (empty($selected_options)) {
            return false;
        }
        return in_array($value, $selected_options) ? true : false;
    }
}
