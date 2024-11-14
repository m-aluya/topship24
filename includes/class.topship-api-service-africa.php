<?php

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
    }
    public static function handle_registration($request) {
        $params = $request->get_json_params();

        // Validate and sanitize inputs
        $first_name = sanitize_text_field($params['firstName'] ?? '');
        $last_name = sanitize_text_field($params['lastName'] ?? '');
        $phone = sanitize_text_field($params['phoneNumber'] ?? '');
        $email = sanitize_email($params['email'] ?? '');
        $address = sanitize_text_field($params['address'] ?? '');
        $country = sanitize_text_field($params['country'] ?? '');
        $state = sanitize_text_field($params['state'] ?? '');
        $city = sanitize_text_field($params['city'] ?? '');
        $postal_code = sanitize_text_field($params['postalCode'] ?? '');
        $password = sanitize_text_field($params['password'] ?? '');
        $recaptchaToken=sanitize_text_field($params['recaptchaToken'] ?? '');

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


}
