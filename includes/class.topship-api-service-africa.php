<?php
require_once plugin_dir_path(__FILE__) . 'class.topship-db-init-service-africa.php';
require_once plugin_dir_path(__FILE__) . 'class.topship-helper.php';
class Topship_API_Service_Africa {

    public static function init() {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes() {
        // Register the route for registration
        //die('say hello');

        register_rest_route('topship/v1', '/register', [
            'methods' => 'POST',
            'callback' => [self::class, 'handle_registration'],
            'permission_callback' => '__return_true', // Adjust permissions as needed
        ]);
        /*register_rest_route('topship/v1', '/order', [
            'methods' => 'POST',
            'callback' => [self::class, 'handle_orders'],
            'permission_callback' => '__return_true', // Adjust permissions as needed
        ]);*/

        register_rest_route('topship/v1', '/order', [
            'methods' => 'POST',
            'callback' => [self::class, 'handle_orders'],
            'permission_callback' => '__return_true', // Adjust as needed
        ]);

        register_rest_route('topship/v1', '/payforbooking', [
            'methods' => 'POST',
            'callback' => [self::class, 'handle_payment'],
            'permission_callback' => '__return_true', // Adjust as needed
        ]);

        // Register route to get countries
        register_rest_route('topship/v1', '/countries', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_countries'],
            'permission_callback' => '__return_true',
        ]);

        // Register route to get states by country code
        register_rest_route('topship/v1', '/states/(?P<countryCode>.+)', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_states'],
            'permission_callback' => '__return_true',
        ]);

        // Register route to get cities by country code
        register_rest_route('topship/v1', '/cities/(?P<countryCode>.+)', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_cities'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('topship/v1', '/pending', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_pending'],
            'permission_callback' => '__return_true',
        ]);

    }

    public static function get_pending(){
        return Shopify_Shipments_Table::get_all_shipments();
    }
    public static function handle_payment( $request) {
        // Get JSON parameters from the request
        $params = $request->get_json_params();

        // Sanitize the booking ID
        $bookingId = sanitize_text_field($params['bookingId'] ?? '');

        // Authenticate and retrieve a token
        $token = Class_Topship_Helper::login();

        // Process the payment from wallet
        $result = Shipment_Bookings_Table::pay_from_wallet($bookingId, $token);

        if ($result) {
            // Return a successful JSON response
            return new \WP_REST_Response($result, 200);
        } else {
            // Return an error JSON response
            return new \WP_REST_Response(['message' => 'Payment failed'], 500);
        }
    }


    public static function handle_registration($request) {
        $params = $request->get_json_params();
        error_log(json_encode($params));
        // Validate and sanitize inputs
        $first_name = sanitize_text_field($params['firstName'] ?? '');
        $last_name = sanitize_text_field($params['lastName'] ?? '');
        $phone = sanitize_text_field($params['phoneNumber'] ?? '');
        $email = sanitize_email($params['email'] ?? '');
        $address = sanitize_text_field($params['address'] ?? '');
        $country = sanitize_text_field($params['country'] ?? '');
        $state = sanitize_text_field($params['state'] ?? '');
        $city = sanitize_text_field($params['city'] ?? '');
        $postal_code = sanitize_text_field($params['zipcode'] ?? '');
        $password = sanitize_text_field($params['password'] ?? '');
        $recaptchaToken=sanitize_text_field($params['recaptchaToken'] ?? '');
        $countryCode=sanitize_text_field($params['country_code'] ?? '');

        // Prepare data to send to Topship API
        $formData = [
            'firstName' => $first_name,
            'lastName' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'country' => $country,
            'state' => $state,
            'city' => $city,
            'postalCode' => $postal_code,
            'password' => $password,
            'recaptchaToken'=>$recaptchaToken,
            'country_code'=>$countryCode,
        ];

        $formData = [
            'registrationInput' => [
                'email' =>$email,
                'password' => $password,
                'phoneNumber' => $phone,
                'fullName' =>$first_name,
                'recaptchaToken' =>$recaptchaToken,
            ],
        ];

        $rd = json_encode($formData);
        //return
        $signupUrl = 'https://topship-staging.africa/api/signup';

        // Log the JSON encoded $rd in case it's useful for debugging
        error_log('JSON encoded Topship registration data: ' . $rd);

        // Send POST request to Topship API with form data
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $signupUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $rd);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($rd),
        ]);
        curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '\\cacert.pem');

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return new WP_REST_Response([
                'message' => 'Error',
                'error' => curl_error($ch)
            ], 500);
        }
        curl_close($ch);

        $resultData = json_decode($result, true);

        if (isset($resultData['message']) && $resultData['message'] == 'User already exists! Please sign in') {
            return new WP_REST_Response([
                'message' => 'User already exists! Please use the appropriate email and password',
                'error' => $resultData['message']
            ], 400);
        } elseif (isset($resultData['topshipId'])) {
            // Registration success; store user data in WordPress database if needed
            global $wpdb;
            $wpdb->insert($wpdb->prefix . 'registrations', [
                'email' => $email,
                'password' => self::encrypt($password),
                'phoneNumber' => $phone,
                'fullName' => $first_name . ' ' . $last_name,
                'topshipId' => $resultData['topshipId'],
                'reg_id' => $resultData['id'],
                'country' => $country,
                'state' => $state,
                'city' => $city,
                'address' => $address,
                'zipcode' => $postal_code,
                'country_code'=>$countryCode,
            ]);

            return new WP_REST_Response([
                'status' => 'success',
                'message' => 'Registration successful!',
                'data' => $resultData
            ], 201);
        } else {
            return new WP_REST_Response([
                'message' => 'Registration failed, please try again later.',
                'data' => $resultData
            ], 400);
        }
    }


    public static function handle_orders($request) {
        // Extract parameters from the request
        $per_page = $request->get_param('per_page') ?: 10; // Default to 10 items per page
        $page = $request->get_param('page') ?: 1; // Default to page 1

        // Fetch all bookings
        $bookings = ShipmentBookingsTable::get_bookings();

        // Paginate the results
        $total = count($bookings);
        $offset = ($page - 1) * $per_page;
        $paginated_bookings = array_slice($bookings, $offset, $per_page);

        // Build the response
        return [
            'data' => $paginated_bookings,
            'meta' => [
                'total' => $total,
                'per_page' => $per_page,
                'current_page' => $page,
                'last_page' => ceil($total / $per_page),
            ]
        ];
    }


//    public static function handle_registration($request) {
//        $params = $request->get_json_params();
//
//        // Validate and sanitize inputs
//        $first_name = sanitize_text_field($params['firstName'] ?? '');
//        $last_name = sanitize_text_field($params['lastName'] ?? '');
//        $phone = sanitize_text_field($params['phone'] ?? '');
//        $email = sanitize_email($params['email'] ?? '');
//        $address = sanitize_text_field($params['address'] ?? '');
//        $country = sanitize_text_field($params['country'] ?? '');
//        $state = sanitize_text_field($params['state'] ?? '');
//        $city = sanitize_text_field($params['city'] ?? '');
//        $postal_code = sanitize_text_field($params['postalCode'] ?? '');
//        $password = sanitize_text_field($params['password'] ?? '');
//
//        // Perform registration logic here, e.g., saving data or creating a user
//
//        // Sample response
//        return new WP_REST_Response([
//            'status' => 'success',
//            'message' => 'Registration successful!',
//            'data' => [
//                'firstName' => $first_name,
//                'lastName' => $last_name,
//                'email' => $email,
//            ]
//        ], 200);
//    }
//

    public static function  encrypt($data) {
        $key = 'base64:A/UdIMm3VXkJkswj9HjE3ooImGJ1SvFg8UctHyrDFiY=';
        $cipher = "aes-256-cbc";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($data, $cipher, $key, $options=0, $iv);
        return base64_encode($iv . $ciphertext);
    }

    public static function  decrypt($data) {
        $key = 'base64:A/UdIMm3VXkJkswj9HjE3ooImGJ1SvFg8UctHyrDFiY=';
        $cipher = "aes-256-cbc";
        $data = base64_decode($data);
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = substr($data, 0, $ivlen);
        $ciphertext = substr($data, $ivlen);
        return openssl_decrypt($ciphertext, $cipher, $key, $options=0, $iv);
    }

    public static function get_countries() {
        // Initialize a cURL session
        $ch = curl_init();

        // Set the URL for the API request
        $url = 'https://api-topship.com/api/get-countries';

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return the response as a string
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout if the request takes too long
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects if any
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Execute the cURL request
        $response = curl_exec($ch);
        die($response);
        // Check for cURL errors
        if ($response === false) {
            // Log the error
            error_log('Error fetching countries: ' . curl_error($ch));

            // Close the cURL session
            curl_close($ch);

            // Return an empty array if an error occurs
            return [];
        }

        // Close the cURL session
        curl_close($ch);

        // Decode the JSON response
        $countries = json_decode($response, true);

        // Check if the response contains countries data
        if (empty($countries)) {
            // Return an empty array if no countries are found
            return [];
        }

        // Return the countries data
        return $countries;
    }


    public static function get_states($request) {
        // Get the countryCode from the request parameters
        $countryCode = $request->get_param('countryCode');

        // Initialize a cURL session
        $ch = curl_init();

        // Set the URL for the API request
        $url = 'https://api-topship.com/api/get-states?countryCode=' . urlencode($countryCode);

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return the response as a string
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout if the request takes too long
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects if any
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for cURL errors
        if ($response === false) {
            // Log the error
            error_log('Error fetching states: ' . curl_error($ch));

            // Close the cURL session
            curl_close($ch);

            // Return an empty array if an error occurs
            return [];
        }

        // Close the cURL session
        curl_close($ch);

        // Decode the JSON response
        $states = json_decode($response, true);

        // Check if the response contains states data
        if (empty($states)) {
            // Return an empty array if no states are found
            return [];
        }

        // Return the states data
        return $states;
    }


    public static function get_cities($request) {
        // Initialize a cURL session
        $countryCode = $request->get_param('countryCode');
        $ch = curl_init();

        // Set the URL for the API request with the countryCode parameter
        $url = 'https://api-topship.com/api/get-cities?countryCode=' . urlencode($countryCode);

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return the response as a string
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout if the request takes too long
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects if any
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for cURL errors
        if ($response === false) {
            // Log the error
            error_log('Error fetching cities: ' . curl_error($ch));

            // Close the cURL session
            curl_close($ch);

            // Return an empty array if an error occurs
            return [];
        }

        // Close the cURL session
        curl_close($ch);

        // Decode the JSON response
        $cities = json_decode($response, true);

        // Check if the response contains cities data
        if (empty($cities)) {
            // Return an empty array if no cities are found
            return [];
        }

        // Return the cities data
        return $cities;
    }



    public static function fetch_shipping_rate($address, $package_details) {
        // $api_url = 'https://api.topship.africa/shipping-rate'; // Replace with the actual API endpoint
        // $api_key = 'your-api-key'; // Replace with the actual API key
    
        // $response = wp_remote_post($api_url, [
        //     'headers' => [
        //         'Authorization' => "Bearer $api_key",
        //         'Content-Type' => 'application/json',
        //     ],
        //     'body' => json_encode([
        //         'address' => $address,
        //         'package_details' => $package_details,
        //     ]),
        // ]);
    
        // if (is_wp_error($response)) {
        //     return false; // Handle error appropriately
        // }
    
        // $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
       // return $response_body['shipping_rate'] ?? false;
       return 500;
    }
    



}
