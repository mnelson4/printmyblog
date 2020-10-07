<?php


namespace Twine\forms\strategies\display;


class DatepickerDisplay extends TextInputDisplay {
	public function __construct( $type = 'text' ) {
		parent::__construct( 'datepicker' );
	}
}