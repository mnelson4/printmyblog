<?php

namespace PrintMyBlog\services;

/**
 * Class SvgDoer
 * SVG-related functions
 * @package PrintMyBlog\services
 */
class SvgDoer
{

    /**
     * @var string[]
     */
    protected $raw_svgs = [];

    /**
     * @param string $path
     * @param string $color
     *
     * @return string|string[]
     */
    public function getSvgDataAsColor($path, $color = '')
    {
        if (! isset($this->raw_svgs[$path])) {
            // Gets file contents from local file.
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
            $this->raw_svgs[$path] = file_get_contents($path);
        }
        if ($color) {
            $updated_svg = str_replace('black', $color, $this->raw_svgs[$path]);
        } else {
            $updated_svg = $this->raw_svgs[$path];
        }
        return $this->dataizeAndEncode($updated_svg);
    }

    /**
     * Takes the SVG text, encodes it, and prepends it with the magic string to make it work just like an image.
     * @param string $svg_content
     *
     * @return string
     */
    protected function dataizeAndEncode($svg_content)
    {
        // Encoding content into a format usable as an image (eg on the "src" attribute of an img tag.)
        // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
        return 'data:image/svg+xml;base64,' . base64_encode($svg_content);
    }
}
