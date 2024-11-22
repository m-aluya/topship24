<?php
class Topship_Registration_Table {

    // Define the table name for easy access
    private static $table_name;

    public static function init() {
       // echo 'hello';
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'registrations';
        //die(__FILE__);
        // Hook to run the table creation on plugin activation
       // register_activation_hook(__FILE__, [self::class, 'create_table']);
    }

    public static function create_table() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE " . self::$table_name . " (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            email VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            phoneNumber VARCHAR(50) NOT NULL,
            fullName VARCHAR(255) NOT NULL,
            topshipId VARCHAR(255) NOT NULL,
            reg_id VARCHAR(255) NOT NULL,
            country VARCHAR(255) NOT NULL,
            country_code VARCHAR(10) NOT NULL,
            state VARCHAR(255) NOT NULL,
            city VARCHAR(255) NOT NULL,
            address TEXT NOT NULL,
            zipcode VARCHAR(20) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB " . $wpdb->get_charset_collate() . ";";

        // Execute the SQL statement
        dbDelta($sql);
    }
}