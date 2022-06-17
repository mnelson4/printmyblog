<?php

namespace PrintMyBlog\orm\managers;

use Twine\orm\managers\PostWrapperManager;

/**
 * Class DesignManager
 * @package PrintMyBlog\orm\managers
 */
class DesignManager extends PostWrapperManager
{
    /**
     * @var string
     */
    protected $class_to_instantiate = 'PrintMyBlog\orm\entities\Design';

    /**
     * @var string
     */
    protected $cap_slug = 'pmb_design';
}
