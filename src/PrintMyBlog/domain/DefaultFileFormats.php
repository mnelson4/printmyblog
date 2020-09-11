<?php


namespace PrintMyBlog\domain;


use PrintMyBlog\entities\FileFormat;

class DefaultFileFormats {


	/**
	 * @return FileFormat[]
	 */
	public function registerFileFormats()
	{
		pmb_register_file_format(
			'digital_pdf',
			[
				'title' => __('Digital PDF', 'print-my-blog')
			]
		);
		pmb_register_file_format(
			'print_pdf',
			[
				__('Print PDF', 'print-my-blog')
			]
		);
	}
}