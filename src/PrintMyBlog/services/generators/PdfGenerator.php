<?php


namespace PrintMyBlog\services\generators;

use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\ProjectSection;
use Twine\services\filesystem\File;

/**
 * Class PdfIntermediaryHtmlGenerator
 * Generates an intermediary HTML file on the server which DocRaptor, or the browser, can use to generate a PDF file.
 * @package PrintMyBlog\services\generators
 */
class PdfGenerator extends ProjectFileGeneratorBase {
	/**
	 * @var File
	 */
	protected $file_writer;

	protected function startGenerating()
	{
		wp_enqueue_style('pmb_print_common');
		wp_enqueue_style('pmb-plugin-compatibility');
		wp_enqueue_script('pmb-beautifier-functions');
		$style_file = $this->getDesignDir() . 'assets/style.css';
		$script_file = $this->getDesignDir() . 'assets/script.js';
		if(file_exists($style_file)){
			wp_enqueue_style(
				'pmb-design',
				$this->getDesignUrl() . 'style.css',
				['pmb_print_common', 'pmb-plugin-compatibility'],
				filemtime($style_file)
			);
		}
		if(file_exists($script_file)){
			wp_enqueue_script(
				'pmb-design',
				$this->getDesignUrl() . 'script.js',
				['jquery', 'pmb-beautifier-functions'],
				filemtime($script_file)
			);		}
		add_filter('wp_enqueue_scripts', [$this,'remove_theme_style'],20);
		$this->writeDesignTemplateInDivision(DesignTemplate::IMPLIED_DIVISION_PROJECT);
	}



	protected function generateSection()
	{
		global $post;
		// determine which template to use, depending on the current section's height and how template specified
		if($post->pmb_section instanceof ProjectSection){
			$this->project_generation->setLastSection($post->pmb_section);
			$template = $post->pmb_section->getTemplate();
			if($template){
				$this->writeDesignTemplateInDivision($template);
			} else {
				$height = $post->pmb_section->getHeight();
				$division = $this->mapLevelHeightToMainDivision($height);
				$this->writeDesignTemplateInDivision($division);
			}
		}
	}

	protected function finishGenerating()
	{
		$this->writeDesignTemplateInDivision(DesignTemplate::IMPLIED_DIVISION_PROJECT,false);
	}


	/**
	 * @param string $template_file
	 */
	protected function writeTemplateToFile($template_file){
		$this->getFileWriter()->write('<!-- pmb template: ' . $template_file . '-->' . $this->getHtmlFrom($template_file));
	}

	/**
	 * @return File
	 */
	protected function getFileWriter()
	{
		if(! $this->file_writer instanceof File){
			$this->file_writer = new File($this->project_generation->getGeneratedIntermediaryFilePath());
		}
		return $this->file_writer;
	}

	/**
	 * @param $division
	 * @param bool $beginning whether to show the beginning, or end, of this division.
	 */
	protected function writeDesignTemplateInDivision($division, $beginning = true){
		$this->writeTemplateToFile(
			$this->design->getDesignTemplate()->getTemplatePathToDivision(
				$division,
				$beginning
			)
		);
	}

	protected function writeClosingForDesignTemplate($division){
		if($this->design->getDesignTemplate()->templateFileExists($division,false)){
			$this->writeDesignTemplateInDivision($division,false);
		} else {
			// If the design template didn't delcare that file, it's ok. Assume it just ends in a div.
			$this->getFileWriter()->write('</div>');
		}
	}

	/**
	 * @param int $previous_depth
	 * @param int $current_depth
	 */
	protected function generateDivisionEnd(ProjectSection $previous_section, ProjectSection $current_section){
		$iterate_depth = $previous_section->getDepth();
		while($iterate_depth >= $current_section->getDepth()){
			$this->writeClosingForDesignTemplate(
				$this->mapLevelHeightToMainDivision(
					$previous_section->getHeight()
				)
			);
			$iterate_depth--;
		}
	}
	protected function generateFrontMatter( array $project_sections ) {
		$this->writeDesignTemplateInDivision('front_matter');
		$this->generateSections($project_sections);
		$this->writeClosingForDesignTemplate('front_matter');
	}

	protected function generateMainMatter() {
		$this->writeDesignTemplateInDivision('main');
		$this->generateSections($this->project->getFlatSections(1000,0,false,'main'));
		$this->writeClosingForDesignTemplate('main');
	}

	protected function generateBackMatter( array $project_sections ) {
		$this->writeDesignTemplateInDivision('back_matter');
		$this->generateSections($project_sections);
		$this->writeClosingForDesignTemplate('back_matter');
	}
}