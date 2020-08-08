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
    public function grant_capabilities()
    {
        $post_types = get_post_types([],'objects');
        foreach($post_types as $post_type){
            if($post_type instanceof WP_Post_Type && $post_type->name === CustomPostTypes::PROJECT){
                break;
            }
        }
        $admin_role = get_role( 'administrator' );
        foreach($post_type->cap as $capability){
            $admin_role->add_cap($capability,true);
        }

    }
}