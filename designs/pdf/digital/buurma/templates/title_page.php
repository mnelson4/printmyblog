<?php
/**
 * @var $pmb_project \PrintMyBlog\orm\entities\Project
 * @var $pmb_design \PrintMyBlog\orm\entities\Design
*
 */
?>
<div class="pmb-title-page">
    <span class="buurma-issue"><?php echo $pmb_project->getSetting('issue');?></span>
        <h1 class="project-title"><?php echo $pmb_project->getPublishedTitle(); ?></h1>

        <h2 class="project-byline"><?php echo $pmb_project->getSetting('byline'); ?></h2>
        <span class="project-date"><?php echo $pmb_project->getSetting('date');?></span>
    <div class="project-description"><?php echo $pmb_project->getSetting('cover_preamble');?></div>
</div>