<?php

namespace PrintMyBlog\system;

use PrintMyBlog\services\SvgDoer;

/**
 * Class CustomPostTypes
 *
 * Description
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
class CustomPostTypes
{
    const PROJECT = 'pmb_project';
    const DESIGN = 'pmb_design';
    const CONTENT = 'pmb_content';
    /**
     * @var SvgDoer
     */
    protected $svg_doer;

    public function inject(SvgDoer $svg_doer)
    {
        $this->svg_doer = $svg_doer;
    }

    /**
     * This must not be done before init eh.
     */
    public function register()
    {
        register_post_type(
            self::PROJECT,
            [
                'label' => esc_html__('Projects', 'print-my-blog'),
                'description' => esc_html__('Projects for printing with Print My Blog', 'print-my-blog'),
                // 'show_in_menu' => true,
                // 'show_ui' => true,
                'capability_type' => 'pmb_project',
                'capabilities' => array(
                    'publish_posts' => 'publish_pmb_projects',
                    'edit_posts' => 'edit_pmb_projects',
                    'edit_others_posts' => 'edit_others_pmb_projects',
                    'delete_posts' => 'delete_pmb_projects',
                    'delete_others_posts' => 'delete_others_pmb_projects',
                    'read_private_posts' => 'read_private_pmb_projects',
                ),
            ]
        );
        $cap_slug = 'pmb_project';
        add_filter(
            'map_meta_cap',
            function ($caps, $cap, $user_id, $args) use ($cap_slug) {
                return $this->mapMetaCap($caps, $cap, $user_id, $args, $cap_slug);
            },
            10,
            4
        );

        register_post_type(
            self::DESIGN,
            [
                'label' => esc_html__('Designs', 'print-my-blog'),
                'description' => esc_html__('Designs for printing with Print My Blog', 'print-my-blog'),
                'capability_type' => 'pmb_design',
                'capabilities' => array(
                    'publish_posts' => 'publish_pmb_designs',
                    'edit_posts' => 'edit_pmb_designs',
                    'edit_others_posts' => 'edit_others_pmb_designs',
                    'delete_posts' => 'delete_pmb_designs',
                    'delete_others_posts' => 'delete_others_pmb_designs',
                    'read_private_posts' => 'read_private_pmb_designs',
                ),
            ]
        );
        $cap_slug = 'pmb_design';
        add_filter(
            'map_meta_cap',
            function ($caps, $cap, $user_id, $args) use ($cap_slug) {
                return $this->mapMetaCap($caps, $cap, $user_id, $args, $cap_slug);
            },
            10,
            4
        );

        register_post_type(
            self::CONTENT,
            // WordPress CPT Options Start
            array(
                'labels' => array(
                    'name' => __('Print Materials', 'print-my-blog') ,
                    'singular_name' => __('Print Material', 'print-my-blog')
                ),
                'has_archive' => true,
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => PMB_ADMIN_PROJECTS_PAGE_SLUG,
                'rewrite' => array('slug' => 'pmb'),
                'show_in_rest' => true,
                'supports' => array('title', 'editor', 'revisions', 'author','thumbnail', 'custom-fields'),
                'taxonomies' => array('category', 'post_tag'),
                'menu_icon' => $this->svg_doer->getSvgDataAsColor(PMB_DIR . 'assets/images/menu-icon.svg')
            )
        );
        add_filter('wp_insert_post_data', [$this,'makePrintMaterialsAlwaysPrivate']);
    }

    /**
     * We wanted print materials to not be public... but then again, we want them to have URLs for easy linking
     * and to appear in link searches. So instead we just make them all private
     * @param $post
     * @return mixed
     */
    public function makePrintMaterialsAlwaysPrivate($post)
    {
        if ($post['post_type'] == self::CONTENT) {
            $post['post_status'] = 'private';
        }
        return $post;
    }

    public function mapMetaCap($caps, $cap, $user_id, $args, $cap_slug)
    {

        /* If editing, deleting, or reading a project, get the post and post type object. */
        if ('edit_' . $cap_slug == $cap || 'delete_' . $cap_slug == $cap || 'read_' . $cap_slug == $cap) {
            $post = get_post($args[0]);
            $post_type = get_post_type_object($post->post_type);

            /* Set an empty array for the caps. */
            $caps = array();
        }

        /* If editing a project, assign the required capability. */
        if ('edit_' . $cap_slug == $cap) {
            if ($user_id == $post->post_author) {
                $caps[] = $post_type->cap->edit_posts;
            } else {
                $caps[] = $post_type->cap->edit_others_posts;
            }
        } elseif ('delete_' . $cap_slug == $cap) {
            /* If deleting a project, assign the required capability. */
            if ($user_id == $post->post_author) {
                $caps[] = $post_type->cap->delete_posts;
            } else {
                $caps[] = $post_type->cap->delete_others_posts;
            }
        } elseif ('read_' . $cap_slug == $cap) {
            /* If reading a private project, assign the required capability. */
            if ('private' != $post->post_status) {
                $caps[] = 'read';
            } elseif ($user_id == $post->post_author) {
                $caps[] = 'read';
            } else {
                $caps[] = $post_type->cap->read_private_posts;
            }
        }

        /* Return the capabilities required by the user. */
        return $caps;
    }

    /**
     * @return string[]
     */
    public function getPostTypes()
    {
        return [
            self::CONTENT,
            self::DESIGN,
            self::PROJECT
        ];
    }
}
