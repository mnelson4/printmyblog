<?php


namespace PrintMyBlog\domain;


class ProjectFormatManager {

	/**
	 * Gets the declared project formats
	 * @return ProjectFormat[]
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
	 * @return ProjectFormat[]
	 */
	protected function declareFormats()
	{
		/**
		 * @var $formats ProjectFormat[]
		 */
		$formats = apply_filters(
			'PrintMyBlog\domain\ProjectFormatManager->declareFormats',
			[
				'digital_pdf' => new ProjectFormat(
					__('Digital PDF', 'print-my-blog')
				),
				'print_pdf' => new ProjectFormat(
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