<?php

namespace Twine\orm\entities;

use PrintMyBlog\system\CustomPostTypes;
use WP_Post;

/**
 * Class PostWrapper
 * @package Twine\orm\entities
 */
class PostWrapper
{

    const META_PREFIX = '_pmb_';
    /**
     * @var WP_Post
     */
    protected $wp_post;

    /**
     * Project constructor.
     *
     * @param WP_Post|int $post object or ID
     */
    public function __construct($post)
    {
        if (is_int($post) || is_string($post)) {
            $post = get_post($post);
        }
        $this->wp_post = $post;
    }

    /**
     * @return WP_Post
     */
    public function getWpPost()
    {
        return $this->wp_post;
    }

    /**
     * Generic function to get metadata stored on the post object.
     * @param string $meta_name
     * @return mixed
     */
    public function getMeta($meta_name)
    {
        $metas = $this->getMetas($meta_name);
        if (count($metas)) {
            return reset($metas);
        }
        return null;
    }

    /**
     * Get postmetas and prepend pmb_ to the provided meta name.
     * @param string $meta_name
     * @return mixed
     */
    public function getPmbMetas($meta_name)
    {
        return $this->getMetas(
            self::META_PREFIX . $meta_name
        );
    }

    /**
     * Wrapper of get_post_meta.
     * @param string $meta_name
     * @return mixed
     */
    public function getMetas($meta_name)
    {
        return get_post_meta(
            $this->getWpPost()->ID,
            $meta_name,
            false
        );
    }

    /**
     * Wrapper of update_post_meta.
     * @param string $meta_name
     * @param mixed $value
     *
     * @return bool|int
     */
    public function setMeta($meta_name, $value)
    {
        return update_post_meta(
            $this->getWpPost()->ID,
            $meta_name,
            $value
        );
    }

    /**
     * Wrapper of wp_delete_post.
     * return bool success
     */
    public function delete()
    {
        return wp_delete_post($this->getWpPost()->ID);
    }

    /**
     * Wraps getMeta() and just adds the meta prefix.
     *
     * @param string $meta_name
     *
     * @return mixed
     */
    public function getPmbMeta($meta_name)
    {
        return $this->getMeta(self::META_PREFIX . $meta_name);
    }

    /**
     * Saves the meta and adds pmb_ to the meta key.
     * @param string $meta_name
     * @param mixed $new_value
     *
     * @return bool|int
     */
    public function setPmbMeta($meta_name, $new_value)
    {
        return $this->setMeta(self::META_PREFIX . $meta_name, $new_value);
    }

    /**
     * Adds a new meta row, prepends pmb_ to the meta key.
     * @param string $meta_name
     * @param mixed $new_value
     *
     * @return false|int
     */
    public function addPmbMeta($meta_name, $new_value)
    {
        return $this->addMeta(self::META_PREFIX . $meta_name, $new_value);
    }

    /**
     * Wraps add_post_meta.
     * @param string $meta_name
     * @param mixed $new_value
     *
     * @return false|int
     */
    public function addMeta($meta_name, $new_value)
    {
        return add_post_meta(
            $this->getWpPost()->ID,
            $meta_name,
            $new_value
        );
    }

    /**
     * Deletes meta, prepends pmb_ to the meta key.
     * @param string $meta_name
     * @param mixed $value
     * @return bool
     */
    public function deletePmbMeta($meta_name, $value = '')
    {
        return $this->deleteMeta(self::META_PREFIX . $meta_name, $value);
    }

    /**
     * Wraos delete_post_meta.
     * @param string $meta_name
     * @param string $value
     *
     * @return bool
     */
    public function deleteMeta($meta_name, $value = '')
    {
        return delete_post_meta(
            $this->getWpPost()->ID,
            $meta_name,
            $value
        );
    }

    /**
     * Duplicates this post as a new print material.
     *
     * @param array $args_override
     * @return WP_Post
     */
    public function duplicateAsPrintMaterial($args_override = [])
    {
        return $this->duplicatePost(
            [
                // translators: 1 post title
                'post_title' => sprintf(__('%s (print material)', 'print-my-blog'), $this->getWpPost()->post_title),
                'post_type' => CustomPostTypes::CONTENT,
                'post_status' => 'private',
            ],
            [
                '_pmb_original_post' => $this->getWpPost()->ID,
            ]
        );
    }
    /**
     * Creates a new post from the wrapped post
     * @param array $args_override args to override from their defaults when inserting
     * @param array $new_post_metas keys are meta keys, values are their values
     * @return WP_Post
     */
    public function duplicatePost($args_override = [], $new_post_metas = [])
    {
        $post = $this->getWpPost();
        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;

        $args = array_merge(
            array(
                'comment_status' => $post->comment_status,
                'ping_status'    => $post->ping_status,
                'post_author'    => $new_post_author,
                'post_content'   => $post->post_content,
                'post_excerpt'   => $post->post_excerpt,
                'post_name'      => $post->post_name,
                'post_parent'    => $post->post_parent,
                'post_password'  => $post->post_password,
                'post_status'    => $post->post_status,
                // translators: 1: the name of the original post being duplicated
                'post_title'     => sprintf(__('%s (copy)', 'print-my-blog'), $post->post_title),
                'post_type'      => $post->post_type,
                'to_ping'        => $post->to_ping,
                'menu_order'     => $post->menu_order,
            ),
            $args_override
        );

        // insert the post by wp_insert_post() function
        $new_post_id = wp_insert_post($args);

        /*
         * get all current post terms ad set them to the new post draft
         */
        $taxonomies = get_object_taxonomies(get_post_type($post)); // returns array of taxonomy names for post type, ex array("category", "post_tag");
        if ($taxonomies) {
            foreach ($taxonomies as $taxonomy) {
                $post_terms = wp_get_object_terms($post->ID, $taxonomy, array( 'fields' => 'slugs' ));
                wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
            }
        }

        // duplicate all post meta
        $old_post_meta = get_post_meta($post->ID);
        if ($old_post_meta) {
            foreach ($old_post_meta as $meta_key => $meta_values) {
                if ('_wp_old_slug' === $meta_key) { // do nothing for this meta key
                    continue;
                }

                foreach ($meta_values as $meta_value) {
                    add_post_meta($new_post_id, $meta_key, $meta_value);
                }
            }
        }

        foreach ($new_post_metas as $meta_key => $meta_value) {
            add_post_meta($new_post_id, $meta_key, $meta_value);
        }

        return get_post($new_post_id);
    }
}
