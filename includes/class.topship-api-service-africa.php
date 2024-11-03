<?php

class Topship_API_Service_Africa {

    public static function init() {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes() {
        register_rest_route('topship/v1', '/register', [
            'methods' => 'POST',
            'callback' => [self::class, 'handle_registration'],
            'permission_callback' => '__return_true', // Adjust permissions as needed
        ]);
    }

    public static function handle_registration($request) {
        $params = $request->get_json_params();

        // Validate and sanitize inputs
        $first_name = sanitize_text_field($params['firstName'] ?? '');
        $last_name = sanitize_text_field($params['lastName'] ?? '');
        $phone = sanitize_text_field($params['phone'] ?? '');
        $email = sanitize_email($params['email'] ?? '');
        $address = sanitize_text_field($params['address'] ?? '');
        $country = sanitize_text_field($params['country'] ?? '');
        $state = sanitize_text_field($params['state'] ?? '');
        $city = sanitize_text_field($params['city'] ?? '');
        $postal_code = sanitize_text_field($params['postalCode'] ?? '');
        $password = sanitize_text_field($params['password'] ?? '');

        // Perform registration logic here, e.g., saving data or creating a user

        // Sample response
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Registration successful!',
            'data' => [
                'firstName' => $first_name,
                'lastName' => $last_name,
                'email' => $email,
            ]
        ], 200);
    }
}
