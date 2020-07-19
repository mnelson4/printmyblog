<?php

namespace PrintMyBlog\domain;

use Exception;

/**
 * Class Settings
 *
 * Description
 *
 * @package     Print My Blog
 * @author         Mike Nelson
 * @since         $VID:$
 *
 */
class FrontendPrintSettings
{

    /**
     * @var array
     */
    protected $formats;

    /**
     * @var array
     */
    protected $settings;
    const OPTION_NAME = 'pmb-print-now-settings';

    /**
     * @var PrintOptions
     */
    protected $print_options;

    public function __construct(PrintOptions $print_options)
    {
        $this->print_options = $print_options;
        $this->formats = array(
            'print' => array(
                'admin_label' => esc_html__('Print', 'print-my-blog'),
                'default' => esc_html__('Print 🖨', 'print-my-blog'),
            ),
            'pdf' => array(
                'admin_label' => esc_html__('PDF', 'print-my-blog'),
                'default' => esc_html__('PDF 📄', 'print-my-blog'),
            ),
            'ebook' => array(
                'admin_label' => esc_html__('eBook', 'print-my-blog'),
                'default' => esc_html__('eBook 📱', 'print-my-blog'),
            )
        );
        // Remove emojis if the database doesn't support it.
        global $wpdb;
        foreach ($this->formats as $key => $settings) {
            if (method_exists($wpdb, 'strip_invalid_text_for_column')) {
                $this->formats[$key]['admin_label'] = $wpdb->strip_invalid_text_for_column(
                    $wpdb->options,
                    'option_value',
                    $settings['admin_label']
                );
                $this->formats[$key]['default'] = $wpdb->strip_invalid_text_for_column(
                    $wpdb->options,
                    'option_value',
                    $settings['default']
                );
            } else {
                $this->formats[$key]['admin_label'] = str_replace(['🖨','📄','📱'], ['','',''], $settings['admin_label']);
                $this->formats[$key]['default'] = str_replace(['🖨','📄','📱'], ['','',''], $settings['default']);
            }
        }
        // Initialize the settings with the defaults.
        $this->settings = $this->defaultSettings();
    }

    /**
     * Gets the default settings
     * @since $VID:$
     * @return array
     */
    protected function defaultSettings()
    {
        $defaults =  [
            'show_buttons' => false,
            'show_buttons_pages' => false,
	        'place_above_content' => true
        ];
        foreach ($this->formats as $slug => $format) {
            $defaults[$slug] = array(
                'frontend_label' => $format['default'],
                'active' => true,
                'print_options' => []
            );
        }
        return $defaults;
    }

    /**
     * @since $VID:$
     * @return array 2d. array keys are format slugs, sub-elements contain keys "admin_label" and "default"
     */
    public function formats()
    {
        return $this->formats;
    }

    /**
     * @since $VID:$
     * @return array
     */
    public function formatSlugs()
    {
        return array_keys($this->formats);
    }

    /**
     * @since $VID:$
     * @param $format
     * @return bool
     */
    public function isActive($format)
    {
        if (! isset($this->settings[$format])) {
            return false;
        }
        return (bool)$this->settings[$format]['active'];
    }

    /**
     * @since $VID:$
     * @param $format
     * @param $active
     */
    public function setFormatActive($format, $active)
    {
        $this->beforeSet($format);
        $this->settings[$format]['active'] = (bool)$active;
    }

    /**
     * @since $VID:$
     * @param $format
     * @param $label
     */
    public function setFormatFrontendLabel($format, $label)
    {
        $this->beforeSet($format);
        $this->settings[$format]['frontend_label'] = sanitize_text_field($label);
    }

    public function setPrintOptions($format, $submitted_values)
    {
        $this->beforeSet($format);
        $values_to_save = [];
        foreach ($this->print_options->allPrintOptions() as $option_name => $details) {
            $default = $details['default'];
            $new_value = null;
            if (isset($submitted_values[$option_name])) {
                if (is_bool($default)) {
                    $new_value = (bool)($submitted_values[$option_name]);
                } elseif (is_numeric($default)) {
                    $new_value = (int)$submitted_values[$option_name];
                } else {
                    $new_value = strip_tags($submitted_values[$option_name]);
                }
                if (isset($details['options']) && ! array_key_exists($new_value, $details['options'])) {
                    // that's not one of the acceptable options. Replace it with the default
                    $new_value = $default;
                }
            } else {
                if (is_bool($default)) {
                    $new_value = false;
                } elseif (is_numeric($default)) {
                    $new_value = 0;
                } else {
                    $new_value = '';
                }
            }
            $values_to_save[$option_name] = $new_value;
        }
        $this->settings[$format]['print_options'] = $values_to_save;
    }

    /**
     * Gets the print option names and their current values
     * @since $VID:$
     * @param $format
     * @return array keys are the option names, values are their saved values
     */
    public function getPrintOptionsAndValues($format)
    {
        $frontend_deviations = [
            'show_credit' => false,
            'show_filters' => false,
            'rendering_wait' => 0,
            'show_divider' => false,
            'post_page_break' => false,
        ];
        return array_merge(
            $this->print_options->allPrintOptionDefaults($format),
            $frontend_deviations,
            $this->settings[ $format ]['print_options']
        );
    }

    /**
     * @since $VID:$
     * @param $format
     * @return string
     */
    public function getFrontendLabel($format)
    {
        $this->beforeSet($format);
        return (string)$this->settings[$format]['frontend_label'];
    }

    /**
     * Sets whether to show buttons on posts.
     * @param bool $show
     */
    public function setShowButtons($show = true)
    {
        $this->settings['show_buttons'] = (bool)$show;
    }

    /**
     * Gets whether to show print buttons on posts.
     * @return bool
     */
    public function showButtons()
    {
        return (bool)$this->settings['show_buttons'];
    }


    /**
     * Sets whether to show print buttons on pages.
     * @since 2.7.0
     * @param bool $show
     */
    public function setShowButtonsPages($show = true)
    {
        $this->settings['show_buttons_pages'] = (bool)$show;
    }


    /**
     * Gets whether to show print buttons on pages.
     * @since 2.7.0
     * @return bool
     */
    public function showButtonsPages()
    {
        return (bool)$this->settings['show_buttons_pages'];
    }

	/**
	 * Sets whether buttons should appear above the content or below.
	 *
	 * @param bool $new_value
	 */
    public function setPlaceAbove($new_value)
    {
    	$this->settings['place_above'] = (bool)$new_value;

    }

	/**
	 * Whether buttons should appear above the content, or below.
	 *
	 * @return bool
	 */
    public function showButtonsAbove()
    {
    	return (bool)$this->settings['place_above'];
    }

    /**
     * Verifies the format is valid, and that its initialized in the settings.
     * @since $VID:$
     * @param $format
     */
    protected function beforeSet($format)
    {
        if (! isset($this->formats[$format])) {
            throw new Exception(
                'The format "'
                . $format
                . '" is invalid. It should be one of '
                . implode(', ', $this->formatSlugs())
            );
        }
        if (! isset($this->settings[$format])) {
            $this->settings[$format] = array(
                'frontend_label' => $this->formats[$format]['default'],
                'active' => false
            );
        }
    }

    /**
     * Saves the settings on this class to the database.
     * @since $VID:$
     * return boolean indicating successful saving
     */
    public function save()
    {
        return update_option(self::OPTION_NAME, $this->settings);
    }

    /**
     * Loads settings from the database. If none are set, uses the defaults.
     * @since $VID:$
     */
    public function load()
    {
        $this->settings = array_replace_recursive(
            $this->defaultSettings(),
            (array)get_option(self::OPTION_NAME, [])
        );
    }


    /**
     * Gets which post types are active. Key is the post type slug, value is whether to show print buttons on it.
     * @since 2.7.0
     * @return array
     */
    public function activePostTypes()
    {
        return [
            'post' => $this->settings['show_buttons'],
            'page' => $this->settings['show_buttons_pages']
        ];
    }
}
// End of file Settings.php
// Location: PrintMyBlog\domain/Settings.php
