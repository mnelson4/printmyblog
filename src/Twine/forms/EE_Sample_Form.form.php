<?php
class Sample_Form extends FormSectionProper
{
    public function __construct()
    {
        $this->_subsections = array(
            'h1'=>new FormSectionHtml('hello wordl'),
            'name'=>new Text_Input(array('required'=>true,'default'=>'your name here')),
            'email'=>new Email_Input(array('required'=>false)),
            'shirt_size'=>new Select_Input(array(''=>'Please select...', 's'=>  __("Small", "event_espresso"),'m'=>  __("Medium", "event_espresso"),'l'=>  __("Large", "event_espresso")), array('required'=>true,'default'=>'s')),
            'month_normal'=>new Month_Input(),
            'month_leading_zero'=>new Month_Input(true),
            'year_2'=>new Year_Input(false, 1, 1),
            'year_4'=>new Year_Input(true, 0, 10, array('default'=>'2017')),
            'yes_no'=>new Yes_No_Input(array('html_label_text'=>  __("Yes or No", "event_espresso"))),
            'credit_card'=>new Credit_Card_Input(),
            'image_1'=>new AdminFileUploaderInput(),
            'image_2'=>new AdminFileUploaderInput(),
            'skillz'=>new Checkbox_Multi_Input(array('php'=>'PHP','mysql'=>'MYSQL'), array('default'=>array('php'))),
            'float'=>new Float_Input(),
            'essay'=>new Text_Area_Input(),
            'amenities'=>new Select_Multiple_Input(
                array(
                    'hottub'=>'Hot Tub',
                    'balcony'=>"Balcony",
                    'skylight'=>'SkyLight',
                    'no_axe'=>'No Axe Murderers'
                ),
                array(
                    'default'=>array(
                        'hottub',
                        'no_axe' ),
                )
            ),
            'payment_methods'=>new Select_Multi_Model_Input(EEM_Payment_Method::instance()->get_all()),
            );
        $this->_layout_strategy = new DivPerSectionLayout();
        parent::__construct();
    }

    /**
     * Extra validation for the 'name' input.
     * @param Text_Input $form_input
     */
    public function _validate_name($form_input)
    {
        if ($form_input->raw_value() != 'Mike') {
            $form_input->add_validation_error(__("You are not mike. You must be brent or darren. Thats ok, I guess", 'event_espresso'), 'not-mike');
        }
    }

    public function _validate()
    {
        parent::_validate();
        if ($this->_subsections['shirt_size']->normalized_value() =='s'
                && $this->_subsections['year_4']->normalized_value() < 2010) {
            $this->add_validation_error(__("If you want a small shirt, you should be born after 2010. Otherwise theyre just too big", 'event_espresso'), 'too-old');
        }
    }
}
