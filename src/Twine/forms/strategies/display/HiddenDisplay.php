<?php

namespace Twine\forms\strategies\display;

/**
 * HiddenDisplay
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 *
 * ------------------------------------------------------------------------
 */
class HiddenDisplay extends DisplayBase
{
    /**
     *
     * @return string of html to display the HIDDEN field
     */
    public function display()
    {
        $input = $this->input;
        return "<input 
            type='hidden' 
            id='{$input->htmlId()}' 
            name='{$input->htmlName()}' 
            class='{$input->htmlClass()}' 
            style='{$input->htmlStyle()}' 
            value='{$input->rawValueInForm()}' 
            {$input->otherHtmlAttributesString()}/>";
    }
}
