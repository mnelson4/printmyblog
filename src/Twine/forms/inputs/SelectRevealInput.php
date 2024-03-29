<?php

namespace Twine\forms\inputs;

use Twine\forms\base\FormSectionBase;
use Twine\forms\helpers\InputOption;

/**
 * Class Select_Reveal_Input
 *
 * Generates an HTML <select> form input, which, when selected, will reveal
 * a sibling subsections whose names match the array keys of the $answer_options
 *
 * @package             Event Espresso
 * @subpackage  core
 * @author              Mike Nelson
 * @since               4.6
 *
 */
class SelectRevealInput extends SelectInput
{
    /**
     * Gets all the sibling sections controlled by this reveal select input
     * @return FormSectionBase[] keys are their form section paths
     */
    public function siblingSectionsControlled()
    {
        $sibling_sections = array();
        foreach ($this->options() as $sibling_section_name => $sibling_section) {
            // if it's an empty string just leave it alone
            if (empty($sibling_section_name)) {
                continue;
            }
            $sibling_section = $this->findSectionFromPath('../' . $sibling_section_name);
            if (
                $sibling_section instanceof FormSectionBase
                && ! empty($sibling_section_name)
            ) {
                $sibling_sections[ $sibling_section_name ] = $sibling_section;
            }
        }
        return $sibling_sections;
    }

    /**
     * Adds an entry of 'select_reveal_inputs' to the js data, which is an array
     * whose top-level keys are select reveal input html ids; values are arrays
     * whose keys are select option values and values are the sections they reveal
     * @param array $form_other_js_data
     * @return array
     */
    public function getOtherJsData($form_other_js_data = array())
    {
        $form_other_js_data = parent::getOtherJsData($form_other_js_data);
        if (! isset($form_other_js_data['select_reveal_inputs'])) {
            $form_other_js_data['select_reveal_inputs'] = array();
        }
        $sibling_input_to_html_id_map = array();
        foreach ($this->siblingSectionsControlled() as $sibling_section_path => $sibling_section) {
            $sibling_input_to_html_id_map[ $sibling_section_path ] = $sibling_section->htmlId();
        }
        $form_other_js_data['select_reveal_inputs'][ $this->htmlId() ] = $sibling_input_to_html_id_map;
        return $form_other_js_data;
    }
}
