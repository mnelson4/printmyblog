<?php

namespace PrintMyBlog\domain;

use PrintMyBlog\entities\FileFormat;

class DefaultFileFormats
{
    const DIGITAL_PDF = 'digital_pdf';
    const PRINT_PDF = 'print_pdf';

    /**
     * @return FileFormat[]
     */
    public function registerFileFormats()
    {
        pmb_register_file_format(
            self::DIGITAL_PDF,
            [
                'title' => __('Digital PDF', 'print-my-blog'),
                'icon' => 'dashicons-tablet',
                'generator' => 'PrintMyBlog\services\generators\PdfGenerator',
                'default' => 'classic_digital',
                'desc' => __('PDF file intended for viewing on a computer, tablet or phone, but not necessarily for printing to paper. Usually includes working hyperlinks, ample colors, and other features that require a device.', 'print-my-blog'),
                'color' => '#b3f0ff'
                ]
        );
        pmb_register_file_format(
            self::PRINT_PDF,
            [
                'title' => __('Print-Ready PDF', 'print-my-blog'),
                'icon' => 'dashicons-book-alt',
                'generator' => 'PrintMyBlog\services\generators\PdfGenerator',
                'default' => 'classic_print',
                'desc' => __('PDF file intended for printing on your home printer or with a printer service. Usually removes hyperlinks, avoids excessive ink use, and are designed for viewing the 2-page spread (using the front and back of a page).'),
                'color' => '#B5F2B5'
                ]
        );
    }
}
