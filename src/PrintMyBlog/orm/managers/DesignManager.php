<?php

namespace PrintMyBlog\orm\managers;

use Twine\orm\managers\PostWrapperManager;

class DesignManager extends PostWrapperManager
{
    protected $class_to_instantiate = 'PrintMyBlog\orm\entities\Design';
    protected $cap_slug = 'pmb_design';
}
