<?php


namespace PrintMyBlog\controllers;


use PrintMyBlog\domain\PrintButtons;
use Twine\controllers\BaseController;

/**
 * Class Shortcodes
 *
 * Adds and executes shortcodes.
 * @package PrintMyBlog\controllers
 */
class Shortcodes extends BaseController {

	public function setHooks() {
		add_shortcode(
			'pmb_print_buttons',
			[$this,'print_buttons_shortcode']
		);
	}

	public function print_buttons_shortcode($atts)
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