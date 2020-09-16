<?php


namespace PrintMyBlog\services\config;

use PrintMyBlog\entities\FileFormat;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\managers\DesignManager;
use PrintMyBlog\services\FileFormatRegistry;
use Twine\services\config\Config as TwineConfig;

class Config extends TwineConfig {
	/**
	 * @var FileFormatRegistry
	 */
	protected $format_registry;
	/**
	 * @var DesignManager
	 */
	protected $design_manager;

	protected function optionName() {
		return 'print_my_blog';
	}

	protected function declareDefaults() {
		$defaults = [];
		foreach($this->format_registry->getFormats() as $format){
			$defaults[$this->getSettingForDefaultDesignForFormat($format)] = null;
		}
		return $defaults;
	}

	public function inject(FileFormatRegistry $format_registry, DesignManager $design_manager){
		$this->format_registry = $format_registry;
		$this->design_manager = $design_manager;
	}

	/**
	 * @param FileFormat|string $format
	 *
	 * @return string
	 */
	protected function getSettingForDefaultDesignForFormat($format){
		if($format instanceof FileFormat){
			$format = $format->slug();
		}
		return 'default_' . $format . '_design';
	}

	/**
	 * Gets the default design object for the requested format.
	 * @param $format
	 *
	 * @return Design|null
	 */
	public function getDefaultDesignFor($format){
		if($format instanceof FileFormat){
			$format = $format->slug();
		}
		$key = $this->getSettingForDefaultDesignForFormat($format);
		$design_id = $this->getSetting($key);
		if( $design_id){
			$design = $this->design_manager->getById($design_id);
			if( $design instanceof Design){
				return $design;
			}
		}
		// We didn't find an existing design. Just look for a design with the default slug.
		$design = $this->design_manager->getBySlug('classic_' . $format);
		if($design instanceof Design){
			$this->setSetting($key, $design->getWpPost()->ID);
			return $design;
		}
		// Ok I kinda give up.
		return null;
	}
}