<?php

namespace PrintMyBlog\orm\managers;

use PrintMyBlog\db\TableManager;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\system\Context;
use Twine\orm\managers\PostWrapperManager;
use WP_Post;

/**
 * Class ProjectManager
 * @package PrintMyBlog\orm\managers
 */
class ProjectManager extends PostWrapperManager
{
    /**
     * @var string
     */
    protected $class_to_instantiate = 'PrintMyBlog\orm\entities\Project';

    /**
     * @var string
     */
    protected $cap_slug = 'pmb_project';
}
