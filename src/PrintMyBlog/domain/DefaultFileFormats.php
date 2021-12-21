<?php

namespace PrintMyBlog\domain;

use PrintMyBlog\entities\FileFormat;

class DefaultFileFormats
{
    const DIGITAL_PDF = 'digital_pdf';
    const PRINT_PDF = 'print_pdf';
    const EPUB = 'epub';

    /**
     * @return FileFormat[]
     */
    public function registerFileFormats()
    {
        pmb_register_file_format(
            self::DIGITAL_PDF,
            [
                'title' => __('Digital PDF', 'print-my-blog'),
                'icon' => 'dashicons-desktop',
                'generator' => 'PrintMyBlog\services\generators\PdfGenerator',
                'default' => 'classic_digital',
                'desc' => __('PDF file intended for viewing on a computer, tablet or phone, but not necessarily for printing to paper. Usually includes working hyperlinks, ample colors, and other features that require a device.', 'print-my-blog'),
                'color' => '#b3f0ff',
                'extension' => 'pdf'
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
                'color' => '#B5F2B5',
                'extension' => 'pdf'
                ]
        );
//        if(pmb_fs()->is_plan__premium_only('founding_members')) {
            pmb_register_file_format(
                self::EPUB,
                [
                    'title' => __('e-Book (ePub)', 'print-my-blog'),
                    'icon' => 'dashicons-tablet',
                    'generator' => 'PrintMyBlog\services\generators\EpubGenerator',
                    'default' => 'basic_epub',
                    'desc' => __('ePub file especially good for reading on mobile devices and for selling as e-books on marketplaces like Amazon.com', 'print-my-blog'),
                    'color' => '#ffcc00',
                    'extension' => 'epub'
                ]
            );
//        }
    }
}
