<?php
namespace PrintMyBlog\entities;


use DateTime;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\services\generators\ProjectFileGeneratorBase;
use WP_Post;
use strptime;

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
	const POSTMETA_LAST_DIVISION = '_pmb_last_section_type_';
	const POSTMETA_LAST_LEVEL = '_pmb_last_level_';
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

	/**
	 * Avoids a bit of repetion, when getting, with always appending the format for all the options saved for this project-meta combo.
	 * @param string $key to which the format's slug will automatically get appended.
	 *
	 * @return mixed
	 */
	protected function getPostMetaForFormat($key){
		return get_post_meta(
			$this->project->getWpPost()->ID,
			$key . $this->format->slug(),
			true
		);
	}

	/**
	 * Avoids a bit of repetition, when setting, with always appending the format onto all the options saved for this project-format
	 * combo.
	 * @param $key
	 * @param $value
	 *
	 * @return bool|int
	 */
	protected function setPostMetaForFormat($key, $value){
		return update_post_meta(
			$this->project->getWpPost()->ID,
			$key . $this->format->slug(),
			$value
		);
	}

	protected function deletePostMetaForFormat($key){
		return delete_post_meta(
			$this->project->getWpPost()->ID,
			$key . $this->format->slug()
		);
	}

	/**
	 * @return string like 2020-02-02 02:02:02
	 */
	public function generatedTimeSql(){
		return $this->getPostMetaForFormat(self::POSTMETA_GENERATED);
	}

	public function generatedTimestamp(){
		$d = new DateTime($this->generatedTimeSql());
		return $d->getTimestamp();
	}

	/**
	 * Sets the time the transitionary HTML file was generated for this format.
	 * @return bool
	 */
	public function setIntermediaryGeneratedTime()
	{
		return $this->setPostMetaForFormat(self::POSTMETA_GENERATED, current_time('mysql'));
	}

	/**
	 * Forgets that the intermediary file was ever generated.
	 * @return bool
	 */
	public function clearIntermediaryGeneratedTime()
	{
		return $this->deletePostMetaForFormat(self::POSTMETA_GENERATED);
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
			$design = $this->project->getDesignFor($this->format);
			$this->generator = new $generator_classname($this, $design);
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
		$reasons = $this->getPostMetaForFormat(self::POSTMETA_DIRTY);
		if($reasons){
			return $reasons;
		}
		return [];
	}

	/**
	 * Removes all the dirty reasons. Makes sense when the project file for this format is re-generated
	 * @return success
	 */
	public function clearDirty(){
		$this->deletePostMetaForFormat(self::POSTMETA_DIRTY);
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
		return $this->setPostMetaForFormat(self::POSTMETA_DIRTY,$existing_reasons);
	}

	/**
	 * Deletes the generated files for this project in this format.
	 * @return bool success
	 */
	public function deleteGeneratedFiles()
	{
		$this->clearIntermediaryGeneratedTime();
		return $this->getProjectHtmlGenerator()->deleteFile();
	}

	/**
	 * @return int how far deep the last generated section was.
	 */
	public function getLastLevel(){
		return (int)$this->getPostMetaForFormat(self::POSTMETA_LAST_LEVEL);
	}

	/**
	 * Sets the last-used level. (How many layers deep the last-generated section was.)
	 * @param int $level
	 * @return bool
	 */
	public function setLastLevel($level){
		return $this->setPostMetaForFormat(self::POSTMETA_LAST_LEVEL, (int)$level);
	}


}