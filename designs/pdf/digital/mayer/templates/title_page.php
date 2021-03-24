<?php
/**
 * @var $pmb_project \PrintMyBlog\orm\entities\Project
 * @var $pmb_design \PrintMyBlog\orm\entities\Design
*
 */
?>
<div class="pmb-posts-header">
    <h1 class="project-title mayer-wide"><?php echo $pmb_project->getPublishedTitle(); ?></h1>
    <?php
    $intro = $pmb_project->getSetting('cover_preamble');
    if ($intro) { ?>
        <p class="project-intro"><?php echo $intro; ?></p>
	<?php } ?>