<?php

namespace PrintMyBlog\system;

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
	                'publish_posts' => 'publish_projects',
	                'edit_posts' => 'edit_projects',
	                'edit_others_posts' => 'edit_others_projects',
	                'delete_posts' => 'delete_projects',
	                'delete_others_posts' => 'delete_others_projects',
	                'read_private_posts' => 'read_private_projects',
//	                'edit_post' => 'edit_project',
//	                'delete_post' => 'delete_project',
//	                'read_post' => 'read_project',
                ),
            ]
        );
	    add_filter( 'map_meta_cap', [$this,'map_project_meta_cap'], 10, 4 );
    }

	function map_project_meta_cap( $caps, $cap, $user_id, $args ) {

		/* If editing, deleting, or reading a project, get the post and post type object. */
		if ( 'edit_project' == $cap || 'delete_project' == $cap || 'read_project' == $cap ) {
			$post = get_post( $args[0] );
			$post_type = get_post_type_object( $post->post_type );

			/* Set an empty array for the caps. */
			$caps = array();
		}

		/* If editing a project, assign the required capability. */
		if ( 'edit_project' == $cap ) {
			if ( $user_id == $post->post_author )
				$caps[] = $post_type->cap->edit_posts;
			else
				$caps[] = $post_type->cap->edit_others_posts;
		}

		/* If deleting a project, assign the required capability. */
		elseif ( 'delete_project' == $cap ) {
			if ( $user_id == $post->post_author )
				$caps[] = $post_type->cap->delete_posts;
			else
				$caps[] = $post_type->cap->delete_others_posts;
		}

		/* If reading a private project, assign the required capability. */
		elseif ( 'read_project' == $cap ) {

			if ( 'private' != $post->post_status )
				$caps[] = 'read';
			elseif ( $user_id == $post->post_author )
				$caps[] = 'read';
			else
				$caps[] = $post_type->cap->read_private_posts;
		}

		/* Return the capabilities required by the user. */
		return $caps;
	}
}