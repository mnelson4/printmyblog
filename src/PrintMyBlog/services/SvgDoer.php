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
     *
     */
    protected $raw_svgs = [];

    /**
     * @param $path
     * @param $color
     *
     * @return string|string[]
     */
    public function getSvgDataAsColor($path, $color = '')
    {
        if (! isset($this->raw_svgs[$path])) {
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
     * @param $svg_content
     *
     * @return string
     */
    protected function dataizeAndEncode($svg_content)
    {
        return 'data:image/svg+xml;base64,' . base64_encode($svg_content);
    }
}
