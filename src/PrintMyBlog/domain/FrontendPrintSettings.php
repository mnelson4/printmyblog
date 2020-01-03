<?php
namespace PrintMyBlog\domain;

/**
 * Class Settings
 *
 * Description
 *
 * @package     Event Espresso
 * @author         Mike Nelson
 * @since         $VID:$
 *
 */
class FrontendPrintSettings
{
    protected $formats;
    protected $settings;
    const OPTION_NAME = 'pmb-print-now-settings';

    public function __construct(){
        $this->formats = array(
            'print'=> array(
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
        // Initialize the settings with the defaults.
        $this->settings = [
            'show_buttons' => false
        ];
        foreach($this->formats as $slug => $format){
            $this->settings[$slug] = array(
                'frontend_label' => $format['default'],
                'active' => true
            );
        }
    }

    /**
     * @since $VID:$
     * @return array 2d. array keys are format slugs, sub-elements contain keys "admin_label" and "default"
     */
    public function formats(){
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
    public function isActive($format){
        if(! isset($this->settings[$format])){
            return false;
        }
        return (bool)$this->settings[$format]['active'];
    }

    /**
     * @since $VID:$
     * @param $format
     * @param $active
     */
    public function setFormatActive($format, $active){
        $this->beforeSet($format);
        $this->settings[$format]['active'] = (bool)$active;
    }

    /**
     * @since $VID:$
     * @param $format
     * @param $label
     */
    public function setFormatFrontendLabel($format, $label){
        $this->beforeSet($format);
        $this->settings[$format]['frontend_label'] = sanitize_text_field($label);
    }

    /**
     * @since $VID:$
     * @param $format
     * @return string
     */
    public function getFrontendLabel($format){
        $this->beforeSet($format);
        return (string)$this->settings[$format]['frontend_label'];
    }

    /**
     * @since $VID:$
     * @param bool $show
     */
    public function setShowButtons($show = true){
        $this->settings['show_buttons'] = (bool)$show;
    }

    /**
     * @since $VID:$
     * @return bool
     */
    public function showButtons(){
        return (bool)$this->settings['show_buttons'];
    }

    /**
     * Verifies the format is valid, and that its initialized in the settings.
     * @since $VID:$
     * @param $format
     */
    protected function beforeSet($format){
        if(! isset($this->formats[$format])) {
            throw new Exception('The format "' . $format . '" is invalid. It should be one of ' . implode(', ', $this->formatSlugs()));
        }
        if(! isset($this->settings[$format])){
            $this->settings[$format] = array(
                'frontend_label' => $this->formats[$format]['default'],
                'active' => false
            );
        }
    }

    /**
     * Saves the settings on this class to the database.
     * @since $VID:$
     */
    public function save(){
        update_option(self::OPTION_NAME, $this->settings);
    }

    /**
     * Loads settings from the database. If none are set, uses the defaults.
     * @since $VID:$
     */
    public function load()
    {
        $stored_settings = get_option(self::OPTION_NAME, null);
        if($stored_settings !== null){
            $this->settings = $stored_settings;
        }
    }
}
// End of file Settings.php
// Location: PrintMyBlog\domain/Settings.php
