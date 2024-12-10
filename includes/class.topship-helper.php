<?php
require_once plugin_dir_path(__FILE__) . 'class.topship-db-init-service-africa.php';
class Class_topship_helper{

    //public static $TOPSHIP_BASE_URL = 'https://topship-staging.africa/api';//
    public static $TOPSHIP_BASE_URL = 'https://api-topship.com/api';

    private static function topshipLink(){
        return 'topship-africa-admin-page-01-ba5e0604-954d-4d49-b43e-61ac97f3eb75';
    }

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


    public static function getNameDescription($method){
        if($method['pricingTier']=='Express'){
            $method['mode']='Express';
        }elseif
        ($method['pricingTier']=='SaverPriority'){
            $method['mode']='Saver Priority';
        }
        elseif ($method['pricingTier']=='FedEx'){
            $method['mode']='Saver';
        }elseif
        ($method['pricingTier']=='LastMileBudget'){
            $method['mode']='Budget';
        }
        return $method;
    }

    /**
     * Get the Topship Base URL
     *
     * @return string
     */
    public static function get_base_url() {
        return self::$TOPSHIP_BASE_URL;
    }

    /**
     * Fetch shipment rates from Topship API
     *
     * @param string $origin - The origin address or postal code.
     * @param string $destination - The destination address or postal code.
     * @param float $weight - The weight of the shipment in kilograms.
     * @param array $options - Additional options like dimensions or package type.
     * @return array|WP_Error
     */
    public static function getShipmentRate($payload) {
        $endpoint = 'get-shipment-rate';
        /*$data = array_merge(
            [
                'origin' => $origin,
                'destination' => $destination,
                'weight' => $weight,
            ],
            $options
        );*/

        return self::api_request($endpoint, $payload, 'GET');
    }

    /**
     * Make an API request to the Topship service
     *
     * @param string $endpoint - The API endpoint.
     * @param array $data - Data to be sent in the request.
     * @param string $method - HTTP method (GET, POST, etc.).
     * @return array|WP_Error
     */
    public static function api_request($endpoint, $data = [], $method = 'GET') {
        // Ensure the endpoint is correctly concatenated with the base URL
        $url = self::$TOPSHIP_BASE_URL . '/'.ltrim($endpoint, '/');

        $args = [
            'method' => strtoupper($method), // Ensure method is in uppercase
            'timeout' => 45,
            'headers' => [
                'Content-Type' => 'application/json',
                // Uncomment and provide authorization if needed
                // 'Authorization' => 'Bearer ' . self::get_api_token(),
            ],
        ];

        if ($method === 'POST') {
            // Include data as the body for POST requests
            $args['body'] = wp_json_encode($data);
        } elseif ($method === 'GET' && !empty($data)) {
            $queryParams = http_build_query(['shipmentDetail' => json_encode($data)]);
            // Append data as query parameters for GET requests
            $url .= '?' . $queryParams;
        }

        // Use WordPress HTTP API for the request
        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            // Handle WordPress HTTP API errors
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    public static function value_Added_Tax_Charge($price){
        return (7.5/100.0)*$price;
    }

    /**
     * Login to the Topship API
     *
     * @param string $shop The shop name.
     * @return string|null Access token or null if login fails.
     */
    public static function login() {
        global $wpdb;

        // Build the login URL
        $url = self::$TOPSHIP_BASE_URL . '/login';


        Topship_Registration_Table::init();
        $registration= Topship_Registration_Table::get_user_record();


       /* if (!$registration) {
            return null; // Registration not found
        }

        // Check for an existing valid token
        $access_token_table = $wpdb->prefix . 'access_tokens';
        $access_token = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $access_token_table 
                WHERE user_id = %d AND expires_at > NOW() LIMIT 1",
                $user->ID
            )
        );*/

      /*  if ($access_token) {
            // Return valid token
            return $access_token->token;
        }*/
        // Decrypt the password (if encrypted)
        $decrypted_password = self::decrypt($registration['password']);

        // Prepare the payload for the login request
        $payload = [
            'loginInput' => [
                'email' => $registration['email'],
                'password' => $decrypted_password,
            ],
        ];

        try {
            // Make the API request using wp_remote_post
            $response = wp_remote_post($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => wp_json_encode($payload),
                'timeout' => 45,
            ]);
            error_log(json_encode($response));

            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }

            $response_body = wp_remote_retrieve_body($response);
            $res = json_decode($response_body, true);

            if (isset($res['accessToken'])) {
                // Save the token in the access tokens table
                /*$wpdb->replace(
                    $access_token_table,
                    [
                        'user_id' => $user->ID,
                        'token' => $res['accessToken'],
                        'expires_at' => gmdate('Y-m-d H:i:s', strtotime('+10 minutes')),
                    ],
                    ['%d', '%s', '%s']
                );*/

                return $res['accessToken'];
            }

            return null; // Login failed
        } catch (Exception $e) {
            error_log('Topship login error: ' . $e->getMessage());
            return null;
        }
    }

    public static function loginWithDetails($email,$password) {
        global $wpdb;

        // Build the login URL
        $url = self::$TOPSHIP_BASE_URL . '/login';

        // Prepare the payload for the login request
        $payload = [
            'loginInput' => [
                'email' =>$email,
                'password' =>$password,
            ],
        ];

        try {
            // Make the API request using wp_remote_post
            $response = wp_remote_post($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => wp_json_encode($payload),
                'timeout' => 45,
            ]);


            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }

            $response_body = wp_remote_retrieve_body($response);
            error_log('Topship login : ' .$response_body);
            $res = json_decode($response_body, true);

            if (isset($res['accessToken'])) {
                return $res['accessToken'];
            }

            return null; // Login failed
        } catch (Exception $e) {
            error_log('Topship login error: ' . $e->getMessage());
            return null;
        }
    }


    public static function buildPayload($items, $valueAddedMData, $price, $shippingAddress, $customer, $reg, $shippingLine, $valueAddedTax)
    {
        return [
            "shipment" => [
                [
                    "items" => $items,
                    "itemCollectionMode" => "DropOff",
                    "pricingTier" => isset($valueAddedMData->pricingTier) ? $valueAddedMData->pricingTier : '',
                    "insuranceType" => "None",
                    "insuranceCharge" => 0,
                    "discount" => 0,
                    "shipmentRoute" => "Domestic",
                    "shipmentCharge" =>(int)$price,
                    "pickupCharge" => 0,
                    "deliveryLocation" => isset($shippingAddress['address1']) ? $shippingAddress['address1'] : '',
                    "pickupId" => isset($shippingLine['id']) ? (string)$shippingLine['id'] : '',
                    "pickupPartner" => "Standard",
                    "valueAddedTaxCharge" =>(int) $valueAddedTax,
                    "channel" => "WooCommerce",
                    "receiverDetail" => [
                        "name" => isset($shippingAddress['name']) ? $shippingAddress['name'] : '',
                        "email" => isset($customer['email']) ? $customer['email'] : '',
                        "phoneNumber" => isset($shippingAddress['phone']) ? $shippingAddress['phone'] : '',
                        "addressLine1" => isset($shippingAddress['address1']) ? $shippingAddress['address1'] : '',
                        "addressLine2" => isset($shippingAddress['address2']) ? $shippingAddress['address2'] : '',
                        "addressLine3" => '',
                        "country" => 'United States',//isset($shippingAddress['country']) ? $shippingAddress['country'] : '',
                        "state" =>'California',// isset($shippingAddress['state']) ? $shippingAddress['state'] : '',
                        "city" => isset($shippingAddress['city']) ? $shippingAddress['city'] : '',
                        "countryCode" => isset($shippingAddress['country']) ? $shippingAddress['country'] : '',
                        "postalCode" => isset($shippingAddress['zip']) ? $shippingAddress['zip'] : ''
                    ],
                    "senderDetail" => [
                        "name" => isset($reg['fullName']) ? $reg['fullName'] : '',
                        "email" => isset($reg['email']) ? $reg['email'] : '',
                        "phoneNumber" => isset($reg['phoneNumber']) ? $reg['phoneNumber'] : '',
                        "addressLine1" => isset($reg['address']) ? $reg['address'] : '',
                        "country" => isset($reg['country']) ? $reg['country'] : '',
                        "state" => isset($reg['state']) ? $reg['state'] : '',
                        "city" => isset($reg['city']) ? $reg['city'] : '',
                        "countryCode" => isset($reg['country_code']) ? $reg['country_code'] : '',
                        "postalCode" => isset($reg['zipcode']) ? $reg['zipcode'] : '',
                    ]
                ]
            ]
        ];
    }




}