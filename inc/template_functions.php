<?php
/**
 * This function is only included when rendering Print My Blog
 */

use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;

/**
 * Returns the post's anchor ID.
 * @param WP_Post|null $post
 * @return bool|false|string|WP_Error
 */
function pmb_get_the_post_anchor($post = null)
{
    return pmb_convert_url_to_anchor(get_the_permalink($post));
}

/**
 * Gets the site's domain (site_url minus the "https://" or "http://" prefix).
 *
 * @return string|string[]|void
 */
function pmb_get_domain()
{
    return str_replace('http://','', site_url('', 'http'));
}

/**
 * Echoes the anchor ID for the post. Does not escape it (use pmb_permalink_as_attr
 */
function pmb_the_post_anchor()
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by pmb_convert_url_to_anchor
    echo pmb_convert_url_to_anchor(pmb_get_the_post_anchor());
}

/**
 * Takes a URL and turns it into the form we use for post anchor links in PMB projects.
 * Specifically,
 * * removes the "http://" or "https://",
 * * removes "www."
 * * removes the domain name.
 * @param string $url
 * @return string|void
 */
function pmb_convert_url_to_anchor($url)
{
    return str_replace(
        [
            '%',
            'https://www.',
            'http://www.',
            'https://',
            'http://',
            pmb_get_domain(),
        ],
        [
            '-',
            '',
            '',
            '',
            '',
            '',
        ],
        $url
    );
}

/**
 * @param string $relative_filepath filepath relative to the current design's templates directory
 * @global Design $pmb_design
 * @global Project $pmb_project
 * @global \PrintMyBlog\entities\ProjectGeneration $pmb_project_generation
 */
function pmb_include_design_template($relative_filepath)
{
    /**
     * @var $pmb_design Design
     */
    global $pmb_project, $pmb_design, $pmb_project_generation;
    require $pmb_design->getDesignTemplate()->getTemplatePathToDivision($relative_filepath);
}

/**
 * Add this to the HTML div that wraps a section and its subsections.
 * @param string $class
 */
function pmb_section_wrapper_class($class = '')
{
    global $post;
    $section = $post->pmb_section;
    $pmb_classes = '';
    if ($section instanceof \PrintMyBlog\orm\entities\ProjectSection) {
        $pmb_classes = 'pmb-' . pmb_map_section_to_division($section) . '-wrapper pmb-section-wrapper';
        if ($section->getSectionOrder() === 1) {
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
function pmb_section_class($class = '')
{
    global $post;
    $section = $post->pmb_section;
    $pmb_classes = '';
    if ($section instanceof \PrintMyBlog\orm\entities\ProjectSection) {
        $pmb_classes = ' pmb-section pmb-' . pmb_map_section_to_division($section) . ' pmb-height-' . $section->getHeight() . ' pmb-depth-' . $section->getDepth();
        if ((int)$section->getSectionOrder() === 1) {
            $pmb_classes .= ' pmb-first-section';
        }
    }
    post_class($pmb_classes . ' ' . $class);
    echo 'data-height="' . esc_attr($section->getHeight()) . '" data-depth="' . esc_attr($section->getDepth()) . '"';
}

/**
 * Echoes the ID attribute and value for a PMB section wrapper tag.
 */
function pmb_section_wrapper_id()
{
    global $post;
    echo 'id="' . esc_attr($post->post_name) . '-wrapper"';
}

/**
 * Echoes out the ID attribute to use on the section.
 */
function pmb_section_id(){
	echo 'id="' . esc_attr(pmb_get_the_post_anchor()) . '"';
}

/**
 * Returns the current post's permalink as an attribute
 * @return string|void
 * @deprecated use esc_attr(pmb_get_the_post_anchor())
 */
function pmb_permalink_as_attr(){
    return esc_attr(pmb_get_the_post_anchor());
}

/**
 * Echoes out the section's title and makes sure to add the CSS class PMB expects (especially important for finding the table of contents.)
 */
function pmb_the_title()
{
    echo '<h1 class="pmb-title">' . esc_html(pmb_get_title()) . '</h1>';
}

/**
 * Gets the current post project's title.
 * @return string
 */
function pmb_get_title()
{
    $post = get_post();
    if ($post instanceof WP_Post) {
        $title_from_meta = get_post_meta($post->ID, 'pmb_title', true);
        if ($title_from_meta) {
            $title = $title_from_meta;
        } else {
            $title = get_the_title($post);
        }
    } else {
        $title = '';
    }
    return (string)$title;
}

/**
 * Returns whether or not the $post_content_thing is in the 'post_content' PMB settings. If it isn't, returns $default.
 * @param string $post_content_thing
 * @param mixed $default
 * @return bool
 */
function pmb_design_uses($post_content_thing, $default)
{
    global $pmb_design;
    $post_content = $pmb_design->getSetting('post_content');
    if (! $post_content) {
        return $default;
    }
    return in_array($post_content_thing, $post_content, true);
}


/**
 * Gets the template options select input HTML
 * @param $selecstring ted_template
 * @param Project $project
 *
 * @return string
 */
function pmb_section_template_selector($selected_template, Project $project)
{
    $options = $project->getSectionTemplateOptions();
    $html = '<select class="pmb-template">';
    foreach ($options as $value => $display_text) {
        $html .= '<option value="' . esc_attr($value) . '" ' . selected($value, $selected_template, false) . '>' . $display_text . '</option>';
    }
    $html .= '</select>';
    return $html;
}

/**
 * @param WP_Post_Type $post_type
 *
 * @param string $extra_css_classes
 * @param string $extra_attrs
 * @return string HTML for the post type's icon
 */
function pmb_post_type_icon_html(WP_Post_Type $post_type, $extra_css_classes = '', $extra_attrs = '')
{
    $icon = $post_type->menu_icon;
    if (empty($icon)) {
        $icon = 'dashicons-media-default';
    }
    $img = '<img src="' . $icon . '" alt="" />';
    $img_style = '';
    $img_class = '';
    if ('none' === $icon || 'div' === $icon) {
        $img = '<br />';
    } elseif (0 === strpos($icon, 'data:image/svg+xml;base64,')) {
        $img = '<br />';
        $img_style = ' style=\'background-image:url("' . esc_attr($icon) . '") !important;\'';
        $img_class = 'pmb-svg-icon svg';
    } elseif (0 === strpos($icon, 'dashicons-')) {
        $img = '<br />';
        $img_class = ' dashicons ' . sanitize_html_class($icon);
    }
    return "<div class='{$img_class} " . esc_attr($extra_css_classes) . "'{$img_style} {$extra_attrs}>{$img}</div>";
}

/**
 * Copy of WP's deprecated the_meta() function: echoes out the post's meta keys and values.
 */
function pmb_the_meta() {
    $keys = get_post_custom_keys();
    if ( $keys ) {
        $li_html = '';
        foreach ( (array) $keys as $key ) {
            $keyt = trim( $key );
            if ( is_protected_meta( $keyt, 'post' ) ) {
                continue;
            }

            $values = array_map( 'trim', get_post_custom_values( $key ) );
            $value  = implode( ', ', $values );

            $html = sprintf(
                "<li><span class='post-meta-key'>%s</span> %s</li>\n",
                /* translators: %s: Post custom field name. */
                esc_html( sprintf( _x( '%s:', 'Post custom field name' ), $key ) ),
                esc_html( $value )
            );

            /**
             * Filters the HTML output of the li element in the post custom fields list.
             *
             * @since 2.2.0
             *
             * @param string $html  The HTML output for the li element.
             * @param string $key   Meta key.
             * @param string $value Meta value.
             */
            $li_html .= apply_filters( 'the_meta_key', $html, $key, $value );
        }

        if ( $li_html ) {
            echo "<ul class='post-meta'>\n{$li_html}</ul>\n";
        }
    }
}