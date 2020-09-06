<?php
namespace Twine\forms\base;
/**
 * FormSectionHtml
 * HTML to be laid out like a proper subsection
 *
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 *
 * ------------------------------------------------------------------------
 */
class FormSectionHtml extends FormSectionBase
{

    protected $_html = '';



    /**
     * @param string $html
     * @param array $options_array
     */
    public function __construct($html = '', $options_array = array())
    {
        $this->_html = $html;
        parent::__construct($options_array);
    }



    /**
     * Returns the HTML
     * @return string
     */
    public function get_html()
    {
        return $this->_html;
    }
}

// End of file FormSectionHtml.php
