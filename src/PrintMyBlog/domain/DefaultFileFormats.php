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
				'title' => __('Digital PDF', 'print-my-blog'),
				'generator' => 'PrintMyBlog\services\generators\PdfGenerator',
				'default' => 'classic_digital',
			]
		);
		pmb_register_file_format(
			'print_pdf',
			[
				'title' => __('Print-Ready PDF', 'print-my-blog'),
				'generator' => 'PrintMyBlog\services\generators\PdfGenerator',
				'default' => 'classic_print'
			]
		);
	}
}