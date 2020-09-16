<?php


namespace Twine\services\config;


abstract class Config {
	/**
	 * @var array, keys are setting names, values are whatever we want.
	 */
	protected $settings;

	/**
	 * @var bool indicates the config needs to be saved
	 */
	protected $dirty = false;


	/**
	 * Loads the settings from the database, if not already done. Automatically called when we want to get some
	 * settings.
	 * @return void
	 */
	protected function ensureLoadedFromDb(){
		if($this->settings === null){
			$this->settings = array_merge(
				$this->getDefaults(),
				get_option($this->optionName()), array()
			);
		}
	}

	/**
	 * Returns the option name where the configuration will be stored.
	 * @return string
	 */
	protected abstract function optionName();

	/**
	 * Lazily called when we need to know the default values for settings.
	 * @return array
	 */
	protected abstract function declareDefaults();

	/**
	 * Gets the default values lazily.
	 * @return array
	 */
	public function getDefaults(){
		if($this->defaults === null){
			$this->defaults = $this->declareDefaults();
		}
		return $this->defaults;
	}

	/**
	 * Gets the saved setting
	 * @param $setting_name
	 *
	 * @return mixed
	 */
	public function getSetting($setting_name){
		$this->ensureLoadedFromDb();
		return $this->settings[$setting_name];
	}

	/**
	 * Replaces all the settings with the provided ones. Settings will be automatically saved at the end of the request.
	 * @param array $all_settings keys are the setting names, values are their values.
	 */
	public function setSettings($all_settings){
		$this->ensureLoadedFromDb();
		if( ! $this->dirty ){
			$this->setDirty();
		}
		$this->settings = $all_settings;
	}

	/**
	 * Sets the setting. This will be automatically persisted to the database at the end of the request.
	 * @param string $setting_name
	 * @param mixed $value
	 */
	public function setSetting($setting_name, $value){
		$this->ensureLoadedFromDb();
		if( ! $this->dirty && $this->settings[$setting_name] !== $value){
			$this->setDirty();
		}
		$this->settings[$setting_name] = $value;

	}

	protected function setDirty(){
		$this->dirty = true;
		add_action('shutdown',[$this,'save']);
	}

	/**
	 * Persists the settings to the database for subsequent requests.
	 * You don't need to explicitly call this, as it's automatically enqueued to be done on shutdown after a call to
	 * setSetting().
	 * But this is here in case you'd like to save it early.
	 */
	public function save(){
		update_option($this->optionName(),$this->settings);
	}
}