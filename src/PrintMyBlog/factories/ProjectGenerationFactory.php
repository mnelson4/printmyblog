<?php

namespace PrintMyBlog\factories;

use PrintMyBlog\entities\FileFormat;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\system\Context;

/**
 * Class ProjectGenerationFactory
 * @package PrintMyBlog\factories
 * Makes us some ProjectGeneration objects and makes sure their dependencies get injected.
 */
class ProjectGenerationFactory
{
    /**
     * @param Project $project
     * @param FileFormat $format
     * @return Project
     */
    public function create(Project $project, FileFormat $format)
    {
        return Context::instance()->useNew(
            'PrintMyBlog\entities\ProjectGeneration',
            [
                $project,
                $format,
            ]
        );
    }
}
