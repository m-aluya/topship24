<?php
function enqueue_topship_scripts_rates() {
    echo time();
    // Enqueue jQuery first
    wp_enqueue_jquery();
    
    // Enqueue your custom script
    wp_enqueue_script(
        'topship-shipping', 
        get_template_directory_uri() . '/js/topship-dshipping.js', 
        array('jquery'), 
        '1.0.0', 
        true
    );

    // Localize the script with new data
    wp_localize_script(
        'topship-shipping',
        'ajax_object',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('shipping_rate_nonce')
        )
    );
}
add_action('wp_enqueue_scripts', 'enqueue_topship_scripts_rates');





// Handle the AJAX request
function handle_fetch_shipping_rate() {
    // Verify nonce for security (add this to your AJAX data if needed)
    // if (!wp_verify_nonce($_POST['nonce'], 'shipping_rate_nonce')) {
    //     wp_send_json_error(array('message' => 'Security check failed'));
    // }

    // Sanitize input data
    $state = sanitize_text_field($_POST['state']);
    $city = sanitize_text_field($_POST['city']);
    $address = sanitize_text_field($_POST['address']);
    $country = sanitize_text_field($_POST['country']);
    $postcode = sanitize_text_field($_POST['postcode']);

    // Validate required fields
    if (empty($state) || empty($city) || empty($address) || empty($country) || empty($postcode)) {
        wp_send_json_error(array(
            'message' => 'All fields are required'
        ));
    }

    try {
        // Your shipping rate calculation logic here
        // This is an example - replace with your actual shipping rate calculation
        $shipping_rate = calculate_shipping_rate($state, $city, $address, $country, $postcode);

        wp_send_json_success(array(
            'rate' => $shipping_rate,
            'message' => 'Shipping rate calculated successfully'
        ));

    } catch (Exception $e) {
        wp_send_json_error(array(
            'message' => $e->getMessage()
        ));
    }

    wp_die(); // Required to terminate AJAX request properly
}

// Example shipping rate calculation function
function calculate_shipping_rate($state, $city, $address, $country, $postcode) {
    // Replace this with your actual shipping rate calculation logic
    // This is just a dummy example
    $base_rate = 10.00;
    
    // Add your custom logic here based on location
    // For example:
    if ($country === 'US') {
        if (in_array($state, array('CA', 'NY', 'FL'))) {
            $base_rate += 5.00;
        }
    }

    return number_format($base_rate, 2);
}