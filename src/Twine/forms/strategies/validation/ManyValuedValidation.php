<?php

namespace Twine\forms\strategies\validation;

use Twine\forms\inputs\FormInputBase;

/**
 * Class ManyValuedValidation
 *
 * For validation on an input which has an ARRAY of values, instead of a single value. The
 * individual item validation strategies will be applied to EACH item in the array
 *
 * @package             Event Espresso
 * @subpackage  core
 * @author              Mike Nelson
 * @since               4.6
 *
 */
class ManyValuedValidation extends ValidationBase
{
    /**
     * @var array|ValidationBase[]|ValidationBase[][]
     */
    protected $individual_item_validation_strategies = array();
    /**
     *
     * @param ValidationBase[] $individual_item_validation_strategies (or a single ValidationBase)
     */
    public function __construct($individual_item_validation_strategies)
    {
        if (! is_array($individual_item_validation_strategies)) {
            $individual_item_validation_strategies = array($individual_item_validation_strategies);
        }
        $this->individual_item_validation_strategies = $individual_item_validation_strategies;
        parent::__construct();
    }



    /**
     * Applies all teh individual item validation strategies on each item in the array
     * @param array $normalized_value
     * @return boolean
     */
    public function validate($normalized_value)
    {
        if (is_array($normalized_value)) {
            $items_to_validate = $normalized_value;
        } else {
            $items_to_validate = array($normalized_value);
        }
        foreach ($items_to_validate as $individual_item) {
            foreach ($this->individual_item_validation_strategies as $validation_strategy) {
                if ($validation_strategy instanceof ValidationBase) {
                    $validation_strategy->validate($individual_item);
                }
            }
        }
        return true;
    }



    /**
     * Extends parent's _construct_finalize so we ALSO set the input
     * on each sub-validation-strategy
     *
     * @param FormInputBase $form_input
     */
    public function constructFinalize(FormInputBase $form_input)
    {
        parent::constructFinalize($form_input);
        foreach ($this->individual_item_validation_strategies as $item_validation_strategy) {
            if ($item_validation_strategy instanceof ValidationBase) {
                $item_validation_strategy->constructFinalize($form_input);
            }
        }
    }
}
