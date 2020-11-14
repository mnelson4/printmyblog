<?php


namespace PrintMyBlog\services;

/**
 * Class SvgDoer
 * SVG-related functions
 * @package PrintMyBlog\services
 */
class SvgDoer {
	/**
	 * @var string of SVG XML
	 */
	protected $icon_svg_raw;
	public function getPmbIconSvg(){
		if(! $this->icon_svg_raw){
			$this->icon_svg_raw = file_get_contents(PMB_DIR . 'assets/images/menu-icon.svg');
		}
		return $this->icon_svg_raw;
	}

	public function getPmbIconSvgData(){
		return $this->dataizeAndEncode($this->getPmbIconSvg());
	}

	/**
	 * @param $path
	 * @param $color
	 *
	 * @return string|string[]
	 */
	public function getSvgDataAsColor($path, $color){
		$contents = file_get_contents($path);
		return $this->dataizeAndEncode(str_replace('black', $color, $contents));
	}

	/**
	 * Takes the SVG text, encodes it, and prepends it with the magic string to make it work just like an image.
	 * @param $svg_content
	 *
	 * @return string
	 */
	protected function dataizeAndEncode($svg_content){
		return 'data:image/svg+xml;base64,' . base64_encode($svg_content);
	}
}