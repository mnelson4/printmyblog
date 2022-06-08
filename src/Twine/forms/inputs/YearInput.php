<?php

namespace Twine\forms\inputs;

/**
 * Year_Input
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 *
 * ------------------------------------------------------------------------
 */
class YearInput extends SelectInput
{
    /**
     * YearInput constructor.
     * @param array $input_settings
     * @param bool $four_digit_year
     * @param int $years_behind
     * @param int $years_ahead
     */
    public function __construct(
        $input_settings = array(),
        $four_digit_year = true,
        $years_behind = 100,
        $years_ahead = 0
    ) {
        if ($four_digit_year) {
            $current_year_int = intval(gmdate('Y'));
        } else {
            $current_year_int = intval(gmdate('y'));
        }
        $answer_options = array();
        for ($start = $current_year_int - $years_behind; $start <= ($current_year_int + $years_ahead); $start++) {
            $answer_options[ $start ] = $start;
        }
        parent::__construct($answer_options, $input_settings);
    }
}
