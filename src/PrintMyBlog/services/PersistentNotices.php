<?php

namespace PrintMyBlog\services;

use PrintMyBlog\domain\DefaultPersistentNotices;
use mnelson4\AdminNotices\Notices;

/**
 * Class PersistentNotices
 * @package PrintMyBlog\services
 */
class PersistentNotices
{
    /**
     * @var Notices
     */
    protected $persistent_notices;
    /**
     * @var DefaultPersistentNotices
     */
    protected $default_persistent_notices;

    /**
     * Called by Context.
     * @param Notices $persistent_notices
     * @param DefaultPersistentNotices $default_persistent_notices
     */
    public function inject(
        Notices $persistent_notices,
        DefaultPersistentNotices $default_persistent_notices
    ) {
        $this->persistent_notices = $persistent_notices;
        $this->default_persistent_notices = $default_persistent_notices;
    }

    /**
     * Gets and shows the persistent admin notices
     */
    public function register()
    {
        foreach ($this->default_persistent_notices->getNotices() as $notice) {
            $this->persistent_notices->add_notice($notice);
        }
        $this->persistent_notices->boot();
    }
}
