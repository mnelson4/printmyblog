<?php

namespace PrintMyBlog\services\generators;

class WordGenerator extends HtmlBaseGenerator
{

    public function startGenerating()
    {
        add_filter(
            '\PrintMyBlog\controllers\Shortcodes->tableOfContents',
            [$this, 'wordToc']
        );
        parent::startGenerating();
    }

    /**
     * Writes out the PMB Pro print "window" which appears at the top of pro print pages.
     * Echoes, instead of using `$this->file_writer`, because this is a callback on an action called inside the template HTML.
     * @throws Exception
     */
    public function addPrintWindowToPage()
    {
        echo pmb_get_contents(
            PMB_TEMPLATES_DIR . 'partials/pro_print_page_word_window.php',
            [
                'project' => $this->project,
                'project_generation' => $this->project_generation,
                'generate_url' => $this->getUrlBackToGenerateStep(),
            ]
        );
    }

    public function enqueueStylesAndScripts()
    {
        wp_enqueue_script('pmb_pro_page');
        wp_enqueue_style('pmb_pro_page');

        // https://github.com/koffsyrup/FileSaver.js#examples
        wp_register_script(
            'pmb-filesaver',
            PMB_SCRIPTS_URL . 'libs/filesaver__premium_only.min.js',
            [],
            filemtime(PMB_SCRIPTS_DIR . 'libs/filesaver__premium_only.min.js')
        );

        wp_enqueue_script(
            'pmb-word',
            PMB_SCRIPTS_URL . 'pmb-word.js',
            ['jquery','pmb-filesaver'],
            filemtime(PMB_SCRIPTS_DIR . 'pmb-word.js')
        );
        $css = pmb_get_contents(
            PMB_STYLES_DIR . '/pmb-word.css'
        ) . $this->design->getSetting('custom_css');


        $style_file = $this->getDesignDir() . 'assets/style.css';

        if (file_exists($style_file)) {
            $css .= pmb_get_contents($style_file);
        }
        wp_add_inline_style(
            'pmb_pro_page',
            $css
        );

        $script_file = $this->getDesignDir() . 'assets/script.js';
        if (file_exists($script_file)) {
            wp_enqueue_script(
                'pmb-design',
                $this->getDesignAssetsUrl() . 'script.js',
                ['jquery', 'pmb-beautifier-functions'],
                filemtime($script_file)
            );
        }

        wp_localize_script(
            'pmb-word',
            'pmb_pro',
            [
                'title' => $this->project->getPublishedTitle(),
                'authors' => $this->getAuthors(),
                'cover' => $this->project->getSetting('cover'),
                'css' => $css,
                'pmb_nonce' => wp_create_nonce('pmb_pro_page'),
                'external_resouce_mapping' => $this->external_resource_cache->getMapping(),
                'domains_to_not_map' => $this->external_resource_cache->domainsToNotMap(),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'translations' => [
                    'many_articles' => __('Your project is very big and you might have errors downloading the file. If so, try splitting your content into multiple projects and instead creating multiple smaller files.', 'print-my-blog'),
                    'many_images' => __('Your project has lots of images and you might have errors downloading the file. If so, try spltting your content into multiple projects or reducing the image quality set on your design.', 'print-my-blog'),
                ]
            ]
        );
    }

    /**
     * @return false|string|string[]
     */
    protected function getAuthors()
    {
        $byline = $this->project->getSetting('byline');
        if (! $byline) {
            return '';
        }
        return array_map('trim', explode(',', str_replace(['\n'], ',', $byline)));
    }

    protected function finishGenerating()
    {
        parent::finishGenerating();
        if ($this->design->getSetting('powered_by')) {
            $this->getFileWriter()->write(
                pmb_get_contents($this->design->getDesignTemplate()->getDir() . 'templates/footer.php')
            );
        }
    }

    /**
     * ignores original html
     */
    public function wordToc(){
        return "<p class=MsoToc1> 
<!--[if supportFields]> 
<span style='mso-element:field-begin'></span> 
TOC \o \"1-3\" \u 
<span style='mso-element:field-separator'></span> 
<![endif]--> 
<span style='mso-no-proof:yes'>" . __('Table of content - Please right-click and choose "Update field".', 'print-my-blog') . "</span> 
<!--[if supportFields]> 
<span style='mso-element:field-end'></span> 
<![endif]--> 
</p>";
    }
}
