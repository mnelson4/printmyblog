<?php


namespace Twine\forms\strategies\display;


use Twine\helpers\Html;

class SingleCheckboxDisplay extends DisplayBase {


	public function display(){
		$html = $this->_opening_tag('input');
		$other_attributes = [
			'type' => 'checkbox',
			'value' => 1,
		];
		if($this->_input->normalized_value()){
			$other_attributes[] = 'checked';
		}
		$html .= $this->_attributes_string(
			array_merge(
				$this->_standard_attributes_array(),
				$other_attributes
			)
		);
		$html .= $this->_close_tag();
		return $html;
	}
}