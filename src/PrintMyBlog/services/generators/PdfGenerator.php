<?php

namespace PrintMyBlog\services\generators;

use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\orm\entities\ProjectSection;
use Twine\services\filesystem\File;

/**
 * Class PdfIntermediaryHtmlGenerator
 * Generates an intermediary HTML file on the server which DocRaptor, or the browser, can use to generate a PDF file.
 * @package PrintMyBlog\services\generators
 */
class PdfGenerator extends ProjectFileGeneratorBase
{
    /**
     * @var File
     */
    protected $file_writer;

    /**
     * Enqueues themes and styles we'll use on this AJAX request.
     */
    public function enqueueStylesAndScripts(){
        wp_enqueue_style('pmb_print_common');
        wp_enqueue_style('pmb_pro_page');
        wp_enqueue_style('pmb-plugin-compatibility');
        wp_enqueue_script('pmb-beautifier-functions');
        $style_file = $this->getDesignDir() . 'assets/style.css';
        $script_file = $this->getDesignDir() . 'assets/script.js';
        if (file_exists($style_file)) {
            wp_enqueue_style(
                'pmb-design',
                $this->getDesignAssetsUrl() . 'style.css',
                ['pmb_print_common', 'pmb-plugin-compatibility'],
                filemtime($style_file),
                null
            );
        }
        if (file_exists($script_file)) {
            wp_enqueue_script(
                'pmb-design',
                $this->getDesignAssetsUrl() . 'script.js',
                ['jquery', 'pmb-beautifier-functions'],
                filemtime($script_file)
            );
        }
    }

    /**
     * @global Project $pmb_project
     * @global Design $pmb_design
     */
    protected function startGenerating()
    {
        // Try to get enqueued after the theme, if we're doing that, so we get precedence.
        add_action('wp_enqueue_scripts', [$this,'enqueueStylesAndScripts'], 1000);
        do_action('pmb_pdf_generation_start', $this->project_generation, $this->design);
        $this->writeDesignTemplateInDivision(DesignTemplate::IMPLIED_DIVISION_PROJECT);
    }



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

    protected function finishGenerating()
    {
        $this->writeDesignTemplateInDivision(DesignTemplate::IMPLIED_DIVISION_PROJECT, false);
    }


    /**
     * @param string $template_file
     */
    protected function writeTemplateToFile($template_file)
    {
        $this->getFileWriter()->write(
            '<!-- pmb template: ' . $template_file . '-->' . $this->getHtmlFrom($template_file)
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
     * @param $division
     * @param bool $beginning whether to show the beginning, or end, of this division.
     */
    protected function writeDesignTemplateInDivision($division, $beginning = true)
    {
        $this->writeTemplateToFile(
            $this->design->getDesignTemplate()->getTemplatePathToDivision(
                $division,
                $beginning
            )
        );
    }

    protected function writeClosingForDesignTemplate($division)
    {
        if ($this->design->getDesignTemplate()->templateFileExists($division, false)) {
            $this->writeDesignTemplateInDivision($division, false);
        } else {
            // If the design template didn't delcare that file, it's ok. Assume it just ends in a div.
            $this->getFileWriter()->write('</div>');
        }
    }

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
     * @param int $previous_depth
     * @param int $current_depth
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
}
