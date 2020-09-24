<?php
/**
 * @var $project \PrintMyBlog\orm\entities\Project
 * @var $form_url string
 *
 */

function pmb_template_selector($selected_template){
    $options = [
            'header' => __('Standard', 'print-my-blog'),
            'no_header' => __('No Header', 'print-my-blog')
    ];
    $html = '<select class="pmb-template">';
    foreach($options as $value => $display_text){
        $html .= '<option value="' . esc_attr($value) . '" ' . selected($value, $selected_template, false) . '>' . $display_text . '</option>';
    }
    $html .= '</select>';
    return $html;
}
function pmb_content_item($posty_row, $init_subs){
    ?>
    <div class="list-group-item pmb-grabbable pmb-project-item" data-id="<?php echo esc_attr($posty_row->ID);?>">
        <div class="pmb-project-item-header">
            <span class="pmb-project-item-title"><?php echo $posty_row->post_title;?></span>
            <span class="pmb-project-item-template-container"><?php echo pmb_template_selector(isset($posty_row->template) ? $posty_row->template : null);?></span>
        </div>

        <div class="pmb-nested-sortable <?php echo $init_subs ? 'pmb-sortable' : 'pmb-sortable-inactive';?> pmb-subs">
            <?php if(isset($posty_row->subs)){
                foreach($posty_row->subs as $sub){
	                pmb_content_item($sub, $init_subs);
                }
            }?>
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
                <div class="pmb-project-column">
                    <h2><?php _e('Available Content', 'print-my-blog');?></h2>
                    <div id="pmb-project-choices" class="pmb-draggable-area pmb-project-content-available pmb-selection-list list-group">
                        <?php
                        foreach($post_options as $post){
	                        pmb_content_item($post, false);
                        }
                        ?>
                    </div>
                </div>
                <div class="pmb-project-column">
                    <h2><?php _e('Project Content', 'print-my-blog');?></h2>
                    <div id="pmb-project-sections" class="pmb-draggable-area pmb-project-content-chosen pmb-selection-list list-group pmb-sortable pmb-sortable-base">
                        <?php
                        foreach($parts as $post) {
	                        pmb_content_item( $post, true );
                        }
                        ?>
                    </div>
                    <input type="hidden" name="pmb-project-sections-data" id="pmb-project-sections-data">
                </div>
            </div>
        </div>
        <?php wp_nonce_field( 'pmb-project-edit' );?>
        <button class="button-primary button" id="pmb-save" name="pmb-save" value="save"><?php esc_html_e('Save', 'print-my-blog'); ?></button>
    </form>


</div>