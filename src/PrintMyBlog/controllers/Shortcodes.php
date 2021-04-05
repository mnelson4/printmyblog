<?php

namespace PrintMyBlog\controllers;

use PrintMyBlog\domain\PrintButtons;
use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;
use Twine\controllers\BaseController;

/**
 * Class Shortcodes
 *
 * Adds and executes shortcodes.
 * @package PrintMyBlog\controllers
 */
class Shortcodes extends BaseController
{

    public function setHooks()
    {
        add_shortcode(
            'pmb_print_buttons',
            [$this, 'printButtons' ]
        );
        add_shortcode(
            'pmb_project_title',
            [$this,'projectTitle']
        );
        add_shortcode(
            'pmb_toc',
            [$this,'tableOfContents']
        );
        add_shortcode(
            'pmb_title_page',
            [$this,'titlePage']
        );
        add_shortcode(
            'pmb_byline',
            [$this, 'pmbByline']
        );
        add_shortcode(
            'pmb_footnote',
            [$this,'footnote']
        );
    }
	// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function printButtons($atts)
    {
        $atts = shortcode_atts(
            [
                'ID' => null
            ],
            $atts
        );
        return (new PrintButtons())->getHtmlForPrintButtons($atts['ID']);
    }
    public function projectTitle()
    {
        global $pmb_project;
        if ($pmb_project instanceof Project) {
            return $pmb_project->getPublishedTitle();
        }
        return '<-- pmb there is no project title because this post is not being viewed as part of a project. '
        . 'You should probably not show this post to site visitors by making it private.-->';
    }

    public function tableOfContents()
    {
        return '<div  class="pmb-toc">
	        <ul id="pmb-toc-list" class="pmb-toc-list ">
	            <!-- Populated dynamically by JS -->
	        </ul>
	    </div>';
    }

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
            require($template_path);
        } else {
            return do_shortcode('<h1>[pmb_project_title]</h1>');
        }
    }

    public function pmbByline(){
        global $pmb_project;
        if ($pmb_project instanceof Project) {
            return $pmb_project->getPmbMeta('byline');
        }
        return '<-- pmb there is no project byline because this post is not being viewed as part of a project. '
            . 'You should probably not show this post to site visitors by making it private.-->';
    }

    /**
     * Just wraps the content in a footnote
     * @param $atts
     * @param $content
     * @param $shortcode_tag
     *
     * @return string
     */
    public function footnote($atts, $content, $shortcode_tag)
    {
        return '<span class="pmb-footnote">' . $content . '</span>';
    }
}
