<?php


namespace PrintMyBlog\compatibility\plugins;


use Twine\compatibility\CompatibilityBase;
use TablePress as TablePressInit;

class TablePress extends CompatibilityBase
{

    /**
     * Get TablePress to register its shortcodes on our special AJAX request too.
     * See https://wordpress.org/support/topic/short-code-displaying-when-call-post-data-through-ajax/
     */
    public function setRenderingHooks()
    {
        if(defined('DOING_AJAX') && DOING_AJAX){
            TablePressInit::$controller = TablePressInit::load_controller('frontend');
            TablePressInit::$controller->init_shortcodes();
            add_filter('tablepress_table_js_options',[$this,'optimize_tables_for_pmb']);
        }
        parent::setRenderingHooks(); // TODO: Change the autogenerated stub
    }

    /**
     * Filters TablePress' options to remove interactive elements like pagination, sorting, and searching.
     * See \TablePress_Frontend_Controller::shortcode_table()
     * @param $original_options
     * @return array
     */
    public function optimize_tables_for_pmb($original_options){
        $original_options['datatables_paginate'] = false;
        $original_options['datatables_sort'] = false;
        $original_options['datatables_filter'] = false;
        return $original_options;
    }
}