<?php
namespace Twine\forms\inputs;

use Twine\forms\strategies\display\TextInputDisplay;
use Twine\forms\strategies\normalization\TextNormalization;
use Twine\forms\strategies\validation\PlaintextValidation;

/**
 * Datepicker_Input
 *
 * @package               Event Espresso
 * @subpackage
 * @author                Mike Nelson
 */
class DatepickerInput extends FormInputBase
{

    /**
     * @param array $input_settings
     */
    public function __construct($input_settings = array())
    {
        $this->_set_display_strategy(new TextInputDisplay('datepicker'));
        $this->_set_normalization_strategy(new TextNormalization());
        // we could do better for validation, but at least verify its plaintext
        $this->_add_validation_strategy(
            new PlaintextValidation(
                isset($input_settings['validation_error_message'])
                    ? $input_settings['validation_error_message']
                    : null
            )
        );
        parent::__construct($input_settings);
        $this->set_html_class($this->html_class() . ' datepicker');
        // add some style and make it dance
        add_action('wp_enqueue_scripts', array('Datepicker_Input', 'enqueue_styles_and_scripts'));
        add_action('admin_enqueue_scripts', array('Datepicker_Input', 'enqueue_styles_and_scripts'));
    }



    /**
     *    enqueue_styles_and_scripts
     *
     * @access        public
     * @return        void
     */
    public static function enqueue_styles_and_scripts()
    {
        // load css
        wp_enqueue_style(
            'twine-ui-theme',
            TWINE_STYLES_URL . 'jquery-ui-1.10.3.custom.min.css',
            array(),
            TWINE_STYLES_DIR . 'jquery-ui-1.10.3.custom.min.css'
        );
    }
}
