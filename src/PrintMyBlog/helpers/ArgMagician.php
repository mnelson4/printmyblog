<?php


namespace PrintMyBlog\helpers;


use PrintMyBlog\entities\FileFormat;

class ArgMagician {

	/**
	 * Takes an incoming string or FileFormat and returns a format slug.
	 * @param FileFormat|string $format
	 * @return string
	 */
	public static function castToFormatSlug($format){
		if($format instanceof FileFormat){
			return $format->slug();
		}
		return $format;
	}
}