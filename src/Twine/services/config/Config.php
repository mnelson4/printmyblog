<?php

namespace Twine\services\config;

/**
 * Class Config
 * @package Twine\services\config
 */
abstract class Config
{
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
    protected function ensureLoadedFromDb()
    {
        if ($this->settings === null) {
            $saved_config = get_option($this->optionName());
            if (! is_array($saved_config)) {
                $saved_config = [];
            }
            $this->settings = array_merge(
                $this->declareDefaults(),
                $saved_config
            );
        }
    }

    /**
     * Returns the option name where the configuration will be stored.
     * @return string
     */
    abstract protected function optionName();

    /**
     * Lazily called when we need to know the default values for settings.
     * @return array
     */
    abstract protected function declareDefaults();

    /**
     * Gets the saved setting
     * @param string $setting_name
     *
     * @return mixed
     */
    public function getSetting($setting_name, $default = null)
    {
        $this->ensureLoadedFromDb();
        if(array_key_exists($setting_name, $this->settings)){
            $value = $this->settings[$setting_name];
        } else {
            $value = $default;
        }
        return apply_filters(
            '\Twine\services\config\Config::getSetting',
            $value,
            $setting_name,
            static::class,
            $this->settings
        );
    }

    /**
     * Replaces all the settings with the provided ones. Settings will be automatically saved at the end of the request.
     * @param array $all_settings keys are the setting names, values are their values.
     */
    public function setSettings($all_settings)
    {
        $this->ensureLoadedFromDb();
        if (! $this->dirty) {
            $this->setDirty();
        }
        $this->settings = $all_settings;
    }

    /**
     * Sets the setting. This will be automatically persisted to the database at the end of the request.
     * @param string $setting_name
     * @param mixed $value
     */
    public function setSetting($setting_name, $value)
    {
        $this->ensureLoadedFromDb();
        if (! $this->dirty && $this->settings[$setting_name] !== $value) {
            $this->setDirty();
        }
        $this->settings[$setting_name] = $value;
    }

    /**
     * Records this needs saving on shutdown.
     */
    protected function setDirty()
    {
        $this->dirty = true;
        add_action('shutdown', [$this, 'save']);
    }

    /**
     * Persists the settings to the database for subsequent requests.
     * You don't need to explicitly call this, as it's automatically enqueued to be done on shutdown after a call to
     * setSetting().
     * But this is here in case you'd like to save it early.
     */
    public function save()
    {
        update_option($this->optionName(), $this->settings);
    }
}
