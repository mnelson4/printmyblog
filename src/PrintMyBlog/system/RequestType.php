<?php

namespace PrintMyBlog\system;

/**
 * Class RequestType
 *
 * Knows what type of request this is.
 *
 * Managed by \PrintMyBlog\system\Context.
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

    public function inject(VersionHistory $version_history){
        $this->version_history = $version_history;
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
        if(get_option('pmb_activation')){
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