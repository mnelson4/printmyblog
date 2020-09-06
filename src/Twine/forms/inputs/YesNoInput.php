<?php
namespace Twine\forms\inputs;
/**
 * Yes_No_Input
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 */
class YesNoInput extends SelectInput
{

    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        $select_options = array(true=>  __("Yes", "event_espresso"),false=>  __("No", "event_espresso"));

        parent::__construct($select_options, $options);
    }
}
