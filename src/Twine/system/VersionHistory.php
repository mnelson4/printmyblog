<?php

namespace Twine\system;

/**
 * Class VersionRecorder
 *
 * Keeps track of what version was last active, and the entire history of versions activated on this site.
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
class VersionHistory
{
    /**
     * Begins as 'false' which indicates its not initialized yet. Once initialized it will be a string
     * or null (to indicate brand new install)
     * @var string
     */
    protected $previous_version = false;

    /**
     * Version in code
     * @var string
     */
    protected $current_version;

    /**
     * Name of WP option containing previous version
     * @var string
     */
    protected $previous_version_option_name;

    /**
     * @var string
     */
    protected $version_history_option_name;

    /**
     * @param string $current_version
     * @param string $previous_version_option_name
     * @param string $version_history_option_name
     */
    public function inject(
        $current_version,
        $previous_version_option_name,
        $version_history_option_name
    ) {
        $this->current_version = $current_version;
        $this->previous_version_option_name = $previous_version_option_name;
        $this->version_history_option_name = $version_history_option_name;
    }
    /**
     * Gets the version that was active during the last request
     * @return string|null
     */
    public function previousVersion()
    {
        if ($this->previous_version === false) {
            $this->previous_version = get_option($this->previous_version_option_name, null);
        }
        return $this->previous_version;
    }

    /**
     * Records version change if it's changed
     */
    public function maybeRecordVersionChange()
    {
        if ($this->previousVersion() !== $this->current_version) {
            $this->recordVersionChange();
        }
    }

    /**
     * Records that the version number has changed in the DB
     */
    public function recordVersionChange()
    {
        update_option($this->previous_version_option_name, $this->current_version);
        $previous_versions = get_option($this->version_history_option_name, []);
        if (is_string($previous_versions)) {
            $previous_versions = json_decode($previous_versions, true);
        }
        if (empty($previous_versions)) {
            $previous_versions = [];
        }
        if (! isset($previous_versions[$this->current_version])) {
            $previous_versions[$this->current_version] = [];
        }
        $previous_versions[$this->current_version][] = gmdate('Y-m-d H:i:s');
        update_option($this->version_history_option_name, wp_json_encode($previous_versions));
    }

    /**
     * Gets the version on the current request from the PHP code
     * @return string
     */
    public function currentVersion()
    {
        return $this->current_version;
    }
}
