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
		return 'data:image/svg+xml;base64,' . base64_encode($this->getPmbIconSvg());
	}
}