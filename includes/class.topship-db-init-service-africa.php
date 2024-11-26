<?php
class Topship_Registration_Table {

    // Define the table name for easy access
    public static $table_name;

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
    public static function get_user_record() {
        global $wpdb;

        // Query to fetch the first record based on the primary key (id)
        $query = $wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " ORDER BY id ASC LIMIT 1"
        );

        // Execute the query and get the result
        $result = $wpdb->get_row($query, ARRAY_A);

        return $result;
    }
}
class Topship_Shipment_Bookings_Table {

    // Define the table name for easy access
    private static $table_name;

    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'topship_shipment_bookings';
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

class ValueAddedTaxes_Table {

    // Define the table name for easy access
    private static $table_name;

    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'topship_value_added_taxes';

        // Hook to run the table creation on plugin activation
        register_activation_hook(__FILE__, [self::class, 'create_table']);
    }

    public static function create_table() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE " . self::$table_name . " (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            valueAddedTaxCharge DECIMAL(16, 2) NOT NULL,
            pickup DECIMAL(16, 2) NOT NULL,
            pick_up_url TEXT NOT NULL,
            code VARCHAR(255) UNIQUE NOT NULL,
            price DECIMAL(16, 2) NOT NULL,
            order_id VARCHAR(255) NULL,
            pricingTier VARCHAR(255) NULL,
            shop_id BIGINT(20) UNSIGNED NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB " . $wpdb->get_charset_collate() . ";";

        // Execute the SQL statement
        dbDelta($sql);
    }

    /**
     * Generate a unique code.
     *
     * @return string The unique code.
     */
    public static function generate_unique_code() {
        global $wpdb;

        do {
            // Generate a random alphanumeric code (e.g., VAT-XXXXXX)
            $code = 'VAT' . strtoupper(bin2hex(random_bytes(3)));

            // Check if the code already exists in the database
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM " . self::$table_name . " WHERE code = %s",
                    $code
                )
            );
        } while ($exists > 0); // Repeat if the code is not unique

        return $code;
    }

    /**
     * Retrieve a Value Added Tax record by its unique code.
     *
     * @param string $code The unique code to search for.
     * @return object|null The record as an object or null if not found.
     */
    public static function getValueAddedTaxByCode($code) {
        global $wpdb;

        // Query the database for the record with the given code
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . self::$table_name . " WHERE code = %s",
                $code
            )
        );

        return $result; // Returns null if not found
    }

    /**
     * Update the order_id field for a record identified by its unique code.
     *
     * @param string $code The unique code.
     * @param string $orderId The new order_id value.
     * @return bool True if the update was successful, false otherwise.
     */
    public static function updateOrderIdByCode($code, $orderId) {
        global $wpdb;

        // Update the record
        $result = $wpdb->update(
            self::$table_name,
            ['order_id' => $orderId],  // Fields to update
            ['code' => $code],        // Where condition
            ['%s'],                   // Format of updated fields
            ['%s']                    // Format of where condition
        );

        return $result !== false; // True if successful, false otherwise
    }
    /**
     * Delete records older than 24 hours from the value_added_taxes table.
     *
     * @return int The number of rows deleted.
     */
    public static function deleteOldValueAddedTaxes() {
        global $wpdb;

        // Calculate the timestamp for 24 hours ago
        $threshold = gmdate('Y-m-d H:i:s', strtotime('-24 hours'));

        // Delete records older than 24 hours
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM " . self::$table_name . " WHERE created_at < %s",
                $threshold
            )
        );

        return $deleted; // Returns the number of rows deleted
    }

    /**
     * Create a new value-added tax record.
     *
     * @param float  $valueAddedTaxCharge The VAT charge.
     * @param float  $pickup              The pickup charge.
     * @param string $pickUpUrl           The pickup URL.
     * @param float  $price               The total price.
     * @param string $orderId             The order ID (optional).
     * @param string $pricingTier         The pricing tier (optional).
     * @param int    $shopId              The shop ID (optional).
     * @param string $code                A unique code (optional, auto-generated if not provided).
     * @return int|false The ID of the newly inserted record, or false on failure.
     */
    public static function createValueAddedTax(
        $valueAddedTaxCharge,
        $pickup,
        $pickUpUrl,
        $price,
        $orderId = null,
        $pricingTier = null,
        $shopId = null,
        $code = null
    ) {
        global $wpdb;

       if(self::getValueAddedTaxByCode($code)!=null) return false;
        // Insert the record
        $result = $wpdb->insert(
            self::$table_name,
            [
                'valueAddedTaxCharge' => $valueAddedTaxCharge,
                'pickup' => $pickup,
                'pick_up_url' => $pickUpUrl,
                'price' => $price,
                'order_id' => $orderId,
                'pricingTier' => $pricingTier,
                'shop_id' => $shopId,
                'code' => $code,
            ],
            [
                '%f', // valueAddedTaxCharge
                '%f', // pickup
                '%s', // pick_up_url
                '%f', // price
                '%s', // order_id
                '%s', // pricingTier
                '%d', // shop_id
                '%s', // code
            ]
        );

        return $result ? $wpdb->insert_id : false;
    }
}

class Access_Tokens_Table {
    // Define the table name for easy access
    private static $table_name;

    /**
     * Initialize the class and set the table name
     */
    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'topship_access_tokens';

        // Hook to run the table creation on plugin activation
        register_activation_hook(__FILE__, [self::class, 'create_table']);
        // Hook to run table deletion on plugin deactivation, if required
        register_deactivation_hook(__FILE__, [self::class, 'delete_table']);
    }

    /**
     * Create the `access_tokens` table
     */
    public static function create_table() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE " . self::$table_name . " (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            token TEXT NOT NULL,
            expires_at DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (user_id) REFERENCES " . $wpdb->prefix . "users(ID) ON DELETE CASCADE
        ) ENGINE=InnoDB " . $wpdb->get_charset_collate() . ";";

        // Execute the SQL statement
        dbDelta($sql);
    }

    /**
     * Delete the `access_tokens` table
     */
    public static function delete_table() {
        global $wpdb;
        $sql = "DROP TABLE IF EXISTS " . self::$table_name;
        $wpdb->query($sql);
    }
}