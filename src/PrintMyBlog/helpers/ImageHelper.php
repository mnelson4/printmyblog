<?php

namespace PrintMyBlog\helpers;

/**
 * Class ImageHelper
 * @package PrintMyBlog\helpers
 */
class ImageHelper
{
    /**
     * Gets all registered image/thumbnail sizes
     * Copy-paste from https://wordpress.stackexchange.com/a/251602/52760
     * @return array of arrays with sub-indexes 'width', 'height' and 'crop'
     */
    public function getAllImageSizes()
    {
        global $_wp_additional_image_sizes;

        $default_image_sizes = get_intermediate_image_sizes();

        foreach ($default_image_sizes as $size) {
            $image_sizes[ $size ]['width'] = intval(get_option("{$size}_size_w"));
            $image_sizes[ $size ]['height'] = intval(get_option("{$size}_size_h"));
            $image_sizes[ $size ]['crop'] = get_option("{$size}_crop") ? get_option("{$size}_crop") : false;
        }

        if (isset($_wp_additional_image_sizes) && count($_wp_additional_image_sizes)) {
            $image_sizes = array_merge($image_sizes, $_wp_additional_image_sizes);
        }

        return $image_sizes;
    }
}
