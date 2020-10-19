<?php
use PrintMyBlog\controllers\Admin;
/**
 * @var $form_url string
 * @var $project PrintMyBlog\orm\entities\Project
 * @var $formats PrintMyBlog\entities\FileFormat[]
 * @var $project_content_url string
 * @var $project_metadata_url string
 * @var $project_generate_url string
 */
// Editing project

// Textbox to edit title

// Formats, and the design of each

// Metadata link

// Content

// Generate link
pmb_render_template(
        'partials/project_header.php',
        [
                'project' => $project,
                'page_title' => __('Edit Project', 'print-my-blog'),
                'show_back' => false
        ]
);
?>
    <form id="pmb-project-form" method="POST" action="<?php echo $form_url;?>">
        <?php wp_nonce_field( 'pmb-project-edit' );?>
        <div id="pmb-project-main" class="pmb-project-main form-group">
            <label for="pmb-project-title"><?php esc_html_e('Name', 'event_espresso'); ?></label><span class="pmb-comment description" id="pmb-project-title-saved-status"></span>
            <input type="text" id="pmb-project-title" class="form-control" name="pmb_title" value="<?php echo esc_attr($project->getWpPost()->post_title);?>" autofocus placeholder="<?php echo esc_attr(__('My Great Project...', 'print-my-blog'));?>">
        </div>
        <h2><?php esc_html_e('Project Format(s)', 'print-my-blog');?></h2>
        <p class="pmb-comment description"><?php esc_html_e('File formats you intend to generate for this project.', 'print-my-blog');?></p>
        <table class="form-table">
            <tbody>
            <tr>
                <td colspan="2" class="pmb-formats-row">
            <?php foreach($formats as $format){
                $design = $project->getDesignFor($format);
	            $customize_url = add_query_arg(
		            [
			            'ID' => $project->getWpPost()->ID,
			            'action' => Admin::SLUG_ACTION_EDIT_PROJECT,
			            'subaction' => Admin::SLUG_SUBACTION_PROJECT_CUSTOMIZE_DESIGN,
                        'format' => $format->slug()
		            ],
		            admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
	            );
	            $change_design_url = add_query_arg(
		            [
			            'ID' => $project->getWpPost()->ID,
			            'action' => Admin::SLUG_ACTION_EDIT_PROJECT,
			            'subaction' => Admin::SLUG_SUBACTION_PROJECT_CHANGE_DESIGN,
                        'format' => $format->slug()
		            ],
		            admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
	            );
                ?>
                <div class="pmb-format">
                        <label>
                            <input type="checkbox" name="pmb_format[]" value="<?php echo $format->slug();?>" <?php checked($project->isFormatSelected($format->slug()));?>>
		                    <?php echo $format->title();?>
                        </label>
                    <div class="pmb-chosen-design-for-format">
                        <?php echo pmb_design_preview($design);?>
                        <div class="pmb-actions pmb-format-actions">
                            <a class="button" href="<?php echo esc_attr($customize_url);?>"><?php esc_html_e('Customize', 'print-my-blog');?></a>
                            <a class="button" href="<?php echo esc_attr($change_design_url);?>"><?php esc_html_e('Change','print-my-blog');?></a>
                        </div>
                        <div class="pmb-format-design-title">
	                        <?php printf(esc_html('Design: %s', 'print-my-blog'), '<b>' . $design->getWpPost()->post_title . '</b>');?>
                        </div>


                    </div>
                </div>
            <?php }?>
                </td>
            </tr>
            </tbody>
        </table>

        <h2><?php esc_html_e('Project Contents', 'print-my-blog');?></h2>
        <p class="pmb-comment description"><?php esc_html_e('The chosen posts and content for this project.', 'print-my-blog');?></p>
        <a href="<?php echo esc_attr($project_content_url);?>" class="button"><?php esc_html_e('Edit Contents', 'print-my-blog');?></a>

        <h2><?php esc_html_e('Project Metadata', 'print-my-blog');?></h2>
        <p class="pmb-comment description"><?php esc_html_e('Miscellaneous data used for your chosen file formats and designs.', 'print-my-blog');?></p>
        <a href="<?php echo esc_attr($project_metadata_url);?>" class="button"><?php esc_html_e('Edit Metadata', 'print-my-blog');?></a>
        <br/><br/>
        <a href="<?php echo esc_attr($project_generate_url);?>" class="button button-primary"><?php esc_html_e('Generate Project Files', 'print-my-blog');?></a>
    </form>
<?php pmb_render_template('partials/project_footer.php');