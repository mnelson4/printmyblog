<?php

namespace PrintMyBlog\services\generators;

use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\orm\entities\ProjectSection;
use PrintMyBlog\services\ExternalResourceCache;
use Twine\services\filesystem\File;

/**
 * Class HtmlBaseGenerator
 * Generators that create an HTML intermediary file.
 * @package PrintMyBlog\services\generators
 */
abstract class HtmlBaseGenerator extends ProjectFileGeneratorBase
{

    /**
     * @var File
     */
    protected $file_writer;

    /**
     * @global Project $pmb_project
     * @global Design $pmb_design
     */
    protected function startGenerating()
    {
        parent::startGenerating();
        // Try to get enqueued after the theme, if we're doing that, so we get precedence.
        add_action('wp_enqueue_scripts', [$this, 'enqueueStylesAndScripts'], 1000);
        do_action('pmb_pdf_generation_start', $this->project_generation, $this->design);
        add_filter('should_load_block_editor_scripts_and_styles', '__return_true');
        add_action('pmb_pro_print_window', [$this, 'addPrintWindowToPage']);
        $this->writeDesignTemplateInDivision(DesignTemplate::IMPLIED_DIVISION_PROJECT);
    }

    /**
     * Writes html to the file.
     * @return void
     */
    protected function generateSection()
    {
        global $post;
        // determine which template to use, depending on the current section's height and how template specified
        if ($post->pmb_section instanceof ProjectSection) {
            $this->project_generation->setLastSection($post->pmb_section);
            $template = $post->pmb_section->getTemplate();
            $template = $this->design->getDesignTemplate()->resolveSectionTemplateToUse($template);
            if ($template) {
                $this->writeDesignTemplateInDivision($template);
            } else {
                $this->writeDesignTemplateInDivision(
                    pmb_map_section_to_division($post->pmb_section)
                );
            }
        }
    }

    /**
     * Finishes writing to html file.
     * @return void
     */
    protected function finishGenerating()
    {
        $this->writeDesignTemplateInDivision(DesignTemplate::IMPLIED_DIVISION_PROJECT, false);
        do_action('\PrintMyBlog\services\generators\ProjectFileGeneratorBase->finishGenerating', $this);
    }

    /**
     * @param string $template_file
     * @param array $context
     */
    protected function writeTemplateToFile($template_file, $context = [])
    {
        $this->getFileWriter()->write(
            '<!-- pmb template: ' . $template_file . '-->' . $this->getHtmlFrom($template_file, $context)
        );
    }

    /**
     * @param string $division
     * @param bool $beginning whether to show the beginning, or end, of this division.
     * @param array $context
     */
    protected function writeDesignTemplateInDivision($division, $beginning = true, $context = [])
    {
        $this->writeTemplateToFile(
            $this->design->getDesignTemplate()->getTemplatePathToDivision(
                $division,
                $beginning
            ),
            $context
        );
    }

    /**
     * @return File
     */
    protected function getFileWriter()
    {
        if (! $this->file_writer instanceof File) {
            $this->file_writer = new File($this->project_generation->getGeneratedIntermediaryFilePath());
        }
        return $this->file_writer;
    }

    /**
     * @param string $division
     */
    protected function writeClosingForDesignTemplate($division)
    {
        if ($this->design->getDesignTemplate()->templateFileExists($division, false)) {
            $this->writeDesignTemplateInDivision($division, false);
        } else {
            // If the design template didn't delcare that file, it's ok. Assume it just ends in a div.
            $this->getFileWriter()->write('</div>');
        }
    }

    /**
     * @param ProjectSection|null $last_section
     * @param ProjectSection|null $current_section
     */
    protected function maybeGenerateDivisionStart(
        ProjectSection $last_section = null,
        ProjectSection $current_section = null
    ) {
        if (! $current_section instanceof ProjectSection) {
            return;
        }

        $last_section_placement = null;
        if ($last_section instanceof ProjectSection) {
            $last_section_placement = $last_section->getPlacement();
        }
        if (
            $last_section_placement !== $current_section->getPlacement()
            && $this->design->getDesignTemplate()->supports($current_section->getPlacement())
        ) {
            $this->writeDesignTemplateInDivision($current_section->getPlacement());
        }
    }

    /**
     * @param ProjectSection|null $previous_section
     * @param ProjectSection|null $current_section
     */
    protected function maybeGenerateDivisionEnd(
        ProjectSection $previous_section = null,
        ProjectSection $current_section = null
    ) {
        if (! $previous_section) {
            // no transition necessary
            return;
        }
        $iterate_depth = $previous_section->getDepth();
        $current_depth = 0;
        $current_placement = null;
        if ($current_section instanceof ProjectSection) {
            $current_depth = $current_section->getDepth();
            $current_placement = $current_section->getPlacement();
        }
        while ($iterate_depth >= $current_depth) {
            $this->writeClosingForDesignTemplate(
                pmb_map_section_to_division(
                    $previous_section
                )
            );
            $iterate_depth--;
        }
        // take care of closing front_matter, main_matter, and back_matter
        if ($previous_section->getPlacement() !== $current_placement) {
            $this->writeClosingForDesignTemplate(
                pmb_map_section_to_division($previous_section)
            );
        }
    }

    /**
     * Adds all the main matters to the html file.
     */
    protected function generateMainMatter()
    {
        $this->generateSections($this->project->getFlatSections(1000, 0, false));
    }


    /**
     * Deletes the generated HTML file, if it exists.
     * @return bool
     */
    public function deleteFile()
    {
        return $this->getFileWriter()->delete();
    }

    /**
     * Enqueues the scripts and styles
     * @return void
     */
    abstract public function enqueueStylesAndScripts();

    /**
     * Gets the URL back to the page to generate step.
     * @return string
     */
    abstract protected function addPrintWindowToPage();

    /**
     * @return string
     */
    protected function getUrlBackToGenerateStep()
    {
        return add_query_arg(
            [
                'ID' => $this->project->getWpPost()->ID,
                'action' => \PrintMyBlog\controllers\Admin::SLUG_ACTION_EDIT_PROJECT,
                'subaction' => \PrintMyBlog\entities\ProjectProgress::GENERATE_STEP,
            ],
            admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
        );
    }
}
