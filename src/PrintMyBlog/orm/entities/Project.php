<?php


namespace PrintMyBlog\orm\entities;

use PrintMyBlog\db\PartFetcher;
use PrintMyBlog\domain\FileFormats;
use PrintMyBlog\entities\Format;
use PrintMyBlog\orm\managers\DesignManager;
use PrintMyBlog\services\ProjectHtmlGenerator;
use Twine\orm\entities\PostWrapper;
use WP_Post;
use WP_Query;

/**
 * Class Project
 * @package PrintMyBlog\orm
 * Class that wraps a WP_Post, but also stores related info like parts, and has related methods.
 */
class Project extends PostWrapper{

	const POSTMETA_GENERATED = '_pmb_generated';
	const POSTMETA_CODE = '_pmb_code';
	const POSTMETA_FORMAT = '_pmb_format';
	const POSTMETA_DESIGN = '_pmb_design_for_';

	/**
	 * @var PartFetcher
	 */
	protected $part_fetcher;

	/**
	 * @var ProjectHtmlGenerator
	 */
	protected $html_generator;
	/**
	 * @var FileFormats
	 */
	protected $format_manager;
	/**
	 * @var DesignManager
	 */
	protected $design_manager;

	public function inject(
		PartFetcher $part_fetcher,
		FileFormats $format_manager,
		DesignManager $design_manager
	)
	{
		$this->part_fetcher = $part_fetcher;
		$this->format_manager = $format_manager;
		$this->design_manager = $design_manager;
	}

	/**
	 * Sets the project's title and immediately saves it.
	 * @param $title
	 *
	 * @return int|\WP_Error
	 */
	public function setTitle($title){
		return wp_update_post(
			[
				'ID' => $this->getWpPost()->ID,
				'post_title' => $title
			]
		);
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

	/**
	 * Gets the filepath to the main generated file.
	 * @return string
	 */
	public function generatedHtmlFilePath()
	{
		return $this->generatedHtmlFileFolderPath() . $this->getWpPost()->post_name . '.html';
	}

	/**
	 * Returns the filepath to the folder containing the generated file(s).
	 * @return string
	 */
	public function generatedHtmlFileFolderPath()
	{
		$upload_dir_info = wp_upload_dir();
		return str_replace(
			'..',
			'',
			$upload_dir_info['basedir'] . '/pmb/generated/' . $this->code() . '/'
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
	 * @param $project_format_slug
	 *
	 * @return bool
	 */
	public function isFormatSelected($project_format_slug){
		return in_array(
			$project_format_slug,
			$this->getFormatsSelected()
		);
	}

	/**
	 * Gets the selected formats. Note: it's possible for a format to NOT be selected but still have a chosen design.
	 * @return array of selected format slugs
	 */
	public function getFormatsSelected(){
		return get_post_meta(
			$this->getWpPost()->ID,
			self::POSTMETA_FORMAT,
			false
		);
	}

	/**
	 * @param $new_formats
	 */
	public function setFormatsSelected($new_formats){
		$previous_formats = $this->getFormatsSelected();

		foreach($this->format_manager->getFormats() as $format){
			if(in_array($format->slug(), $new_formats)){
				// It's requested to make this a selected format...
				if(! in_array($format->slug(), $previous_formats)){
					// if it wasn't already, add it.
					add_post_meta(
						$this->getWpPost()->ID,
						self::POSTMETA_FORMAT,
						$format->slug()
					);
				}
				// if it's already selected, no need to do anything.
			} else {
				// We want it remove it...
				if(in_array($format->slug(), $previous_formats)){
					// and it was previously a selected format.
					delete_post_meta(
						$this->getWpPost()->ID,
						self::POSTMETA_FORMAT,
						$format->slug()
					);
				}
				// If it wasn't previously selected, no need to change anything.
			}
		}
	}

	/**
	 * Gets the slug of the design to use for the format specified.
	 * @param $format_slug
	 *
	 * @return string
	 */
	public function getDesignSlugFor($format_slug){
		$value = get_post_meta(
			$this->getWpPost()->ID,
			self::POSTMETA_DESIGN . $format_slug,
			true
		);
		if($value){
			return $value;
		}
		return 'classic_' . $format_slug;
	}

	/**
	 * Gets the design object for this project in the given format.
	 *
	 * @param string|Format $format
	 *
	 * @return Design|null
	 */
	public function getDesignFor( $format){
		if ( $format instanceof Format){
			$format = $format->slug();
		}
		$design_slug = $this->getDesignSlugFor( $format);
		return $this->design_manager->getBySlug($design_slug);
	}

	/**
	 * Gets an the chosen designs for the chosen formats.
	 * Keys are format slugs, values are design slugs.
	 * @return array
	 */
	public function getDesigns()
	{
		$designs = [];
		foreach($this->format_manager->getFormats() as $format){
			$design = $this->getDesignFor($format->slug());
			if($design){
				$designs[$format->slug()] = $design;
			}
		}
		return $designs;
	}

	/**
	 * Sets the project's chosen design for the specified format.
	 * @param $format_slug
	 * @param $design_slug
	 *
	 * @return bool success
	 */
	public function setDesignFor($format_slug, $design_slug){
		return (bool)update_post_meta(
			$this->getWpPost()->ID,
			self::POSTMETA_DESIGN . $format_slug,
			$design_slug
		);
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
		return parent::delete();
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