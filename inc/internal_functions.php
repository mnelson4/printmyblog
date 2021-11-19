<?php
/**
 * Functions used internally by Print My Blog that other devs probably won't need.
 */

use PrintMyBlog\controllers\Admin;
use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\orm\entities\ProjectSection;
use PrintMyBlog\system\Context;
use PrintMyBlog\system\CustomPostTypes;

/**
 * Adds a help icon with text
 * @param string $explanation
 * @return string
 */
function pmb_hover_help($explanation = ''){
    return '<span data-help="' . esc_attr($explanation) . '" class="dashicons dashicons-info pmb-hover"></span>';
}

/**
 * Adds an icon which has explanatory text when hovered over.
 * @param string $explanation
 * @param string $extra_css_classes
 * @return string HTML
 */
function pmb_hover_tip($explanation = '', $extra_css_classes = ''){
    return '<span data-help="' . esc_attr($explanation) . '" class="dashicons dashicons-superhero pmb-hover ' . $extra_css_classes . '"></span>';
}
/**
 * Returns a string that says this feature only works with Pro Print Service (not supported by browsers).
 * @return string
 */
function pmb_pro_print_service_only($explanation = ''){
    if(pmb_fs()->is_plan__premium_only('hobbyist')){
        return '';
    } else {
        $hover_text = '<b>' . sprintf(
                __('Only works with %1$sPro Print Service%2$s', 'print-my-blog'),
                '<a href="' . pmb_fs()->get_upgrade_url() . '">',
                '</a>'
            ) . '</b>';
        if ($explanation) {
            $hover_text .= "<br>" . $explanation;
        }
        return pmb_hover_tip($hover_text, 'pmb-pro-only');
    }
}

/**
 * Echoes out that this feature only works with pro print service (not supported by browsers)
 */
function pmb_pro_print_service_only_e($explanation = ''){
    echo pmb_pro_print_service_only($explanation);
}

/**
 * Returns a string that says this feature works best with Print My Blog Pro.
 * @return string
 */
function pmb_pro_print_service_best($explanation = ''){
    if(pmb_fs()->is_plan__premium_only('hobbyist')){
        return '';
    } else {
        $hover_text = '<b>' . sprintf(
                __('Works better with %1$sPro Print Service%2$s', 'print-my-blog'),
                '<a href="' . pmb_fs()->get_upgrade_url() . '">',
                '</a>'
            ) . '</b>';
        if ($explanation) {
            $hover_text .= "<br>" . $explanation;
        }
        return pmb_hover_tip($hover_text, 'pmb-pro-best');
    }
}

/**
 * Echoes out this feature works best with pro print service.
 */
function pmb_pro_print_service_best_e($explanation = ''){
    echo pmb_pro_print_service_best($explanation);
}

/**
 * Echoes out that there's a better feature in Pro Print
 * @param string $explanation
 */
function pmb_pro_better_e($explanation = ''){
    echo pmb_pro_better($explanation);
}
/**
 * To tell folks about features of Pro Print not available for Quick print
 * @param string $explanation
 * @return string
 */
function pmb_pro_better($explanation = ''){
    $hover_text = '<b>' . sprintf(
            __('More Advanced Features in %1$sPro Print%2$s', 'print-my-blog'),
        '<a href="'. admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH) . '">',
        '</a>'
    ) . '</b>';
    if($explanation){
        $hover_text .= "<br>" . $explanation;
    }
    return pmb_hover_tip($hover_text);
}


/**
 * Maps
 * @param \PrintMyBlog\orm\entities\ProjectSection $section
 *
 * @return string
 */
function pmb_map_section_to_division(ProjectSection $section){
	if($section->getPlacement() === DesignTemplate::IMPLIED_DIVISION_FRONT_MATTER){
		return 'front_matter_article';
	}
	if($section->getPlacement() === DesignTemplate::IMPLIED_DIVISION_BACK_MATTER){
		return 'back_matter_article';
	}
	return apply_filters(
		'pmb_map_section_to_division',
		pmb_map_section_height_to_division($section->getHeight()),
		$section
	);
}

function pmb_map_section_height_to_division($height){
	switch($height){
		case 1:
			$division_name = 'part';
			break;
		case 2:
			$division_name = 'volume';
			break;
		case 3:
			$division_name = 'anthology';
			break;
		case 0:
		default:
			$division_name = 'article';
	}
	return $division_name;
}

/**
 * Renders a PMB template in the template directory
 * @param $template_name
 * @param array $context
 */
function pmb_render_template($template_name, $context=[]){
    echo pmb_get_contents(PMB_TEMPLATES_DIR . $template_name, $context);
}

/**
 * Renders any file
 * @param $filename
 * @param array $context
 */
function pmb_get_contents($filename, $context=[]){
    extract($context);
    ob_start();
    require($filename);
    return ob_get_clean();
}

function pmb_design_preview(\PrintMyBlog\orm\entities\Design $design){
	return pmb_render_template(
		'partials/design_preview.php',
		[
			'design' => $design
		]
	);
}

/**
 * Gets a post item div HTML for the sortable.js sorting area.
 * @param ProjectSection|WP_Post $posty_row
 * @param Project $project
 * @param int $max_nesting
 */
function pmb_content_item($posty_row, Project $project, $max_nesting = null){
    if($posty_row instanceof \PrintMyBlog\orm\entities\ProjectSection){
        $id = $posty_row->getPostId();
        $title = $posty_row->getPostTitle();
        $post_type = get_post_type_object(get_post_type($posty_row->getPostId()));
        $template = $posty_row->getTemplate();
        $height = $posty_row->getHeight();
        $subs = $posty_row->getCachedSubsections();
        $depth = $posty_row->getDepth();
    } else {
        $id = $posty_row->ID;
        $title = $posty_row->post_title;
        $post_type = get_post_type_object($posty_row->post_type);
        $template = null;
        $height = 0;
        $subs = [];
        $depth = 1;

    }
    $edit_url = get_edit_post_link($id);
    $view_url = get_permalink($id);
    $duplicate_url = wp_nonce_url(
        add_query_arg(
            [
                'action' => Admin::SLUG_ACTION_DUPLICATE_PRINT_MATERIAL,
                'ID' => $id
            ],
            admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
        ),
        Admin::SLUG_ACTION_DUPLICATE_PRINT_MATERIAL
    );
    // if the post type is no longer registered, the plugin that added it probably got removed, so hide this item.
    if(! $post_type){
        return;
    }
    if($max_nesting === null){
        $max_nesting = $project->getLevelsAllowed();
    }
    ?>
    <div class="list-group-item pmb-project-item" tabindex="0" id="pmb-content-item-<?php echo esc_attr($id);?>" data-id="<?php echo esc_attr($id);?>">
        <div class="pmb-project-item-header" title="<?php
        echo esc_attr(
            sprintf(
                __('Drag the %s "%s" into your project', 'print-my-blog'),
                $post_type->labels->singular_name,
                $title
            )
        );
        ?>">
            <div class="pmb-grabbable pmb-project-item-title">
                <?php echo pmb_post_type_icon_html($post_type);?>
                <span class="pmb-project-item-title-text"><?php echo $title;?></span>
            </div>

            <div class="pmb-project-item-options">
                <div class="pmb-project-item-options-inner">
                    <div class="pmb-project-item-options-buttons no-drag">
                        <?php
                        // only show the viewing button if it will work
                        if($post_type->public){
                            ?>
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
                                    target="_blank"><span class="dashicons dashicons-visibility pmb-icon pmb-clickable"></span></a>
                            <?php
                        }
                        ?>
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
                            target="_blank"><span class="dashicons dashicons-edit pmb-icon pmb-clickable"></span></a>
                        <?php
                        if(true || pmb_fs()->is_plan__premium_only('founding_members')){
                            $label = '';
                            if($post_type->name !== CustomPostTypes::CONTENT){
                                $print_material = null;
                                $print_materials = Context::instance()->reuse('Twine\orm\managers\PostWrapperManager')->getByPostMeta('_pmb_original_post', (string)$id, 1);
                                if($print_materials){
                                    $print_material = reset($print_materials);
                                }
                                if($print_material){
                                    // translators: 1: post title
                                    $label = sprintf(__('Replace "%s" with the existing Print Material for customizing.', 'print-my-blog'), $print_material->getWpPost()->post_title);
                                } else {
                                    $label = sprintf(__('Replace "%s" with a New Print Material for customizing', 'print-my-blog'), $title);
                                }
                            }
                            if($label){
                                ?><a
                                title="<?php
                                echo esc_attr($label);
                                ?>"
                                data-id="<?php echo esc_attr($id);?>" class="pmb-duplicate-post-button" tabindex="0"><span class="dashicons dashicons-update pmb-icon pmb-clickable"></span></a><?php

                                }
                        } else {
                            if($post_type->name !== CustomPostTypes::CONTENT){
                                ?>
                                <span tabindex="0" class="dashicons dashicons-update pmb-icon pmb-disabled-icon" title="<?php echo esc_attr(esc_html__('Upgrade to Professional License for one-click copying to Print Materials for customization.', 'print-my-blog'));?>"></span>
                                <?php
                            }
                        }
                        ?>
                        <a
                                title="<?php
                                echo esc_attr(
                                    sprintf(
                                        __('Add "%s" to project', 'print-my-blog'),
                                        $title
                                    )
                                );
                                ?>"
                                class="pmb-add-item" href="#pmb-content-item-<?php echo esc_attr($id);?>"><span class="dashicons dashicons-plus pmb-icon pmb-clickable"></span></a>
                        <a
                                title="<?php
                                echo esc_attr(
                                    sprintf(
                                        __('Remove "%s"', 'print-my-blog'),
                                        $title
                                    )
                                );
                                ?>"
                                tabindex="0"><span class="dashicons dashicons-no-alt pmb-icon pmb-remove-item pmb-clickable"></span></a>
                    </div>
                    <span class="pmb-project-item-template-container"><?php echo pmb_section_template_selector($template, $project);?></span>
                </div>
            </div>
        </div>

        <div class="pmb-nested-sortable pmb-draggable-area <?php echo $depth < $max_nesting ? 'pmb-sortable' : 'pmb-sortable-inactive';?> pmb-subs">
            <?php
            foreach($subs as $sub){
                pmb_content_item($sub, $project, $max_nesting);
            }
            pmb_drag_here();
            ?>
        </div>

    </div>
    <?php
}

function pmb_drag_here(){
    ?>
    <div class="pmb-no-sort pmb-drag-here no-drag">
        <div class="pmb-drag-here-inner">
            <a class="pmb-add-material" href="#ui-id-1">
                <?php esc_html_e('Drag or click here', 'print-my-blog');?> <span class="pmb-add-section dashicons
                dashicons-plus-alt"></span>
            </a>
        </div>
    </div>
    <?php
}