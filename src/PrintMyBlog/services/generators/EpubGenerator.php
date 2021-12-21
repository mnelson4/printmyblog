<?php


namespace PrintMyBlog\services\generators;


class EpubGenerator extends HtmlBaseGenerator
{
    /**
     * Writes out the PMB Pro print "window" which appears at the top of pro print pages.
     * Echoes, instead of using `$this->file_writer`, because this is a callback on an action called inside the template HTML.
     * @throws Exception
     */
    public function addPrintWindowToPage()
    {
        echo pmb_get_contents(PMB_TEMPLATES_DIR . 'partials/pro_print_page_epub_window.php', ['project' => $this->project, 'project_generation' => $this->project_generation, 'generate_url' => $this->getUrlBackToGenerateStep()]);
    }

    public function enqueueStylesAndScripts()
    {
        wp_register_script(
            'epub-gen-memory',
            PMB_SCRIPTS_URL . 'libs/epub-gen-memory__premium_only.min.js',
            [],
            filemtime(PMB_SCRIPTS_DIR . 'libs/epub-gen-memory__premium_only.min.js')
        );
        wp_enqueue_script(
            'pmb-epub',
            PMB_SCRIPTS_URL . 'epub-generator.js',
            ['epub-gen-memory','jquery'],
            filemtime(PMB_SCRIPTS_DIR . 'epub-generator.js')
        );
        wp_localize_script(
            'pmb-epub',
            'pmb_epub',
            [
                'title' => $this->project->getPublishedTitle(),
            ]
        );
    }
}