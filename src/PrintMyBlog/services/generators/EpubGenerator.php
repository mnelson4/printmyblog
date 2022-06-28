<?php

namespace PrintMyBlog\services\generators;

use Exception;

/**
 * Class EpubGenerator
 * @package PrintMyBlog\services\generators
 */
class EpubGenerator extends HtmlBaseGenerator
{

    /**
     * Begins writing to html intermediary file.
     */
    public function startGenerating()
    {
        $this->disableEmojis();
        parent::startGenerating();
    }

    /**
     * Emojis break Amazon Kindle Previewer
     */
    protected function disableEmojis()
    {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    }
    /**
     * Writes out the PMB Pro print "window" which appears at the top of pro print pages.
     * Echoes, instead of using `$this->file_writer`, because this is a callback on an action called inside the template HTML.
     */
    public function addPrintWindowToPage()
    {
        // The template file does the escaping.
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo pmb_get_contents(
            PMB_TEMPLATES_DIR . 'partials/pro_print_page_epub_window.php',
            [
                'project' => $this->project,
                'project_generation' => $this->project_generation,
                'generate_url' => $this->getUrlBackToGenerateStep(),
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function enqueueStylesAndScripts()
    {
        wp_enqueue_script('pmb_pro_page');
        wp_enqueue_style('pmb_pro_page');
        wp_register_script(
            'epub-gen-memory',
            PMB_SCRIPTS_URL . 'libs/epub-gen-memory__premium_only.min.js',
            [],
            filemtime(PMB_SCRIPTS_DIR . 'libs/epub-gen-memory__premium_only.min.js')
        );
        wp_register_script(
            'pmb-web-streams-ponyfill',
            PMB_SCRIPTS_URL . 'libs/web-streams-ponyfill__premium_only.min.js',
            [],
            filemtime(PMB_SCRIPTS_DIR . 'libs/web-streams-ponyfill__premium_only.min.js')
        );
        // https://github.com/jimmywarting/StreamSaver.js
        wp_register_script(
            'pmb-streamsaver',
            PMB_SCRIPTS_URL . 'libs/streamsaver__premium_only.min.js',
            ['pmb-web-streams-ponyfill'],
            filemtime(PMB_SCRIPTS_DIR . 'libs/streamsaver__premium_only.min.js')
        );
        // https://github.com/koffsyrup/FileSaver.js#examples
        wp_register_script(
            'pmb-filesaver',
            PMB_SCRIPTS_URL . 'libs/filesaver__premium_only.min.js',
            [],
            filemtime(PMB_SCRIPTS_DIR . 'libs/filesaver__premium_only.min.js')
        );

        wp_enqueue_script(
            'pmb-epub',
            PMB_SCRIPTS_URL . 'epub-generator.js',
            ['epub-gen-memory', 'jquery', 'pmb-beautifier-functions', 'pmb-streamsaver', 'pmb-filesaver'],
            filemtime(PMB_SCRIPTS_DIR . 'epub-generator.js')
        );
        $css = pmb_get_contents(
            PMB_STYLES_DIR . '/pmb-epub.css'
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
            'pmb-epub',
            'pmb_pro',
            [
                'title' => $this->project->getPublishedTitle(),
                'authors' => $this->getAuthors(),
                'cover' => $this->project->getSetting('cover'),
                'css' => $css,
                'version' => '3',
                'pmb_nonce' => wp_create_nonce('pmb_pro_page'),
                'external_resouce_mapping' => $this->external_resource_cache->getMapping(),
                'domains_to_not_map' => $this->external_resource_cache->domainsToNotMap(),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'translations' => [
                    'many_articles' => __('Your project is very big and you might have errors downloading the file. If so, try splitting your content into multiple projects and instead creating multiple smaller files.', 'print-my-blog'),
                    'many_images' => __('Your project has lots of images and you might have errors downloading the file. If so, try spltting your content into multiple projects or reducing the image quality set on your design.', 'print-my-blog'),
                ],
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

    /**
     * @return void
     * @throws Exception
     */
    protected function finishGenerating()
    {
        parent::finishGenerating();
        if ($this->design->getSetting('powered_by')) {
            $this->getFileWriter()->write(
                pmb_get_contents($this->design->getDesignTemplate()->getDir() . 'templates/footer.php')
            );
        }
    }
}
