<?php


namespace PrintMyBlog\services;


class ColorGuru {
	/**
	 * Returns an array where the values are RGB values from the color.
	 * @param $hex_code
	 *
	 * @return array|false
	 */
	public function convertHexToRgb($hex_code){
		return sscanf($hex_code, "#%02x%02x%02x");
	}

	/**
	 * @param $hex_code
	 *
	 * @return string
	 */
	public function convertHextToRgba($hex_code, $alpha){
		$rgb = $this->convertHexToRgb($hex_code);
		$rgb[] = $alpha;
		return 'rgba(' . implode(
			',',
			$rgb
		) . ')';
	}
}