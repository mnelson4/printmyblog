<?php

namespace Twine\forms\strategies\validation;

use Twine\forms\helpers\ValidationError;
use Twine\helpers\Html;

/**
 * Class FullHtmlValidation
 *
 * Makes sure there are only 'simple' html tags in the normalized value. Eg, line breaks, lists, links. No js etc though
 *
 * @package             Event Espresso
 * @subpackage          core
 * @author              Mike Nelson
 * @since               4.6
 *
 */
class FullHtmlValidation extends ValidationBase
{

    /**
     * @param null $validation_error_message
     */
    public function __construct($validation_error_message = null)
    {
        if (! $validation_error_message) {
            $validation_error_message = sprintf(
            // translators: 1: html for a line break, 2: list of allowed tags
                __('You are using HTML that might be beyond your permission. Only the following HTML tags are allowed:%1$s%2$s, and the following attributes: %3$s. If you are using the style attribute, make sure you don’t end in a semicolon and remove whitespace between CSS properties (e.g. "style=\'color:red;height:12px\'")', 'print-my-blog'),
                '<br />',
                $this->getListOfAllowedTags(),
                $this->getListOfAllowedAttributes()
            );
        }
        parent::__construct($validation_error_message);
    }


    /**
     * Generates and returns a string that lists the top-level HTML tags that are allowable for this input
     *
     * @return string
     */
    public function getListOfAllowedTags()
    {
        $tags_we_allow = $this->getAllowedTags();
        ksort($tags_we_allow);
        return implode(', ', array_keys($tags_we_allow));
    }

    /**
     * Returns an array whose keys are allowed attributes.
     * @return bool[]
     */
    public function getAllowedAttributes()
    {
        return [
            'class' => true,
            'id' => true,
            'style' => true,
            'src' => true,
            'alt' => true,
            'width' => true,
            'height' => true,
            'target' => true,
        ];
    }

    /**
     * Generates and returns a string that lists the top-level HTML tags that are allowable for this input
     *
     * @return string
     */
    public function getListOfAllowedAttributes()
    {
        $tags_we_allow = $this->getAllowedAttributes();
        ksort($tags_we_allow);
        return implode(', ', array_keys($tags_we_allow));
    }


    /**
     * Returns an array whose keys are allowed tags and values are an array of allowed attributes
     *
     * @return array
     */
    protected function getAllowedTags()
    {
        global $allowedtags;
        $tags_we_allow['p'] = array();
        $allowed_attributes = $this->getAllowedAttributes();
        $tags_we_allow = array_merge_recursive(
            $allowedtags,
            array(
                'ol' => $allowed_attributes,
                'ul' => $allowed_attributes,
                'li' => $allowed_attributes,
                'br' => $allowed_attributes,
                'p' => $allowed_attributes,
                'a' => $allowed_attributes,
                'h1' => $allowed_attributes,
                'h2' => $allowed_attributes,
                'h3' => $allowed_attributes,
                'h4' => $allowed_attributes,
                'h5' => $allowed_attributes,
                'h6' => $allowed_attributes,
                'hr' => $allowed_attributes,
                'img' => $allowed_attributes,
            )
        );
        return apply_filters('Twine\forms\strategies\validation\FullHtmlValidation::getAllowedTags', $tags_we_allow);
    }


    /**
     * Validates HTML contains no prohibited tags.
     * @param string $normalized_value
     * @throws ValidationError
     */
    public function validate($normalized_value)
    {
        parent::validate($normalized_value);
        $normalized_value_sans_tags = wp_kses("$normalized_value", $this->getAllowedTags());
        if (strlen($normalized_value) > strlen($normalized_value_sans_tags)) {
            throw new ValidationError($this->getValidationErrorMessage(), 'complex_html_tags');
        }
    }
}
