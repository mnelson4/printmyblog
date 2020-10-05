<?php
/**
 * @var $project \PrintMyBlog\orm\entities\Project
 * @var $form_url string
 * @var $project_support_front_matter bool
 * @var $project_support_back_matter bool
 * @var $front_matter_sections \PrintMyBlog\orm\entities\ProjectSection[]
 * @var $main_matter_sections \PrintMyBlog\orm\entities\ProjectSection[]
 * @var $back_matter_sections \PrintMyBlog\orm\entities\ProjectSection[]
 *
 *
 */

function pmb_template_selector($selected_template){
    $options = [
            '' => __('Default Template', 'print-my-blog'),
            'just_content' => __('Just Content', 'print-my-blog')
    ];
    $html = '<select class="pmb-template">';
    foreach($options as $value => $display_text){
        $html .= '<option value="' . esc_attr($value) . '" ' . selected($value, $selected_template, false) . '>' . $display_text . '</option>';
    }
    $html .= '</select>';
    return $html;
}
function pmb_content_item($posty_row, $max_nesting = 0){
    if($posty_row instanceof \PrintMyBlog\orm\entities\ProjectSection){
        $id = $posty_row->getPostId();
        $title = $posty_row->getPostTitle();
        $template = $posty_row->getTemplate();
        $height = $posty_row->getHeight();
        $subs = $posty_row->getCachedSubsections();
        $depth = $posty_row->getDepth();
    } else {
        $id = $posty_row->ID;
        $title = $posty_row->post_title;
        $template = null;
        $height = 0;
        $subs = [];
        $depth = 1;
    }
    ?>
    <div class="list-group-item pmb-grabbable pmb-project-item" data-id="<?php echo esc_attr($id);?>" data-height="<?php echo esc_attr($height);?>">
        <div class="pmb-project-item-header">
            <span class="pmb-project-item-title"><?php echo $title;?></span>
            <span class="pmb-project-item-template-container"><?php echo pmb_template_selector($template);?></span>
        </div>

        <div class="pmb-nested-sortable <?php echo $depth < $max_nesting ? 'pmb-sortable' : 'pmb-sortable-inactive';?> pmb-subs">
            <?php
                foreach($subs as $sub){
	                pmb_content_item($sub, $max_nesting);
                }
            ?>
        </div>

    </div>
<?php
}
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
                <div class="pmb-project-column pmb-project-choices-column">
                    <h2><?php _e('Available Content', 'print-my-blog');?></h2>
                    <div id="pmb-project-choices" class="pmb-draggable-area pmb-project-content-available pmb-scrollable-window list-group">
                        <?php
                        foreach($post_options as $post){
	                        pmb_content_item($post, 1);
                        }
                        ?>
                    </div>
                </div>
                <div class="pmb-project-column pmb-project-matters-wrapper">
                    <h2><?php esc_html_e('Project Content', 'print-my-blog');?></h2>
                    <div class="pmb-project-matters pmb-scrollable-window">
                        <?php if($project_support_front_matter){
                           ?>
                            <h2><?php esc_html_e('Front Matter', 'print-my-blog');?></h2>
                            <div id="pmb-project-front-matter" class="pmb-draggable-area pmb-project-content-chosen list-group pmb-sortable pmb-sortable-base pmb-sortable-root" data-max-nesting="1">
	                            <?php
	                            foreach($front_matter_sections as $post) {
		                            pmb_content_item( $post, 1 );
	                            }
	                            ?>
                            </div>
                        <?php
                        }?>
                        <h2>
                            <?php _e('Main Content', 'print-my-blog');?>
                            <span class="pmb-help"><?php printf(__('Project designs support %d layers of nested divisions.','print-my-blog'), $project->getLevelsAllowed());?></span>
                        </h2>
                        <div id="pmb-project-main-matter" class="pmb-draggable-area pmb-project-content-chosen list-group pmb-sortable pmb-sortable-base pmb-sortable-root" data-max-nesting="<?php echo esc_attr($project->getLevelsAllowed());?>">
                            <?php
                            foreach($sections as $post) {
                                pmb_content_item( $post, $project->getLevelsAllowed() );
                            }
                            ?>
                        </div>
	                    <?php if($project_support_back_matter){
		                    ?>
                            <h2><?php esc_html_e('Back Matter', 'print-my-blog');?></h2>
                            <div id="pmb-project-back-matter" class="pmb-draggable-area pmb-project-content-chosen list-group pmb-sortable pmb-sortable-base pmb-sortable-root" data-max-nesting="1">
			                    <?php
			                    foreach($back_matter_sections as $post) {
				                    pmb_content_item( $post, $project->getLevelsAllowed() );
			                    }
			                    ?>
                            </div>
		                    <?php
	                    }?>
                    </div>
                    <input type="hidden" name="pmb-project-front-matter-data" id="pmb-project-front-matter-data">
                    <input type="hidden" name="pmb-project-main-matter-data" id="pmb-project-main-matter-data">
                    <input type="hidden" name="pmb-project-back-matter-data" id="pmb-project-back-matter-data">
                    <input type="hidden" name="pmb-project-depth" id="pmb-project-depth">
                </div>
            </div>
        </div>
        <?php wp_nonce_field( 'pmb-project-edit' );?>
        <button class="button-primary button" id="pmb-save" name="pmb-save" value="save"><?php esc_html_e('Save', 'print-my-blog'); ?></button>
    </form>


</div>