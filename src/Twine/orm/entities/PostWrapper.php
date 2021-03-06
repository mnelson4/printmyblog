<?php

namespace Twine\orm\entities;

use WP_Post;

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
     * @param $meta_name
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

    public function getPmbMetas($meta_name)
    {
        return $this->getMetas(
            self::META_PREFIX . $meta_name
        );
    }

    public function getMetas($meta_name)
    {
        return get_post_meta(
            $this->getWpPost()->ID,
            $meta_name,
            false
        );
    }

    /**
     * @param $meta_name
     * @param $value
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
     *
     * return bool success
     */
    public function delete()
    {
        return wp_delete_post($this->getWpPost()->ID);
    }

    /**
     * Wraps getMeta() and just adds the meta prefix.
     *
     * @param $meta_name
     *
     * @return mixed
     */
    public function getPmbMeta($meta_name)
    {
        return $this->getMeta(self::META_PREFIX . $meta_name);
    }

    /**
     * @param $meta_name
     * @param $new_value
     *
     * @return bool|int
     */
    public function setPmbMeta($meta_name, $new_value)
    {
        return $this->setMeta(self::META_PREFIX . $meta_name, $new_value);
    }

    /**
     * @param $meta_name
     * @param $new_value
     *
     * @return false|int
     */
    public function addPmbMeta($meta_name, $new_value)
    {
        return $this->addMeta(self::META_PREFIX . $meta_name, $new_value);
    }

    /**
     * @param $meta_name
     * @param $new_value
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
     * @param $meta_name
     * @param string $value
     */
    public function deletePmbMeta($meta_name, $value = '')
    {
        $this->deleteMeta(self::META_PREFIX . $meta_name, $value);
    }

    /**
     * @param $meta_name
     *
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
}
