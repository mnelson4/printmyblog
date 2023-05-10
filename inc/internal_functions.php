<?php
/**
 * Functions used internally by Print My Blog that other devs probably won't need.
 */

use PrintMyBlog\controllers\Admin;
use PrintMyBlog\controllers\Frontend;
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
function pmb_hover_help($explanation = '')
{
    return '<span data-help="' . esc_attr($explanation) . '" class="dashicons dashicons-info pmb-hover"></span>';
}

/**
 * Adds an icon which has explanatory text when hovered over.
 * @param string $explanation
 * @param string $extra_css_classes
 * @param string $url not escaped
 * @return string HTML
 */
function pmb_hover_tip($explanation = '', $extra_css_classes = '', $url = '')
{
    $html = '';
    if ($url) {
        $html .= '<a href="' . esc_url($url) . '">';
    }
    $html .= '<span data-help="' . esc_attr($explanation) . '" class="dashicons dashicons-superhero pmb-hover ' . $extra_css_classes . '"></span>';
    if ($url) {
        $html .= '</a>';
    }
    return $html;
}

/**
 * Returns a string that says this feature only works with Pro Print Service (not supported by browsers).
 * @return string
 */
function pmb_pro_print_service_only($explanation = '')
{
    if (pmb_fs()->is_plan__premium_only('hobbyist')) {
        return '';
    } else {
        $upgrade_url = pmb_fs()->get_upgrade_url();
        $hover_text = '<b><a href="' . $upgrade_url . '">' .
            __('Purchase Required', 'print-my-blog') .
            '</a></b>';
        if ($explanation) {
            $hover_text .= '<br>' . $explanation;
        }
        return pmb_hover_tip($hover_text, 'pmb-pro-only', $upgrade_url);
    }
}

/**
 * Echoes out that this feature only works with pro print service (not supported by browsers)
 * @param string $explanation
 */
function pmb_pro_print_service_only_e($explanation = '')
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- outputting HTML
    echo pmb_pro_print_service_only($explanation);
}

/**
 * Returns a string that says this feature works best with Print My Blog Pro.
 * @return string
 */
function pmb_pro_print_service_best($explanation = '')
{
    if (pmb_fs()->is_plan__premium_only('hobbyist')) {
        return '';
    } else {
        $upgrade_url = pmb_fs()->get_upgrade_url();
        $hover_text = '<b>' .
            sprintf(
            // translators: 1: opening anchor tag, 2: closing anchor tag.
                __('Works better with %1$sPro PDF Service%2$s', 'print-my-blog'),
                '<a href="' . $upgrade_url . '">',
                '</a>'
            ) .
            '</b>';
        if ($explanation) {
            $hover_text .= '<br>' . $explanation;
        }
        return pmb_hover_tip($hover_text, 'pmb-pro-best', $upgrade_url);
    }
}

/**
 * Echoes out this feature works best with pro print service.
 * @param string $explanation
 */
function pmb_pro_print_service_best_e($explanation = '')
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- outputting HTML
    echo pmb_pro_print_service_best($explanation);
}

/**
 * Echoes out that there's a better feature in Pro Print
 * @param string $explanation
 */
function pmb_pro_better_e($explanation = '')
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- outputting HTML
    echo pmb_pro_better($explanation);
}

/**
 * To tell folks about features of Pro Print not available for Quick print
 * @param string $explanation
 * @return string
 */
function pmb_pro_better($explanation = '')
{
    $url = admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH);
    $hover_text = '<b>' .
        sprintf(
                // translators: 1: opening anchor tag, 2: closing anchor tag.
            __('More Advanced Features in %1$sPro Print%2$s', 'print-my-blog'),
            '<a href="' . esc_url($url) . '">',
            '</a>'
        ) .
        '</b>';
    if ($explanation) {
        $hover_text .= '<br>' . $explanation;
    }
    return pmb_hover_tip($hover_text, '', $url);
}


/**
 * Maps
 * @param \PrintMyBlog\orm\entities\ProjectSection $section
 *
 * @return string
 */
function pmb_map_section_to_division(ProjectSection $section)
{
    if ($section->getPlacement() === DesignTemplate::IMPLIED_DIVISION_FRONT_MATTER) {
        return 'front_matter_article';
    }
    if ($section->getPlacement() === DesignTemplate::IMPLIED_DIVISION_BACK_MATTER) {
        return 'back_matter_article';
    }
    return apply_filters(
        'pmb_map_section_to_division',
        pmb_map_section_height_to_division($section->getHeight()),
        $section
    );
}

/**
 * @param int $height
 * @return string
 */
function pmb_map_section_height_to_division($height)
{
    switch ($height) {
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
 * @param string $template_name
 * @param array $context
 */
function pmb_render_template($template_name, $context = [])
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- outputting HTML
    echo pmb_get_contents(PMB_TEMPLATES_DIR . $template_name, $context);
}

/**
 * Renders any file
 * @param $filename
 * @param array $context
 */
function pmb_get_contents($filename, $context = [])
{
    // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- we're making them available while rendering the template file.
    extract($context);
    ob_start();
    require $filename;
    return ob_get_clean();
}

/**
 * @param \PrintMyBlog\orm\entities\Design $design
 */
function pmb_design_preview(\PrintMyBlog\orm\entities\Design $design)
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- outputting HTML
    pmb_render_template(
        'partials/design_preview.php',
        [
            'design' => $design,
        ]
    );
}

/**
 * Gets a post item div HTML for the sortable.js sorting area.
 * @param ProjectSection|WP_Post $posty_row
 * @param Project $project
 * @param int $max_nesting
 */
function pmb_content_item($posty_row, Project $project, $max_nesting = null)
{
    if ($posty_row instanceof \PrintMyBlog\orm\entities\ProjectSection) {
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
                'ID' => $id,
            ],
            admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
        ),
        Admin::SLUG_ACTION_DUPLICATE_PRINT_MATERIAL
    );
    // if the post type is no longer registered, the plugin that added it probably got removed, so hide this item.
    if (! $post_type) {
        return;
    }
    if ($max_nesting === null) {
        $max_nesting = $project->getLevelsAllowed();
    }
    ?>
    <div class="list-group-item pmb-project-item" tabindex="0" id="pmb-content-item-<?php echo esc_attr($id); ?>" data-id="<?php echo esc_attr($id); ?>">
        <div class="pmb-project-item-header" title="<?php
        echo esc_attr(
            sprintf(
                __('Drag the %s "%s" into your project', 'print-my-blog'),
                $post_type->labels->singular_name,
                $title
            )
        );
        ?>
">
            <div class="pmb-grabbable pmb-project-item-title">
                <?php
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- function returns HTML and prepares it.
                echo pmb_post_type_icon_html($post_type);
                ?>
                <span class="pmb-project-item-title-text"><?php echo esc_html($title); ?></span>
                <?php do_action('pmb_content_items__project-item-title end', $id, $title, $post_type, $template, $subs, $depth); ?>
            </div>

            <div class="pmb-project-item-options">
                <div class="pmb-project-item-options-inner">
                    <div class="pmb-project-item-options-buttons no-drag">
                        <?php
                        // only show the viewing button if it will work
                        if ($post_type->public) {
                            ?><a
                                    href="<?php echo esc_attr($view_url); ?>"
                                    title="<?php
                                    echo esc_attr(
                                        sprintf(
                                        // translators: %s: post title.
                                            __('View "%s"', 'print-my-blog'),
                                            $title
                                        )
                                    );
                                    ?>"
                                    target="_blank"><span
                                        class="dashicons dashicons-visibility pmb-icon pmb-clickable"></span></a><?php
                        }
                        ?><a
                                href="<?php echo esc_attr($edit_url); ?>"
                                title="<?php
                                echo esc_attr(
                                    sprintf(
                                        // translators: %s: post title.
                                        __('Edit "%s"', 'print-my-blog'),
                                        $title
                                    )
                                );
                                ?>"
                                target="_blank"><span
                                    class="dashicons dashicons-edit pmb-icon pmb-clickable"></span></a><?php
                        if (pmb_fs()->is_plan__premium_only('founding_members')) {
                            $label = '';
                            if ($post_type->name !== CustomPostTypes::CONTENT) {
                                $print_material = null;
                                $print_materials = Context::instance()->reuse('Twine\orm\managers\PostWrapperManager')->getByPostMeta('_pmb_original_post', (string)$id, 1);
                                if ($print_materials) {
                                    $print_material = reset($print_materials);
                                }
                                if ($print_material) {
                                    // translators: 1: post title
                                    $label = sprintf(__('Replace "%s" with the existing Print Material for customizing.', 'print-my-blog'), $print_material->getWpPost()->post_title);
                                } else {
                                    $label = sprintf(__('Replace "%s" with a New Print Material for customizing', 'print-my-blog'), $title);
                                }
                            }
                            if ($label) {
                                ?><a
                                title="<?php
                                echo esc_attr($label);
                                ?>"
                                data-id="<?php echo esc_attr($id); ?>" class="pmb-duplicate-post-button" tabindex="0"><span class="dashicons dashicons-update pmb-icon pmb-clickable"></span></a><?php
                            }
                        } else {
                            if ($post_type->name !== CustomPostTypes::CONTENT) {
                                ?><span tabindex="0" class="dashicons dashicons-update pmb-icon pmb-disabled-icon" title="<?php echo esc_attr(esc_html__('Upgrade to Professional License for one-click copying to Print Materials for customization.', 'print-my-blog')); ?>"></span>
                                <?php
                            }
                        }
                        ?><a
                                title="<?php
                                echo esc_attr(
                                    sprintf(
                                            // translators: %s: post title
                                        __('Add "%s" to project', 'print-my-blog'),
                                        esc_html($title)
                                    )
                                );
                                ?>"
                                class="pmb-add-item" href="#pmb-content-item-<?php echo esc_attr($id); ?>"><span
                                    class="dashicons dashicons-plus pmb-icon pmb-clickable"></span></a><a
                                title="<?php
                                echo esc_attr(
                                    sprintf(
                                            // translators: %s: post title
                                        __('Remove "%s"', 'print-my-blog'),
                                        $title
                                    )
                                );
                                ?>"
                                tabindex="0"><span
                                    class="dashicons dashicons-no-alt pmb-icon pmb-remove-item pmb-clickable"></span></a>
                    </div>
                    <span class="pmb-project-item-template-container"><?php
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- that's meant to output HTML.
                        echo pmb_section_template_selector($template, $project); ?></span>
                </div>
            </div>
        </div>

        <div class="pmb-nested-sortable pmb-draggable-area <?php echo $depth < $max_nesting ? 'pmb-sortable' : 'pmb-sortable-inactive'; ?> pmb-subs">
            <?php
            foreach ($subs as $sub) {
                pmb_content_item($sub, $project, $max_nesting);
            }
            pmb_drag_here();
            ?>
        </div>

    </div>
    <?php
}

/**
 * Echoes HTML to show that project items can be dragged to this area.
 */
function pmb_drag_here()
{
    ?>
    <div class="pmb-no-sort pmb-drag-here no-drag">
        <div class="pmb-drag-here-inner">
            <a class="pmb-add-material" href="#ui-id-1">
                <?php esc_html_e('Drag or click here', 'print-my-blog'); ?> <span class="pmb-add-section dashicons
                dashicons-plus-alt"></span>
            </a>
        </div>
    </div>
    <?php
}

/**
 * Gets the URL to perform "PMB AJAX" requests (just like regular WP_AJAX, except on a request that's technically
 * for the frontend, which most plugins think it's a frontend request).
 * @return string
 */
function pmb_ajax_url(){
    return add_query_arg(
        [
            Frontend::PMB_AJAX_INDICATOR => 1,
        ],
        site_url('/')
    );
}

function pmb_get_filename($url, $remove_extension = false){
    $filename_and_extension = basename($url);
    if($remove_extension){
        $filename_and_extension = substr($filename_and_extension, 0, strpos($filename_and_extension, '.'));
    }
    return $filename_and_extension;
}

/**
 * Returns whether or not to use PMB Central for generate requests. (We like to use DocRaptor's server on free sites
 * to keep the load down on printmy.blog; but for paying customers we like to monitor their API requests for quality assurance.)
 * @return int
 */
function pmb_use_pmb_central(){
    $use_pmb_central = 0;
    try{
        if (pmb_fs()->is_plan__premium_only('business') || (defined('PMB_USE_CENTRAL') && PMB_USE_CENTRAL)) {
            $use_pmb_central = 1;
        }
    }catch(Freemius_Exception $e){
        // ignore it
    }

    return $use_pmb_central;
}