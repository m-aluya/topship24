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
            email VARCHAR(255) NOT NULL UNIQUE,
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
class Topship_Shipment_Bookings_Table {

    // Define the table name for easy access
    private static $table_name;

    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'shipment_bookings';
        // Hook to run the table creation on plugin activation
        register_activation_hook(__FILE__, [self::class, 'create_table']);
    }

    public static function create_table() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE " . self::$table_name . " (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            shipment_id VARCHAR(255) NOT NULL UNIQUE,
            created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_date DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            tracking_id VARCHAR(255) NULL,
            shipment_route VARCHAR(255) NOT NULL,
            shipment_status VARCHAR(255) NOT NULL,
            total_charge DECIMAL(15, 4) NOT NULL,
            currency VARCHAR(10) NOT NULL,
            sender_detail JSON NOT NULL,
            receiver_detail JSON NOT NULL,
            items JSON NOT NULL,
            total_weight DECIMAL(10, 4) NOT NULL,
            estimated_delivery_date DATETIME NULL,
            pickup_date DATETIME NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) ENGINE=InnoDB " . $wpdb->get_charset_collate() . ";";

        // Execute the SQL statement
        dbDelta($sql);
    }
}