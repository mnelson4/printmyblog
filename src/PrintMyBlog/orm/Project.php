<?php


namespace PrintMyBlog\orm;

use PrintMyBlog\db\PartFetcher;
use PrintMyBlog\services\ProjectHtmlGenerator;
use WP_Post;
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

	/**
	 * @var ProjectHtmlGenerator
	 */
	protected $html_generator;

	/**
	 * Project constructor.
	 *
	 * @param WP_Post|int $post object or ID
	 */
	public function __construct($post){
		if(is_int($post) || is_string($post)){
			$post = get_post($post);
		}
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
		return (string)get_post_meta($this->getWpPost()->ID, self::POSTMETA_CODE, true);
	}

	/**
	 * Sets the project's code in postmeta.
	 *
	 * @return bool
	 */
	public function setCode()
	{
		return (bool)add_post_meta($this->getWpPost()->ID, self::POSTMETA_CODE, wp_generate_password(20,false));
	}

	/**
	 * @return string
	 */
	public function generatedHtmlFileUrl()
	{
		$upload_dir_info = wp_upload_dir();
		return $upload_dir_info['baseurl'] . '/pmb/generated/' . $this->code() . '/' . $this->getWpPost()->post_name . '.html';
	}

	public function generatedHtmlFilePath()
	{
		$upload_dir_info = wp_upload_dir();
		return str_replace(
			'..',
			'',
			$upload_dir_info['basedir'] . '/pmb/generated/' . $this->code() . '/' . $this->getWpPost()->post_name . '.html'
		);
	}

	/**
	 * @return bool complete
	 */
	public function generateHtmlFile()
	{
		$complete = $this->getProjectHtmlGenerator()->generateHtmlFile();
		if($complete){
			$this->setGenerated(true);
		}
		return $complete;
	}

	/**
	 * Gets the database rows indicating the parts
	 * @return int[]
	 */
	public function getPartPostIds()
	{
		return $this->part_fetcher->fetchPartPostIdsUnordered($this->getWpPost()->ID);
	}

	/**
	 * @return ProjectHtmlGenerator
	 */
	protected function getProjectHtmlGenerator()
	{
		if( ! $this->html_generator instanceof ProjectHtmlGenerator){
			$this->html_generator = new ProjectHtmlGenerator($this);
		}
		return $this->html_generator;
	}

	/**
	 *
	 * return bool success
	 */
	public function delete()
	{
		$successes = $this->part_fetcher->clearPartsFor($this->getWpPost()->ID);
		if( $successes === false){
			return false;
		}
		$success = $this->getProjectHtmlGenerator()->deleteHtmlFile();
		if( ! $success ){
			return false;
		}
		return wp_delete_post($this->getWpPost()->ID);
	}

	/**
	 * Clears out the generated files. Useful in case the project has changed and so should be re-generated.
	 * @return bool
	 */
	public function clearGeneratedFiles()
	{
		$this->setGenerated(false);
		$this->getProjectHtmlGenerator()->deleteHtmlFile();
		return true;
	}
}