<?php

namespace PrintMyBlog\services\config;

use PrintMyBlog\entities\FileFormat;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\managers\DesignManager;
use PrintMyBlog\services\FileFormatRegistry;
use Twine\services\config\Config as TwineConfig;

/**
 * Class Config
 * @package PrintMyBlog\services\config
 */
class Config extends TwineConfig
{
    const ADMIN_PRINT_BUTTONS_FORMATS_SETTING_NAME = 'admin_print_buttons_formats';
    const ADMIN_PRINT_BUTTONS_POST_TYPES_SETTING_NAME = 'admin_print_buttons_post_types';

    /**
     * @var FileFormatRegistry
     */
    protected $format_registry;
    /**
     * @var DesignManager
     */
    protected $design_manager;

    /**
     * @return string
     */
    protected function optionName()
    {
        return 'pmb_config';
    }

    /**
     * @return array
     */
    protected function declareDefaults()
    {
        $defaults = [
            self::ADMIN_PRINT_BUTTONS_FORMATS_SETTING_NAME => [],
            self::ADMIN_PRINT_BUTTONS_POST_TYPES_SETTING_NAME => [],
        ];
        $post_types = get_post_types(array( 'exclude_from_search' => false ), 'names');
        $defaults[self::ADMIN_PRINT_BUTTONS_POST_TYPES_SETTING_NAME] = $post_types;
        foreach ($this->format_registry->getFormats() as $format) {
            $defaults[$this->getSettingNameForDefaultDesignForFormat($format)] = null;
            $defaults[self::ADMIN_PRINT_BUTTONS_FORMATS_SETTING_NAME][] = $format->slug();
        }
        return $defaults;
    }

    /**
     * @param FileFormatRegistry $format_registry
     * @param DesignManager $design_manager
     */
    public function inject(FileFormatRegistry $format_registry, DesignManager $design_manager)
    {
        $this->format_registry = $format_registry;
        $this->design_manager = $design_manager;
    }

    /**
     * @param FileFormat|string $format
     *
     * @return string
     */
    public function getSettingNameForDefaultDesignForFormat($format)
    {
        if ($format instanceof FileFormat) {
            $format = $format->slug();
        }
        return 'default_' . $format . '_design';
    }

    /**
     * Gets the default design object for the requested format.
     * @param string|FileFormat $format
     *
     * @return Design|null
     */
    public function getDefaultDesignFor($format)
    {
        if (! $format instanceof FileFormat) {
            $format = $this->format_registry->getFormat($format);
        }
        $key = $this->getSettingNameForDefaultDesignForFormat($format);
        $design_id = $this->getSetting($key);
        if ($design_id) {
            $design = $this->design_manager->getById($design_id);
            if ($design instanceof Design) {
                return $design;
            }
        }
        // We didn't find an existing design. Just look for a design with the default slug.
        $design = $this->design_manager->getBySlug($format->getDefaultDesignTemplate()->getDefaultDesignSlug());
        if ($design instanceof Design) {
            $this->setSetting($key, $design->getWpPost()->ID);
            return $design;
        }
        // Ok I kinda give up.
        return null;
    }
}
