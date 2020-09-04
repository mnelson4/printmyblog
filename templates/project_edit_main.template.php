<?php
/**
 * @var $form_url string
 * @var $project \PrintMyBlog\orm\Project
 */
// Editing project

// Textbox to edit title

// Formats, and the design of each

// Metadata link

// Content

// Generate link
?>
<div class="wrap nosubsub">
    <h1><?php esc_html_e('Print My Blog - Edit Project', 'event_espresso'); ?></h1>
    <form id="pmb-project-form" method="POST" action="<?php echo $form_url;?>">
        <?php wp_nonce_field( 'pmb-project-edit' );?>
        <div id="pmb-project-main" class="pmb-project-main form-group">
            <label for="pmb-project-title"><?php esc_html_e('Name', 'event_espresso'); ?></label><span class="pmb-comment description" id="pmb-project-title-saved-status"></span>
            <input type="text" id="pmb-project-title" class="form-control" name="pmb_title" value="<?php echo esc_attr($project->getWpPost()->post_title);?>">
        </div>
        <h2><?php esc_html_e('Project Format(s)', 'print-my-blog');?></h2>
        <p class="pmb-comment description"><?php esc_html_e('File formats you intend to generate for this project.', 'print-my-blog');?></p>
        <table class="form-table">
            <tbody>
            <?php foreach($formats as $format){?>
            <tr>
                <th scope="row">
                    <label>
                        <input type="checkbox" name="pmb_format[]" value="<?php echo $format->slug();?>" <?php checked($project->isFormatSelected($format->slug()));?>>
	                    <?php echo $format->title();?>
                    </label>
                </th>
                <td>
                    Design:x Edit | Choose Another
                </td>
            </tr>
            <?php }?>
            </tbody>
        </table>
    </form>
</div>