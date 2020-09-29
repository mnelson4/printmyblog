<?php


namespace PrintMyBlog\domain;


use PrintMyBlog\system\CustomPostTypes;
use WP_Error;
use WP_Post;

class DefaultProjectContents {
	public function addDefaultContents(){
		foreach($this->getDefaultContents() as $slug => $postargs){
			$post = get_page_by_path($slug, OBJECT, CustomPostTypes::CONTENT);
			if( ! $post instanceof WP_Post){
				$postargs['post_type'] = CustomPostTypes::CONTENT;
				$postargs['post_name'] = $slug;
				$result = wp_insert_post($postargs, true);
				if($result instanceof WP_Error){
					// record and show the error somehow
				}
			}
		}
	}

	/**
	 * Returns an array describing all the default PMB content posts. Keys are their post_name,
	 * values will be passed into wp_insert_post() (plus we'll automatically set the post_type to the PMB content type,
	 * and set post_name using the array key.)
	 * @return array
	 */
	protected function getDefaultContents(){
		return apply_filters(
			'PrintMyBlog\domain\DefaultProjectContents->getDefaultContents()',
			[
				'pmb-title-page' => [
					'post_title' => __('Title Page', 'print-my-blog'),
					'post_content' => '[pmb_title_page]'
				],
				'pmb-toc' => [
					'post_title' => __('Table of Contents', 'print-my-blog'),
					'post_content' => '[pmb_toc]'
				]
			]
		);
	}
}