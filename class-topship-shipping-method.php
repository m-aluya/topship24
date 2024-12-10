<?php

require_once plugin_dir_path(__FILE__) . 'includes/shipping_rate.php';
require_once plugin_dir_path(__FILE__) . 'includes/class.topship-db-init-service-africa.php';

if (!class_exists('Topship_Shipping_Method')) {
    class Topship_Shipping_Method extends WC_Shipping_Method {
        public function __construct() {
            $this->id                 = 'topship_last_mile';
            $this->method_title       = __('Topship Last Mile Delivery', 'woocommerce');
            $this->method_description = __('Delivery service across Africa using Topship.', 'woocommerce');

            $this->enabled  = 'yes'; // Enable the method by default
            $this->title    = __('Topship Delivery', 'woocommerce'); // Title shown to users

            $this->init();
        }

        public function init() {
            require_once plugin_dir_path(__FILE__) . 'includes/class.topship-db-init-service-africa.php';
            Topship_Registration_Table::init();
            // Load the settings
            $this->init_form_fields();

            $this->init_settings();

            $this->enabled = $this->get_option('enabled');

            $this->title   = $this->get_option('title');

            //add_filter('woocommerce_checkout_fields', [self::class, 'ensure_postcode_checkout_field']);

            error_log('init is running');
            //Save settings
            //add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
            add_filter('woocommerce_checkout_fields', [$this,'ensure_postcode_checkout_field']);


            $fields = apply_filters('woocommerce_checkout_fields', WC()->checkout->get_checkout_fields());

            //var_dump($fields);
            //Modify the postal code directly
            //$fields['billing']['billing_postcode']['required'] = true;

            //Now, update the checkout fields (if needed)
            //WC()->checkout->set_checkout_fields($fields);


        }

       public  function ensure_postcode_checkout_field($fields) {
            // Add billing postcode if not present
            error_log('ensure_postcode_checkout_field is running');

            if (!isset($fields['billing']['billing_postcode'])) {
                $fields['billing']['billing_postcode'] = array(
                    'label'        => __('Postcode / ZIP', 'woocommerce'),
                    'placeholder'  => __('Postcode / ZIP', 'woocommerce'),
                    'required'     => true,
                    'class'        => array('form-row-wide'),
                    'clear'        => true,
                    'type'         => 'text',
                    'validate'     => array('postcode'),
                    'priority'     => 65
                );
            }

            // Add shipping postcode if not present
            if (!isset($fields['shipping']['shipping_postcode'])) {
                $fields['shipping']['shipping_postcode'] = array(
                    'label'        => __('Postcode / ZIP', 'woocommerce'),
                    'placeholder'  => __('Postcode / ZIP', 'woocommerce'),
                    'required'     => false,
                    'class'        => array('form-row-wide'),
                    'clear'        => true,
                    'type'         => 'text',
                    'validate'     => array('postcode'),
                    'priority'     => 65
                );
            }

            // Log fields for debugging
           // error_log('Modified checkout fields: ' . print_r($fields, true));

            return $fields;
        }


        public function init_form_fields() {
            $this->form_fields = [
                'enabled' => [
                    'title'       => __('Enable/Disable', 'woocommerce'),
                    'type'        => 'checkbox',
                    'label'       => __('Enable this shipping method', 'woocommerce'),
                    'default'     => 'yes',
                ],
                'title' => [
                    'title'       => __('Method Title', 'woocommerce'),
                    'type'        => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                    'default'     => __('Topship Delivery', 'woocommerce'),
                    'desc_tip'    => true,
                ],
            ];
        }

      public static function getId(){

        }
        public function calculate_shipping($package = []) {

            // Get checkout session ID (example implementation)
            $checkout_session_id = WC()->session->get('order_awaiting_payment'); // WooCommerce session data
            if (!$checkout_session_id) {
                $checkout_session_id = uniqid('checkout_', true); // Generate a unique fallback ID if not available
                WC()->session->set('order_awaiting_payment', $checkout_session_id);
            }

            error_log('Checkout Session ID: ' . $checkout_session_id);

            // Define multiple shipping options
            // Access user information from the package
            $user_country  = isset($package['destination']['country']) ? $package['destination']['country'] : '';
            $user_state    = isset($package['destination']['state']) ? $package['destination']['state'] : '';
            $user_postcode = isset($package['destination']['postcode']) ? $package['destination']['postcode'] : '';
            $user_city     = isset($package['destination']['city']) ? $package['destination']['city'] : '';

            // Log or use the user information
            error_log("User Country: $user_country");
            error_log("User State: $user_state");
            error_log("User Postcode: $user_postcode");
            error_log("User City: $user_city");

            error_log("package: ". json_encode($package));

            $reg=  Topship_Registration_Table::get_user_record();

            error_log("reg: ". json_encode($reg));


            // Calculate total weight of the package
            $total_weight = 0;
            foreach ($package['contents'] as $item_id => $item) {
                $product = $item['data']; // Get the product object
                if ($product) {
                    $item_weight = $product->get_weight()?: 1; // Get the weight of each item in the package
                    $total_weight += $item_weight * $item['quantity']; // Add weight based on quantity
                }
            }

            // Log the total weight for debugging
            //error_log("Total Package Weight: " . $total_weight);

            if(($total_weight/count($package['contents']))>1){
                //$total_weight=$total_weight/1000;
            }
            if($reg==null)return[];
           // $total_weight=100;
            $resq='{
              "shipmentDetail": {
                "senderDetails": {
                  "cityName":' . ' "' . $reg['city'] . '",
                  "countryCode": "' . $reg['country_code'] . '",
                   "postalCode": "'.$reg['zipcode'].'"
                },
                "receiverDetails": {
                  "cityName":"' . $user_city. '",
                  "countryCode": "' . $user_country . '",
                  "postalCode": "'.$user_postcode.'"
                },
                "totalWeight": '.$total_weight.'
              }
            }';

            error_log("data: ". $resq);

            $data = json_decode( $resq);

            $res=  Class_topship_helper::getShipmentRate($data->shipmentDetail);

            error_log("res: ". json_encode( $res));


            if($res){
                //dd($res[0]);
                //$res= json_decode($res);
                $rates = [];
                $_SESSION['topship_shipping_rates'] = [];
                foreach($res as $key => $method){

                    if($method['pricingTier']=='LastMileBudget')
                    {
                        if(strtolower( $reg['country_code'] )=='us'){
                            if($data->shipmentDetail->totalWeight>=10){
                                $newMethod=Class_topship_helper::getNameDescription($method);
                                $newPrice =$method['cost'];
                                $code =$checkout_session_id. $method['pricingTier'];//ValueAddedTaxes_Table::generate_unique_code($method['pricingTier']);
                                //Log::info('currency: '.$currency);
                                $valueAddedTax = ceil(Class_topship_helper::value_Added_Tax_Charge($newPrice));
                                $totalPrice=$newPrice+$valueAddedTax;
                                ValueAddedTaxes_Table::createValueAddedTax($valueAddedTax,2000,'',$method['cost'],'',$method['pricingTier'],'',$code);
                                $rates[]=

                                $rate=[
                                    'id' => $code,
                                    'label' =>__($newMethod['mode'], 'woocommerce') ,// 'Top Ship',
                                    'cost' => $totalPrice/100, // 50.00 in the currency
                                    'description' =>__($newMethod['duration'], 'woocommerce'),
                                ];
                                $rates[]= $rate;
                                // Start the session if not already started
                                if (session_status() === PHP_SESSION_NONE) {
                                    session_start();
                                }

                                // Save the rate details in the session
                                if (!isset($_SESSION['topship_shipping_rates'])) {
                                    $_SESSION['topship_shipping_rates'] = [];
                                }

                                // Add the rate to the session array
                                $_SESSION['topship_shipping_rates'][$code] = $rate;
                            }

                        }else{
                            $newMethod=Class_topship_helper::getNameDescription($method);
                            $newPrice =$method['cost'];
                            $code =$checkout_session_id. $method['pricingTier'];//ValueAddedTaxes_Table::generate_unique_code($method['pricingTier']);
                            //Log::info('currency: '.$currency);
                            $valueAddedTax = ceil(Class_topship_helper::value_Added_Tax_Charge($newPrice));
                            $totalPrice=$newPrice+$valueAddedTax;
                            ValueAddedTaxes_Table::createValueAddedTax($valueAddedTax,2000,'',$method['cost'],'',$method['pricingTier'],'',$code);
                            $rates[]=

                            $rate=[
                                'id' => $code,
                                'label' =>__($newMethod['mode'], 'woocommerce') ,// 'Top Ship',
                                'cost' => $totalPrice/100, // 50.00 in the currency
                                'description' =>__($newMethod['duration'], 'woocommerce'),
                            ];
                            $rates[]= $rate;
                            // Start the session if not already started
                            if (session_status() === PHP_SESSION_NONE) {
                                session_start();
                            }

                            // Save the rate details in the session
                            if (!isset($_SESSION['topship_shipping_rates'])) {
                                $_SESSION['topship_shipping_rates'] = [];
                            }

                            // Add the rate to the session array
                            $_SESSION['topship_shipping_rates'][$code] = $rate;
                        }
                    }

                    elseif ($method['pricingTier']=='Express'){
                        $newMethod=Class_topship_helper::getNameDescription($method);
                        $newPrice =$method['cost'];
                        $code =$checkout_session_id. $method['pricingTier'];
                        //Log::info('currency: '.$currency);
                        $valueAddedTax = ceil(Class_topship_helper::value_Added_Tax_Charge($newPrice));
                        $totalPrice=$newPrice+$valueAddedTax;
                        ValueAddedTaxes_Table::createValueAddedTax($valueAddedTax,2000,'',$method['cost'],'',$method['pricingTier'],'',$code);
                        $rates[]=

                        $rate=[
                            'id' => $code,
                            'label' =>__($newMethod['mode'], 'woocommerce') ,// 'Top Ship',
                            'cost' => $totalPrice/100, // 50.00 in the currency
                            'description' =>__($newMethod['duration'], 'woocommerce'),
                        ];
                        $rates[]= $rate;
                        // Start the session if not already started


                        // Save the rate details in the session
                        //if (!isset($_SESSION['topship_shipping_rates'])) {

                        //}


                        // Add the rate to the session array
                        $_SESSION['topship_shipping_rates'][$code] = $rate;
                    }
                    else{

                      /*  $newMethod=Class_topship_helper::getNameDescription($method);
                        $newPrice =$method['cost'];
                        $code =$checkout_session_id. $method['pricingTier'];
                        //Log::info('currency: '.$currency);
                        $valueAddedTax = ceil(Class_topship_helper::value_Added_Tax_Charge($newPrice));
                        $totalPrice=$newPrice+$valueAddedTax;
                        ValueAddedTaxes_Table::createValueAddedTax($valueAddedTax,2000,'',$method['cost'],'',$method['pricingTier'],'',$code);
                        $rates[]=

                        $rate=[
                            'id' => $code,
                            'label' =>__($newMethod['mode'], 'woocommerce') ,// 'Top Ship',
                            'cost' => $totalPrice/100, // 50.00 in the currency
                            'description' =>__($newMethod['duration'], 'woocommerce'),
                        ];
                        $rates[]= $rate;
                        // Start the session if not already started


                        // Save the rate details in the session
                        //if (!isset($_SESSION['topship_shipping_rates'])) {

                        //}


                        // Add the rate to the session array
                        $_SESSION['topship_shipping_rates'][$code] = $rate;*/

                    }
                }
                foreach ($rates as $option) {
                    $rate = [
                        'id'    => $option['id'],
                        'label' => $option['label'],
                        'cost'  => $option['cost'],
                        // Optionally include meta data
                        'meta_data' => [
                            'description' => $option['description'],
                        ],
                    ];
                    error_log('rate: '.json_encode($rate));
                    $this->add_rate($rate);
                }
                //Log::info('rate',$rates);
                // return response()->json(['rates' => $rates]);
            }
            else {

                $shipping_options = [
                    [
                        'id' =>  '_standard',
                        'label' => __('Topship Standard Delivery', 'woocommerce'),
                        'cost' => 20.00, // Standard delivery cost
                        'description' => __('Delivery within 3-5 business days.', 'woocommerce'), // Optional
                    ],
                    [
                        'id' =>  '_express',
                        'label' => __('Topship Express Delivery', 'woocommerce'),
                        'cost' => 50.00, // Express delivery cost
                        'description' => __('Delivery within 1-2 business days.', 'woocommerce'), // Optional
                    ],
                    [
                        'id' => '_same_day',
                        'label' => __('Topship Same Day Delivery', 'woocommerce'),
                        'cost' => 100.00, // Same-day delivery cost
                        'description' => __('Delivery within the same day.', 'woocommerce'), // Optional
                    ],
                ];

                // Add each shipping option to the checkout
                foreach ($shipping_options as $option) {
                    $rate = [
                        'id' => $option['id'],
                        'label' => $option['label'],
                        'cost' => $option['cost'],
                        // Optionally include meta data
                        'meta_data' => [
                            'description' => $option['description'],
                        ],
                    ];
                    //$this->add_rate($rate);
                }
            }
        }
    }
}
