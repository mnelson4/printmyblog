<?php

namespace PrintMyBlog\system;

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
    const PREVIOUS_VERSION_OPTION_NAME = 'pmb_previous_version';
    const VERSION_HISTORY_OPTION_NAME = 'pmb_version_history';

    /**
     * Gets the version that was active during the last request
     * @return string|null
     */
    public function previousVersion(){
        return get_option(self::PREVIOUS_VERSION_OPTION_NAME,null);
    }

    public function maybeRecordVersionChange(){
        if($this->previousVersion() !== PMB_VERSION){
            $this->recordVersionChange();
        }
    }
    public function recordVersionChange(){
        update_option(self::PREVIOUS_VERSION_OPTION_NAME,PMB_VERSION);
        $previous_versions = get_option(self::VERSION_HISTORY_OPTION_NAME, []);
        if(is_string($previous_versions)){
            $previous_versions = json_decode($previous_versions,true);
        }
        if(empty($previous_versions)){
            $previous_versions = [];
        }
        if(! isset($previous_versions[PMB_VERSION])){
            $previous_versions[PMB_VERSION] = [];
        }
        $previous_versions[PMB_VERSION][] = date('Y-m-d H:i:s');
        update_option(self::VERSION_HISTORY_OPTION_NAME, wp_json_encode($previous_versions));
    }
}