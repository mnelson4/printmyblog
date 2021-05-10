<?php
/**
 * Functions used internally by Print My Blog that other devs probably won't need.
 */

use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\orm\entities\ProjectSection;

/**
 * Returns a string that says this feature only works with Print My Blog Pro.
 * @return string
 */
function pmb_pro_print_service_only($explanation = ''){
    if(apply_filters('pmb_pro_only__is_premium',pmb_fs()->is_premium())){
        return '';
    }
    $hover_text = '<b>' . sprintf(
            __('Only works with %1$sPro Print Service%2$s', 'print-my-blog'),
        '<a href="' . pmb_fs()->get_upgrade_url() . '">',
        '</a>'
    ) . '</b>';
    if($explanation){
        $hover_text .= "<br>" . $explanation;
    }
	return '<span data-help="' . esc_attr($hover_text) . '" class="dashicons dashicons-superhero pmb-pro-only pmb-hover"></span>';

}

/**
 * Echoes out that this feature only works with pro
 */
function pmb_pro_print_service_only_e($explanation = ''){
    echo pmb_pro_print_service_only($explanation);
}

/**
 * Returns a string that says this feature works best with Print My Blog Pro.
 * @return string
 */
function pmb_pro_print_service_best($explanation = ''){
    if(apply_filters('pmb_pro_only__is_premium',pmb_fs()->is_premium())){
        return '';
    }
    $hover_text = '<b>' . sprintf(
            __('Works better with %1$sPro Print Service%2$s', 'print-my-blog'),
            '<a href="' . pmb_fs()->get_upgrade_url() . '">',
           '</a>'
    ) . '</b>';
    if($explanation){
        $hover_text .= "<br>" . $explanation;
    }
    return '<span data-help="' . esc_attr($hover_text)  . '" class="dashicons dashicons-superhero pmb-pro-best pmb-hover"></span>';
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
    return '<span data-help="' . esc_attr($hover_text)  . '" class="dashicons dashicons-superhero pmb-hover"></span>';
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
        $edit_url = get_edit_post_link($posty_row->getPostId());
        $view_url = get_permalink($posty_row->getPostId());
    } else {
        $id = $posty_row->ID;
        $title = $posty_row->post_title;
        $post_type = get_post_type_object($posty_row->post_type);
        $template = null;
        $height = 0;
        $subs = [];
        $depth = 1;
        $edit_url = get_edit_post_link($posty_row->ID);
        $view_url = get_permalink($posty_row->ID);
    }
    // if the post type is no longer registered, the plugin that added it probably got removed, so hide this item.
    if(! $post_type){
        return;
    }
    if($max_nesting === null){
        $max_nesting = $project->getLevelsAllowed();
    }
    ?>
    <div class="list-group-item pmb-project-item" data-id="<?php echo esc_attr($id);?>" data-height="<?php echo esc_attr($height);?>">
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
                target="_blank"><span class="dashicons dashicons-visibility pmb-icon"></span></a>
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
            <span class="pmb-project-item-template-container"><?php echo pmb_section_template_selector($template, $project);?></span>
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
    <div class="pmb-hover pmb-no-sort pmb-drag-here no-drag">
        <div class="pmb-drag-here-inner">
            <a class="pmb-add-material">
                <?php esc_html_e('Drag or click here', 'print-my-blog');?> <span class="pmb-add-section dashicons
                dashicons-plus-alt"></span>
            </a>
        </div>
    </div>
    <?php
}