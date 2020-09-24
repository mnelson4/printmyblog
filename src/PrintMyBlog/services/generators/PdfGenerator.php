<?php


namespace PrintMyBlog\services\generators;

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
		$style_file = $this->getDesignDir() . 'style.css';
		$script_file = $this->getDesignDir() . 'script.js';
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
		global $pmb_project;
		$pmb_project = $this->project;
		$pmb_show_site_title = true;
		$pmb_show_site_tagline = false;
		$pmb_site_name = $pmb_project->getWpPost()->post_title;
		$pmb_site_description = '';
		$pmb_show_date_printed = true;
		$pmb_show_credit = true;
		ob_start();
		$file = $this->getDesignDir() . 'projet_start.php';
		include( $file );
		$this->getFileWriter()->write(ob_get_clean());
	}

	protected function generatePost()
	{
		ob_start();
		include( $this->getDesignDir() . 'section.php');
		$this->getFileWriter()->write(ob_get_clean());
	}

	protected function finishGenerating()
	{
		ob_start();
		include( $this->getDesignDir() . 'project_stop.php');
		$this->getFileWriter()->write(ob_get_clean());
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
}