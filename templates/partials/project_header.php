<?php

use PrintMyBlog\controllers\Admin;
use PrintMyBlog\orm\entities\Project;
/**
 * @var Project|null $project
 * @var bool $show_back
 * @var string $current_step
 * @var array $steps_to_urls
 */
pmb_render_template('partials/pro_header.php');
?>
<div id="pmb-wizard-progress" class="pmb-progress">
<?php

if($project instanceof Project){
    $steps = $project->getProgress()->getSteps();
    $progress = $project->getProgress()->getStepProgress();
    $next_step = $project->getProgress()->getNextStep();
    foreach($steps as $slug => $display_text){
        $completed = $progress[$slug] ? true : false;
        $current = $current_step === $slug ? true : false;
        $next = $next_step === $slug ? true : false;
        $accessible = $completed || $next;
        ?><span class="pmb-step
            <?php echo esc_attr($completed ? 'pmb-completed' : 'pmb-incomplete');?>
            <?php echo esc_attr($current ? 'pmb-current-step' : '');?>
            <?php echo esc_attr($next ? 'pmb-next-step' : '');?>
            <?php echo esc_attr($accessible ? 'pmb-accessible-step' : 'pmb-inaccessible-step');?>
            "><?php if (($completed || $next) && ! $current){
            ?>
        <a href="<?php echo esc_attr($steps_to_urls[$slug]);?>">
        <?php }
        echo $display_text;
        if ($completed || $next){
            ?></a><?php
        }?></span><?php
    }
}
?>
</div>
<div class="wrap nosubsub">
    <h1><?php echo $page_title;?></h1>
    <?php if ($project){?><h2 class="pmb-project-title"><?php echo $project->getWpPost()->post_title;?></h2>
    <?php }?>
    <?php
    //div will be closed by project_footer.phps

