<?php
/**
 * @var $pmb_project \PrintMyBlog\orm\entities\Project
 * @var $pmb_design \PrintMyBlog\orm\entities\Design
 *
 */
?>
<div class="pmb-haller-frontpage-header">
<div class="pmb-haller-frontpage-above-header">
    <span class="pmb-haller-frontpage-date"><?php $pmb_project->echoSetting('date');?></span>
    <span class="pmb-haller-frontpage-issue"><?php printf(__('Issue %s', 'print-my-blog'), $pmb_project->getSetting('issue'));?></span>
</div>
<div class="pmb-haller-frontpage-main">
    <?php $left_side = $pmb_project->getSetting('frontpage_left_side');
    if($left_side){
        ?>
        <div class="pmb-haller-frontpage-main-sidebar pmb-haller-frontpage-area left">
            <?php echo do_shortcode($left_side);?>
        </div>
        <?php
    }
    ?>
    <div class="pmb-haller-frontpage-main-title-area pmb-haller-frontpage-area">
        <?php
            $image_url = $pmb_design->getSetting('publication_logo');
            if( $image_url ){
                ?>
                <img src ="<?php echo esc_url($image_url);?>">
                <?php
            } else {
            ?>
                <h1 class="pmb-haller-frontpage-main-title project-title"><?php echo $pmb_design->getSetting('publication_title'); ?></h1>
            <?php
            }
        if ($pmb_design->getSetting('publication_subtitle')){
            ?>
            <h2 class="pmb-haller-frontpage-main-subtitle"><?php echo $pmb_design->getSetting('publication_subtitle');?></h2>
            <?php
        }
        ?>

    </div>
    <?php $right_side = $pmb_project->getSetting('frontpage_right_side');
    if($right_side){
        ?>
        <div class="pmb-haller-frontpage-main-sidebar pmb-haller-frontpage-area right">
            <?php echo do_shortcode($right_side);?>
        </div>
        <?php
    }
    ?>
</div>
<div class="pmb-haller-frontpage-preamble">
<span class="pmb-haller-frontpage-preamble-text"><?php echo $pmb_design->getSetting('cover_preamble');?></span>
</div>