<?php
/**
 * @var $pmb_project \PrintMyBlog\orm\entities\Project
 * @var $pmb_design \PrintMyBlog\orm\entities\Design
*
 */
?>
<div class="pmb-title-page">
    <span class="buurma-issue"><?php echo $pmb_project->getPmbMeta('issue');?></span>
        <h1 class="project-title"><?php echo $pmb_project->getPublishedTitle(); ?></h1>

        <h2 class="project-byline"><?php echo $pmb_project->getPmbMeta('byline'); ?></h2>
        <span class="project-date"><?php echo $pmb_project->getPmbMeta('date');?></span>
    <div class="project-description"><?php echo $pmb_project->getPmbMeta('cover_preamble');?></div>
</div>