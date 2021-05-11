<?php

namespace Twine\forms\strategies\layout;

use Twine\forms\base\FormSectionBase;
use Twine\forms\base\FormSection;
use Twine\forms\inputs\FormInputBase;
use Twine\forms\strategies\display\HiddenDisplay;
use Twine\helpers\Html;

/**
 * Abstract parent class for all form layouts. Mostly just contains a reference to the form
 * we are to lay out.
 * Form layouts should add HTML content for each form section (eg a header and footer)
 * for the form section, and dictate how to layout all the inputs and proper subsections
 * (laying out where to put the input's label, the actual input widget, and its errors; and
 * stating where the proper subsections should be placed (but usually leaving them to layout
 * their own headers and footers etc).
 */
abstract class FormSectionLayoutBase
{

    /**
     * Form form section to lay out
     *
     * @var FormSection
     */
    protected $Form_section;



    /**
     *  __construct
     */
    public function __construct()
    {
    }



    /**
     * The form section on which this strategy is to perform
     *
     * @param FormSection $form
     */
    public function constructFinalize(FormSection $form)
    {
        $this->Form_section = $form;
    }



    /**
     * @return FormSection
     */
    public function formSection()
    {
        return $this->Form_section;
    }



    /**
     * Also has teh side effect of enqueuing any needed JS and CSS for
     * this form.
     * Creates all the HTML necessary for displaying this form, its inputs, and
     * proper subsections.
     * Returns the HTML
     *
     * @return string HTML for displaying
     * @throws Error
     */
    public function layoutForm()
    {
        $html = '';
        // layout_form_begin
        $html .= apply_filters(
            'FH_FormSectionLayoutBase__layout_form__start__for_' . $this->Form_section->name(),
            $this->layoutFormBegin(),
            $this->Form_section
        );
        // layout_form_loop
        $html .= apply_filters(
            'FH_FormSectionLayoutBase__layout_form__loop__for_' . $this->Form_section->name(),
            $this->layoutFormLoop(),
            $this->Form_section
        );
        // layout_form_end
        $html .= apply_filters(
            'FH_FormSectionLayoutBase__layout_form__end__for_' . $this->Form_section->name(),
            $this->layoutFormEnd(),
            $this->Form_section
        );
        $html = $this->addFormSectionHooksAndFilters($html);
        if($this->formSection()->useNonce()){
            $html .= wp_nonce_field($this->formSection()->name(), $this->formSection()->name() . '_nonce');
        }
        return $html;
    }



    /**
     * @return string
     * @throws Error
     */
    public function layoutFormLoop()
    {
        $html = '';
        foreach ($this->Form_section->subsections() as $name => $subsection) {
            if ($subsection instanceof FormInputBase) {
                $html .= apply_filters(
                    'FH_FormSectionLayoutBase__layout_form__loop_for_input_'
                    . $name . '__in_' . $this->Form_section->name(),
                    $this->layoutInput($subsection),
                    $this->Form_section,
                    $subsection
                );
            } elseif ($subsection instanceof FormSectionBase) {
                $html .= apply_filters(
                    'FH_FormSectionLayoutBase__layout_form__loop_for_non_input_'
                    . $name . '__in_' . $this->Form_section->name(),
                    $this->layoutSubsection($subsection),
                    $this->Form_section,
                    $subsection
                );
            }
        }
        return $html;
    }



    /**
     * Should be used to start teh form section (Eg a table tag, or a div tag, etc.)
     *
     * @return string
     */
    abstract public function layoutFormBegin();



    /**
     * Should be used to end the form section (eg a /table tag, or a /div tag, etc)
     *
     * @return string
     */
    abstract public function layoutFormEnd();



    /**
     * Should be used internally by layout_form() to layout each input (eg, if this layout
     * is putting each input in a row of its own, this should probably be called by a
     *  foreach loop in layout_form() (WITHOUT adding any content directly within layout_form()'s foreach loop.
     * Eg, this method should add the tr and td tags). This method is exposed in case you want to completely
     * customize the form's layout, but would like to make use of it for laying out
     * 'easy-to-layout' inputs
     *
     * @param FormInputBase $input
     *
     * @return string html
     */
    abstract public function layoutInput($input);



    /**
     * Similar to layout_input(), should be used internally by layout_form() within a
     * loop to layout each proper subsection. Unlike layout_input(), however, it is assumed
     * that the proper subsection will layout its container, label, etc on its own.
     *
     * @param FormSectionBase $subsection
     * @return string html
     */
    abstract public function layoutSubsection($subsection);



    /**
     * Gets the HTML for the label tag and its contents for the input
     *
     * @param FormInputBase $input
     *
     * @return string
     */
    public function displayLabel($input)
    {
        if ($input->getDisplayStrategy() instanceof HiddenDisplay) {
            return '';
        }
        $class = $input->required()
            ? 'twine-required-label ' . $input->htmlLabelClass()
            : $input->htmlLabelClass();
        $label_text = $input->required()
            ? $input->htmlLabelText() . '<span class="twine-asterisk">*</span>'
            : $input->htmlLabelText();
        return '<label id="'
               . $input->htmlLabelId()
               . '" class="'
               . $class
               . '" style="'
               . $input->htmlLabelStyle()
               . '" for="' . $input->htmlId()
               . '">'
               . $label_text
               . '</label>';
    }



    /**
     * Gets the HTML for all the form's form-wide errors (ie, errors which
     * are not for specific inputs. E.g., if two inputs somehow disagree,
     * those errors would probably be on the form section, not one of its inputs)
     * @return string
     */
    public function displayFormWideErrors()
    {
        $html = '';
        if ($this->Form_section->getValidationErrors()) {
            $html .= "<div class='twine-form-wide-errors'>";
            // get all the errors on THIS form section (errors which aren't
            // for specific inputs, but instead for the entire form section)
            foreach ($this->Form_section->getValidationErrors() as $error) {
                $html .= $error->getMessage() . '<br>';
            }
            $html .= '</div>';
        }
        return apply_filters(
            'FH_FormSectionLayoutBase__display_form_wide_errors',
            $html,
            $this
        );
    }



    /**
     * returns the HTML for the server-side validation errors for the specified input
     * Note that if JS is enabled, it should remove these and instead
     * populate the form's errors in the jquery validate fashion
     * using the localized data provided to the JS
     *
     * @param FormInputBase $input
     *
     * @return string
     */
    public function displayErrors($input)
    {
        if ($input->getValidationErrors()) {
            return "<label  id='"
                   . $input->htmlId()
                   . "-error' class='twine-error' for='{$input->htmlName()}'>"
                   . $input->getValidationErrorString()
                   . '</label>';
        }
        return '';
    }



    /**
     * Displays the help span for the specified input
     *
     * @param FormInputBase $input
     *
     * @return string
     */
    public function displayHelpText($input)
    {
        $help_text  = $input->htmlHelpText();
        if ($help_text !== '' && $help_text !== null) {
            $tag = is_admin() ? 'p' : 'span';
            return '<'
                   . $tag
                   . ' id="'
                   . $input->htmlId()
                   . '-help" class="'
                   . $input->htmlHelpClass()
                   . '" style="'
                   . $input->htmlHelpStyle()
                   . '">'
                   . $help_text
                   . '</'
                   . $tag
                   . '>';
        }
        return '';
    }



    /**
     * Does an action and hook onto the end of teh form
     *
     * @param string $html
     * @return string
     */
    public function addFormSectionHooksAndFilters($html)
    {
        $html_generator = Html::instance();
        // replace dashes and spaces with underscores
        $hook_name = str_replace(array('-', ' '), '_', $this->Form_section->htmlId());
        do_action('AH_Form_Section_Layout__' . $hook_name, $this->Form_section);
        $html = (string) apply_filters(
            'AF_Form_Section_Layout__' . $hook_name . '__html',
            $html,
            $this->Form_section
        );
        $html .= $html_generator->nl() . '<!-- AH_Form_Section_Layout__' . $hook_name . '__html -->';
        $html .= $html_generator->nl() . '<!-- AF_Form_Section_Layout__' . $hook_name . ' -->';
        return $html;
    }
}
