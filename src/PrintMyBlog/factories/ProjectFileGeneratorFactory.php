<?php

namespace PrintMyBlog\factories;

use PrintMyBlog\entities\ProjectGeneration;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\services\generators\ProjectFileGeneratorBase;
use PrintMyBlog\system\Context;

/**
 * Class ProjectFileGeneratorFactory
 * @package PrintMyBlog\factories
 */
class ProjectFileGeneratorFactory
{
    /**
     * @param string $classname
     * @param ProjectGeneration $project_generation
     * @param Design|null $design
     * @return ProjectFileGeneratorBase
     */
    public function create($classname, ProjectGeneration $project_generation, Design $design = null)
    {
        return Context::instance()->useNew(
            $classname,
            [
                $project_generation,
                $design,
            ]
        );
    }
}
