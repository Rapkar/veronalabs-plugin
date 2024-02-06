<?php

// Create BookStore post type , taxonomies and metabox of that 
class BookStoreHandler
{
    public function __construct()
    {

        add_action('init', [$this, 'makePostType']);
        add_action('init', [$this, 'makeTaxonomies'], 0);
        add_action('init', [$this, 'makebookStoreTable']);
        add_action('save_post', [$this, 'savebookStoreMetaBox']);
        add_action('add_meta_boxes', [$this, 'makeMetaBox']);
    }

    public function makePostType()
    {
        $this->bookStorePostType();
    }
    public function makeTaxonomies()
    {
        $this->bookStoreTaxonomies();
    }
    public function makeMetaBox()
    {
        $this->bookStoreMetaBox();
    }
    public function makeMetaBoxform()
    {
        $this->bookStoreMetaBoxform();
    }
    public function makebookStoreTable()
    {
        $this->bookStoreTable();
    }
    public function savebookStoreMetaBox()
    {
        global $post_id;
        $this->bookStoreMetaBoxSave($post_id);
    }

    // Make BookStore Table  with [books_info] name
    protected function bookStoreTable()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'books_info';

        // Check if the table already exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            // Table doesn't exist, so create it
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                    ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    post_id bigint(20) UNSIGNED NOT NULL,
                    isbn varchar(255) NOT NULL,
                    PRIMARY KEY  (ID),
                    KEY post_id (post_id)
                ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
    // Make BookStore postType 
    protected function bookStorePostType()
    {
        $labels = [
            'name'                  => _x('Books', 'Post type general name', 'textdomain'),
            'singular_name'         => _x('Book', 'Post type singular name', 'textdomain'),
            'menu_name'             => _x('Books', 'Admin Menu text', 'textdomain'),
            'name_admin_bar'        => _x('Book', 'Add New on Toolbar', 'textdomain'),
            'add_new'               => __('Add New', 'textdomain'),
            'add_new_item'          => __('Add New Book', 'textdomain'),
            'new_item'              => __('New Book', 'textdomain'),
            'edit_item'             => __('Edit Book', 'textdomain'),
        ];
        $args = array(
            'public'    => true,
            'labels'     =>   $labels,
            'menu_icon' => 'dashicons-book',
        );
        register_post_type('book', $args);
    }

    // Make BookStore Taxonomies  ['Publisher', 'Authors']
    protected function bookStoreTaxonomies()
    {

        $tax = ['Publisher', 'Authors'];
        foreach ($tax as $item) {
            $labels = [
                'name' => __($item, 'book', 'BookStorePlugin'),
                'singular_name' => __($item, 'book', 'BookStorePlugin'),
            ];
            register_taxonomy($item, 'book', ['labels' => $labels]);
        }
    }

    // Make BookStore ISBN Number field 
    protected  function bookStoreMetaBox()
    {
        add_meta_box('isbnnumber', __('ISBN Number', 'BookStorePlugin'), [$this, 'makeMetaBoxform'], 'book', 'side', 'low');
    }
    protected function bookStoreMetaBoxform()
    {
        global $post_id;
        $value = '';
        if (get_post_meta($post_id, 'isbn', true) != '') {
            $value = get_post_meta($post_id, 'isbn', true);
        }
        echo '<input type="number" name="isbn" value="' . $value . '" placeholder="ISBN NUMBER">';
    }
    protected function bookStoreMetaBoxSave($post_id)
    {
        if (array_key_exists('isbn', $_POST)) {
            update_post_meta($post_id, 'isbn', sanitize_text_field($_POST['isbn']));
            // Insert into books_info table
            global $wpdb;
            $table_name = $wpdb->prefix . 'books_info';
            $isbn = sanitize_text_field($_POST['isbn']);
            $post_id = $post_id;
            $wpdb->insert(
                $table_name,
                array(
                    'post_id' => $post_id,
                    'isbn' => $isbn
                ),
                array('%d', '%s')
            );
        }
    }
}
 
$BookStoreHandler = new BookStoreHandler;

// Make table  book in dashboard 
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/screen.php');
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
require_once 'BookInfo.php';
