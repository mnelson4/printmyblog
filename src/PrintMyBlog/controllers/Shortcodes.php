<?php

namespace PrintMyBlog\controllers;

use PrintMyBlog\domain\PrintButtons;
use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\entities\FileFormat;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\system\Context;
use Twine\controllers\BaseController;

/**
 * Class Shortcodes
 *
 * Adds and executes shortcodes.
 * @package PrintMyBlog\controllers
 */
class Shortcodes extends BaseController
{

    /**
     * Adds shortcodes.
     */
    public function setHooks()
    {
        add_shortcode(
            'pmb_print_buttons',
            [$this, 'printButtons' ]
        );
        add_shortcode(
            'pmb_print_page_url',
            [$this, 'printPageUrl']
        );
        add_shortcode(
            'pmb_project_title',
            [$this, 'projectTitle']
        );
        add_shortcode(
            'pmb_toc',
            [$this, 'tableOfContents']
        );
        add_shortcode(
            'pmb_title_page',
            [$this, 'titlePage']
        );
        add_shortcode(
            'pmb_byline',
            [$this, 'pmbByline']
        );
        add_shortcode(
            'pmb_footnote',
            [$this, 'footnote']
        );
        add_shortcode(
            'pmb_web_only_text',
            [$this, 'webOnlyText']
        );
        add_shortcode(
            'pmb_web_only_blocks',
            [$this, 'webOnlyBlocks']
        );
        add_shortcode(
            'pmb_print_only_text',
            [$this, 'printOnlyText']
        );
        add_shortcode(
            'pmb_print_only_blocks',
            [$this, 'printOnlyBlocks']
        );
        add_shortcode(
            'pmb_project_setting',
            [$this, 'projectSetting']
        );
    }
	// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

    /**
     * Adds a span whose contents will only be shown in the screen
     * @param array $atts
     * @param string $content
     * @return string
     */
    public function webOnlyText($atts, $content)
    {
        return '<span class="pmb-screen-only">' . $content . '</span>';
    }

    /**
     * Adds a div whose contents will only be shown on the screen
     * @param array $atts
     * @param string $content
     * @return string
     */
    public function webOnlyBlocks($atts, $content)
    {
        return '<div class="pmb-screen-only">' . $content . '</div>';
    }

    /**
     * Adds a span whose contents will only be shown in the screen
     * @param array $atts
     * @param string $content
     * @return string
     */
    public function printOnlyText($atts, $content)
    {
        return '<span class="pmb-print-only">' . $content . '</span>';
    }

    /**
     * Adds a div whose contents will only be shown on the screen
     * @param array $atts
     * @param string $content
     * @return string
     */
    public function printOnlyBlocks($atts, $content)
    {
        return '<div class="pmb-print-only">' . $content . '</div>';
    }
    /**
     * @param array $atts
     * @return string|string[]
     */
    public function printPageUrl($atts)
    {
        $atts = shortcode_atts(
            [
                'ID' => null,
                'format' => 'print',
                'add_protocol' => true,
            ],
            $atts
        );
        // if it was put in a URL, the most likely place to put this shortcode, the quotes became HTML entities which
        // need to be removed
        foreach ($atts as $key => $val) {
            $atts[$key] = str_replace('"', '', html_entity_decode($val, ENT_COMPAT));
        }

        $url = Context::instance()->reuse('PrintMyBlog\domain\PrintPageUrlGenerator', [$atts['ID']])->getUrl($atts['format']);
        // remove the starting "http://" and "https://" because, if used in an anchor link, those get added automatically
        if (! $atts['add_protocol']) {
            $url = str_replace(
                ['http://', 'https://', '://'],
                '',
                $url
            );
        }
        return $url;
    }

    /**
     * @param array $atts
     * @return string
     */
    public function printButtons($atts)
    {
        $atts = shortcode_atts(
            [
                'ID' => null,
            ],
            $atts
        );
        return Context::instance()->reuse('PrintMyBlog\domain\PrintButtons')->getHtmlForPrintButtons($atts['ID']);
    }

    /**
     * @return string
     */
    public function projectTitle()
    {
        global $pmb_project;
        if ($pmb_project instanceof Project) {
            return $pmb_project->getPublishedTitle();
        }
        return '<-- pmb there is no project title because this post is not being viewed as part of a project. '
        . 'You should probably not show this post to site visitors by making it private.-->';
    }

    /**
     * @return string
     */
    public function tableOfContents()
    {
        return apply_filters(
            '\PrintMyBlog\controllers\Shortcodes->tableOfContents',
            '<div  class="pmb-toc">
	        <ul id="pmb-toc-list" class="pmb-toc-list ">
	            <!-- Populated dynamically by JS -->
	        </ul>
	    </div>'
        );
    }

    /**
     * @return string
     * @throws \PrintMyBlog\exceptions\TemplateDoesNotExist
     */
    public function titlePage()
    {
        global $pmb_project, $pmb_design, $pmb_format;
        if (
            $pmb_design instanceof Design
            && $pmb_design->getDesignTemplate() instanceof DesignTemplate
            && $pmb_design->getDesignTemplate()->supports(DesignTemplate::TEMPLATE_TITLE_PAGE)
        ) {
            $template_path = $pmb_design->getDesignTemplate()->getTemplatePathToDivision(
                DesignTemplate::TEMPLATE_TITLE_PAGE
            );
            require $template_path;
        } else {
            return do_shortcode('<h1>[pmb_project_title]</h1>');
        }
    }

    /**
     * @return string
     */
    public function pmbByline()
    {
        global $pmb_project;
        if ($pmb_project instanceof Project) {
            return $pmb_project->getPmbMeta('byline');
        }
        return '<-- pmb there is no project byline because this post is not being viewed as part of a project. '
            . 'You should probably not show this post to site visitors by making it private.-->';
    }

    /**
     * Just wraps the content in a footnote
     * @param array $atts
     * @param string $content
     * @param string $shortcode_tag
     *
     * @return string
     */
    public function footnote($atts, $content, $shortcode_tag)
    {
        return '<span class="pmb-footnote">' . $content . '</span>';
    }

    /**
     * Shortcode for getting a setting on a project.
     * @param array $atts
     * @return mixed|string|null
     * @throws \Exception
     */
    public function projectSetting($atts){
        global $pmb_project, $pmb_format;
        $key = isset($atts['name']) ? (string)$atts['name'] : '';
        if($key){
            if($pmb_project instanceof Project) {
                return $pmb_project->getSetting($key);
            } else {
                return 'Project Setting ' . $key;
            }
        } else {
            if($pmb_project instanceof Project && $pmb_format instanceof FileFormat) {
                return '[pmb_project_setting] requires you provide the setting\'s name. Available settings are: ' . wp_json_encode($pmb_project->getDesignFor($pmb_format)->getSettings());
            } else {
                return 'Project Setting ' . $key;
            }
        }
    }
}
