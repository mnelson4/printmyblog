<?php
/**
 * This function is only included when rendering Print My Blog
 */
/**
 * @param $post
 *
 * @return bool|false|string|WP_Error
 */

function pmb_get_the_post_anchor($post){
	if( ! $post instanceof WP_Post){
		global $post;
	}
	return get_permalink($post);
}

function pmb_the_post_anchor(){
	global $post;
	echo pmb_get_the_post_anchor($post);
}

function pmb_convert_url_to_anchor($url){
	return esc_attr($url);
}