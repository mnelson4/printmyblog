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
    const DESIGN = 'pmb_design';

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
		    function( $caps, $cap, $user_id, $args) use ($cap_slug) {
			    return $this->map_meta_cap( $caps, $cap, $user_id, $args, $cap_slug );
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
		    function( $caps, $cap, $user_id, $args) use ($cap_slug) {
				return $this->map_meta_cap( $caps, $cap, $user_id, $args, $cap_slug );
		    },
		    10,
		    4
	    );

	    register_post_type( 'pmb_content',
		    // WordPress CPT Options Start
		    array(
			    'labels' => array(
				    'name' => __( 'Project Contents' ),
				    'singular_name' => __( 'Project Content' )
			    ),
			    'has_archive' => true,
			    'public' => false,
			    'show_ui' => true,
			    'show_in_menu' => PMB_ADMIN_PAGE_SLUG,
			    'rewrite' => array('slug' => 'pmb'),
			    'show_in_rest' => true,
			    'supports' => array('title', 'editor', 'revisions', 'author','thumbnail', 'custom-fields'),
			    'taxonomies' => array('category', 'post_tag')
		    )
	    );
    }

	function map_meta_cap( $caps, $cap, $user_id, $args, $cap_slug ) {

		/* If editing, deleting, or reading a project, get the post and post type object. */
		if ( 'edit_' . $cap_slug == $cap || 'delete_' . $cap_slug == $cap || 'read_' . $cap_slug == $cap ) {
			$post = get_post( $args[0] );
			$post_type = get_post_type_object( $post->post_type );

			/* Set an empty array for the caps. */
			$caps = array();
		}

		/* If editing a project, assign the required capability. */
		if ( 'edit_' . $cap_slug == $cap ) {
			if ( $user_id == $post->post_author )
				$caps[] = $post_type->cap->edit_posts;
			else
				$caps[] = $post_type->cap->edit_others_posts;
		}

		/* If deleting a project, assign the required capability. */
		elseif ( 'delete_' . $cap_slug == $cap ) {
			if ( $user_id == $post->post_author )
				$caps[] = $post_type->cap->delete_posts;
			else
				$caps[] = $post_type->cap->delete_others_posts;
		}

		/* If reading a private project, assign the required capability. */
		elseif ( 'read_' . $cap_slug == $cap ) {

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