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
     * @var string
     */
    protected $current_version;
    /**
     * @var string
     */
    protected $previous_version_option_name;

    /**
     * @var string
     */
    protected $version_history_option_name;

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
        return get_option($this->previous_version_option_name, null);
    }

    public function maybeRecordVersionChange()
    {
        if ($this->previousVersion() !== $this->current_version) {
            $this->recordVersionChange();
        }
    }
    public function recordVersionChange()
    {
        update_option($this->previous_version_option_name, PMB_VERSION);
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
        $previous_versions[$this->current_version][] = date('Y-m-d H:i:s');
        update_option($this->version_history_option_name, wp_json_encode($previous_versions));
    }
}
