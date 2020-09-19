<?php


namespace PrintMyBlog\entities;


use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\services\generators\ProjectFileGeneratorBase;

/**
 * Class ProjectGeneration
 * Handles logic relating to creating a file for a project.
 * (Sure we could stuff all this into PrintMyBlog\orm\entities\Project, but all the methods would need to receive a
 * format parameter which makes it clear its best in a separate class.)
 *
 * @package PrintMyBlog\orm\entities
 */
class ProjectGeneration {
	const POSTMETA_GENERATED = '_pmb_generated_';
	const POSTMETA_DIRTY = '_pmb_dirty_';
	/**
	 * @var Project
	 */
	private $project;
	/**
	 * @var FileFormat
	 */
	private $format;
	/**
	 * @var ProjectFileGeneratorBase
	 */
	protected $generator;

	public function __construct(Project $project, FileFormat $format){

		$this->project = $project;
		$this->format = $format;
	}

	/**
	 * Gets whether or not the transitionary HTML file has been generated for making the specified format.
	 * @param string|FileFormat $format
	 *
	 * @return bool
	 */
	public function isGenerated()
	{
		return (bool)$this->generatedTimeSql();
	}

	public function generatedTimeSql(){
		return get_post_meta($this->project->getWpPost()->ID, $this->generatedPostMetaKey(), true);
	}

	/**
	 * @return int
	 */
	public function generatedTimestamp(){
		return strtotime($this->generatedTimeSql());
	}

	/**
	 * Gets the postmeta key for this file generation
	 * @return string
	 */
	protected function generatedPostMetaKey()
	{
		return self::POSTMETA_GENERATED . $this->format->slug();
	}

	/**
	 * Sets the time the transitionary HTML file was generated for this format.
	 * @return bool
	 */
	public function setIntermediaryGeneratedTime()
	{
		return update_post_meta($this->project->getWpPost()->ID,$this->generatedPostMetaKey(), current_time('mysql'));
	}

	/**
	 * Forgets that the intermediary file was ever generated.
	 * @return bool
	 */
	public function clearIntermediaryGeneratedTime()
	{
		return delete_post_meta($this->project->getWpPost()->ID, $this->generatedPostMetaKey());
	}

	/**
	 * Gets the URL of the intermediary file
	 * @return string
	 */
	public function getGeneratedIntermediaryFileUrl(){
		$upload_dir_info = wp_upload_dir();
		return $upload_dir_info['baseurl'] . '/pmb/generated/' . $this->project->code() . '/' . $this->project->getWpPost()->post_name . '.html';
	}

	public function generatedFileUrl(){
		return null;
	}

	/**
	 * Gets the filepath to the intermediary generated file.
	 * @return string
	 */
	public function getGeneratedIntermediaryFilePath()
	{
		return $this->getGeneratedIntermediaryFileFolderPath() . $this->project->getWpPost()->post_name . '.html';
	}

	/**
	 * Returns the filepath to the folder containing the generated file(s).
	 * @return string
	 */
	public function getGeneratedIntermediaryFileFolderPath()
	{
		$upload_dir_info = wp_upload_dir();
		return str_replace(
			'..',
			'',
			$upload_dir_info['basedir'] . '/pmb/generated/' . $this->project->code() . '/'
		);
	}

	/**
	 * @param FileFormat|string $format
	 * @return bool complete
	 */
	public function generateIntermediaryFile()
	{
		$complete = $this->getProjectHtmlGenerator()->generateHtmlFile();
		if($complete){
			$this->setIntermediaryGeneratedTime();
		}
		return $complete;
	}

	/**
	 * @param FileFormat|string $format
	 *
	 * @return ProjectFileGeneratorBase
	 */
	public function getProjectHtmlGenerator()
	{
		if( ! $this->generator instanceof ProjectFileGeneratorBase){
			$generator_classname = $this->format->generatorClassname();
			$this->generator = new $generator_classname($this);
		}
		return $this->generator;
	}

	/**
	 * @return Project
	 */
	public function getProject() {
		return $this->project;
	}

	/**
	 * @return FileFormat
	 */
	public function getFormat() {
		return $this->format;
	}

	/**
	 * Returns whether or not the last generation of this project for this format is stale/needs-to-be-updated.
	 * @return bool
	 */
	public function isDirty(){
		return (bool)$this->getDirtyReasons();
	}

	/**
	 * Gets all the reasons this project generation may be dirty.
	 * @return array
	 */
	public function getDirtyReasons(){
		$reasons = get_post_meta(
			$this->project->getWpPost()->ID,
			$this->dirtyPostmetaName(),
			true
		);
		if($reasons){
			return $reasons;
		}
		return [];
	}

	/**
	 * Removes all the dirty reasons. Makes sense when the project file for this format is re-generated
	 */
	public function clearDirty(){
		delete_post_meta(
			$this->project->getWpPost()->ID,
			$this->dirtyPostmetaName()
		);
	}

	/**
	 * Adds another reason this project generation is dirty and should be re-done.
	 *
	 * @param string $key used to avoid duplicates
	 * @param string $dirty_reason translated string to be shown to end user.
	 * @return bool
	 */
	public function addDirtyReason($key, $dirty_reason){
		$existing_reasons = $this->getDirtyReasons();
		$existing_reasons[$key] = $dirty_reason;
		return update_post_meta(
			$this->project->getWpPost()->ID,
			$this->dirtyPostmetaName(),
			$existing_reasons
		);
	}

	protected function dirtyPostmetaName(){
		return self::POSTMETA_DIRTY . $this->format->slug();
	}

	/**
	 * Deletes the generated files for this project in this format.
	 * @return bool success
	 */
	public function deleteGeneratedFiles()
	{
		return $this->getProjectHtmlGenerator()->deleteFile();
	}
}