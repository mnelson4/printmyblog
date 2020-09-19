<?php


namespace PrintMyBlog\orm\entities;

use Exception;
use PrintMyBlog\db\PartFetcher;
use PrintMyBlog\domain\DefaultFileFormats;
use PrintMyBlog\entities\FileFormat;
use PrintMyBlog\entities\ProjectGeneration;
use PrintMyBlog\helpers\ArgMagician;
use PrintMyBlog\orm\managers\DesignManager;
use PrintMyBlog\services\config\Config;
use PrintMyBlog\services\FileFormatRegistry;
use PrintMyBlog\services\generators\ProjectFileGeneratorBase;
use Twine\forms\base\FormSectionProper;
use Twine\forms\inputs\FormInputBase;
use Twine\orm\entities\PostWrapper;
use WP_Post;
use WP_Query;

/**
 * Class Project
 * @package PrintMyBlog\orm
 * Class that wraps a WP_Post, but also stores related info like parts, and has related methods.
 */
class Project extends PostWrapper{

	const POSTMETA_CODE = '_pmb_code';
	const POSTMETA_FORMAT = '_pmb_format';
	const POSTMETA_DESIGN = '_pmb_design_for_';

	/**
	 * @var PartFetcher
	 */
	protected $part_fetcher;

	/**
	 * @var ProjectGeneration[]
	 */
	protected $generations = [];

	/**
	 * @var FileFormatRegistry
	 */
	protected $format_registry;

	/**
	 * @var DesignManager
	 */
	protected $design_manager;

	/**
	 * @var Config
	 */
	protected $config;

	public function inject(
		PartFetcher $part_fetcher,
		FileFormatRegistry $format_manager,
		DesignManager $design_manager,
		Config $config
	)
	{
		$this->part_fetcher    = $part_fetcher;
		$this->format_registry = $format_manager;
		$this->design_manager  = $design_manager;
		$this->config          = $config;
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
	 * Gets the database rows indicating the parts
	 * @return int[]
	 */
	public function getPartPostIds()
	{
		return $this->part_fetcher->fetchPartPostIdsUnordered($this->getWpPost()->ID);
	}



	/**
	 * @param $project_format_slug
	 *
	 * @return bool
	 */
	public function isFormatSelected($project_format_slug){
		return in_array(
			$project_format_slug,
			$this->getFormatSlugsSelected()
		);
	}

	/**
	 * Gets the slugs of selected formats. Note: it's possible for a format to NOT be selected but still have a chosen design.
	 * @return array of selected format slugs
	 */
	public function getFormatSlugsSelected(){
		return get_post_meta(
			$this->getWpPost()->ID,
			self::POSTMETA_FORMAT,
			false
		);
	}

	/**
	 * Like Project::getFormatSlugsSelected(), but gets actual FileFormat objects.
	 * @return FileFormat[]
	 */
	public function getFormatsSelected(){
		$format_slugs = $this->getFormatSlugsSelected();
		$formats = [];
		foreach($format_slugs as $slug){
			$formats[$slug] = $this->format_registry->getFormat($slug);
		}
		return $formats;
	}

	/**
	 * @param $new_formats
	 */
	public function setFormatsSelected($new_formats){
		$previous_formats = $this->getFormatSlugsSelected();

		foreach($this->format_registry->getFormats() as $format){
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
	 * @param FileFormat|string $format_slug
	 *
	 * @return int
	 */
	public function getDesignIdFor($format){
		if ( $format instanceof FileFormat){
			$format = $format->slug();
		}
		$value = get_post_meta(
			$this->getWpPost()->ID,
			self::POSTMETA_DESIGN . $format,
			true
		);
		if($value){
			return $value;
		}
		return 0;
	}

	/**
	 * Gets the design object for this project in the given format.
	 *
	 * @param string|FileFormat $format
	 *
	 * @return Design|null
	 */
	public function getDesignFor($format){
		$format = ArgMagician::castToFormatSlug($format);
		$design_id = $this->getDesignIdFor( $format);
		if( $design_id ) {
			return $this->design_manager->getById( $design_id );
		}
		// Ok fallback to default
		return $this->config->getDefaultDesignFor($format);

	}

	/**
	 * Gets an the chosen designs for the chosen formats.
	 * Keys are format slugs, values are design slugs.
	 * @return array
	 */
	public function getDesigns()
	{
		$designs = [];
		foreach($this->format_registry->getFormats() as $format){
			$design = $this->getDesignFor($format->slug());
			if($design){
				$designs[$format->slug()] = $design;
			}
		}
		return $designs;
	}

	/**
	 * Sets the project's chosen design for the specified format.
	 *
	 * @param string|FileFormat $format
	 * @param int|Design $design
	 *
	 * @return bool success
	 */
	public function setDesignFor( $format, $design){
		if($format instanceof FileFormat){
			$format = $format->slug();
		}
		if($design instanceof Design){
			$design = $design->getWpPost()->ID;
		}
		return (bool)update_post_meta(
			$this->getWpPost()->ID,
			self::POSTMETA_DESIGN . $format,
			$design
		);
	}

	/**
	 *
	 * return bool success
	 */
	public function delete()
	{
		$this->part_fetcher->clearPartsFor($this->getWpPost()->ID);
		// delete the generated files for the project too
		foreach($this->getFormatsSelected() as $format){
			$project_generation = new ProjectGeneration($this, $format);
			$project_generation->deleteGeneratedFiles();
		}
		return parent::delete();
	}

	/**
	 * Gets the object with all the logic around generating files for projects, for the given format.
	 * @param FileFormat|string $format
	 *
	 * @return ProjectGeneration
	 */
	public function getGenerationFor($format){
		$format_slug = ArgMagician::castToFormatSlug($format);
		if( ! isset($this->generations[$format_slug]) || ! $this->generations[$format_slug] instanceof ProjectGeneration){
			if( ! $format instanceof FileFormat){
				$format = $this->format_registry->getFormat($format);
			}
			$this->generations[$format_slug] = new ProjectGeneration($this, $format);
		}
		return $this->generations[$format_slug];
	}

	/**
	 * Gets all the project generations of this project
	 * @return ProjectGeneration[]
	 */
	public function getAllGenerations(){
		$generations = [];
		foreach($this->getFormatsSelected() as $format){
			$generations[$format->slug()] = $this->getGenerationFor($format);
		}
		return $generations;
	}

	/**
	 * Clears out the generated files. Useful in case the project has changed and so should be re-generated.
	 * @return bool
	 */
	public function clearGeneratedFiles()
	{
		$project_generation = new ProjectGeneration($this, $this->getFormatsSelected());
		$project_generation->clearIntermediaryGeneratedTime();
		$project_generation->getProjectHtmlGenerator()->deleteFile();
		return true;
	}

	/**
	 * Gets a form that is actually a combination of all the forms for the project's chosen designs.
	 * @param Project $project
	 *
	 * @return FormSectionProper
	 * @throws ImproperUsageException
	 */
	public function getMetaForm(){
		$formats = $this->getFormatSlugsSelected();
		$forms = [];
		foreach($formats as $format){
			$forms[] = $this->getDesignFor($format)->getProjectForm();
		}
		$project_form = new FormSectionProper();
		foreach($forms as $form){
			$project_form->add_subsections($form->subsections(false));
		}
		// If there's a field named "title", set its default to be the post title.
		$title_input = $project_form->get_subsection('title');
		if($title_input instanceof FormInputBase){
			$title_input->set_default($this->getWpPost()->post_title);
		}
		return $form;
	}

	public function getSetting($setting_name) {
		// tries to get the setting from a postmeta
		$setting = $this->getPmbMeta( $setting_name );
		if ( $setting !== null ) {
			return $setting;
		}
		$form    = $this->getMetaForm();
		$section = $form->findSection( $setting_name );
		if ( $section instanceof FormInputBase ) {
			return $section->get_default();
		}
		return null;
	}

	/**
	 * @param $setting_name string
	 * @param $value mixed
	 */
	public function setSetting($setting_name, $value){
		$this->setPmbMeta($setting_name, $value);
	}
}