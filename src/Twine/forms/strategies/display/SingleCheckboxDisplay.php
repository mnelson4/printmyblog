<?php


namespace Twine\forms\strategies\display;


use Twine\helpers\Html;

class SingleCheckboxDisplay extends TextInputDisplay{

	public function __construct( $type = 'text' ) {
		parent::__construct( 'checkbox' );
	}

	public function display(){
		$input = $this->get_input();
		if($input->normalized_value()){
			$input->addOtherHtmlAttribute('checked');
		}
		return parent::display();
	}
}