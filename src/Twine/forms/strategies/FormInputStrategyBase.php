<?php
namespace Twine\forms\strategies;


use Twine\forms\inputs\FormInputBase;

/**
 * base class for all strategies which operate on form inputs. Generally, they
 * all need to know about the form input they are operating on.
 */
abstract class FormInputStrategyBase
{

    /**
     * Form Input to display
     *
     * @var FormInputBase
     */
    protected $_input;



    public function __construct()
    {
    }



    /**
     * The form input on which this strategy is to perform
     *
     * @param FormInputBase $form_input
     */
    public function _construct_finalize(FormInputBase $form_input)
    {
        $this->_input = $form_input;
    }



    /**
     * Gets this strategy's input
     *
     * @return FormInputBase
     */
    public function get_input()
    {
        return $this->_input;
    }
}
