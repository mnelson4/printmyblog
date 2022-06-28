<?php

namespace Twine\forms\strategies\layout;

use Twine\forms\base\FormSection;
use Twine\forms\inputs\FormInputBase;

/**
 * TemplateLayout
 * For very customized layouts, where you provide this class with the location of
 * a template file to use for laying out the form section. Inherits from Div_per_Section
 * in case you call layout_input() or layout_subsection(), or get_html_for_label(),
 * get_html_for_input(), or get_html_for_errors() on one if the form section's inputs.
 * When would you want to use this instead of just laying out the form's subsections manually
 * in a template file? When you want a very customized layout, but that layout is essential
 * to the form; so that if you were to use the same form on two different pages (eg a contact form,
 * one on the website's frontend for contacting the site admin, and then again on the backend for
 * contacting the plugin's developer), you would still use this exact same template layout strategy.
 * (Eg, if you wanted to add a button to that same form for automatically adding "@gmail.com" or "@yahoo.com"
 * onto the 'from' input. The input is important to the form section on either page, but isn't an input so it's best
 * added as a part of the template layout.)
 *
 * @package               Event Espresso
 * @subpackage
 * @author                Mike Nelson
 * ------------------------------------------------------------------------
 */
class TemplateLayout extends DivPerSectionLayout
{

    /**
     * @var string|null
     */
    protected $layout_template_file = null;

    /**
     * @var string|null
     */
    protected $layout_begin_template_file = null;

    /**
     * @var string|null
     */
    protected $input_template_file = null;

    /**
     * @var string|null
     */
    protected $subsection_template_file = null;

    /**
     * @var string|null
     */
    protected $layout_end_template_file = null;

    /**
     * @var array
     */
    protected $template_args = array();



    /**
     * @param array $template_options {
     * @type string $_layout_template_file
     * @type string $_begin_template_file
     * @type string $_input_template_file
     * @type string $_subsection_template_file
     * @type string $_end_template_file
     * @type array  $_template_args
     *                                }
     */
    public function __construct($template_options = array())
    {
        // loop through incoming options
        foreach ($template_options as $key => $value) {
            // add underscore to $key to match property names
            $_key = '_' . $key;
            if (property_exists($this, $_key)) {
                $this->{$_key} = $value;
            }
        }
        parent::__construct();
    }



    /**
     * Also has the side effect of enqueuing any needed JS and CSS for
     * this form.
     * Creates all the HTML necessary for displaying this form, its inputs, and
     * proper subsections.
     * Returns the HTML
     *
     * @return string
     */
    public function layoutForm()
    {
        if ($this->layout_template_file) {
            return $this->renderTemplate($this->layout_template_file);
        } else {
            return parent::layoutForm();
        }
    }



    /**
     * Opening div tag for a form
     *
     * @return string
     */
    public function layoutFormBegin()
    {
        if ($this->layout_begin_template_file) {
            return $this->renderTemplate(
                $this->layout_begin_template_file,
                $this->templateArgs()
            );
        } else {
            return parent::layoutFormBegin();
        }
    }



    /**
     * If an input_template_file was provided upon construction, uses that to layout the input. Otherwise uses parent.
     *
     * @see DIv_Per_Section_Layout::layout_input() for documentation
     * @param FormInputBase $input
     * @return string
     */
    public function layoutInput($input)
    {
        if ($this->input_template_file) {
            return $this->renderTemplate($this->input_template_file, array('input' => $input));
        }
        return parent::layoutInput($input);
    }



    /**
     * If a subsection_template_file was provided upon construction, uses that to layout the subsection. Otherwise uses
     * parent.
     *
     * @param FormSection $form_section
     * @return string
     * @see DivPerSectionLayout::layoutSubsection() for documentation
     */
    public function layoutSubsection($form_section)
    {
        if ($this->subsection_template_file) {
            return $this->renderTemplate($this->subsection_template_file);
        }
        return parent::layoutSubsection($form_section);
    }



    /**
     * Closing div tag for a form
     *
     * @return string
     */
    public function layoutFormEnd()
    {
        if ($this->layout_end_template_file) {
            return $this->renderTemplate($this->layout_end_template_file);
        } else {
            return parent::layoutFormEnd();
        }
    }



    /**
     * @param array $template_args
     */
    public function setTemplateArgs($template_args = array())
    {
        $this->template_args = $template_args;
    }



    /**
     * @param array $template_args
     */
    public function addTemplateArgs($template_args = array())
    {
        $this->template_args = array_merge_recursive($this->template_args, $template_args);
    }



    /**
     * @return array
     */
    public function templateArgs()
    {
        foreach ($this->formSection()->subsections() as $subsection_name => $subsection) {
            $subsection_name = self::prepFormSubsectionKeyName($subsection_name);
            if (strpos($subsection_name, '[') !== false) {
                $sub_name = explode('[', $subsection_name);
                $this->template_args[ $sub_name[0] ][ rtrim($sub_name[1], ']') ] = $this->layoutSubsection(
                    $subsection
                );
            } else {
                $this->template_args[ $subsection_name ] = $this->layoutSubsection($subsection);
            }
        }
        return $this->template_args;
    }



    /**
     * Sanitize input name.
     *
     * @access public
     * @param string $subsection_name
     * @return string
     */
    public static function prepFormSubsectionKeyName($subsection_name = '')
    {
        $subsection_name = str_replace(array('-', ' '), array('', '_'), $subsection_name);
        return is_numeric(substr($subsection_name, 0, 1)) ? 'form_' . $subsection_name : $subsection_name;
    }



    /**
     * Just a wrapper for the above method
     *
     * @access public
     * @param string $subsection_name
     * @return string
     */
    public static function getSubformName($subsection_name = '')
    {
        return self::prepFormSubsectionKeyName($subsection_name);
    }

    /**
     * @param string $filepath
     * @param array|null $args
     * @return string
     */
    protected function renderTemplate($filepath, $args = null)
    {
        if (! $args) {
            $args = $this->templateArgs();
        }
        // extract args so they're available in the template file.
        // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
        extract($args);
        ob_start();
        require $filepath;
        return ob_get_clean();
    }
}
