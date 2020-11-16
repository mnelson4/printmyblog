<?php

namespace PrintMyBlog\factories;

use PrintMyBlog\entities\ProjectGeneration;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\system\Context;

class ProjectFileGeneratorFactory
{
    public function create($classname, ProjectGeneration $project_generation, Design $design)
    {
        return Context::instance()->useNew(
            $classname,
            [
                $project_generation,
                $design
            ]
        );
    }
}
