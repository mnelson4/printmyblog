<?php


namespace PrintMyBlog\orm\managers;


use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\system\Context;
use Twine\orm\managers\PostWrapperManager;
use WP_Post;

class ProjectManager extends PostWrapperManager {
	protected $class_to_instantiate = 'PrintMyBlog\orm\entities\Project';
	protected $cap_slug = 'pmb_project';
}