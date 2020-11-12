<?php

use PrintMyBlog\orm\entities\Project;

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

function pmb_template_selector($selected_template, Project $project){
    $options = $project->getSectionTemplateOptions();
    $html = '<select class="pmb-template">';
    foreach($options as $value => $display_text){
        $html .= '<option value="' . esc_attr($value) . '" ' . selected($value, $selected_template, false) . '>' . $display_text . '</option>';
    }
    $html .= '</select>';
    return $html;
}

/**
 * @param $posty_row
 * @param Project $project
 * @param int $max_nesting
 */
function pmb_content_item($posty_row, Project $project, $max_nesting = 0){
    if($posty_row instanceof \PrintMyBlog\orm\entities\ProjectSection){
        $id = $posty_row->getPostId();
        $title = $posty_row->getPostTitle();
        $template = $posty_row->getTemplate();
        $height = $posty_row->getHeight();
        $subs = $posty_row->getCachedSubsections();
        $depth = $posty_row->getDepth();
        $edit_url = get_edit_post_link($posty_row->getPostId());
        $view_url = get_permalink($posty_row->getPostId());
    } else {
        $id = $posty_row->ID;
        $title = $posty_row->post_title;
        $template = null;
        $height = 0;
        $subs = [];
        $depth = 1;
        $edit_url = get_edit_post_link($posty_row->ID);
        $view_url = get_permalink($posty_row->ID);
    }
    ?>
    <div class="list-group-item pmb-project-item" data-id="<?php echo esc_attr($id);?>" data-height="<?php echo esc_attr($height);?>">
        <div class="pmb-project-item-header" title="<?php
        echo esc_attr(
	        sprintf(
		        __('Drag "%s" into your project', 'print-my-blog'),
		        $title
	        )
        );
        ?>">
            <span class="pmb-grabbable pmb-project-item-title">
                <span
                class="dashicons dashicons-menu"></span>
                <span class="pmb-project-item-title-text"><?php echo $title;?></span>
            </span>
            <a
                    href="<?php echo esc_attr($view_url);?>"
                    title="<?php
                    echo esc_attr(
                            sprintf(
                                    __('View "%s"', 'print-my-blog'),
                                    $title
                            )
                    );
                    ?>"
                    target="_blank"><span class="dashicons dashicons-visibility pmb-icon"></span></a>
            <a
                    href="<?php echo esc_attr($edit_url);?>"
                    title="<?php
                    echo esc_attr(
	                    sprintf(
		                    __('Edit "%s"', 'print-my-blog'),
		                    $title
	                    )
                    );
                    ?>"
                    target="_blank"><span class="dashicons dashicons-edit pmb-icon"></span></a>
            <a
                    class="pmb-remove-item"
                    title="<?php
                    echo esc_attr(
	                    sprintf(
		                    __('Remove "%s" from project', 'print-my-blog'),
		                    $title
	                    )
                    );
                    ?>"
            ><span class="dashicons dashicons-no-alt pmb-icon"></span></a>
            <a
                    class="pmb-add-item"
                    title="<?php
			        echo esc_attr(
				        sprintf(
					        __('Add "%s" to project', 'print-my-blog'),
					        $title
				        )
			        );
			        ?>"
            ><span class="dashicons dashicons-plus-alt2 pmb-icon"></span></a>
            <span class="pmb-project-item-template-container"><?php echo pmb_template_selector($template, $project);?></span>
        </div>

        <div class="pmb-nested-sortable <?php echo $depth < $max_nesting ? 'pmb-sortable' : 'pmb-sortable-inactive';?> pmb-subs">
            <?php
                foreach($subs as $sub){
	                pmb_content_item($sub, $project, $max_nesting);
                }
            ?>
        </div>

    </div>
<?php
}
    function pmb_drag_here(){
    // nm don't do anythign for now
    return;
    ?>
    <div class="pmb-help pmb-no-sort pmb-drag-here">
        <span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e('Drag content here', 'print-my-blog');?>
    </div>
    <?php
}
pmb_render_template(
	'partials/project_header.php',
	[
		'project' => $project,
		'page_title' => __('Edit Project Content', 'print-my-blog'),
		'current_step' => $current_step,
		'steps_to_urls' => $steps_to_urls
	]
);
?>
    <form id="pmb-project-form" method="POST" action="<?php echo $form_url;?>">
        <div id="pmb-project-layout" class="pmb-project-layout">
            <div class="pmb-project-layout-inner">
                <div class="pmb-project-column pmb-project-choices-column">
                    <h2><?php _e('Available Content', 'print-my-blog');?></h2>
                    <div id="pmb-project-choices" class="pmb-draggable-area pmb-project-content-available pmb-scrollable-window list-group">
                        <?php
                        foreach($post_options as $post){
	                        pmb_content_item($post, $project, 0);
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
                            <div id="pmb-project-front-matter" class="pmb-draggable-area pmb-project-content-chosen list-group pmb-sortable pmb-sortable-base pmb-sortable-root" data-max-nesting="0">
	                            <?php
	                            foreach($front_matter_sections as $post) {
		                            pmb_content_item( $post, $project, 0 );
	                            }
	                            pmb_drag_here();
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
                                pmb_content_item( $post, $project, $project->getLevelsAllowed() );
                            }
                            pmb_drag_here();
                            ?>
                        </div>
	                    <?php if($project_support_back_matter){
		                    ?>
                            <h2><?php esc_html_e('Back Matter', 'print-my-blog');?></h2>
                            <div id="pmb-project-back-matter" class="pmb-draggable-area pmb-project-content-chosen list-group pmb-sortable pmb-sortable-base pmb-sortable-root" data-max-nesting="0">
			                    <?php
			                    foreach($back_matter_sections as $post) {
				                    pmb_content_item( $post, $project, 0 );
			                    }
			                    pmb_drag_here();
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

<?php pmb_render_template('partials/project_footer.php');