<?php

namespace Twine\orm\managers;

use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\system\Context;
use Twine\orm\entities\PostWrapper;
use WP_Post;
use WP_Query;

class PostWrapperManager
{
    /**
     * @var string sub-classes of this should use their own subclass of PostWrapper
     */
    protected $class_to_instantiate = 'Twine\orm\entities\PostWrapper';

    /**
     * @var string singular slug
     */
    protected $cap_slug = 'post';

    /**
     * @param $post_id
     *
     * @return PostWrapper|null
     */
    public function getById($post_id)
    {
        $wp_post = get_post($post_id);
        if (! $wp_post) {
            return null;
        }
        $post_wrapper = $this->createWrapperAroundPost($wp_post);
        return $post_wrapper;
    }

    /**
     * @param $slug
     * @return PostWrapper|null
     */
    public function getBySlug($slug)
    {
        $post_object = get_page_by_path($slug, OBJECT, $this->cap_slug);
        if ($post_object instanceof WP_Post) {
            return $this->createWrapperAroundPost($post_object);
        }
        return null;
    }

    /**
     * @param WP_Query $query
     *
     * @return array|PostWrapper[]
     */
    public function getAll(WP_Query $query = null)
    {
        $query = $this->setQueryForThisPostType($query);
        return $this->createWrapperAroundPosts($query->get_posts());
    }

    /**
     * @param WP_Query $query
     * @return int
     */
    public function count(WP_Query $query)
    {
        $query = $this->setQueryForThisPostType($query);
        return $query->post_count;
    }

    protected function setQueryForThisPostType(WP_Query $query = null)
    {
        if (! $query instanceof WP_Query) {
            $query = new WP_Query();
        }
        $query->set('post_type', $this->cap_slug);
        return $query;
    }

    /**
     * @param WP_Post[] $posts
     * @return PostWrapper[]
     */
    protected function createWrapperAroundPosts($posts){
        $wrapped_posts = [];
        foreach($posts as $post){
            $wrapped_posts[] = $this->createWrapperAroundPost($post);
        }
        return $wrapped_posts;
    }
    /**
     * @param WP_Post $post
     *
     * @return PostWrapper
     */
    protected function createWrapperAroundPost(WP_Post $post)
    {
        $post_wrapper = Context::instance()->useNew(
            $this->class_to_instantiate,
            [$post]
        );

        /**
         * @var $post_wrapper PostWrapper
         */
        return $post_wrapper;
    }

    /**
     * @param int[] $ids
     */
    public function deleteProjects($ids)
    {
        foreach ($ids as $id) {
            $post = get_post($id);
            if (! current_user_can('delete_' . $this->cap_slug . 's', $post)) {
                continue;
            }
            $project = $this->createWrapperAroundPost($post);
            if ($project instanceof Project) {
                $project->delete();
            }
        }
    }

    /**
     * @param $meta_key
     * @param $meta_value
     * @param int $count
     * @return PostWrapper[]
     */
    public function getByPostMeta($meta_key, $meta_value, $count = -1){
        $args = array(
            'posts_per_page'   => $count,
            'orderby'          => 'ID',
            'order'            => 'DESC',
            'post_status'      => 'any',
            'post_type' => 'any',
            'meta_query' => array(
                array(
                    'key'     => $meta_key,
                    'value'   => $meta_value
                ),
            ),
        );
        return $this->createWrapperAroundPosts(get_posts($args));
    }
}
