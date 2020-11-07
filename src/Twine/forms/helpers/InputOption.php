<?php


namespace Twine\forms\helpers;


class InputOption {
	protected $display_text;
	protected $help_text;
	public function __construct($display_text, $help_text = null) {
		$this->display_text = (string)$display_text;
		$this->help_text = (string)$help_text;
	}

	/**
	 * @return string
	 */
	public function getDisplayText(){
		return $this->display_text;
	}

	/**
	 * @return string
	 */
	public function getHelpText(){
		return $this->help_text;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getDisplayText();
	}
}