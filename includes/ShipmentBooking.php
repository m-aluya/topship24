<?php

class ShipmentBooking
{
    public static function build_items_array($line_items, $currency) {
        $items = [];

        foreach ($line_items as $line_item) {
            $items[] = [
                "category" => "Appliance",
                "description" => isset($line_item['title']) ? $line_item['title'] : '',
                "weight" => isset($line_item['grams']) && (float)$line_item['grams'] > 0 ? (float)$line_item['grams'] / get_option('gram_to_kilo', 1000) : 1,
                "quantity" => isset($line_item['quantity']) ? (float)$line_item['quantity'] : 1,
                "value" => self::convert($currency, 'NGN', isset($line_item['price']) ? (float)$line_item['price'] : 0) * 100
            ];
        }

        return $items;
    }

    public static function convert($from, $to, $amount) {
        // If currencies are the same, return the original amount.
        if ($from === $to) {
            return $amount;
        }

        $url = 'https://topship-staging.africa/rate-converter';

        try {
            // Build query parameters.
            $query_params = [
                'from' => $from,
                'to' => $to,
                'amount' => $amount,
            ];

            // Send GET request using WordPress HTTP API.
            $response = wp_remote_get(add_query_arg($query_params, $url));

            // Check for errors in the response.
            if (is_wp_error($response)) {
                error_log('Currency conversion request error: ' . $response->get_error_message());
                return 0;
            }

            // Decode the response body.
            $response_body = wp_remote_retrieve_body($response);
            $data = json_decode($response_body, true);

            // Validate response structure and log errors if necessary.
            if (isset($data['finalAmount'])) {
                return $data['finalAmount'];
            } else {
                error_log('Currency conversion response missing finalAmount: ' . $response_body);
                return 0;
            }
        } catch (Exception $e) {
            // Log exception message if an error occurs.
            error_log('Error during currency conversion: ' . $e->getMessage());
            return 0;
        }
    }


}