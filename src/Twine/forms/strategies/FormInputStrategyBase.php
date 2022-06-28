<?php

namespace Twine\forms\strategies;

use Twine\forms\inputs\FormInputBase;

/**
 * Base class for all strategies which operate on form inputs. Generally, they
 * all need to know about the form input they are operating on.
 */
abstract class FormInputStrategyBase
{

    /**
     * Form Input to display
     *
     * @var FormInputBase
     */
    protected $input;


    /**
     * FormInputStrategyBase constructor.
     */
    public function __construct()
    {
    }



    /**
     * The form input on which this strategy is to perform
     *
     * @param FormInputBase $form_input
     */
    public function constructFinalize(FormInputBase $form_input)
    {
        $this->input = $form_input;
    }



    /**
     * Gets this strategy's input
     *
     * @return FormInputBase
     */
    public function getInput()
    {
        return $this->input;
    }
}
