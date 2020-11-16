<?php

namespace PrintMyBlog\controllers\helpers;

use PrintMyBlog\controllers\Admin;
use PrintMyBlog\system\CustomPostTypes;
use WP_List_Table;
use WP_Post;
use WP_Query;

// phpcs:disable PSR12.Files.FileHeader.IncorrectOrder
// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
/**
 * Class ProjectsListTable
 *
 * Description
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */

/** * Create a new table class that will extend the WP_List_Table */

class ProjectsListTable extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'project',
            'plural' => 'projects',
            // 'ajax' => true
        ));
    }

    /** * Prepare the items for the table to process
     * * @return Void
     */
    public function prepare_items()
    {
        // $this->_column_headers = $this->get_column_info();
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array(
            $columns,
            $hidden,
            $sortable
        );
        $per_page = $this->get_items_per_page('records_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items = self::recordCount();
        $data = $this->get_records($per_page, $current_page);
        $this->set_pagination_args(
            [
                'total_items' => $total_items, //WE have to calculate the total number of items
                'per_page' => $per_page // WE have to determine how many items to show on a page
            ]
        );
        $this->items = $data;
    }

    /** *
    Retrieve records data from the database
     * * @param int $per_page
     * @param int $page_number
     * * @return mixed
     */
    public function get_records($per_page = 10, $page_number = 1)
    {
        $wp_query = $this->wp_query($per_page, $page_number);
        return $wp_query->posts;
        global $wpdb;
        $sql = "select * from {$wpdb->prefix}pmb_projects";
        // if (isset($_REQUEST['s'])) {
        //     $sql.= ' where column1 LIKE "%' . $_REQUEST['s'] . '%" or column2 LIKE "%' . $_REQUEST['s'] . '%"';
        // }
        // if (!empty($_REQUEST['orderby'])) {
        //     $sql.= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
        //     $sql.= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        // }
        // $sql.= " LIMIT $per_page";
        // $sql.= ' OFFSET ' . ($page_number - 1) * $per_page;
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        return $result;
    }


    /**
     * @param int $per_page
     * @param int $page_number
     * @return WP_Query
     */
    protected function wp_query($per_page = null, $page_number = null)
    {
        $params = [
            'post_type' => CustomPostTypes::PROJECT,
        ];
        if (isset($per_page)) {
            $params['posts_per_page'] = $per_page;
        }
        if (isset($page_number)) {
            $params['paged'] = $page_number;
        }
        return new WP_Query(
            $params
        );
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     * * @return array
     */
    public function get_columns()
    {
        $columns = [
            'cb' => '<input type="checkbox" />',
            'ID' => __('ID', 'print-my-blog') ,
        ];
        return $columns;
    }

    public function get_hidden_columns()
    {
        // Setup Hidden columns and return them
        return array();
    }

    /**
     * Columns to make sortable.
     * * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'ID' => array('ID',true) ,
            // 'second_column_name' => array('second_column_name',false) ,
            // 'fifth_column_name' => array('fifth_column_name',false) ,
            // 'created' => array('created',false)
        );
        return $sortable_columns;
    }

    protected function get_bulk_actions()
    {
        return [
            'delete' => __('Delete')
        ];
    }

    /**
     * Render the bulk edit checkbox
     * * @param WP_Post $post
     * * @return string
     */
    public function column_cb($post)
    {
        return sprintf('<input type="checkbox" name="ID[]" value="%s" />', $post->ID);
    }

    /**
     * Render the bulk edit checkbox
     * * @param WP_Post $post
     * * @return string
     */
    public function column_ID($post)
    {
        $title = $post->post_title ? $post->post_title : __('Untitled', 'print-my-blog');
        return sprintf(
            '<a href="%s" class="btn btn-primary"/>%s</a>',
            add_query_arg(
                [
                    'ID' => $post->ID,
                    'action' => 'edit',
                    'subaction' => Admin::SLUG_SUBACTION_PROJECT_SETUP
                ],
                admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
            ),
            $title
        );
    }

    /**
     *Text displayed when no record data is available
     */
    public function no_items()
    {
        _e('Click "Add New" 👆 To Make Your First Project!', 'print-my-blog');
    }

    /**
     * Returns the count of records in the database.
     * * @return null|string
     */
    public function recordCount()
    {
        $wp_query = $this->wp_query();
        return $wp_query->post_count;
    }
}
