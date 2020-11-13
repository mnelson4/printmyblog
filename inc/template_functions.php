<?php
/**
 * This function is only included when rendering Print My Blog
 */

use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;

/**
 * @param $post
 *
 * @return bool|false|string|WP_Error
 */

function pmb_get_the_post_anchor($post){
	if( ! $post instanceof WP_Post){
		global $post;
	}
	return get_permalink($post);
}

function pmb_the_post_anchor(){
	global $post;
	echo pmb_get_the_post_anchor($post);
}

function pmb_convert_url_to_anchor($url){
	return esc_attr($url);
}

/**
 * @param string $relative_filepath filepath relative to the current design's templates directory
 * @global Design $pmb_design
 */
function pmb_include_design_template($relative_filepath){
	/**
	 * @var $pmb_design Design
	 */
	global $pmb_design;
	require($pmb_design->getDesignTemplate()->getTemplatePathToDivision($relative_filepath));
}

/**
 * Add this to the HTML div that wraps a section and its subsections.
 * @param string $class
 */
function pmb_section_wrapper_class($class = ''){
	global $post;
	$section = $post->pmb_section;
	$pmb_classes = '';
	if($section instanceof \PrintMyBlog\orm\entities\ProjectSection){
		$pmb_classes = 'pmb-' . pmb_map_section_to_division($section) . '-wrapper pmb-section-wrapper';
		if($section->getSectionOrder() === 1){
			$pmb_classes .= ' pmb-first-section-wrapper';
		}
	}
	echo 'class="' . esc_attr($pmb_classes . ' ' . $class) . '"';
}

/**
 * Add this to any article tags for a PMB section.
 * @param string $class
 * @return void echoes
 */
function pmb_section_class($class = ''){
	global $post;
	$section = $post->pmb_section;
	$pmb_classes = '';
	if($section instanceof \PrintMyBlog\orm\entities\ProjectSection){
		$pmb_classes = ' pmb-section pmb-' . pmb_map_section_to_division($section) . ' pmb-height-' . $section->getHeight() . ' pmb-depth-' . $section->getDepth();
		if($section->getSectionOrder() == 1){
			$pmb_classes .= ' pmb-first-section';
		}
	}
	post_class($pmb_classes . ' ' . $class);
	echo 'data-height="' . esc_attr($section->getHeight()) . '" data-depth="' . esc_attr($section->getDepth()) . '"';
}

function pmb_section_wrapper_id(){
	global $post;
	echo 'id="' . esc_attr($post->post_name) . '-wrapper"';
}
/**
 * Echoes out the ID attribute to use on the section.
 */
function pmb_section_id(){
	echo 'id="' . esc_attr(get_the_permalink()) . '"';
}

/**
 * Echoes out the section's title and makes sure to add the CSS class PMB expects (especially important for finding the table of contents.)
 */
function pmb_the_title(){
	the_title('<h1 class="pmb-title">','</h1>');
}

function pmb_design_uses($post_content_thing, $default){
	global $pmb_design;
	$post_content = $pmb_design->getSetting('post_content');
	if(! $post_content){
		return $default;
	}
	return in_array($post_content_thing, $post_content);
}

/**
 * Gets a post item div HTML for the sortable.js sorting area.
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
            <div class="pmb-grabbable pmb-project-item-title">
                <span
	                class="dashicons dashicons-move"></span>
                <span class="pmb-project-item-title-text"><?php echo $title;?></span>
            </div>
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

/**
 * Gets the template options select input HTML
 * @param $selected_template
 * @param Project $project
 *
 * @return string
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