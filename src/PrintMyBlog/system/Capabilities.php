<?php

namespace PrintMyBlog\system;

use WP_Post_Type;

/**
 * Class Capabilities
 *
 * Description
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
class Capabilities
{
    /**
     * @var CustomPostTypes
     */
    private $custom_post_types;

    /**
     * @param CustomPostTypes $custom_post_types
     */
    public function inject(CustomPostTypes $custom_post_types)
    {
        $this->custom_post_types = $custom_post_types;
    }

    /**
     * Gives capabilities for each custom post type.
     */
    public function grantCapabilities()
    {
        $post_types = get_post_types([], 'objects');
        $pmb_post_types = $this->custom_post_types->getPostTypes();
        foreach ($post_types as $post_type) {
            if ($post_type instanceof WP_Post_Type && in_array($post_type->name, $pmb_post_types)) {
                $this->grantCapsForCPT($post_type);
            }
        }
    }

    /**
     * Grants the post's caps to the specified role.
     * @param WP_Post_Type $post_type
     * @param string $role
     */
    public function grantCapsForCPT($post_type, $role = 'administrator')
    {
        $role = get_role($role);
        foreach ($post_type->cap as $capability) {
            $role->add_cap($capability, true);
        }
    }
}
