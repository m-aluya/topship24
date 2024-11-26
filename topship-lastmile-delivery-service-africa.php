<?php
/**
 * Plugin name: Topship
 * Description: Send and Receive items from your doorstep to any location in the world.
 * Author : Topship
 * Author URI: https://topship.africa
 * Plugin URI: https://topship.africa
 * Version: 1.0.10
 */


if(!defined("ABSPATH")){
    exit;
}
require_once plugin_dir_path(__FILE__) . 'includes/class.topship-delivery-service-africa.php';
require_once plugin_dir_path(__FILE__) . 'includes/class.topship-api-service-africa.php';
require_once plugin_dir_path(__FILE__) . 'includes/class.topship-db-init-service-africa.php';
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
        add_filter( 'woocommerce_checkout_fields', [$this,'make_postcode_compulsory'] );

        add_action('wp_enqueue_scripts', [$this, 'enqueue_topship_shipping_scripts']);

        add_action('admin_menu', [$this, 'topship_admin_page_plugin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('plugins_loaded', function() {
            Topship_API_Service_Africa::init();
        });

        Topship_Registration_Table::init();
        Topship_Registration_Table::create_table();
        ValueAddedTaxes_Table::init();
        ValueAddedTaxes_Table::create_table();
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

    }

    public function enqueue_admin_scripts($hook) {
        // Load Bootstrap on all plugin admin pages

        if (
            strpos($hook, Class_topship_delivery_service_africa::topshipLink()) !== false ||
            strpos($hook, Class_topship_delivery_service_africa::topshipLink() . '-contact-us') !== false ||
            strpos($hook, Class_topship_delivery_service_africa::topshipLink() . '-guide') !== false
        ) {
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


  

    function make_postcode_compulsory( $fields ) {
        if ( isset( $fields['billing']['billing_postcode'] ) ) {
            $fields['billing']['billing_postcode']['required'] = true; // Make the billing postcode required
        }
    
        if ( isset( $fields['shipping']['shipping_postcode'] ) ) {
            $fields['shipping']['shipping_postcode']['required'] = true; // Make the shipping postcode required
        }
    
        return $fields;
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



    public function topship_contact_us_page_content() {
        Class_topship_delivery_service_africa::topship_contact_us_page();
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

        $stored_value = get_option('my_field_name', '');
        Class_topship_delivery_service_africa::topship_register();
    }


    /**
     * Enqueue necessary scripts.
     */
    public function enqueue_topship_shipping_scripts() {
        wp_enqueue_script('jquery');
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