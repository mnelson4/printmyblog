<?php
/**
 * @var $project \PrintMyBlog\orm\entities\Project
 * @var $form_url string
 *
 */
?>

<div class="wrap nosubsub">

<!--        <h2 class="nav-tab-wrapper">-->
<!--            <a href="#" class="nav-tab">Display Options</a>-->
<!--            <a href="#" class="nav-tab">Social Options</a>-->
<!--        </h2>-->
        <!-- clunky tabs: https://code.tutsplus.com/tutorials/the-wordpress-settings-api-part-5-tabbed-navigation-for-settings--wp-24971 -->
        <!-- or use https://gcostudios.com/how-to-add-jquery-ui-tabs-into-wordpress/ for jquery ui tabs -->
    <h1><?php esc_html_e('Print My Blog - Edit Project Content', 'event_espresso'); ?></h1>
    <h2><?php esc_html($project->getWpPost()->post_title);?></h2>
    <form id="pmb-project-form" method="POST" action="<?php echo $form_url;?>">
        <div id="pmb-project-layout" class="pmb-project-layout">
            <div class="pmb-project-layout-inner">
                <div class="pmb-project-column">
                    <h2><?php _e('Available Content', 'print-my-blog');?></h2>
                    <ul id="pmb-project-choices" class="pmb-draggable-area pmb-selection-list list-group">
                        <?php foreach($post_options as $post){
                            ?><li class="list-group-item pmb-grabbable pmb-project-item" data-id="<?php echo esc_attr($post->ID);?>"><?php echo $post->post_title;?></li><?php
                        }
                        ?>
                    </ul>
                </div>
                <div class="pmb-project-column">
                    <h2><?php _e('Project Content', 'print-my-blog');?></h2>
                    <ul id="pmb-project-sections" class="pmb-draggable-area pmb-selection-list list-group">
                        <?php foreach($parts as $post){
                        ?><li class="list-group-item pmb-grabbable pmb-project-item" data-id="<?php echo esc_attr($post->ID);?>"><?php echo $post->post_title;?></li><?php
                        }
                        ?>
                    </ul>
                    <input type="hidden" name="pmb-project-sections-data" id="pmb-project-sections-data">
                </div>
            </div>
        </div>
        <?php wp_nonce_field( 'pmb-project-edit' );?>
        <button class="button-primary button" id="pmb-save" name="pmb-save" value="save"><?php esc_html_e('Save', 'print-my-blog'); ?></button>
    </form>


</div>