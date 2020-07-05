<?php
namespace PrintMyBlog\controllers\helpers;
use PrintMyBlog\system\CustomPostTypes;
use WP_List_Table;
use WP_Post;
use WP_Query;

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
if (!class_exists('WP_List_Table')) {
    require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/** * Create a new table class that will extend the WP_List_Table */
class ProjectsListTable extends WP_List_Table

{
    public function __construct()
    {
        parent::__construct(array(
            'singular' => esc_html__('Project', 'print-my-blog'),
            'plural' => esc_html__('Projects', 'print-my-blog'),
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
        /** Process bulk action */
        $this->process_bulk_action();
        $per_page = $this->get_items_per_page('records_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items = self::record_count();
        $data = $this->get_records($per_page, $current_page);
        $this->set_pagination_args(
            ['total_items' => $total_items, //WE have to calculate the total number of items
             'per_page' => $per_page // WE have to determine how many items to show on a page
            ]);
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
            'post_type' => CustomPostTypes::PROJECTS,
        ];
        if(isset($per_page)){
            $params['posts_per_page'] = $per_page;
        }
        if(isset($page_number)){
            $params['paged'] = $page_number;
        }
        return new WP_Query(
            $params
        );
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     * * @return Array
     */
    function get_columns()
    {
        $columns = [
            'cb' => '<input type="checkbox" />',
            'ID' => __('ID', 'print-my-blog') ,
            // 'second_column_name' => __('Second Column Name', 'ux') ,
            // 'third_column_name' => __('Third Column Name', 'ux') ,
            // 'fourth_column_name' => __('Fourth Column Name', 'ux') ,
            // 'fifth_column_name' => __('Fifth Column Name', 'ux') ,
            // 'sicth_column_name' => __('Sixth Column Name', 'ux') ,
            // 'created' => __('Date', 'ux')
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

    /**
     * Render the bulk edit checkbox
     * * @param WP_Post $post
     * * @return string
     */
    function column_cb($post)
    {
        return sprintf('<input type="checkbox" name="ID[]" value="%s" />', $post->ID);
    }

    /**
     * Render the bulk edit checkbox
     * * @param WP_Post $post
     * * @return string
     */
    function column_ID($post)
    {
        return sprintf(
            '<a href="%s" class="btn btn-primary"/>%s</a>',
            add_query_arg(
                [
                    'ID' => $post->ID,
                    'action' => 'edit',
                ],
                admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
            ),
            $post->post_name
        );
    }

    /**
     * Returns an associative array containing the bulk action
     * * @return array */
    public function get_bulk_actions()
    {
        return [];
        // $actions = ['bulk-delete' => 'Delete'];
        // return $actions;
    }
    public function process_bulk_action()
    {
        // Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {
            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr($_REQUEST['_wpnonce']);
            if (!wp_verify_nonce($nonce, 'bx_delete_records')) {
                die('The request has expired. Please refresh the previous page and try again.');
            }
            else {
                $this->delete_records(absint($_GET['record']));
                $redirect = admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH);
                wp_redirect($redirect);
                exit;
            }
        }

        // If the delete bulk action is triggered
        if ((isset($_POST['action']) &&
        $_POST['action'] == 'bulk-delete') || (isset($_POST['action2']) &&
        $_POST['action2'] == 'bulk-delete')) {
            $delete_ids = esc_sql($_POST['bulk-delete']);
            // loop over the array of record IDs and delete them
            foreach ($delete_ids as $id) {
                wp_delete_post($id,true);
            }

            $redirect = admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH);
            wp_redirect($redirect);
            exit;
        }
    }
    /**
     * Delete a record.
     * * @param int $id customer ID
     */
    public function delete_records($id)
    {
        {
            global $wpdb;
            $wpdb->delete("custom_records", ['id' => $id], ['%d']);
        }
    }

    /**
     *Text displayed when no record data is available
     */
    public function no_items()
    {
        _e('No record found in the database.', 'bx');
    }

    /**
     * Returns the count of records in the database.
     * * @return null|string
     */
    public function record_count()
    {
        $wp_query = $this->wp_query();
        return $wp_query->post_count;
    }
}
