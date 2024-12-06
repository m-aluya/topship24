<?php
/**
 * Plugin name: Topship
 * Description: Send and Receive items from your doorstep to any location in the world.
 * Author : Topship
 * Author URI: https://topship.africa
 * Plugin URI: https://topship.africa
 * Version: 1.0.10
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!defined("ABSPATH")){
    exit;
}
require_once plugin_dir_path(__FILE__) . 'includes/class.topship-delivery-service-africa.php';
require_once plugin_dir_path(__FILE__) . 'includes/class.topship-api-service-africa.php';
require_once plugin_dir_path(__FILE__) . 'includes/class.topship-db-init-service-africa.php';
require_once plugin_dir_path(__FILE__) . 'includes/class.topship-helper.php';
//require_once plugin_dir_path(__FILE__) . 'includes/shipping_rate.php';



class topshipLastMileDeliveryServiceAfrica {
    public function __construct() {
        add_action('wp_ajax_andaf', [$this, 'andaf']);
        // add_action('wp_ajax_nopriv_adaf', 'andaf');
        //wp_enqueue_jquery();



      /*  add_action('woocommerce_cart_calculate_fees', [$this,'andaf']);*/

    /*    add_filter('woocommerce_billing_fields', 'make_billing_postcode_required');
        add_filter('woocommerce_shipping_fields', 'make_shipping_postcode_required');*/

        add_action('woocommerce_shipping_init', [$this, 'init_shipping_method']);
        add_filter('woocommerce_shipping_methods', [$this, 'add_shipping_method']);

       /* add_action('woocommerce_checkout_order_processed', 'handle_topship_checkout_submission', 10, 3);*/

        add_action('woocommerce_thankyou', [$this,'handle_topship_checkout_submission']);

        add_action('woocommerce_checkout_create_order', [$this,'handle_topship_checkout_create_order']);


        /* add_action('woocommerce_checkout_order_processed', [this,'handle_topship_checkout_submission']);*/

        add_action('wp_enqueue_scripts', [$this, 'enqueue_topship_shipping_scripts']);

        add_action('admin_menu', [$this, 'topship_admin_page_plugin_menu']);

        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

        add_action('plugins_loaded', function() {
            Topship_API_Service_Africa::init();
        });

        TableManager::initialize_tables();
       // Topship_Registration_Table::init();
        //Topship_Registration_Table::create_table();
        //ValueAddedTaxes_Table::init();
        // ShipmentBookingsTable::init();

        //ShipmentBookingsTable::init();
        //ShipmentBookingsTable::create_table();
        //Shopify_Shipments_Table::init();
       // Shopify_Shipments_Table::create_table();
        //ValueAddedTaxes_Table::create_table();
        //register_activation_hook(__FILE__, ['Topship_Registration_Table', 'create_table']);
    }

    public function init_shipping_method() {
        include_once 'class-topship-shipping-method.php';
    }

    public function add_shipping_method($methods) {
        $methods['topship_last_mile'] = 'Topship_Shipping_Method';
        return $methods;
    }

    /**
     * Make the billing postcode field required.
     *
     * @param array $fields The billing fields.
     * @return array Modified fields.
     */
    function make_billing_postcode_required($fields) {
        if (isset($fields['billing_postcode'])) {
            $fields['billing_postcode']['required'] = true;
        }
        return $fields;
    }

    /**
     * Make the shipping postcode field required.
     *
     * @param array $fields The shipping fields.
     * @return array Modified fields.
     */
    function make_shipping_postcode_required($fields) {
        if (isset($fields['shipping_postcode'])) {
            $fields['shipping_postcode']['required'] = true;
        }
        return $fields;
    }

    public function topship_admin_page_plugin_menu() {
        add_menu_page(
            'Create your Topship account',
            'Topship',
            'manage_options',
            Class_topship_delivery_service_africa::topshipLink(),
            [$this, 'topship_admin_page_content']
        );
        add_submenu_page(
            Class_topship_delivery_service_africa::topshipLink(),
            'Topship Guide',
            'Guide',
            'manage_options',
            Class_topship_delivery_service_africa::topshipLink() . '-guide',
            [$this, 'topship_guide_page']
        );

        add_submenu_page(
            Class_topship_delivery_service_africa::topshipLink(),
            'Contact Us',
            'Contact Us',
            'manage_options',
            Class_topship_delivery_service_africa::topshipLink() . '-contact-us',
            [$this, 'topship_contact_us_page_content']
        );

        add_submenu_page(
            Class_topship_delivery_service_africa::topshipLink(),
            'Pending Request',
            'Pending Request',
            'manage_options',
            Class_topship_delivery_service_africa::topshipLink() . '-pending',
            [$this, 'topship_pending_request_content']
        );

    }

    public function topship_guide_page(){
        Class_topship_delivery_service_africa::topship_guide_page();
    }
    public function enqueue_admin_scripts($hook) {
        //wp_enqueue_style('distort', plugins_url('css/distort.css', __FILE__));
        if (
            strpos($hook, Class_topship_delivery_service_africa::topshipLink()) !== false ||
            strpos($hook, Class_topship_delivery_service_africa::topshipLink() . '-contact-us') !== false ||
            strpos($hook, Class_topship_delivery_service_africa::topshipLink() . '-guide') !== false
        ) {
            //wp_enqueue_style('distort', plugins_url('css/distort.css', __FILE__));
            // Enqueue Bootstrap CSS and JS
            wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css');
            wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js', ['jquery'], null, false);

            // Enqueue Vue.js 3 globally
            wp_enqueue_script('vue-js', 'https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js', [], null, false);

            // Enqueue custom styles and external libraries
            /*  wp_enqueue_style('uptown-css', plugins_url('css/uptown.css', __FILE__));*/
            wp_enqueue_style('toastify-css', 'https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css');
            wp_enqueue_style('font-awesome-css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', [], null);

            // Enqueue custom scripts and external libraries
            wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js', [], null, true);
            wp_enqueue_script('jquer', 'https://code.jquery.com/jquery-3.7.1.min.js', [], null, false);
            wp_enqueue_script('toastify-js', 'https://cdn.jsdelivr.net/npm/toastify-js', [], null, true);
            wp_enqueue_script('axios', 'https://cdnjs.cloudflare.com/ajax/libs/axios/1.7.7/axios.min.js', [], null, true);

        }
    }


    public function enqueue_topship_shipping_scripts1() {
        // Check if we are on a WooCommerce page (cart, checkout, etc.)
        if (is_cart() || is_checkout()) {

            wp_enqueue_script(
                'topship-shipping',

                plugins_url('js/topship-shipping.js', __FILE__),
                array('jquery'),
                '1.0.0',
                true
            );

            // Localize the script with new data
            wp_localize_script(
                'topship-shipping',
                'topship_rate_object',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('shipping_rate_nonce')
                )
            );


            // wp_enqueue_script(
            //     'topship-shipping-script', // Handle
            //     plugins_url('js/topship-shipping.js', __FILE__), // Path to your JS file
            //     ['jquery'], // Dependencies
            //     null, // Version
            //     true // Load in footer
            // );

            // wp_localize_script(
            //     'topship-shipping',
            //     'ajax_object',
            //     array(
            //         'ajax_url' => admin_url('admin-ajax.php'),
            //         'nonce' => wp_create_nonce('shipping_rate_nonce')
            //     )
            // );



        }
    }


    public function andaf($cart){
        error_log('andaf is running');
        error_log(json_encode($cart));
        error_log(json_encode($_POST));

        error_log('WC Customer Billing Country: ' . WC()->customer->get_billing_country());
        error_log('WC Customer Shipping Postcode: ' . WC()->customer->get_shipping_postcode());


        $customer = WC()->customer; // Assuming this is the customer object

        if ($customer instanceof WC_Customer) {
            // Access the shipping data
            $shipping_data = $customer->get_shipping();
            $first_name =$shipping_data['first_name'];
            $address_1 =$shipping_data['address_1'];
            $city =$shipping_data['city'];
            $country =$shipping_data['country'];
            $state =$shipping_data['state'];
            $phone =$shipping_data['phone'];
            // Log or use the shipping data
            error_log('$first_name: ' . $first_name);
            error_log('$address_1: ' . $address_1);
            error_log('$city: ' . $city);
            error_log('$country: ' . $country);
            error_log('$state: ' . $state);
            error_log('$phone: ' . $phone);

        }



        $custom_fee = 1000;

        if ($custom_fee > 0) {
            $cart->add_fee(__('Custom Delivery Fee', 'topship'), $custom_fee, true);
        }
    }

    /**
     * Custom logic to calculate fees based on address and cart.
     *
     * @param string $billing_country The billing country.
     * @param string $shipping_postcode The shipping postal code.
     * @param float $cart_total The total cart amount.
     * @return float Calculated fee.
     */
    private function calculate_fee_based_on_address_and_cart($billing_country, $shipping_postcode, $cart_total) {
        $base_fee = 500; // Example base fee in currency
        $distance_fee = 0;

        // Add logic to calculate distance-based fees (e.g., based on postal codes)
        if ($billing_country === 'NG' && !empty($shipping_postcode)) {
            $distance_fee = strlen($shipping_postcode) * 10; // Example logic
        }

        // Calculate final fee (example logic)
        return $base_fee + $distance_fee + ($cart_total * 0.01); // 1% of the cart value
    }

    function handle_topship_checkout_create_order($order, $data) {
        // Ensure the $order object is valid
        if (!$order instanceof WC_Order) {
            error_log('Invalid order object');
            return;
        }

        // Access the shipping method(s) from the order
        $shipping_methods = $order->get_shipping_methods();

        foreach ($shipping_methods as $shipping_method) {
            // Get the shipping method ID
            $method_id = $shipping_method->get_method_id();
            $method_title = $shipping_method->get_method_title(); // Optional: Get the method title

            // Log or handle the selected shipping method
            error_log('Selected shipping method: ' . $method_id);
            error_log('Shipping method title: ' . $method_title);

            // Perform logic for Topship-specific methods
            if (strpos($method_id, 'topship') === 0) {
                error_log('Topship shipping method selected.');

                // Perform Topship-specific actions here
            }
        }
    }

    public function topship_contact_us_page_content() {
        Class_topship_delivery_service_africa::topship_contact_us_page();
    }

    public function topship_pending_request_content() {
        Class_topship_delivery_service_africa::topship_pending_request_page();
    }

    public function topship_admin_page_content() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['my_admin_form_submitted'])) {
            $my_field_value = sanitize_text_field($_POST['my_field_name']);
            update_option('my_field_name', $my_field_value);
            echo '<div class="updated"><p>Settings saved.</p></div>';
        }

        $user=Topship_Registration_Table::get_user_record();


        $stored_value = get_option('my_field_name', '');
        if($user==null) {
            Class_topship_delivery_service_africa::topship_register();
        }else{
            Class_topship_delivery_service_africa::topship_dashboard();
        }
    }


    /**
     * Enqueue necessary scripts.
     */
    public function enqueue_topship_shipping_scripts() {
        wp_enqueue_script('jquery');
    }

    /**
     * Handle WooCommerce checkout submission.
     *
     * @param int $order_id Order ID.
     * @param array $posted_data Posted checkout data.
     * @param WC_Order $order WooCommerce order object.
     */
   /* function handle_topship_checkout_submission($order_id, $posted_data, $order) {
        // Get the order object
        $order = wc_get_order($order_id);

        // Get the chosen shipping method ID
        $chosen_shipping_methods = $order->get_shipping_methods();
        foreach ($chosen_shipping_methods as $shipping_method) {
            $shipping_method_id = $shipping_method->get_method_id(); // e.g., 'topship_standard'
            $shipping_method_instance_id = $shipping_method->get_instance_id(); // e.g., '123'
        }

        // Log shipping method for debugging
        error_log('Chosen shipping method: ' . $shipping_method_id);

        // Check if the shipping method is from Topship
        if (strpos($shipping_method_id, 'topship') === 0) {
            // Example: Get shipping details
            $shipping_address = [
                'country'  => $order->get_shipping_country(),
                'state'    => $order->get_shipping_state(),
                'postcode' => $order->get_shipping_postcode(),
                'city'     => $order->get_shipping_city(),
                'address'  => $order->get_shipping_address_1(),
            ];

            error_log('Shipping address: ' . json_encode($shipping_address));

            // Handle Topship-specific logic
            $shipment_data = [
                'order_id'      => $order_id,
                'shipping_rate' => $shipping_method_id,
                'shipping_cost' => $shipping_method->get_total(),
                'address'       => $shipping_address,
            ];

            // Example: Call Topship API for shipping creation
            $response = Class_topship_helper::createShipment($shipment_data);
            error_log('Topship API response: ' . json_encode($response));

            if ($response['success']) {
                // Save shipment tracking ID to the order
                $order->update_meta_data('topship_tracking_id', $response['tracking_id']);
                $order->save();
            } else {
                // Log error and handle fallback logic
                error_log('Topship shipment creation failed: ' . $response['error_message']);
            }
        }
    }*/

   /* function handle_topship_checkout_submission($order_id) {
        // Ensure the order ID is valid

        if (!$order_id) {
            error_log('Invalid order ID');
            return;
        }

        // Load the order object
        $order = wc_get_order($order_id);
        if (!$order) {
            error_log('Could not retrieve order');
            return;
        }

        // Retrieve order details
        $order_total = $order->get_total();
        $billing_email = $order->get_billing_email();
        $billing_address = $order->get_billing_address_1();
        $shipping_method = $order->get_shipping_methods();
        $payment_method = $order->get_payment_method_title();

        // Log or process order details
        error_log("Order ID: $order_id");
        error_log("Order Total: $order_total");
        error_log("Billing Email: $billing_email");
        error_log("Billing Address: $billing_address");
        error_log("Payment Method: $payment_method");


        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Retrieve session data
        $rates = isset($_SESSION['topship_shipping_rates']) ? $_SESSION['topship_shipping_rates'] : [];
        if (!$rates) {
            error_log('No shipping rates found in session.');
            return;
        }
        error_log(json_encode($rates));
        foreach ($shipping_method as $method) {
            $method_id = $method->get_method_id();
            $method_title = $method->get_method_title();
            error_log("Shipping Method ID: $method_id");
            error_log("Shipping Method Title: $method_title");


            // Search for a matching rate
            $matched_rate = null;
            foreach ($rates as $rate_key => $rate) {
                if (isset($rate['label']) && $rate['label'] === $method_title) {
                    $matched_rate = $rate;
                    break;
                }
            }

            if ($matched_rate) {
                error_log("Matched Shipping Rate: " . json_encode($matched_rate));
            } else {
                error_log("No matching rate found for the shipping method title: $method_title");
            }
        }

        $reg=  Topship_Registration_Table::get_user_record();

        $valueAddedMData = ValueAddedTaxes_Table::getValueAddedTaxByCode($matched_rate['id']);

        if (!$valueAddedMData) {
           error_log('valueAddedMData not found');
           return;
        }
        $token = Class_topship_helper::login();

        if (!$token) {
            error_log('token is null');
            return;
        }

        error_log('token is '.$token);

        // Retrieve order items
        $items = $order->get_items();
        foreach ($items as $item) {
            $product_name = $item->get_name();
            $product_qty = $item->get_quantity();
            $product_total = $item->get_total();
            error_log("Product: $product_name, Quantity: $product_qty, Total: $product_total");
        }



    }*/

    function handle_topship_checkout_submission($order_id) {
        // Validate order ID
        if (!$order_id) {
            error_log('Invalid order ID');
            return;
        }

        // Load the WooCommerce order object
        $order = wc_get_order($order_id);
        if (!$order) {
            error_log('Could not retrieve order');
            return;
        }

        // Retrieve order details

        $billing_email = $order->get_billing_email();
        $shipping_address = [
            'address1' => $order->get_shipping_address_1(),
            'address2' => $order->get_shipping_address_2(),
            'city' => $order->get_shipping_city(),
            'state' => $order->get_shipping_state(),
            'country' => $order->get_shipping_country(),
            'zip' => $order->get_shipping_postcode(),
            'phone' => $order->get_billing_phone(),
            'name' => trim($order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name()),
            'country_code' => '', // Add country code logic if applicable
        ];
        $shipping_methods = $order->get_shipping_methods();
        $payment_method = $order->get_payment_method_title();

        // Log order details for debugging
        error_log("Order ID: $order_id");
        //error_log("Order Total: $order_total");
        error_log("Billing Email: $billing_email");
        error_log("Shipping Address: " . json_encode($shipping_address));
        error_log("Payment Method: $payment_method");
        error_log("Shipment Methods: $payment_method");


        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Retrieve shipping rates from session
        $rates = isset($_SESSION['topship_shipping_rates']) ? $_SESSION['topship_shipping_rates'] : [];
        if (empty($rates)) {
            error_log('No shipping rates found in session.');
            return;
        }
        error_log('Shipping Rates: ' . json_encode($rates));

        // Match shipping method with a rate
        $matched_rate = null;
        foreach ($shipping_methods as $method) {
            $method_title = $method->get_method_title();
            foreach ($rates as $rate) {
                if (isset($rate['label']) && $rate['label'] === $method_title) {
                    $matched_rate = $rate;
                    break 2;
                }
            }
        }

        if (!$matched_rate) {
            error_log('No matching shipping rate found.');
            return;
        }
        error_log('Matched Shipping Rate: ' . json_encode($matched_rate));

        // Retrieve user registration details
        $reg = Topship_Registration_Table::get_user_record();
        if (!$reg) {
            error_log('User registration data not found.');
            return;
        }
        ValueAddedTaxes_Table::init();

        // Retrieve value-added tax data
        $valueAddedMData = ValueAddedTaxes_Table::getValueAddedTaxByCode($matched_rate['id']);
        $order_total =$valueAddedMData->price; //$order->get_total();
        if (!$valueAddedMData) {
            error_log('Value-added tax data not found.');
            return;
        }

        // Authenticate to retrieve token
        $token = Class_topship_helper::login();
        if (!$token) {
            error_log('Authentication failed. Token is null.');
            return;
        }
        error_log('Token retrieved: ' . $token);

        // Build items array
        $items = [];
        foreach ($order->get_items() as $item) {
            $items[] = [
                "category" => "Appliance",
                "description" => $item->get_name(),
                "weight" => $item->get_meta('weight', true) ?: 1, // Example: Adjust weight logic as needed
                "quantity" => (float)$item->get_quantity(),
                "value" => (float)$item->get_total(),
            ];
        }
        error_log('Order Items: ' . json_encode($items));

        // Calculate additional charges
        $valueAddedTax = isset($valueAddedMData->valueAddedTaxCharge) ? $valueAddedMData->valueAddedTaxCharge : 0;

        // Call buildPayload
        $payload =    Class_topship_helper::buildPayload(
            $items,
            $valueAddedMData,
            $order_total,
            $shipping_address,
            ['email' => $billing_email],
            $reg,
            $matched_rate,
            $valueAddedTax
        );

        // Log the payload for debugging
        error_log('Payload: ' . json_encode($payload));

        // Log the order in the Shopify shipments table
        $shipment_data = [
            'order_id' => $order_id,
            'shopify_order' => $payload,
        ];

        // Insert into Shopify shipments table
        $shipment_id = Shopify_Shipments_Table::insert_shipment($shipment_data);
        if (!$shipment_id) {
            error_log('Failed to log shipment in Shopify shipments table.');
            return;
        }
        // Send payload to Topship API
        $url = 'https://topship-staging.africa/api/save-shipment';
        try {
            $response = wp_remote_post(
                $url,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode($payload),
                    'timeout' => 45,
                ]
            );

            if (is_wp_error($response)) {
                error_log('API Request failed: ' . $response->get_error_message());
                return;
            }

            // Log the response
            $response_body = wp_remote_retrieve_body($response);

            $response_code = wp_remote_retrieve_response_code($response);

            error_log('API Response Code: ' . $response_code);
            error_log('API Response Body: ' . $response_body);

            if ($response_code === 200) {
                error_log('Shipment successfully created on Topship.');
                Shopify_Shipments_Table::update_booked($shipment_id,1);
                ShipmentBookingsTable::processResponse(json_decode( $response_body,true),$token);
                // Optionally update order meta or post status here
            } else {
                $resp=json_decode($response_body,true);
                Shopify_Shipments_Table::update_reason($shipment_id,$resp['message']);
                error_log('Shipment creation failed: ' . $response_body);
                error_log('Reason: ' . $resp['message']);
            }
        } catch (Exception $e) {
            error_log('Error during Topship API call: ' . $e->getMessage());
        }
        // Continue with further processing, e.g., send the payload to an API
        //$response = Class_topship_helper::sendShipmentPayload($payload, $token);
        /*if ($response) {
            error_log('Shipment created successfully: ' . json_encode($response));
        } else {
            error_log('Failed to create shipment.');
        }*/
    }

}

new topshipLastMileDeliveryServiceAfrica();


// Hook into WordPress to enqueue the shipping script
// function enqueue_shipping_script() {
//     // Enqueue the custom script
//     wp_enqueue_script('custom-shipping-script', plugin_dir_url(__FILE__) . 'js/topship-shipping.js', array('jquery'), '1.0', true);

//     // Localize the script with the AJAX URL
//     wp_localize_script(
//         'custom-shipping-script',
//      'ajax_object', array(
//     'ajax_url' => admin_url('admin-ajax.php'),
//     ));
// }

// Load the script only on WooCommerce checkout or cart pages
// function enqueue_script_on_woocommerce_pages() {
//     if (is_checkout() || is_cart()) {
//         enqueue_shipping_script();
//     }
// }
// add_action('wp_enqueue_scripts', 'enqueue_script_on_woocommerce_pages');




function fetch_shipping_rate() {
    try {
        // Retrieve input data
        $state = sanitize_text_field($_POST['state']);
        $city = sanitize_text_field($_POST['city']);
        $address = sanitize_text_field($_POST['address']);
        $country = sanitize_text_field($_POST['country']);
        $postcode = sanitize_text_field($_POST['postcode']);

        // Calculate shipping rate (example logic)
        $shipping_rate = rand(1000,6000);
        return $shipping_rate;
        //code...
    } catch (\Throwable $th) {
        //throw $th;
        $filePath = "my_error.txt";
        $fileHandle = fopen($filePath, "w");
        fwrite($fileHandle,$th->getMessage());

    }
}

add_action('wp_ajax_fetch_shipping_rate', 'fetch_shipping_rate');
add_action('wp_ajax_nopriv_fetch_shipping_rate', 'fetch_shipping_rate');


function andrex(){
    return 40;
}