<?php
/**
 * @var $pmb_project \PrintMyBlog\orm\entities\Project
 * @var $pmb_design \PrintMyBlog\orm\entities\Design
*
 */
?>
<div class="pmb-posts-header">
    <h1 class="project-title mayer-wide"><?php $pmb_project->echoPublishedTitle(); ?></h1>
    <h2 class="project-byline mayer-wide"><?php $pmb_project->echoSetting('byline'); ?></h2>
    <?php
    $intro = $pmb_project->renderSetting('cover_preamble');
    if ($intro) { ?>
        <p class="project-intro"><?php echo $intro; ?></p>
	<?php } ?>