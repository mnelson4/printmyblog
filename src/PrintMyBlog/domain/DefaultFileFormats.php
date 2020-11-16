<?php

namespace PrintMyBlog\domain;

use PrintMyBlog\entities\FileFormat;

class DefaultFileFormats
{


    /**
     * @return FileFormat[]
     */
    public function registerFileFormats()
    {
        pmb_register_file_format(
            'digital_pdf',
            [
                'title' => __('Digital PDF', 'print-my-blog'),
                'dashicon' => 'dashicons-tablet',
                'generator' => 'PrintMyBlog\services\generators\PdfGenerator',
                'default' => 'classic_digital',
                // phpcs:disable Generic.Files.LineLength.TooLong
                'desc' => __('PDF file intended for viewing on a computer, tablet or phone, but not necessarily for printing to paper. Usually includes working hyperlinks, ample colors, and other features that require a device.', 'print-my-blog')
                // phpcs:enable Generic.Files.LineLength.TooLong
            ]
        );
        pmb_register_file_format(
            'print_pdf',
            [
                'title' => __('Print-Ready PDF', 'print-my-blog'),
                'dashicon' => 'dashicons-book-alt',
                'generator' => 'PrintMyBlog\services\generators\PdfGenerator',
                'default' => 'classic_print',
                // phpcs:disable Generic.Files.LineLength.TooLong
                'desc' => __('PDF file intended for printing on your home printer or with a printer service. Usually removes hyperlinks, avoids excessive ink use, and are designed for viewing the 2-page spread (using the front and back of a page).')
                // phpcs:enable Generic.Files.LineLength.TooLong
            ]
        );
    }
}
