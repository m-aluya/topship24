<?php
class Shopify_Shipments_Table {

    // Define the table name
    public static $table_name;

    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'topship_shipments';
        // Hook to run the table creation on plugin activation
        register_activation_hook(__FILE__, [self::class, 'create_table']);
    }
    public static function table_exists() {
        global $wpdb;
        self::init();
        return $wpdb->get_var("SHOW TABLES LIKE '" . self::$table_name . "'") === self::$table_name;
    }
    public static function create_table() {
        self::init();
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE " . self::$table_name . " (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id VARCHAR(255) NOT NULL UNIQUE,
            shopify_order LONGTEXT NOT NULL,
            booked TINYINT(1) NOT NULL DEFAULT 0,
            reason TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB " . $wpdb->get_charset_collate() . ";";

        // Execute the SQL statement
        dbDelta($sql);
    }

    public static function insert_shipment($data) {
        self::init();
        global $wpdb;

        $insert_data = [
            'order_id' => $data['order_id'],
            'shopify_order' => json_encode($data['shopify_order']),
            'booked' => 0, // Default to false
            'reason' => $data['reason'] ?? null, // Add reason if provided
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ];

        $result = $wpdb->insert(self::$table_name, $insert_data);

        return $result ? $wpdb->insert_id : false;
    }

    public static function get_shipment_by_order_id($order_id) {
        self::init();
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " WHERE order_id = %s",
            $order_id
        );

        $result = $wpdb->get_row($query, ARRAY_A);

        return $result;
    }

    public static function get_all_shipments() {
        self::init();
        global $wpdb;

        $query = "SELECT * FROM " . self::$table_name . " WHERE booked = 0";

        $results = $wpdb->get_results($query, ARRAY_A);

        return $results;
    }


    public static function update_shipment($id, $data) {
        self::init();
        global $wpdb;

        $update_data = [
            'shopify_order' => json_encode($data['shopify_order']),
            'booked' => $data['booked'], // Update the booked status
            'reason' => $data['reason'], // Update the reason
            'updated_at' => current_time('mysql'),
        ];

        $where = ['id' => $id];

        $result = $wpdb->update(self::$table_name, $update_data, $where);

        return $result !== false;
    }

    public static function update_reason($id, $reason) {
        self::init();
        global $wpdb;

        $update_data = [
            'reason' => $reason,
            'updated_at' => current_time('mysql'),
        ];

        $where = ['id' => $id];

        $result = $wpdb->update(self::$table_name, $update_data, $where);

        return $result !== false;
    }

    public static function update_booked($id, $booked) {
        self::init();
        global $wpdb;

        $update_data = [
            'booked' => $booked ? 1 : 0, // Ensure boolean value
            'updated_at' => current_time('mysql'),
        ];

        $where = ['id' => $id];

        $result = $wpdb->update(self::$table_name, $update_data, $where);

        return $result !== false;
    }



    public static function delete_shipment($id) {
        self::init();
        global $wpdb;

        $where = ['id' => $id];

        $result = $wpdb->delete(self::$table_name, $where);

        return $result !== false;
    }
}


class Topship_Registration_Table {

    // Define the table name for easy access
    public static $table_name;

    public static function init() {
       // echo 'hello';
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'top_ship_registrations';
        //die(__FILE__);
        // Hook to run the table creation on plugin activation
       // register_activation_hook(__FILE__, [self::class, 'create_table']);
    }
    public static function table_exists() {
        global $wpdb;
        self::init();
        return $wpdb->get_var("SHOW TABLES LIKE '" . self::$table_name . "'") === self::$table_name;
    }

    public static function create_table() {
        self::init();
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
        self::init();
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
    public static function table_exists() {
        global $wpdb;
        self::init();
        return $wpdb->get_var("SHOW TABLES LIKE '" . self::$table_name . "'") === self::$table_name;
    }


    public static function create_table() {
        self::init();
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

    public static function table_exists() {
        global $wpdb;
        self::init();
        return $wpdb->get_var("SHOW TABLES LIKE '" . self::$table_name . "'") === self::$table_name;
    }

    public static function create_table() {
        self::init();
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
        self::init();
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
        self::init();
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
        self::init();
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
        self::init();
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
        self::init();
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

    public static function table_exists() {
        global $wpdb;
        self::init();
        return $wpdb->get_var("SHOW TABLES LIKE '" . self::$table_name . "'") === self::$table_name;
    }

    /**
     * Create the `access_tokens` table
     */
    public static function create_table() {
        self::init();
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
        self::init();
        global $wpdb;
        $sql = "DROP TABLE IF EXISTS " . self::$table_name;
        $wpdb->query($sql);
    }
}

class ShipmentBookingsTable {

    // Define the table name for easy access
    private static $table_name;

    /**
     * Initialize the class and set the table name
     */
    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'topship_shipment_bookings';

        // Hook to run the table creation on plugin activation
       // register_activation_hook(__FILE__, [self::class, 'create_table']);
    }

    public static function table_exists() {
        global $wpdb;
        self::init();
        return $wpdb->get_var("SHOW TABLES LIKE '" . self::$table_name . "'") === self::$table_name;
    }

    /**
     * Create the table.
     */
    public static function create_table() {
        self::init();
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $table_name = self::$table_name; // Use the initialized table name
        $charset_collate = $wpdb->get_charset_collate();
        //die('db');
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            shipment_id VARCHAR(255) NOT NULL UNIQUE,
            created_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            tracking_id VARCHAR(255) NULL,
            shipment_route VARCHAR(255) NOT NULL,
            shipment_status VARCHAR(255) NOT NULL,
            total_charge DECIMAL(15, 4) NOT NULL,
            currency VARCHAR(10) NOT NULL,
            sender_detail TEXT NOT NULL,
            receiver_detail TEXT NOT NULL,
            items TEXT NOT NULL,
            total_weight DECIMAL(10, 4) NOT NULL,
            estimated_delivery_date DATETIME NULL,
            pickup_date DATETIME NULL,
            PRIMARY KEY  (id)
        ) ENGINE=InnoDB $charset_collate;";

        dbDelta($sql);
    }


    /**
     * Process response data and insert or update the shipment bookings in the database.
     *
     * @param array $response The shipment response data.
     * @param object $user The user object.
     * @param string $token An optional token (for future use).
     */
    public static function processResponse($response, $token) {
        global $wpdb;
        self::init();

        error_log('API Response Code: ' . json_encode($response));
        foreach ($response as $shipment) {
            $shipment_id = $shipment['id'];

            // Prepare data for insertion or update
            $data = [
                'user_id'                  => 1, // Adjust for WordPress user ID
                'shipment_id'              => $shipment_id,
                'created_date'             => $shipment['createdDate'],
                'updated_date'             => $shipment['updatedDate'],
                'tracking_id'              => $shipment['trackingId'],
                'shipment_route'           => $shipment['shipmentRoute'],
                'shipment_status'          => $shipment['shipmentStatus'],
                'total_charge'             => $shipment['totalCharge'],
                'currency'                 => $shipment['currency'],
                'sender_detail'            => json_encode($shipment['senderDetail']),
                'receiver_detail'          => json_encode($shipment['receiverDetail']),
                'items'                    => json_encode($shipment['items']),
                'total_weight'             => $shipment['totalWeight'],
                'estimated_delivery_date'  => $shipment['estimatedDeliveryDate'],
                'pickup_date'              => $shipment['pickupDate']
            ];

            // Check if the shipment_id already exists
            $existing_shipment = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM " . self::$table_name . " WHERE shipment_id = %s",
                $shipment_id
            ));

            if ($existing_shipment) {
                // Update existing record
                $wpdb->update(
                    self::$table_name,
                    $data,
                    ['shipment_id' => $shipment_id]
                );
            } else {
                // Insert new record
                $wpdb->insert(
                    self::$table_name,
                    $data
                );
            }

            self::payFromWallet($shipment_id, $token);
        }
    }

    public static function get_bookings() {
        global $wpdb;
        self::init();

        $table_name = self::$table_name;
        $query = "SELECT * FROM $table_name ORDER BY id DESC"; // Fetch all bookings ordered by ID descending

        return $wpdb->get_results($query); // Execute the query and return results
    }
    public static function updateBookingStatus($shipmentId, $status) {
        global $wpdb;
        self::init();

        $table_name = self::$table_name;

        // Update the shipment status in the database
        $updated = $wpdb->update(
            $table_name,
            ['shipment_status' => $status], // New status
            ['shipment_id' => $shipmentId], // Where condition
            ['%s'], // Data type for the updated value
            ['%s']  // Data type for the condition
        );

        if ($updated !== false) {
           error_log("Shipment ID $shipmentId updated to status $status");
        } else {
            error_log("Failed to update status for Shipment ID $shipmentId");
        }

        return $updated;
    }

    public static function payFromWallet($shipmentId, $token) {
        $url = Class_topship_helper::$TOPSHIP_BASE_URL . '/pay-from-wallet'; // Use WordPress getenv for environment variables
        $payload = [
            'detail' => [
                'shipmentId' => $shipmentId,
            ],
        ];

        try {
            // Perform the HTTP POST request using WordPress's wp_remote_post
            $response = wp_remote_post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($payload),
                'timeout' => 45, // Optional timeout
            ]);

            if (is_wp_error($response)) {
                // Log the error and return
                error_log('Error during payment request: ' . $response->get_error_message());
                return ['status' => false, 'message' => 'Failed to connect to payment API.'];
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);

            error_log('payment response'.json_encode( $body));
            // Check if payment was successful
            if (isset($body['isPaid']) && $body['isPaid']) {
                // Update the shipment booking status
                self::updateBookingStatus($shipmentId, 'Paid');

                error_log("Payment successful for shipment ID: $shipmentId");
                return ['status' => true, 'message' => "Payment successful for booking $shipmentId"];
            }

            // Log and return a failure message if payment failed
            error_log("Payment failed for shipment ID: $shipmentId");
            return ['status' => false, 'message' => "Payment failed for booking $shipmentId"];
        } catch (Exception $e) {
            // Log exceptions
            error_log('Error during payment request: ' . $e->getMessage());
            return ['status' => false, 'message' => 'An error occurred while processing the payment.'];
        }
    }

}

class TableManager {
    public static function initialize_tables() {
        // Check and initialize Topship_Registration_Table
        Shopify_Shipments_Table::init();
        if (!Shopify_Shipments_Table::table_exists()) {
            Shopify_Shipments_Table::create_table();
        }

        Topship_Registration_Table::init();
        if (!Topship_Registration_Table::table_exists()) {
            Topship_Registration_Table::create_table();
        }

        Topship_Shipment_Bookings_Table::init();
        if (!Topship_Shipment_Bookings_Table::table_exists()) {
            Topship_Shipment_Bookings_Table::create_table();
        }

        // Check and initialize ValueAddedTaxes_Table
        ValueAddedTaxes_Table::init();
        if (!ValueAddedTaxes_Table::table_exists()) {
            ValueAddedTaxes_Table::create_table();
        }

        // Check and initialize ShipmentBookingsTable
      /*  Access_Tokens_Table::init();
        if (!Access_Tokens_Table::table_exists()) {
            Access_Tokens_Table::create_table();
        }*/

        // Check and initialize Shopify_Shipments_Table
        ShipmentBookingsTable::init();
        if (!ShipmentBookingsTable::table_exists()) {
            ShipmentBookingsTable::create_table();
        }
    }
}


