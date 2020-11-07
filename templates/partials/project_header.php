<?php

use PrintMyBlog\orm\entities\Project;
/**
 * @var Project $project
 * @var bool $show_back
 * @var string $current_step
 */
if($project instanceof Project){
    $steps = $project->getProgress()->getSteps();
    $progress = $project->getProgress()->getStepProgress();
    foreach($steps as $slug => $display_text){
        $completed = $progress[$slug] ? true : false;
        $current = $current_step === $slug ? true : false;
        ?>
        <span class="<?php echo esc_attr($completed ? 'pmb-completed' : 'pmb-incomplete');?> <?php echo esc_attr($current ? 'pmb-current-step' : '');?>"><a href=""><?php echo $display_text;?></a></span>
        <?php
    }
}
?>
<div class="wrap nosubsub">
    <h1><?php echo $page_title;?></h1>
    <?php //div will be closed by project_footer.phps

