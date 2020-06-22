<?php

namespace Twine\system;

/**
 * Class RequestType
 *
 * Description
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
class RequestType
{
    const REQUEST_TYPE_NEW_INSTALL = 'new_install';
    const REQUEST_TYPE_UPDATE = 'update';
    const REQUEST_TYPE_NORMAL = 'normal';
    const REQUEST_TYPE_REACTIVATION = 'reactivation';

    /**
     * @var VersionHistory
     */
    protected $version_history;

    /**
     * @var string|null name of the WP option that's set upon activation.
     */
    protected $activation_option_name;
    public function inject(VersionHistory $version_history, $activation_option_name = null){
        $this->version_history = $version_history;
        $this->activation_option_name = $activation_option_name;

    }


    /**
     * @var string
     */
    protected $request_type;


    /**
     * @return bool
     */
    public function shouldCheckDb(){
        return in_array(
            $this->getRequestType(),
            [
                self::REQUEST_TYPE_NEW_INSTALL,
                self::REQUEST_TYPE_UPDATE,
                self::REQUEST_TYPE_REACTIVATION
            ]
        );
    }


    /**
     * @return bool
     */
    public function isBrandNewInstall(){
        return $this->getRequestType() === self::REQUEST_TYPE_NEW_INSTALL;
    }

    /**
     * @return string
     */
    private function detectRequestType(){
        $previous_version = $this->version_history->previousVersion();
        if ($previous_version === null) {
            return self::REQUEST_TYPE_NEW_INSTALL;
        }
        if ($previous_version !== PMB_VERSION){
            return self::REQUEST_TYPE_UPDATE;
        }
        if(isset($this->activation_option_name) && get_option($this->activation_option_name)){
            return self::REQUEST_TYPE_REACTIVATION;
        }
        return self::REQUEST_TYPE_NORMAL;
    }


    /**
     * @since $VID:$
     * @return string
     */
    public function getRequestType(){
        if($this->request_type === null) {
            $this->request_type = $this->detectRequestType();
        }
        return $this->request_type;
    }
}