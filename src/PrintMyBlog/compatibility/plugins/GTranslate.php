<?php


namespace PrintMyBlog\compatibility\plugins;


use Twine\compatibility\CompatibilityBase;

/**
 * Class GTranslate for plugin https://wordpress.org/plugins/gtranslate/
 * Adds a GTranslate dropdown
 * @package PrintMyBlog\compatibility\plugins
 */
class GTranslate extends CompatibilityBase
{
    public function setHooks()
    {
        add_action('pmb_print_page_ready_instructions_start', [$this,'addGtranslateDropdown']);
        add_action('pmb_pro_print_page_window_end', [$this,'addGtranslateDropdown']);
        //add_action('wp_enqueue_scripts', [$this,'enqueueScripts']);
    }

    public function addGtranslateDropdown(){
        echo '<div style="text-align:center; margin:20px">' . do_shortcode('[gtranslate]') . '</div>';
        $this->enqueueScripts();
    }

    public function enqueueScripts(){
        // gTranslate adds a unique, random code onto the end of their script handle. So we need to do some digging to find it.
        global $wp_scripts;
        foreach($wp_scripts->queue as $key => $item){
            if(strpos($item,'gt_widget_script_') === 0){
                $gt_script_handle = $item;
            }
        }
        if(! $gt_script_handle){
            return;
        }
        // Modify gTranslate's variable to disable redirecting when switching languages
        wp_add_inline_script($gt_script_handle,'   
        for (var prop in window.gtranslateSettings) {
            if (Object.prototype.hasOwnProperty.call(window.gtranslateSettings, prop)) {
                var prop_value = window.gtranslateSettings[prop];
                if(typeof(prop_value.url_structure) !== "undefined"){
                    prop_value.url_structure="none";
                }
            }
        }
        jQuery(document).on("pmb_doc_conversion_ready", function(){
            jQuery(".gtranslate_wrapper").remove();
        });        
',
            'before');
    }
}