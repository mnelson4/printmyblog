<?php

namespace PrintMyBlog\entities;

use DateTime;
use PrintMyBlog\factories\ProjectFileGeneratorFactory;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\orm\entities\ProjectSection;
use PrintMyBlog\orm\managers\ProjectSectionManager;
use PrintMyBlog\services\generators\ProjectFileGeneratorBase;
use WP_Post;

/**
 * Class ProjectGeneration
 * Handles logic relating to creating a file for a project.
 * (Sure we could stuff all this into PrintMyBlog\orm\entities\Project, but all the methods would need to receive a
 * format parameter which makes it clear its best in a separate class.)
 *
 * @package PrintMyBlog\orm\entities
 */
class ProjectGeneration
{
    const POSTMETA_GENERATED = '_pmb_generated_';
    const POSTMETA_DIRTY = '_pmb_dirty_';
    const POSTMETA_LAST_DIVISION = '_pmb_last_section_type_';
    const POSTMETA_LAST_SECTION_ID = '_pmb_last_section_';
    const POSTMETA_ERROR = '_pmb_error_';
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
    /**
     * @var ProjectSectionManager
     */
    protected $section_manager;

    /**
     * @var ProjectSection|null
     */
    protected $last_section;
    /**
     * @var ProjectFileGeneratorFactory
     */
    protected $project_generator_factory;

    /**
     * ProjectGeneration constructor.
     * @param Project $project
     * @param FileFormat $format
     */
    public function __construct(Project $project, FileFormat $format)
    {
        $this->project = $project;
        $this->format = $format;
    }

    /**
     * @param ProjectSectionManager $section_manager
     * @param ProjectFileGeneratorFactory $project_generator_factory
     */
    public function inject(
        ProjectSectionManager $section_manager,
        ProjectFileGeneratorFactory $project_generator_factory
    ) {
        $this->section_manager = $section_manager;
        $this->project_generator_factory = $project_generator_factory;
    }

    /**
     * Gets whether or not the transitionary HTML file has been generated for making the specified format.
     *
     * @return bool
     */
    public function isGenerated()
    {
        return (bool)$this->generatedTimeSql();
    }

    /**
     * Avoids a bit of repetition, when getting, with always appending the format for all the options saved for this
     * project-meta combo.
     * @param string $key to which the format's slug will automatically get appended.
     *
     * @return mixed
     */
    protected function getPostMetaForFormat($key)
    {
        return get_post_meta(
            $this->project->getWpPost()->ID,
            $key . $this->format->slug(),
            true
        );
    }

    /**
     * Avoids a bit of repetition, when setting, with always appending the format onto all the options saved for this
     * project-format
     * combo.
     * @param string $key
     * @param mixed $value
     *
     * @return bool|int
     */
    protected function setPostMetaForFormat($key, $value)
    {
        return update_post_meta(
            $this->project->getWpPost()->ID,
            $key . $this->format->slug(),
            $value
        );
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function deletePostMetaForFormat($key)
    {
        return delete_post_meta(
            $this->project->getWpPost()->ID,
            $key . $this->format->slug()
        );
    }

    /**
     * @return string like 2020-02-02 02:02:02
     */
    public function generatedTimeSql()
    {
        return $this->getPostMetaForFormat(self::POSTMETA_GENERATED);
    }

    /**
     * Gets a timestamp showing when this file was generated.
     * @return int
     * @throws \Exception
     */
    public function generatedTimestamp()
    {
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
     * Gets the URL of the intermediary file ("Pro Print Page")
     * @return string
     */
    public function getGeneratedIntermediaryFileUrl()
    {
        $upload_dir_info = wp_upload_dir();
        if (is_ssl()) {
            $start = str_replace('http://', 'https://', $upload_dir_info['baseurl']);
        } else {
            $start = $upload_dir_info['baseurl'];
        }
        return apply_filters(
            '\PrintMyBlog\entities\ProjectGeneration::getGeneratedIntermediaryFileUrl return',
            $start . '/pmb/generated/' . $this->project->code() . '/' . $this->format->slug()
            . '/' . rawurlencode($this->getFileName()) . '.html?uniqueness=' . current_time('timestamp'),
            $this
        );
    }

    /**
     * The name of the file (minus extension)
     * @return string
     */
    protected function getFileName()
    {
        return $this->project->getWpPost()->post_name;
    }

    /**
     * @return string
     */
    public function getFileNameWithExtension()
    {
        return $this->getFileName() . '.' . $this->format->extension();
    }

    /**
     * @return null
     */
    public function generatedFileUrl()
    {
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
            $upload_dir_info['basedir'] . '/pmb/generated/' . $this->project->code() . '/' . $this->format->slug() . '/'
        );
    }

    /**
     * @return bool complete
     */
    public function generateIntermediaryFile()
    {
        $complete = $this->getProjectHtmlGenerator()->generateHtmlFile();
        if ($complete) {
            $this->setIntermediaryGeneratedTime();
        }
        return $complete;
    }

    /**
     *
     * @return ProjectFileGeneratorBase
     */
    public function getProjectHtmlGenerator()
    {
        if (! $this->generator instanceof ProjectFileGeneratorBase) {
            $generator_classname = $this->format->generatorClassname();
            $design = $this->project->getDesignFor($this->format);
            $this->generator = $this->project_generator_factory->create($generator_classname, $this, $design);
        }
        return $this->generator;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @return FileFormat
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Returns whether or not the last generation of this project for this format is stale/needs-to-be-updated.
     * @return bool
     */
    public function isDirty()
    {
        return (bool)$this->getDirtyReasons();
    }

    /**
     * Gets all the reasons this project generation may be dirty.
     * @return array
     */
    public function getDirtyReasons()
    {
        $reasons = $this->getPostMetaForFormat(self::POSTMETA_DIRTY);
        if ($reasons) {
            return $reasons;
        }
        return [];
    }

    /**
     * Removes all the dirty reasons. Makes sense when the project file for this format is re-generated
     */
    public function clearDirty()
    {
        $this->deletePostMetaForFormat(self::POSTMETA_DIRTY);
    }

    /**
     * Adds another reason this project generation is dirty and should be re-done.
     *
     * @param string $key used to avoid duplicates
     * @param string $dirty_reason translated string to be shown to end user.
     * @return bool
     */
    public function addDirtyReason($key, $dirty_reason)
    {
        $existing_reasons = $this->getDirtyReasons();
        $existing_reasons[$key] = $dirty_reason;
        return $this->setPostMetaForFormat(self::POSTMETA_DIRTY, $existing_reasons);
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
     * @return int ID
     */
    public function getLastSectionId()
    {
        return (int)$this->getPostMetaForFormat(self::POSTMETA_LAST_SECTION_ID);
    }

    /**
     * @return ProjectSection|null
     */
    public function getLastSection()
    {
        if (! $this->last_section instanceof ProjectSection) {
            $id = $this->getLastSectionId();
            if (! $id) {
                return null;
            }
            $this->last_section = $this->section_manager->getSection($id);
        }
        return $this->last_section;
    }

    /**
     * Sets the last-used level. (How many layers deep the last-generated section was.)
     *
     * @param int $id
     *
     * @return bool
     */
    public function setLastSectionId($id)
    {
        return $this->setPostMetaForFormat(self::POSTMETA_LAST_SECTION_ID, (int)$id);
    }

    /**
     * @param ProjectSection $section
     *
     * @return bool|int
     */
    public function setLastSection(ProjectSection $section)
    {
        $this->last_section = $section;
        return $this->setLastSectionId($section->getId());
    }

    /**
     * @param string $error_message
     */
    public function setLastError($error_message)
    {
        $this->setPostMetaForFormat(self::POSTMETA_ERROR, $error_message);
    }

    /**
     * Gets a string of text from the last error concerning this project generation
     * @return string
     */
    public function getLastError()
    {
        return (string)$this->getPostMetaForFormat(self::POSTMETA_ERROR);
    }
}
