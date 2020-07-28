<?php


namespace PrintMyBlog\controllers;


use PrintMyBlog\domain\PrintButtons;
use Twine\controllers\BaseController;

class Shortcode extends BaseController {

	public function setHooks() {
		add_shortcode(
			'print_my_blog',
			[$this,'do_shortcode']
		);
	}

	public function do_shortcode($atts)
	{
		$atts = shortcode_atts(
			[
				'ID' => null
			],
			$atts
		);
		return (new PrintButtons())->getHtmlForPrintButtons($atts['ID']);
	}
}