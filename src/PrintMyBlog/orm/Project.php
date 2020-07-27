<?php


namespace PrintMyBlog\orm;

use PrintMyBlog\db\PartFetcher;
use PrintMyBlog\services\ProjectHtmlGenerator;
use WP_Query;

/**
 * Class Project
 * @package PrintMyBlog\orm
 * Class that wraps a WP_Post, but also stores related info like parts, and has related methods.
 */
class Project {

	const POSTMETA_GENERATED = '_generated';
	const POSTMETA_CODE = '_code';



	/**
	 * @var WP_Post
	 */
	protected $wp_post;

	/**
	 * @var PartFetcher
	 */
	protected $part_fetcher;

	public function __construct(WP_Post $post){
		$this->wp_post = $post;
	}

	public function inject(PartFetcher $part_fetcher)
	{
		$this->part_fetcher = $part_fetcher;
	}

	/**
	 * @return WP_Post
	 */
	public function getWpPost() {
		return $this->wp_post;
	}

	public function generated()
	{
		return (bool)get_post_meta($this->getWpPost()->ID, self::POSTMETA_GENERATED, true);
	}

	/**
	 * @return success
	 */
	public function setGenerated($new_value)
	{
		return update_post_meta($this->getWpPost()->ID,self::POSTMETA_GENERATED, (bool)$new_value);
	}

	/**
	 * @return string
	 */
	public function code()
	{
		return (string)get_post_meta($this->getWpPost()->ID, self::POSTMETA_CODE);
	}

	/**
	 * @return string
	 */
	public function generatedHtmlFileUrl()
	{
		$upload_dir_info = wp_upload_dir();
		return $upload_dir_info['url'] . '/pmb/generated/' . $this->code() . '/' . $this->getWpPost()->post_name . '.html';
	}

	public function generatedHtmlFilePath()
	{
		$upload_dir_info = wp_upload_dir();
		return str_replace(
			'..',
			'',
			$upload_dir_info['path'] . '/pmb/generated/' . $this->code() . '/' . $this->getWpPost()->post_name . '.html'
		);
	}

	/**
	 * @return bool success
	 */
	public function generateHtmlFile()
	{
		$generator = new ProjectHtmlGenerator($this);
		return $generator->generateHtmlFile();
	}

	/**
	 * Gets the database rows indicating the parts
	 * @return int[]
	 */
	public function getPartPostIds()
	{
		$this->part_fetcher->fetchPartPostIdsUnordered($this->getWpPost()->ID);
	}
}