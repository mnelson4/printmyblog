<?php
/**
 * @var $pmb_project \PrintMyBlog\orm\entities\Project
 * @var $pmb_design \PrintMyBlog\orm\entities\Design
*
 */
?>
<div class="pmb-title-page">
    <span class="buurma-issue"><?php $pmb_project->echoSetting('issue');?></span>
        <h1 class="project-title"><?php $pmb_project->echoPublishedTitle(); ?></h1>

        <h2 class="project-byline"><?php $pmb_project->echoSetting('byline'); ?></h2>
        <span class="project-date"><?php $pmb_project->echoSetting('date');?></span>
    <div class="project-description"><?php $pmb_project->echoSetting('cover_preamble');?></div>
</div>