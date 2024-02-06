<?php
// Create  Books_Info page in admin_menu
class Books_Info_Admin_Page
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }


    public function add_admin_menu()
    {
        add_menu_page('Books Info', 'Books Info', 'manage_options', 'books-info', [$this, 'books_info_page']);
    }


    public function books_info_page()
    {

        global $wpdb;
        $table_name = $wpdb->prefix . 'books_info';
        $books_info = $wpdb->get_results("SELECT * FROM $table_name");
        $books_info_table = new Books_Info_List_Table();
        $books_info_table->prepare_items($books_info);
        $books_info_table->display();
    }
}


// Create a custom class that extends WP_List_Table
class Books_Info_List_Table extends WP_List_Table
{
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();

        // Retrieve data from the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'books_info';
        $books_info = $wpdb->get_results("SELECT * FROM $table_name");

        // Set up the data for the table
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $books_info;
    }

    public function get_columns()
    {
        return array(
            'ID' => 'ID',
            'post_id' => 'Post ID',
            'isbn' => 'ISBN'
        );
    }

    public function column_default($item, $column_name)
    {
        return $item->$column_name;
    }
}


$books_info_admin_page = new Books_Info_Admin_Page();
