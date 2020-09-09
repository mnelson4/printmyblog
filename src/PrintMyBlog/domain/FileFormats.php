<?php


namespace PrintMyBlog\domain;


use PrintMyBlog\entities\Format;

class FileFormats {

	/**
	 * Gets the declared project formats
	 * @return Format[]
	 */
	public function getFormats()
	{
		$formats = wp_cache_get('project_formats','pmb', null, $found);
		if( ! $found){
			$formats = $this->declareFormats();
			wp_cache_set('project_formats',$formats, 'pmb');
		}
		return $formats;
	}

	/**
	 * @return Format[]
	 */
	protected function declareFormats()
	{
		/**
		 * @var $formats Format[]
		 */
		$formats = apply_filters(
			'PrintMyBlog\domain\ProjectFormatManager->declareFormats',
			[
				'digital_pdf' => new Format(
					__('Digital PDF', 'print-my-blog')
				),
				'print_pdf' => new Format(
					__('Print PDF', 'print-my-blog')
				)
			]
		);
		foreach($formats as $slug => $format){
			$format->construct_finalize($slug);
		}
		return $formats;
	}
}