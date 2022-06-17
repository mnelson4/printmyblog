<?php

namespace PrintMyBlog\services;

/**
 * Class ColorGuru
 * @package PrintMyBlog\services
 */
class ColorGuru
{
    /**
     * Returns an array where the values are RGB values from the color.
     * @param string $hex_code
     *
     * @return array|false
     */
    public function convertHexToRgb($hex_code)
    {
        return sscanf($hex_code, '#%02x%02x%02x');
    }

    /**
     * @param string $hex_code
     * @param string $alpha
     * @return string
     */
    public function convertHextToRgba($hex_code, $alpha)
    {
        $rgb = $this->convertHexToRgb($hex_code);
        $rgb[] = $alpha;
        return 'rgba(' . implode(
            ',',
            $rgb
        ) . ')';
    }
}
